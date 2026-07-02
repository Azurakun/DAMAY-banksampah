<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\WasteCategory;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Spatie Roles if they don't exist
        Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'walikelas', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'manajer', 'guard_name' => 'web']);

        // 2. Create Manager
        $manager = User::create([
            'name'     => 'Haji Mulyono',
            'email'    => 'mulyono@ecobank.com',
            'password' => Hash::make('password'),
            'role'     => 'manajer',
            'balance'  => 0,
            'points'   => 0,
            'status'   => 'approved',
        ]);
        $manager->assignRole('manajer');

        // 3. Create Operator
        $operator = User::create([
            'name'     => 'Agus Hermawan',
            'email'    => 'agus@ecobank.com',
            'password' => Hash::make('password'),
            'role'     => 'operator',
            'balance'  => 0,
            'points'   => 0,
            'status'   => 'approved',
        ]);
        $operator->assignRole('operator');

        // 4. Create Wali Kelas
        $walikelas = User::create([
            'name'     => 'Dra. Sri Wahyuni',
            'email'    => 'sri@ecobank.com',
            'password' => Hash::make('password'),
            'role'     => 'walikelas',
            'class'    => 'XII RPL 1',
            'balance'  => 0,
            'points'   => 0,
            'status'   => 'approved',
        ]);
        $walikelas->assignRole('walikelas');

        // 5. Create Kategori Sampah
        WasteCategory::insert([
            [
                'name' => 'Plastik (Botol/Gelas)',
                'key' => 'plastik',
                'price_per_kg' => 3000,
                'points_per_kg' => 30,
                'icon' => '🥤',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Kertas & Karton',
                'key' => 'kertas',
                'price_per_kg' => 2000,
                'points_per_kg' => 20,
                'icon' => '📦',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Logam & Kaleng',
                'key' => 'logam',
                'price_per_kg' => 6000,
                'points_per_kg' => 60,
                'icon' => '🥫',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
