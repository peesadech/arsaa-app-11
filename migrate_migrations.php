<?php

$src = 'D:\\myWork\\arsaa-app\\laravel-vue-app-master';
$dest = 'D:\\myWork\\arsaa-app\\arsaa-app';

function copyFile($srcPath, $destPath) {
    if (!file_exists($srcPath)) return;
    $content = file_get_contents($srcPath);
    
    $dir = dirname($destPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    file_put_contents($destPath, $content);
    echo "Copied to $destPath\n";
}

$migrations = scandir("$src/database/migrations");
foreach ($migrations as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        // Skip default ones that might be conflicting or handle them specifically
        if (strpos($file, 'create_users_table') === false && 
            strpos($file, 'create_password_resets_table') === false &&
            strpos($file, 'create_failed_jobs_table') === false &&
            strpos($file, 'create_cache_table') === false &&
            strpos($file, 'create_jobs_table') === false) {
            copyFile("$src/database/migrations/$file", "$dest/database/migrations/$file");
        }
    }
}

echo "Migrations copied.\n";
