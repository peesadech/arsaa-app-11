<?php

$src = 'D:\\myWork\\arsaa-app\\laravel-vue-app-master';
$dest = 'D:\\myWork\\arsaa-app\\arsaa-app';

function copyFile($srcPath, $destPath, $type) {
    if (!file_exists($srcPath)) return;
    $content = file_get_contents($srcPath);
    
    if ($type === 'model') {
        $content = str_replace('namespace App;', 'namespace App\\Models;', $content);
    } elseif ($type === 'controller') {
        $content = str_replace('namespace App\\Http\\Controllers;', 'namespace App\\Http\\Controllers;', $content);
        // also add use App\Models\... instead of App\...
        $content = preg_replace('/use App\\\\([A-Z][a-zA-Z0-9_]*);/', 'use App\\Models\\\\$1;', $content);
    }
    
    $dir = dirname($destPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    file_put_contents($destPath, $content);
    echo "Copied to $destPath\n";
}

// Copy Models
$models = ['User.php', 'PermissionType.php', 'Setting.php'];
foreach ($models as $m) {
    copyFile("$src/app/$m", "$dest/app/Models/$m", 'model');
}

// Copy API Controllers
$apiControllers = [
    'AuthController.php', 'PermissionController.php', 'PermissionTypeController.php',
    'RoleController.php', 'UserController.php', 'UserRoleController.php'
];
foreach ($apiControllers as $c) {
    copyFile("$src/app/Http/Controllers/Api/$c", "$dest/app/Http/Controllers/Api/$c", 'controller');
}

// Copy Admin Controllers
$adminControllers = [
    'SettingController.php', 'UserController.php'
];
foreach ($adminControllers as $c) {
    copyFile("$src/app/Http/Controllers/Admin/$c", "$dest/app/Http/Controllers/Admin/$c", 'controller');
}

echo "Pre-copy phase models and controllers completed.\n";
