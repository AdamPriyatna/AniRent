<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['nama_kategori' => 'Cosplay',            'deskripsi' => 'Kostum dan aksesoris cosplay karakter anime'],
            ['nama_kategori' => 'Koleksi',             'deskripsi' => 'Manga, light novel, dan koleksi fisik anime'],
            ['nama_kategori' => 'Game & Console',      'deskripsi' => 'Console game dan judul game bertema anime'],
            ['nama_kategori' => 'Peralatan Kreator',   'deskripsi' => 'Peralatan untuk kreator konten anime'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
