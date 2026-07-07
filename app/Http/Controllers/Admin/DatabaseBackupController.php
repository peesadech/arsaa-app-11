<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    public function __construct(private DatabaseBackupService $service)
    {
    }

    /**
     * Show the backup overview page.
     */
    public function index()
    {
        $info = $this->service->getDatabaseInfo();

        return view('admin.database-backup.index', compact('info'));
    }

    /**
     * Generate a fresh .sql backup and stream it to the browser.
     */
    public function download(): BinaryFileResponse|RedirectResponse
    {
        try {
            $result = $this->service->createBackup();
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.database-backup.index')
                ->with('error', __('Backup failed:') . ' ' . $e->getMessage());
        }

        // Remove expired backups after a successful run (keep the fresh one).
        $this->service->cleanupOldBackups();

        return response()
            ->download($result['path'], $result['filename'], [
                'Content-Type' => 'application/sql',
            ])
            ->deleteFileAfterSend(false);
    }
}
