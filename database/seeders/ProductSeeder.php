<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create([
            'name' => 'Soporte computadora',
            'price' => 25.00,
            'description' => 'Soporte para computadora y monitor al tiempo',
            'stock' => 15,
            'image' => 'product-one.jpg'
        ]);

        Product::create([
            'name' => 'Teclado Gaming',
            'price' => 30.00,
            'description' => 'Teclado gaming de ultima generación',
            'stock' => 25,
            'image' => 'product-two.jpg',
        ]);

        Product::create([
            'name' => 'Silla gaming',
            'price' => 150.00,
            'description' => 'Silla gaming de ultima generación',
            'stock' => 5,
            'image' => 'product-three.jpg',
        ]);
    }
}
