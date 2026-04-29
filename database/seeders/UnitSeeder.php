<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\Category;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['kode_unit' => 'COS-001', 'nama_unit' => 'Kostum Naruto',       'status' => 'tersedia', 'kondisi' => 'Baik', 'category' => 'Cosplay'],
            ['kode_unit' => 'COS-002', 'nama_unit' => 'Katana Tanjiro',       'status' => 'tersedia', 'kondisi' => 'Baik', 'category' => 'Cosplay'],
            ['kode_unit' => 'COL-001', 'nama_unit' => 'Naruto Vol 1-10',      'status' => 'tersedia', 'kondisi' => 'Baik', 'category' => 'Koleksi'],
            ['kode_unit' => 'GAME-001','nama_unit' => 'PS4 Naruto Storm 4',   'status' => 'tersedia', 'kondisi' => 'Baik', 'category' => 'Game & Console'],
            ['kode_unit' => 'CRT-001', 'nama_unit' => 'Wacom Tablet CTL-472', 'status' => 'tersedia', 'kondisi' => 'Baik', 'category' => 'Peralatan Kreator'],
        ];

        foreach ($units as $data) {
            $category = Category::where('nama_kategori', $data['category'])->first();
            $unit = Unit::create([
                'kode_unit'  => $data['kode_unit'],
                'nama_unit'  => $data['nama_unit'],
                'status'     => $data['status'],
                'kondisi'    => $data['kondisi'],
            ]);
            $unit->categories()->attach($category->id);
        }
    }
}
