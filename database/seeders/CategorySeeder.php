<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Bento Box & Rice Sets', 'icon' => '🍱'],
            ['name' => 'Prasmanan Tradisional Nusantara', 'icon' => '🍲'],
            ['name' => 'Aneka Snack Box', 'icon' => '🥐'],
            ['name' => 'Menu Sehat & Salad Box', 'icon' => '🥗'],
            ['name' => 'Paket Minuman Segar', 'icon' => '☕'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'is_active' => true,
                ]
            );
        }
    }
}
