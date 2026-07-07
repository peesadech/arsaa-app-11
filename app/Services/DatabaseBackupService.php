<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use RuntimeException;

/**
 * Database backup service.
 *
 * Produces a self-contained .sql dump (structure + data) that can be re-imported
 * via phpMyAdmin / DBeaver / mysql CLI / psql CLI.
 *
 * Strategy (Hybrid):
 *   1. Preferred  : native dump binary (mysqldump / pg_dump) — fastest.
 *   2. Fallback   : pure PHP dump via PDO — always works, even on shared hosting
 *                   where shell_exec/proc_open are disabled or the binary is missing.
 *
 * The method is chosen automatically; the caller never has to decide.
 */
class DatabaseBackupService
{
    /** Keep backup files for this many days before auto-cleanup. */
    public const RETENTION_DAYS = 7;

    /** Directory (relative to storage/app) where dumps are written. */
    public const BACKUP_DIR = 'backups';

    /** Rows per multi-row INSERT statement in pure-PHP mode. */
    private const INSERT_CHUNK = 200;

    /**
     * Create a backup file and return metadata about the run.
     *
     * @return array{path:string, filename:string, driver:string, method:string, size:int, duration:float, database:string}
     *
     * @throws RuntimeException when even the pure-PHP fallback fails.
     */
    public function createBackup(): array
    {
        $connection = config('database.default');
        $driver     = config("database.connections.{$connection}.driver");
        $database   = config("database.connections.{$connection}.database");

        $dir = storage_path('app/' . self::BACKUP_DIR);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $filename = 'backup_database_' . now()->format('Ymd_His') . '.sql';
        $path     = $dir . DIRECTORY_SEPARATOR . $filename;

        $detection = $this->detectBackupMethod();
        $method    = $detection['method'];
        $start     = microtime(true);

        try {
            if ($method === 'mysqldump' || $method === 'pg_dump') {
                $ok = $this->backupUsingCommand($method, $path);
                if (! $ok) {
                    // Native binary failed at runtime — fall back to pure PHP.
                    Log::warning('Database backup: native command failed, falling back to pure PHP.', [
                        'method' => $method,
                    ]);
                    $method = 'php';
                    $this->backupUsingPurePhp($path);
                }
            } else {
                $this->backupUsingPurePhp($path);
                $method = 'php';
            }
        } catch (\Throwable $e) {
            // Absolute last resort: if a native command threw, try pure PHP once.
            if ($method !== 'php') {
                Log::warning('Database backup: command path threw, retrying with pure PHP.', [
                    'error' => $e->getMessage(),
                ]);
                try {
                    $method = 'php';
                    $this->backupUsingPurePhp($path);
                } catch (\Throwable $inner) {
                    $this->logResult(false, $driver, $method, $database, 0, 0, $inner->getMessage());
                    throw new RuntimeException('Database backup failed: ' . $inner->getMessage(), 0, $inner);
                }
            } else {
                $this->logResult(false, $driver, $method, $database, 0, 0, $e->getMessage());
                throw new RuntimeException('Database backup failed: ' . $e->getMessage(), 0, $e);
            }
        }

        $duration = round(microtime(true) - $start, 3);
        $size     = is_file($path) ? (int) filesize($path) : 0;

        if ($size === 0) {
            $this->logResult(false, $driver, $method, $database, 0, $duration, 'Empty backup file produced.');
            throw new RuntimeException('Database backup produced an empty file.');
        }

        $this->logResult(true, $driver, $method, $database, $size, $duration, null);

        return [
            'path'     => $path,
            'filename' => $filename,
            'driver'   => $driver,
            'method'   => $method,
            'size'     => $size,
            'duration' => $duration,
            'database' => $database,
        ];
    }

    /**
     * Decide which backup method to use, based on driver + environment capabilities.
     *
     * @return array{method:string, reason:string, binary_available:bool, shell_available:bool}
     */
    public function detectBackupMethod(): array
    {
        $connection = config('database.default');
        $driver     = config("database.connections.{$connection}.driver");

        $shellAvailable = $this->shellFunctionsAvailable();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $binary = $this->findBinary('mysqldump');
            if ($shellAvailable && $binary) {
                return [
                    'method'           => 'mysqldump',
                    'reason'           => 'mysqldump binary detected',
                    'binary_available' => true,
                    'shell_available'  => true,
                ];
            }
        } elseif ($driver === 'pgsql') {
            $binary = $this->findBinary('pg_dump');
            if ($shellAvailable && $binary) {
                return [
                    'method'           => 'pg_dump',
                    'reason'           => 'pg_dump binary detected',
                    'binary_available' => true,
                    'shell_available'  => true,
                ];
            }
        }

