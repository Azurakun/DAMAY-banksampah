<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $siswaRole = Role::firstOrCreate(['name' => 'siswa']);
        $operatorRole = Role::firstOrCreate(['name' => 'operator']);
        $walikelasRole = Role::firstOrCreate(['name' => 'walikelas']);
        $manajerRole = Role::firstOrCreate(['name' => 'manajer']);

        // Assign roles to existing users based on their 'role' column
        $users = User::all();
        foreach ($users as $user) {
            if ($user->role === 'siswa' && !$user->hasRole('siswa')) {
                $user->assignRole($siswaRole);
            } elseif ($user->role === 'operator' && !$user->hasRole('operator')) {
                $user->assignRole($operatorRole);
            } elseif ($user->role === 'walikelas' && !$user->hasRole('walikelas')) {
                $user->assignRole($walikelasRole);
            } elseif ($user->role === 'manajer' && !$user->hasRole('manajer')) {
                $user->assignRole($manajerRole);
            }
        }
    }
}
