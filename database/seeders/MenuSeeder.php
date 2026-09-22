<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = Vendor::firstOrCreate(
            ['slug' => 'dapur-utama-catering'],
            ['name' => 'Dapur Utama Catering', 'phone' => '081234567890', 'rating' => 4.9]
        );

        $categories = Category::all()->keyBy('slug');

        $bentoCat     = $categories->get('bento-box-rice-sets') ?? Category::first();
        $buffetCat    = $categories->get('prasmanan-tradisional-nusantara') ?? Category::first();
        $snackCat     = $categories->get('aneka-snack-box') ?? Category::first();
        $saladCat     = $categories->get('menu-sehat-salad-box') ?? Category::first();
        $beverageCat  = $categories->get('paket-minuman-segar') ?? Category::first();

        // 35+ MENUS (20 Makanan, 10 Minuman, 5 Snack)
        $menus = [
            // ==========================================
            // 20 MAKANAN
            // ==========================================
            ['cat_id' => $bentoCat->id,  'name' => 'Paket Nasi Ayam Lengkuas',     'price' => 35000, 'calories' => 650, 'description' => 'Ayam goreng lengkuas khas Sunda, tahu, tempe, lalapan segar, dan sambal terasi.'],
            ['cat_id' => $bentoCat->id,  'name' => 'Paket Nasi Rendang Daging',    'price' => 45000, 'calories' => 750, 'description' => 'Rendang sapi olahan rempah khas Minang, daun singkong rebus, dan sambal ijo.'],
            ['cat_id' => $bentoCat->id,  'name' => 'Paket Nasi Ikan Dabu-Dabu',   'price' => 40000, 'calories' => 580, 'description' => 'Fillet ikan goreng renyah disiram sambal dabu-dabu segar khas Manado.'],
            ['cat_id' => $bentoCat->id,  'name' => 'Paket Nasi Bebek Goreng',     'price' => 48000, 'calories' => 820, 'description' => 'Bebek goreng empuk dengan bumbu hitam Madura dan lalapan komplit.'],
            ['cat_id' => $bentoCat->id,  'name' => 'Paket Nasi Liwet Komplit',    'price' => 42000, 'calories' => 690, 'description' => 'Nasi liwet beras gurih disajikan dengan teri medan, ayam suwir, dan petai.'],
            ['cat_id' => $bentoCat->id,  'name' => 'Bento Box Chicken Teriyaki',  'price' => 38000, 'calories' => 620, 'description' => 'Dada ayam tumis teriyaki, tamagoyaki, salad wijen sangrai, dan nasi jepang.'],
            ['cat_id' => $bentoCat->id,  'name' => 'Bento Box Beef Yakiniku',     'price' => 46000, 'calories' => 710, 'description' => 'Daging sapi iris tipis bumbu yakiniku gurih manis, ebi furai, dan nasi jepang.'],
            ['cat_id' => $saladCat->id,  'name' => 'Western Chicken Caesar Salad','price' => 42000, 'calories' => 410, 'description' => 'Salad romaine segar, dada ayam panggang, crouton renyah, dan dressing caesar.'],
            ['cat_id' => $saladCat->id,  'name' => 'Salmon Teriyaki Grain Bowl',   'price' => 55000, 'calories' => 530, 'description' => 'Fillet salmon panggang bumbu teriyaki, edamame, jagung manis, dan quinoa.'],
            ['cat_id' => $buffetCat->id, 'name' => 'Gado-Gado Spesial Surabaya',   'price' => 30000, 'calories' => 450, 'description' => 'Sayuran rebus segar, lontong, lontong tahu-tempe disiram bumbu kacang tanah gurih.'],
            ['cat_id' => $bentoCat->id,  'name' => 'Paket Nasi Kuning Komplit',   'price' => 36000, 'calories' => 670, 'description' => 'Nasi kuning wangi, perkedel kentang, telur iris, mie goreng, dan sambal goreng ati.'],
            ['cat_id' => $bentoCat->id,  'name' => 'Paket Nasi Uduk Betawi',      'price' => 34000, 'calories' => 640, 'description' => 'Nasi uduk santan gurih, semur tahu-telur, emping renyah, dan sambal kacang.'],
            ['cat_id' => $buffetCat->id, 'name' => 'Soto Ayam Lamongan Spesial',   'price' => 32000, 'calories' => 490, 'description' => 'Soto ayam kuah kuning gurih dengan koya renyah, suwiran ayam, dan lontong.'],
            ['cat_id' => $buffetCat->id, 'name' => 'Rawon Daging Sapi Daging',    'price' => 44000, 'calories' => 680, 'description' => 'Sup daging sapi kuah kluwek hitam pekat khas Jawa Timur dengan tauge pendek.'],
            ['cat_id' => $bentoCat->id,  'name' => 'Bakmi Goreng Katering',       'price' => 32000, 'calories' => 590, 'description' => 'Bakmi telur goreng tumis udang, bakso sapi, telur, dan sayuran segar.'],
            ['cat_id' => $bentoCat->id,  'name' => 'Kwetiau Sapi Goreng',         'price' => 38000, 'calories' => 630, 'description' => 'Kwetiau beras tumis iris daging sapi empuk, tauge, dan telur.'],
            ['cat_id' => $buffetCat->id, 'name' => 'Capcay Seafood Spesial',      'price' => 36000, 'calories' => 390, 'description' => 'Tumisan berbagai jenis sayuran segar dengan udang, cumi, dan bakso ikan.'],
            ['cat_id' => $buffetCat->id, 'name' => 'Ayam Bakar Taliwang khas Katering','price' => 42000,'calories' => 660, 'description' => 'Ayam muda bakar bumbu plecing pedas khas Lombok dengan beberuk terong.'],
            ['cat_id' => $buffetCat->id, 'name' => 'Gurame Asam Manis',          'price' => 50000, 'calories' => 720, 'description' => 'Ikan gurame filet goreng tepung disiram saus asam manis nanas gurih.'],
            ['cat_id' => $saladCat->id,  'name' => 'Paket Vegetarian Sehat',      'price' => 38000, 'calories' => 480, 'description' => 'Tumis sayuran organik segar, tahu bacem, urap kelapa, dan buah potong segar.'],

            // ==========================================
            // 10 MINUMAN
            // ==========================================
            ['cat_id' => $beverageCat->id, 'name' => 'Air Mineral Botol 600ml',   'price' => 5000,  'calories' => 0,   'description' => 'Air mineral kemasan botol 600ml steril dan segar.'],
            ['cat_id' => $beverageCat->id, 'name' => 'Es Teh Manis Segar',         'price' => 6000,  'calories' => 90,  'description' => 'Seduhan teh melati manis dipadu es batu dingin dalam gelas cup.'],
            ['cat_id' => $beverageCat->id, 'name' => 'Kopi Hitam Tubruk Arabika', 'price' => 12000, 'calories' => 40,  'description' => 'Seduhan kopi arabika gayo asli aroma harum dan nikmat.'],
            ['cat_id' => $beverageCat->id, 'name' => 'Es Jeruk Peras Alami',       'price' => 10000, 'calories' => 110, 'description' => 'Perasan jeruk peras asli kaya vitamin C dengan gula murni.'],
            ['cat_id' => $beverageCat->id, 'name' => 'Teh Tarik Khas Katering',   'price' => 14000, 'calories' => 180, 'description' => 'Seduhan teh hitam pekat dipadu susu kental manis ditarik lembut.'],
            ['cat_id' => $beverageCat->id, 'name' => 'Jus Alpukat Kocok Creamy',  'price' => 18000, 'calories' => 260, 'description' => 'Alpukat mentega murni dikocok halus disiram susu cokelat kental.'],
            ['cat_id' => $beverageCat->id, 'name' => 'Es Cendol Dawet Ayu',        'price' => 15000, 'calories' => 220, 'description' => 'Cendol tepung beras hijau segar, santan gurih, dan gula merah cair.'],
            ['cat_id' => $beverageCat->id, 'name' => 'Matcha Latte Iced',          'price' => 20000, 'calories' => 190, 'description' => 'Seduhan hijau matcha khas Jepang dipadu susu segar UHT.'],
            ['cat_id' => $beverageCat->id, 'name' => 'Lemon Tea Fresh Iced',       'price' => 12000, 'calories' => 95,  'description' => 'Teh hitam dipadu perasan buah lemon segar rasa asam manis.'],
            ['cat_id' => $beverageCat->id, 'name' => 'Soft Drink Can 330ml',       'price' => 10000, 'calories' => 140, 'description' => 'Minuman berkarbonasi dingin kemasan kaleng 330ml.'],

            // ==========================================
            // 5 SNACK
            // ==========================================
            ['cat_id' => $snackCat->id, 'name' => 'Snack Box Spesial Acara',      'price' => 22000, 'calories' => 380, 'description' => 'Kotak snack berisi risoles ragout, lemper ketan, fruit pie, dan air mineral.'],
            ['cat_id' => $snackCat->id, 'name' => 'Brownies Cokelat Kukus Keju',  'price' => 15000, 'calories' => 280, 'description' => 'Brownies cokelat lembut dipanggang halus dengan parutan keju keju manis.'],
            ['cat_id' => $snackCat->id, 'name' => 'Risoles Mayo Daging Ayam',     'price' => 12000, 'calories' => 240, 'description' => 'Kulit risol renyah berisi daging ayam suwir, telur, dan mayo gurih.'],
            ['cat_id' => $snackCat->id, 'name' => 'Pastel Sayur Telur Gurih',     'price' => 10000, 'calories' => 210, 'description' => 'Pastel goreng renyah berisi tumis wortel, kentang, dan potongan telur.'],
            ['cat_id' => $snackCat->id, 'name' => 'Pie Buah Segar Mini',           'price' => 14000, 'calories' => 190, 'description' => 'Kue pie renyah diisi custard vanilla lembut dan topping buah segar.'],
        ];

        foreach ($menus as $m) {
            Menu::updateOrCreate(
                ['slug' => Str::slug($m['name'])],
                [
                    'category_id'  => $m['cat_id'],
                    'vendor_id'    => $vendor->id,
                    'name'         => $m['name'],
                    'price'        => $m['price'],
                    'calories'     => $m['calories'],
                    'description'  => $m['description'],
                    'is_halal'     => true,
                    'is_available' => true,
                ]
            );
        }
    }
}
