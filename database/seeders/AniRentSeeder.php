<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AniRentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Kategori
        $categories = [
            Category::create(['nama_kategori' => 'Cosplay', 'deskripsi' => 'Beragam kostum karakter anime & game.']),
            Category::create(['nama_kategori' => 'Game & Console', 'deskripsi' => 'Konsol dan permainan video.']),
            Category::create(['nama_kategori' => 'Koleksi (Manga/Novel)', 'deskripsi' => 'Buku, novel ringan, dan komik Jepang.']),
            Category::create(['nama_kategori' => 'Peralatan Kreator', 'deskripsi' => 'Peralatan gambar, lighting, dll.']),
            Category::create(['nama_kategori' => 'Aksesoris & Properti', 'deskripsi' => 'Properti tambahan untuk cosplay.']),
        ];

        // 2. Buat Data Unit (Total 50)
        $unitsData = [
            // --- Cosplay (10 items) ---
            ['prefix' => 'COS', 'nama' => 'Kostum Naruto Uzumaki (Shippuden)', 'kondisi' => 'Sangat Baik', 'harga' => 35000, 'cat' => 0],
            ['prefix' => 'COS', 'nama' => 'Kostum Sasuke Uchiha (Akatsuki)', 'kondisi' => 'Baik', 'harga' => 40000, 'cat' => 0],
            ['prefix' => 'COS', 'nama' => 'Kostum Tanjiro Kamado', 'kondisi' => 'Seperti Baru', 'harga' => 45000, 'cat' => 0],
            ['prefix' => 'COS', 'nama' => 'Kostum Nezuko Kamado', 'kondisi' => 'Sangat Baik', 'harga' => 45000, 'cat' => 0],
            ['prefix' => 'COS', 'nama' => 'Seragam Jujutsu High (Megumi)', 'kondisi' => 'Baik', 'harga' => 35000, 'cat' => 0],
            ['prefix' => 'COS', 'nama' => 'Kostum Gojo Satoru', 'kondisi' => 'Baik', 'harga' => 50000, 'cat' => 0],
            ['prefix' => 'COS', 'nama' => 'Kostum Giyu Tomioka', 'kondisi' => 'Sangat Baik', 'harga' => 45000, 'cat' => 0],
            ['prefix' => 'COS', 'nama' => 'Seragam Anya Forger', 'kondisi' => 'Baru', 'harga' => 30000, 'cat' => 0],
            ['prefix' => 'COS', 'nama' => 'Kostum Yor Forger (Thorn Princess)', 'kondisi' => 'Sangat Baik', 'harga' => 60000, 'cat' => 0],
            ['prefix' => 'COS', 'nama' => 'Kostum Loid Forger (Suit)', 'kondisi' => 'Baik', 'harga' => 55000, 'cat' => 0],

            // --- Game & Console (10 items) ---
            ['prefix' => 'GAM', 'nama' => 'PlayStation 5 Disc Edition', 'kondisi' => 'Sangat Baik', 'harga' => 150000, 'cat' => 1],
            ['prefix' => 'GAM', 'nama' => 'PlayStation 4 Pro', 'kondisi' => 'Baik', 'harga' => 80000, 'cat' => 1],
            ['prefix' => 'GAM', 'nama' => 'Nintendo Switch OLED', 'kondisi' => 'Sangat Baik', 'harga' => 100000, 'cat' => 1],
            ['prefix' => 'GAM', 'nama' => 'Nintendo Switch v2', 'kondisi' => 'Baik', 'harga' => 75000, 'cat' => 1],
            ['prefix' => 'GAM', 'nama' => 'Kaset PS5: Spider-Man 2', 'kondisi' => 'Sangat Baik', 'harga' => 35000, 'cat' => 1],
            ['prefix' => 'GAM', 'nama' => 'Kaset PS5: Final Fantasy XVI', 'kondisi' => 'Sangat Baik', 'harga' => 35000, 'cat' => 1],
            ['prefix' => 'GAM', 'nama' => 'Kaset Switch: Zelda Tears of the Kingdom', 'kondisi' => 'Baik', 'harga' => 30000, 'cat' => 1],
            ['prefix' => 'GAM', 'nama' => 'Kaset Switch: Mario Kart 8 Deluxe', 'kondisi' => 'Sangat Baik', 'harga' => 25000, 'cat' => 1],
            ['prefix' => 'GAM', 'nama' => 'DualSense Wireless Controller (White)', 'kondisi' => 'Baik', 'harga' => 40000, 'cat' => 1],
            ['prefix' => 'GAM', 'nama' => 'DualSense Wireless Controller (Black)', 'kondisi' => 'Baik', 'harga' => 40000, 'cat' => 1],

            // --- Koleksi Manga/Novel (15 items) ---
            ['prefix' => 'KOL', 'nama' => 'Manga One Piece Vol. 1-10', 'kondisi' => 'Baik', 'harga' => 15000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Manga One Piece Vol. 11-20', 'kondisi' => 'Baik', 'harga' => 15000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Manga One Piece Vol. 91-100', 'kondisi' => 'Sangat Baik', 'harga' => 20000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Manga Naruto Vol. 1-10', 'kondisi' => 'Cukup', 'harga' => 12000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Manga Naruto Vol. 61-72 (End)', 'kondisi' => 'Baik', 'harga' => 15000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Manga Bleach Vol. 1-10', 'kondisi' => 'Baik', 'harga' => 12000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Manga Jujutsu Kaisen Vol. 1-5', 'kondisi' => 'Sangat Baik', 'harga' => 10000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Manga Jujutsu Kaisen Vol. 6-10', 'kondisi' => 'Sangat Baik', 'harga' => 10000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Manga Demon Slayer Vol. 1-5', 'kondisi' => 'Baik', 'harga' => 10000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Manga Demon Slayer Vol. 6-10', 'kondisi' => 'Baik', 'harga' => 10000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Light Novel Sword Art Online Vol. 1-3', 'kondisi' => 'Baik', 'harga' => 15000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Light Novel Overlord Vol. 1-2', 'kondisi' => 'Sangat Baik', 'harga' => 12000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Light Novel Re:Zero Vol. 1-3', 'kondisi' => 'Sangat Baik', 'harga' => 15000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Light Novel Mushoku Tensei Vol. 1', 'kondisi' => 'Baik', 'harga' => 8000, 'cat' => 2],
            ['prefix' => 'KOL', 'nama' => 'Manga Attack on Titan Vol. 30-34', 'kondisi' => 'Baik', 'harga' => 10000, 'cat' => 2],

            // --- Peralatan Kreator (10 items) ---
            ['prefix' => 'CRT', 'nama' => 'Wacom Intuos Pro Medium', 'kondisi' => 'Sangat Baik', 'harga' => 85000, 'cat' => 3],
            ['prefix' => 'CRT', 'nama' => 'Wacom Cintiq 16', 'kondisi' => 'Sangat Baik', 'harga' => 180000, 'cat' => 3],
            ['prefix' => 'CRT', 'nama' => 'XP-Pen Deco 01 V2', 'kondisi' => 'Baik', 'harga' => 45000, 'cat' => 3],
            ['prefix' => 'CRT', 'nama' => 'Huion Kamvas 13', 'kondisi' => 'Baik', 'harga' => 110000, 'cat' => 3],
            ['prefix' => 'CRT', 'nama' => 'Kamera Canon EOS M50 Mark II', 'kondisi' => 'Sangat Baik', 'harga' => 150000, 'cat' => 3],
            ['prefix' => 'CRT', 'nama' => 'Lensa Canon EF 50mm f/1.8 STM', 'kondisi' => 'Sangat Baik', 'harga' => 40000, 'cat' => 3],
            ['prefix' => 'CRT', 'nama' => 'Ring Light LED 18 inch + Tripod', 'kondisi' => 'Baik', 'harga' => 35000, 'cat' => 3],
            ['prefix' => 'CRT', 'nama' => 'Microphone Rode VideoMicro', 'kondisi' => 'Baik', 'harga' => 25000, 'cat' => 3],
            ['prefix' => 'CRT', 'nama' => 'Microphone Fifine K669B USB', 'kondisi' => 'Sangat Baik', 'harga' => 20000, 'cat' => 3],
            ['prefix' => 'CRT', 'nama' => 'Softbox Lighting Studio Kit (2 pcs)', 'kondisi' => 'Cukup', 'harga' => 45000, 'cat' => 3],

            // --- Aksesoris & Properti (5 items) ---
            ['prefix' => 'AKS', 'nama' => 'Pedang Katana Tanjiro (Kayu/Props)', 'kondisi' => 'Baik', 'harga' => 25000, 'cat' => 4],
            ['prefix' => 'AKS', 'nama' => 'Pedang Katana Zenitsu (Kayu/Props)', 'kondisi' => 'Baik', 'harga' => 25000, 'cat' => 4],
            ['prefix' => 'AKS', 'nama' => 'Wig Gojo Satoru', 'kondisi' => 'Sangat Baik', 'harga' => 20000, 'cat' => 4],
            ['prefix' => 'AKS', 'nama' => 'Wig Yor Forger', 'kondisi' => 'Sangat Baik', 'harga' => 20000, 'cat' => 4],
            ['prefix' => 'AKS', 'nama' => 'Headset Kucing RGB (Props)', 'kondisi' => 'Baik', 'harga' => 15000, 'cat' => 4],
        ];

        $units = [];
        $i = 1;
        foreach ($unitsData as $data) {
            $kode = $data['prefix'] . '-' . str_pad($i++, 3, '0', STR_PAD_LEFT);
            $unit = Unit::create([
                'kode_unit'  => $kode,
                'nama_unit'  => $data['nama'],
                'deskripsi'  => 'Ini adalah deskripsi untuk ' . $data['nama'] . '. Barang disewakan dalam kondisi ' . strtolower($data['kondisi']) . '.',
                'kondisi'    => $data['kondisi'],
                'harga_sewa' => $data['harga'],
                'status'     => 'tersedia',
            ]);
            // Attach ke kategori
            $unit->categories()->attach($categories[$data['cat']]->id);
            $units[] = $unit;
        }

        // 3. Buat Bundles (10 Bundles)
        $bundlesData = [
            ['nama' => 'Paket Cosplay Kamado Siblings', 'desc' => 'Kostum Tanjiro + Nezuko lengkap.', 'units' => [2, 3], 'harga' => 80000],
            ['nama' => 'Paket Mabar PS5', 'desc' => 'PS5 + 2 Stik DualSense', 'units' => [10, 18, 19], 'harga' => 200000],
            ['nama' => 'Paket Forger Family', 'desc' => 'Kostum Loid, Yor, dan Anya Forger.', 'units' => [7, 8, 9], 'harga' => 120000],
            ['nama' => 'Paket Marathon One Piece', 'desc' => 'Manga One Piece dari volume 1 sampai 20.', 'units' => [20, 21], 'harga' => 25000],
            ['nama' => 'Paket Marathon Demon Slayer', 'desc' => 'Manga Demon Slayer Vol 1 - 10.', 'units' => [28, 29], 'harga' => 15000],
            ['nama' => 'Paket Kreator Pemula', 'desc' => 'Wacom Intuos + Ring Light', 'units' => [35, 41], 'harga' => 100000],
            ['nama' => 'Paket Vlogger Studio', 'desc' => 'Kamera Canon M50 + Mic + Softbox', 'units' => [39, 42, 44], 'harga' => 200000],
            ['nama' => 'Paket Katana Demon Slayer', 'desc' => 'Properti Katana Tanjiro & Zenitsu.', 'units' => [45, 46], 'harga' => 45000],
            ['nama' => 'Paket Cosplay Gojo Lengkap', 'desc' => 'Kostum + Wig Gojo Satoru.', 'units' => [5, 47], 'harga' => 60000],
            ['nama' => 'Paket Nintendo Mabar', 'desc' => 'Nintendo Switch OLED + Mario Kart 8.', 'units' => [12, 17], 'harga' => 110000],
        ];

        foreach ($bundlesData as $bData) {
            $bundle = Bundle::create([
                'nama_bundle'    => $bData['nama'],
                'deskripsi'      => $bData['desc'],
                'status'         => 'tersedia',
                'harga_per_hari' => $bData['harga'],
            ]);

            // Dapatkan array ID unit yang sesuai
            $unitIds = array_map(function($index) use ($units) {
                return $units[$index]->id;
            }, $bData['units']);

            $bundle->units()->attach($unitIds);
        }
    }
}
