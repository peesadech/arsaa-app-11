<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('12345678'),
            ]
        );
        $admin->assignRole('SuperAdmin');

        $this->call([
            LanguageSeeder::class,
            StudentMasterDataSeeder::class,
            AttendanceStatusSeeder::class,
        ]);
    }
}
