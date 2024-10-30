<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Type;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // carichiamo 3 type casuali a ogni product
        $products = Product::all();
        foreach ($products as $product) {
            $product->types()->attach(Type::inRandomOrder()->limit(3)->get());
        }
    }
}
