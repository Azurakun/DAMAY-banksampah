<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WasteCategory;
use App\Models\Classroom;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure Roles exist
        Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'walikelas', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'manajer', 'guard_name' => 'web']);

        // 0. Seed Settings
        Setting::setValue('school_year', '2025/2026');

        // 0.5. Seed Classrooms
        $classNames = [
            '10 RPL 1', '10 RPL 2', '10 RPL 3',
            '11 RPL 1', '11 RPL 2', '11 RPL 3',
            '12 RPL 1', '12 RPL 2', '12 RPL 3',
            'XI TKJ 1'
        ];
        $classrooms = [];
        foreach ($classNames as $name) {
            $classrooms[$name] = Classroom::firstOrCreate(['name' => $name]);
        }

        // 1. Seed Waste Categories
        $categories = [
            [
                'name' => 'Plastik (Botol/Gelas)',
                'key' => 'plastik',
                'price_per_kg' => 3000,
                'points_per_kg' => 30,
                'icon' => '🥤'
            ],
            [
                'name' => 'Kertas & Karton',
                'key' => 'kertas',
                'price_per_kg' => 2000,
                'points_per_kg' => 20,
                'icon' => '📦'
            ],
            [
                'name' => 'Logam & Kaleng',
                'key' => 'logam',
                'price_per_kg' => 6000,
                'points_per_kg' => 60,
                'icon' => '🥫'
            ],
            [
                'name' => 'Sampah Organik',
                'key' => 'organik',
                'price_per_kg' => 1000,
                'points_per_kg' => 10,
                'icon' => '🍂'
            ],
            [
                'name' => 'Kaca & Cermin',
                'key' => 'kaca',
                'price_per_kg' => 1500,
                'points_per_kg' => 15,
                'icon' => '🍾'
            ]
        ];

        $wasteCategories = [];
        foreach ($categories as $cat) {
            $wasteCategories[$cat['key']] = WasteCategory::create($cat);
        }

        // 2. Seed Users
        // Manager
        $manajer = User::create([
            'name' => 'Manajer EcoBank',
            'email' => 'manajer@ecobank.com',
            'password' => Hash::make('password'),
            'role' => 'manajer',
            'status' => 'approved',
            'balance' => 0,
            'points' => 0
        ]);
        $manajer->assignRole('manajer');

        // Operator
        $operator = User::create([
            'name' => 'Agus Hermawan',
            'email' => 'agus@ecobank.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'status' => 'approved',
            'balance' => 0,
            'points' => 0
        ]);
        $operator->assignRole('operator');

        // Wali Kelas (Homeroom Teacher)
        $walikelas = User::create([
            'name' => 'Dra. Sri Wahyuni',
            'email' => 'sri@ecobank.com',
            'password' => Hash::make('password'),
            'role' => 'walikelas',
            'class' => '12 RPL 1',
            'status' => 'approved',
            'balance' => 0,
            'points' => 0
        ]);
        $walikelas->assignRole('walikelas');
        $walikelas->classrooms()->sync([$classrooms['12 RPL 1']->id]);

        // Students (Siswa)
        $budi = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@ecobank.com',
            'password' => Hash::make('password'),
            'nisn' => '12345678',
            'role' => 'siswa',
            'classroom_id' => $classrooms['12 RPL 1']->id,
            'class' => '12 RPL 1',
            'balance' => 0,
            'points' => 0,
            'status' => 'approved'
        ]);
        $budi->assignRole('siswa');

        $siti = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@ecobank.com',
            'password' => Hash::make('password'),
            'nisn' => '87654321',
            'role' => 'siswa',
            'classroom_id' => $classrooms['12 RPL 1']->id,
            'class' => '12 RPL 1',
            'balance' => 0,
            'points' => 0,
            'status' => 'approved'
        ]);
        $siti->assignRole('siswa');

        $rian = User::create([
            'name' => 'Rian Hidayat',
            'email' => 'rian@ecobank.com',
            'password' => Hash::make('password'),
            'nisn' => '11223344',
            'role' => 'siswa',
            'classroom_id' => $classrooms['XI TKJ 1']->id,
            'class' => 'XI TKJ 1',
            'balance' => 0,
            'points' => 0,
            'status' => 'approved'
        ]);
        $rian->assignRole('siswa');
    }
}

