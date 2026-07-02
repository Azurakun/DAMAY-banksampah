<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WasteCategory;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
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
        // Students
        $budi = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@ecobank.com',
            'password' => Hash::make('password'),
            'nisn' => '12345678',
            'role' => 'siswa',
            'class' => 'XII RPL 1',
            'phone' => '081234567890',
            'balance' => 54000,
            'points' => 540,
            'avatar' => null
        ]);

        $siti = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@ecobank.com',
            'password' => Hash::make('password'),
            'nisn' => '87654321',
            'role' => 'siswa',
            'class' => 'XII RPL 1',
            'phone' => '082134567891',
            'balance' => 78000,
            'points' => 780,
            'avatar' => null
        ]);

        $rian = User::create([
            'name' => 'Rian Hidayat',
            'email' => 'rian@ecobank.com',
            'password' => Hash::make('password'),
            'nisn' => '11223344',
            'role' => 'siswa',
            'class' => 'XI TKJ 1',
            'phone' => '083134567892',
            'balance' => 12000,
            'points' => 120,
            'avatar' => null
        ]);

        // Operator
        $operator = User::create([
            'name' => 'Agus Hermawan',
            'email' => 'agus@ecobank.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'phone' => '085134567893'
        ]);

        // Wali Kelas (Homeroom Teacher)
        $walikelas = User::create([
            'name' => 'Dra. Sri Wahyuni',
            'email' => 'sri@ecobank.com',
            'password' => Hash::make('password'),
            'role' => 'walikelas',
            'class' => 'XII RPL 1'
        ]);

        // Manager
        $manajer = User::create([
            'name' => 'Haji Mulyono',
            'email' => 'mulyono@ecobank.com',
            'password' => Hash::make('password'),
            'role' => 'manajer'
        ]);

        // 3. Seed Sample Transactions
        // Budi's transactions
        Transaction::create([
            'user_id' => $budi->id,
            'operator_id' => $operator->id,
            'type' => 'setor',
            'waste_category_id' => $wasteCategories['plastik']->id,
            'weight' => 10.00,
            'amount' => 30000,
            'points' => 300,
            'status' => 'Berhasil',
            'note' => 'Setoran botol plastik bersih',
            'created_at' => now()->subDays(5)
        ]);

        Transaction::create([
            'user_id' => $budi->id,
            'operator_id' => $operator->id,
            'type' => 'setor',
            'waste_category_id' => $wasteCategories['kertas']->id,
            'weight' => 12.00,
            'amount' => 24000,
            'points' => 240,
            'status' => 'Berhasil',
            'note' => 'Buku-buku bekas tidak terpakai',
            'created_at' => now()->subDays(3)
        ]);

        // Siti's transactions
        Transaction::create([
            'user_id' => $siti->id,
            'operator_id' => $operator->id,
            'type' => 'setor',
            'waste_category_id' => $wasteCategories['logam']->id,
            'weight' => 13.00,
            'amount' => 78000,
            'points' => 780,
            'status' => 'Berhasil',
            'note' => 'Kaleng minuman bekas',
            'created_at' => now()->subDays(2)
        ]);

        // Rian's transactions
        Transaction::create([
            'user_id' => $rian->id,
            'operator_id' => $operator->id,
            'type' => 'setor',
            'waste_category_id' => $wasteCategories['kaca']->id,
            'weight' => 8.00,
            'amount' => 12000,
            'points' => 120,
            'status' => 'Berhasil',
            'note' => 'Botol sirup kaca bekas',
            'created_at' => now()->subDays(1)
        ]);

        // Pending withdrawal request for Budi
        Transaction::create([
            'user_id' => $budi->id,
            'operator_id' => $operator->id,
            'type' => 'tarik',
            'waste_category_id' => null,
            'weight' => null,
            'amount' => 15000,
            'points' => 0,
            'status' => 'Menunggu',
            'note' => 'Pengajuan penarikan dana saku',
            'created_at' => now()->subHours(4)
        ]);
    }
}
