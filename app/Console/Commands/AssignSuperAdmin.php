<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignSuperAdmin extends Command
{
    protected $signature = 'assign:superadmin {email}';
    protected $description = 'Assign SuperAdmin role to a user';

    public function handle()
    {
        $email = $this->argument('email');

        $role = Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
        $this->line("Role: {$role->name} (id={$role->id})");

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User not found: {$email}");
            return 1;
        }

        $user->syncRoles(['SuperAdmin']);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = $user->fresh()->getRoleNames()->implode(', ');
        $this->info("User: {$user->email} => roles: {$roles}");

        return 0;
    }
}
