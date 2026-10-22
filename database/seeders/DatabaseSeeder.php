<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder Akun Users
        User::firstOrCreate(
            ['username' => 'admin_kantin'],
            ['password' => 'admin123', 'role' => 'admin']
        );

        User::firstOrCreate(
            ['username' => 'budi_siswa'],
            ['password' => '123', 'role' => 'siswa']
        );

        // Seeder Menu Awal
        Menu::firstOrCreate(
            ['nama_menu' => 'Nasi Goreng Spesial'],
            ['harga' => 15000, 'kategori' => 'Makanan']
        );

        Menu::firstOrCreate(
            ['nama_menu' => 'Es Teh Manis'],
            ['harga' => 4000, 'kategori' => 'Minuman']
        );
    }
}
