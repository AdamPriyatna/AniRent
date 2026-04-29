<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;     

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        $admin = User::create([
            'name'     => 'Admin AniRent',
            'email'    => 'admin@anirent.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);
        $admin->profile()->create(['nim_nip' => 'ADM001']);

        // Anggota contoh
        $anggota = User::create([
            'name'     => 'Budi Santoso',
            'email'    => 'budi@anirent.com',
            'password' => bcrypt('password'),
            'role'     => 'anggota',
        ]);
        $anggota->profile()->create([
            'nim_nip'     => '2021001',
            'no_telepon'  => '081234567890',
            'alamat'      => 'Jl. Anime No. 1, Jakarta',
        ]);
    }
}