        return [
            'method'           => 'php',
            'reason'           => $shellAvailable
                ? 'dump binary not found — using pure PHP'
                : 'shell functions disabled — using pure PHP',
            'binary_available' => false,
            'shell_available'  => $shellAvailable,
        ];
    }

    // ---------------------------------------------------------------------
    // Native command backup
    // ---------------------------------------------------------------------

    /**
     * Backup using mysqldump / pg_dump. Returns true on success, false on failure
     * (so the caller can fall back to pure PHP).
     */
    public function backupUsingCommand(string $method, string $path): bool
    {
        $connection = config('database.default');
        $cfg        = config("database.connections.{$connection}");

        if ($method === 'mysqldump') {
            return $this->runMysqldump($cfg, $path);
        }

        if ($method === 'pg_dump') {
            return $this->runPgDump($cfg, $path);
        }

        return false;
    }

    private function runMysqldump(array $cfg, string $path): bool
    {
        $binary = $this->findBinary('mysqldump') ?: 'mysqldump';

        // Pass credentials via a temporary defaults file so the password never
        // appears in the process list / command line.
        $cnf = tempnam(sys_get_temp_dir(), 'myb');
        file_put_contents($cnf, sprintf(
            "[client]\nuser=%s\npassword=%s\nhost=%s\nport=%s\n",
            $cfg['username'] ?? 'root',
            $cfg['password'] ?? '',
            $cfg['host'] ?? '127.0.0.1',
            $cfg['port'] ?? 3306
        ));

        $args = [
            $binary,
            '--defaults-extra-file=' . $cnf,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            '--add-drop-table',
            '--default-character-set=utf8mb4',
            $cfg['database'],
        ];

        $ok = $this->runProcess($args, $path, []);
        @unlink($cnf);

        return $ok && is_file($path) && filesize($path) > 0;
    }

    private function runPgDump(array $cfg, string $path): bool
    {
        $binary = $this->findBinary('pg_dump') ?: 'pg_dump';

        $args = [
            $binary,
            '--host=' . ($cfg['host'] ?? '127.0.0.1'),
            '--port=' . ($cfg['port'] ?? 5432),
            '--username=' . ($cfg['username'] ?? 'postgres'),
            '--no-owner',
            '--no-privileges',
            '--clean',
            '--if-exists',
            '--encoding=UTF8',
            $cfg['database'],
        ];

        // pg_dump reads the password from PGPASSWORD.
        $env = ['PGPASSWORD' => (string) ($cfg['password'] ?? '')];

        $ok = $this->runProcess($args, $path, $env);

        return $ok && is_file($path) && filesize($path) > 0;
    }

    /**
     * Run an external process, streaming stdout to $outFile.
     * Returns true if the process exited 0.
     */
    private function runProcess(array $args, string $outFile, array $extraEnv): bool
    {
        if (! function_exists('proc_open')) {
            return false;
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['file', $outFile, 'w'],
            2 => ['pipe', 'w'],
        ];

        // Merge current env with extra vars (null => inherit full env on some SAPIs).
        $env = array_merge($this->currentEnv(), $extraEnv);

        $process = @proc_open($args, $descriptors, $pipes, null, $env);
        if (! is_resource($process)) {
            return false;
        }

        fclose($pipes[0]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            Log::warning('Database backup: external dump command returned non-zero exit code.', [
                'exit_code' => $exitCode,
                'stderr'    => mb_substr((string) $stderr, 0, 2000),
            ]);
            return false;
        }

        return true;
    }

    // ---------------------------------------------------------------------
    // Pure PHP backup
    // ---------------------------------------------------------------------

    /**
     * Backup entirely in PHP via PDO. Streams data row-by-row so memory usage
     * stays flat even for hundreds of thousands of records.
     */
    public function backupUsingPurePhp(string $path): void
    {
        $connection = config('database.default');
        $driver     = config("database.connections.{$connection}.driver");

        $handle = fopen($path, 'w');
        if ($handle === false) {
            throw new RuntimeException("Cannot open backup file for writing: {$path}");
        }

        try {
            if ($driver === 'pgsql') {
                $this->dumpPostgresPurePhp($handle);
            } else {
                $this->dumpMysqlPurePhp($handle);
            }
        } finally {
            fclose($handle);
        }
    }

    // ---------------------- MySQL / MariaDB pure PHP ----------------------

    private function dumpMysqlPurePhp($handle): void
    {
        $connection = config('database.default');
        $database   = config("database.connections.{$connection}.database");
        $pdo        = DB::connection()->getPdo();

        $this->w($handle, "-- ------------------------------------------------------\n");
        $this->w($handle, "-- Database backup (pure PHP)\n");
        $this->w($handle, "-- Database: {$database}\n");
        $this->w($handle, "-- Generated: " . now()->toDateTimeString() . "\n");
        $this->w($handle, "-- ------------------------------------------------------\n\n");

        $this->w($handle, "SET NAMES utf8mb4;\n");
        $this->w($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        $this->w($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
        $this->w($handle, "SET time_zone='+00:00';\n\n");

        // Separate base tables from views (views must be created last).
        $tables = [];
        $views  = [];
        foreach (DB::select('SHOW FULL TABLES') as $row) {
            $vals = array_values((array) $row);
            $name = $vals[0];
            $type = $vals[1] ?? 'BASE TABLE';
            if (strtoupper($type) === 'VIEW') {
                $views[] = $name;
            } else {
                $tables[] = $name;
            }
        }

        // Structure + data for base tables.
        foreach ($tables as $table) {
            $this->dumpMysqlTableStructure($handle, $table);
            $this->dumpMysqlTableData($handle, $pdo, $table, $database);
        }

        // Views.
        foreach ($views as $view) {
            $this->dumpMysqlView($handle, $view);
        }

        // Triggers.
        $this->dumpMysqlTriggers($handle);

        $this->w($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
    }

    private function dumpMysqlTableStructure($handle, string $table): void
    {
        $row = DB::select("SHOW CREATE TABLE `{$table}`");
        $create = $row[0]->{'Create Table'} ?? array_values((array) $row[0])[1] ?? null;

        $this->w($handle, "\n-- ----------------------------\n");
        $this->w($handle, "-- Table structure for `{$table}`\n");
        $this->w($handle, "-- ----------------------------\n");
        $this->w($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
        $this->w($handle, $create . ";\n\n");
    }

    private function dumpMysqlTableData($handle, PDO $pdo, string $table, string $database): void
    {
        // Column metadata (name + whether the column is binary) in definition order.
        $binaryTypes = ['blob', 'tinyblob', 'mediumblob', 'longblob', 'binary', 'varbinary', 'bit'];
        $cols        = [];
        foreach (DB::select(
            'SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS '
            . 'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
            [$database, $table]
        ) as $c) {
            $cols[$c->COLUMN_NAME] = in_array(strtolower($c->DATA_TYPE), $binaryTypes, true);
        }

        if (empty($cols)) {
            return;
        }

        $colNames = array_keys($cols);
        $colList  = '`' . implode('`, `', $colNames) . '`';

        // Unbuffered streaming read so large tables never hit memory.
        $stmt = $pdo->prepare("SELECT * FROM `{$table}`", [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt->execute();

        $this->w($handle, "-- Data for `{$table}`\n");

        $batch = [];
        $wrote = false;
        while ($rowData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $wrote = true;
            $values = [];
            foreach ($colNames as $col) {
                $values[] = $this->mysqlValue($pdo, $rowData[$col], $cols[$col]);
            }
            $batch[] = '(' . implode(', ', $values) . ')';

            if (count($batch) >= self::INSERT_CHUNK) {
                $this->w($handle, "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $batch) . ";\n");
                $batch = [];
            }
        }
        if (! empty($batch)) {
            $this->w($handle, "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $batch) . ";\n");
        }
        $stmt->closeCursor();
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

        if ($wrote) {
            $this->w($handle, "\n");
        }
    }

    private function mysqlValue(PDO $pdo, $value, bool $isBinary): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if ($isBinary || ! mb_check_encoding((string) $value, 'UTF-8')) {
            return '0x' . bin2hex((string) $value);
        }
        return $pdo->quote((string) $value);
    }

    private function dumpMysqlView($handle, string $view): void
    {
        $row = DB::select("SHOW CREATE VIEW `{$view}`");
        $create = $row[0]->{'Create View'} ?? null;
        if (! $create) {
            return;
        }
        $this->w($handle, "\n-- ----------------------------\n");
        $this->w($handle, "-- View structure for `{$view}`\n");
        $this->w($handle, "-- ----------------------------\n");
        $this->w($handle, "DROP VIEW IF EXISTS `{$view}`;\n");
        $this->w($handle, $create . ";\n\n");
    }

    private function dumpMysqlTriggers($handle): void
    {
        $triggers = DB::select('SHOW TRIGGERS');
        if (empty($triggers)) {
            return;
        }

        $this->w($handle, "\n-- ----------------------------\n");
        $this->w($handle, "-- Triggers\n");
        $this->w($handle, "-- ----------------------------\n");

        foreach ($triggers as $t) {
            $name      = $t->Trigger;
            $timing    = $t->Timing;
            $event     = $t->Event;
            $table     = $t->Table;
            $statement = $t->Statement;

            $this->w($handle, "DROP TRIGGER IF EXISTS `{$name}`;\n");
            $this->w($handle, "DELIMITER ;;\n");
            $this->w($handle, "CREATE TRIGGER `{$name}` {$timing} {$event} ON `{$table}` FOR EACH ROW {$statement};;\n");
            $this->w($handle, "DELIMITER ;\n\n");
        }
    }

    // ------------------------- PostgreSQL pure PHP ------------------------

    /**
     * Best-effort pure-PHP PostgreSQL dump built from information_schema.
     * (Native pg_dump is strongly preferred; this is the shared-hosting fallback.)
     */
    private function dumpPostgresPurePhp($handle): void
    {
        $pdo      = DB::connection()->getPdo();
        $database = config('database.connections.' . config('database.default') . '.database');

        $this->w($handle, "-- ------------------------------------------------------\n");
        $this->w($handle, "-- Database backup (pure PHP)\n");
        $this->w($handle, "-- Database: {$database}\n");
        $this->w($handle, "-- Generated: " . now()->toDateTimeString() . "\n");
        $this->w($handle, "-- ------------------------------------------------------\n\n");

        $this->w($handle, "SET client_encoding = 'UTF8';\n");
        $this->w($handle, "SET standard_conforming_strings = on;\n\n");

        $tables = DB::select(
            "SELECT table_name FROM information_schema.tables "
            . "WHERE table_schema = 'public' AND table_type = 'BASE TABLE' ORDER BY table_name"
        );

        foreach ($tables as $tRow) {
            $table = $tRow->table_name;
            $this->dumpPostgresTableStructure($handle, $table);
            $this->dumpPostgresTableData($handle, $pdo, $table);
        }
    }

    private function dumpPostgresTableStructure($handle, string $table): void
    {
        $columns = DB::select(
            "SELECT column_name, data_type, character_maximum_length, numeric_precision, "
            . "numeric_scale, is_nullable, column_default "
            . "FROM information_schema.columns WHERE table_schema = 'public' AND table_name = ? "
            . "ORDER BY ordinal_position",
            [$table]
        );

        $defs = [];
        foreach ($columns as $col) {
            $type = $col->data_type;
            if ($col->character_maximum_length) {
                $type .= "({$col->character_maximum_length})";
            } elseif ($col->data_type === 'numeric' && $col->numeric_precision) {
                $type .= "({$col->numeric_precision},{$col->numeric_scale})";
            }

            $def = '  "' . $col->column_name . '" ' . $type;
            if ($col->column_default !== null) {
                $def .= ' DEFAULT ' . $col->column_default;
            }
            if ($col->is_nullable === 'NO') {
                $def .= ' NOT NULL';
            }
            $defs[] = $def;
        }

        // Primary key.
        $pk = DB::select(
            "SELECT kcu.column_name FROM information_schema.table_constraints tc "
            . "JOIN information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name "
            . "WHERE tc.table_schema = 'public' AND tc.table_name = ? AND tc.constraint_type = 'PRIMARY KEY' "
            . "ORDER BY kcu.ordinal_position",
            [$table]
        );
        if (! empty($pk)) {
            $pkCols = array_map(fn ($r) => '"' . $r->column_name . '"', $pk);
            $defs[] = '  PRIMARY KEY (' . implode(', ', $pkCols) . ')';
        }

        $this->w($handle, "\n-- ----------------------------\n");
        $this->w($handle, "-- Table structure for \"{$table}\"\n");
        $this->w($handle, "-- ----------------------------\n");
        $this->w($handle, "DROP TABLE IF EXISTS \"{$table}\" CASCADE;\n");
        $this->w($handle, "CREATE TABLE \"{$table}\" (\n" . implode(",\n", $defs) . "\n);\n\n");
    }

    private function dumpPostgresTableData($handle, PDO $pdo, string $table): void
    {
        $colRows  = DB::select(
            "SELECT column_name, data_type FROM information_schema.columns "
            . "WHERE table_schema = 'public' AND table_name = ? ORDER BY ordinal_position",
            [$table]
        );
        if (empty($colRows)) {
            return;
        }

        $colNames = [];
        $binary   = [];
        foreach ($colRows as $c) {
            $colNames[]           = $c->column_name;
            $binary[$c->column_name] = ($c->data_type === 'bytea');
        }
        $colList = '"' . implode('", "', $colNames) . '"';

        $stmt = $pdo->prepare("SELECT * FROM \"{$table}\"", [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->execute();

        $this->w($handle, "-- Data for \"{$table}\"\n");

        $batch = [];
        while ($rowData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $values = [];
            foreach ($colNames as $col) {
                $values[] = $this->pgValue($pdo, $rowData[$col], $binary[$col]);
            }
            $batch[] = '(' . implode(', ', $values) . ')';

            if (count($batch) >= self::INSERT_CHUNK) {
                $this->w($handle, "INSERT INTO \"{$table}\" ({$colList}) VALUES\n" . implode(",\n", $batch) . ";\n");
                $batch = [];
            }
        }
        if (! empty($batch)) {
            $this->w($handle, "INSERT INTO \"{$table}\" ({$colList}) VALUES\n" . implode(",\n", $batch) . ";\n");
        }
        $stmt->closeCursor();
        $this->w($handle, "\n");
    }

    private function pgValue(PDO $pdo, $value, bool $isBinary): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if ($isBinary) {
            return "decode('" . bin2hex((string) $value) . "', 'hex')";
        }
        return $pdo->quote((string) $value);
    }

    // ---------------------------------------------------------------------
    // Cleanup / info / detection helpers
    // ---------------------------------------------------------------------

    /**
     * Delete backup files older than RETENTION_DAYS. Returns count removed.
     */
    public function cleanupOldBackups(?int $days = null): int
    {
        $days    = $days ?? self::RETENTION_DAYS;
        $dir     = storage_path('app/' . self::BACKUP_DIR);
        $removed = 0;

        if (! is_dir($dir)) {
            return 0;
        }

        $cutoff = now()->subDays($days)->getTimestamp();
        foreach (glob($dir . DIRECTORY_SEPARATOR . 'backup_database_*.sql') ?: [] as $file) {
            if (filemtime($file) < $cutoff) {
                @unlink($file);
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * High-level info shown on the backup page.
     *
     * @return array{driver:string, database:string, table_count:int, size_human:?string, method:string, method_reason:string, last_backup_at:?string}
     */
    public function getDatabaseInfo(): array
    {
        $connection = config('database.default');
        $driver     = config("database.connections.{$connection}.driver");
        $database   = config("database.connections.{$connection}.database");

        $detection = $this->detectBackupMethod();

        return [
            'driver'         => $driver,
            'database'       => $database,
            'table_count'    => $this->countTables($driver, $database),
            'size_human'     => $this->humanBytes($this->databaseSize($driver, $database)),
            'method'         => $detection['method'],
            'method_reason'  => $detection['reason'],
            'last_backup_at' => $this->lastBackupAt(),
        ];
    }

    private function countTables(string $driver, string $database): int
    {
        try {
            if ($driver === 'pgsql') {
                $row = DB::selectOne(
                    "SELECT COUNT(*) AS c FROM information_schema.tables "
                    . "WHERE table_schema = 'public' AND table_type = 'BASE TABLE'"
                );
            } else {
                $row = DB::selectOne(
                    'SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = ?',
                    [$database]
                );
            }
            return (int) ($row->c ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function databaseSize(string $driver, string $database): ?int
    {
        try {
            if ($driver === 'pgsql') {
                $row = DB::selectOne('SELECT pg_database_size(current_database()) AS s');
                return (int) ($row->s ?? 0);
            }
            $row = DB::selectOne(
                'SELECT SUM(data_length + index_length) AS s FROM information_schema.tables WHERE table_schema = ?',
                [$database]
            );
            return $row && $row->s !== null ? (int) $row->s : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function lastBackupAt(): ?string
    {
        $dir = storage_path('app/' . self::BACKUP_DIR);
        if (! is_dir($dir)) {
            return null;
        }

        $latest = 0;
        foreach (glob($dir . DIRECTORY_SEPARATOR . 'backup_database_*.sql') ?: [] as $file) {
            $latest = max($latest, filemtime($file));
        }

        return $latest > 0 ? date('Y-m-d H:i:s', $latest) : null;
    }

    private function shellFunctionsAvailable(): bool
    {
        if (! function_exists('proc_open')) {
            return false;
        }
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        return ! in_array('proc_open', $disabled, true);
    }

    /**
     * Locate a dump binary. Returns the resolved path/name if runnable, else null.
     */
    private function findBinary(string $name): ?string
    {
        if (! $this->shellFunctionsAvailable()) {
            return null;
        }

        $candidates = [];

        // Windows: prefer known-good absolute locations (e.g. XAMPP's, which matches
        // the bundled server) before whatever happens to be first on PATH.
        if (stripos(PHP_OS, 'WIN') === 0) {
            // glob() treats backslashes as escapes — use forward slashes on Windows.
            foreach (glob('C:/xampp/mysql/bin/' . $name . '.exe') ?: [] as $g) {
                $candidates[] = $g;
            }
            foreach (glob('C:/Program Files/*/bin/' . $name . '.exe') ?: [] as $g) {
                $candidates[] = $g;
            }
            $candidates[] = $name . '.exe';
        }

        $candidates[] = $name;

        foreach ($candidates as $candidate) {
            if ($this->binaryRunnable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function binaryRunnable(string $binary): bool
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open([$binary, '--version'], $descriptors, $pipes, null, $this->currentEnv());
        if (! is_resource($process)) {
            return false;
        }

        fclose($pipes[0]);
        stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        return proc_close($process) === 0;
    }

    /**
     * Snapshot of current environment variables to pass through to child processes.
     */
    private function currentEnv(): array
    {
        $env = [];
        foreach (['PATH', 'Path', 'SystemRoot', 'TEMP', 'TMP', 'HOME', 'LD_LIBRARY_PATH'] as $key) {
            $val = getenv($key);
            if ($val !== false) {
                $env[$key] = $val;
            }
        }
        return $env;
    }

    private function humanBytes(?int $bytes): ?string
    {
        if ($bytes === null) {
            return null;
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i     = 0;
        $size  = (float) $bytes;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    private function logResult(bool $success, string $driver, string $method, string $database, int $size, float $duration, ?string $error): void
    {
        $user    = auth()->user();
        $context = [
            'user_id'     => $user?->id,
            'user_email'  => $user?->email,
            'driver'      => $driver,
            'method'      => $method,
            'database'    => $database,
            'size_bytes'  => $size,
            'size_human'  => $this->humanBytes($size),
            'duration_s'  => $duration,
            'status'      => $success ? 'success' : 'failed',
        ];
        if ($error !== null) {
            $context['error'] = $error;
        }

        if ($success) {
            Log::info('Database backup completed.', $context);
        } else {
            Log::error('Database backup failed.', $context);
        }
    }

    private function w($handle, string $text): void
    {
        fwrite($handle, $text);
    }
}
