<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            ['id' => 1, 'quantity' => 1, 'price' => 15.00],
            ['id' => 2, 'quantity' => 1, 'price' => 30.00],
            ['id' => 3, 'quantity' => 1, 'price' => 150.00],
        ];

        $totalPrice = array_reduce($products, function ($carry, $product) {
            return $carry + ($product['price'] * $product['quantity']);
        }, 0);

        $order = Order::create([
            'customer_name' => 'Jose Gutierrez',
            'customer_email' => 'me@joseiguti.com',
            'total_price' => $totalPrice,
        ]);

        foreach ($products as $product) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product['id'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
            ]);

            $productInstance = Product::find($product['id']);
            if ($productInstance) {
                $productInstance->stock -= $product['quantity'];
                $productInstance->save();
            } else {
                echo "Product's Id {$product['id']} not found.\n";
            }
        }
    }
}
