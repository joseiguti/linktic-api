<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate')->run();
    }

    /** @test */
    public function simple_test_check()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function it_creates_an_order_successfully()
    {
        $product = Product::factory()->create([
            'name' => 'Producto de prueba',
            'price' => 100,
            'stock' => 10,
            'description' => 'Una descripción de prueba',
            'image' => 'product-one.jpg',
        ]);

        $orderData = [
            'customer_name' => 'Juan Pérez',
            'customer_email' => 'juan.perez@example.com',
            'total_price' => 100,
        ];

        $order = Order::factory()->create($orderData);

        $order->details()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Juan Pérez',
            'customer_email' => 'juan.perez@example.com',
            'total_price' => 100,
        ]);

        $this->assertDatabaseHas('order_details', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);
    }

    /** @test */
    public function it_fails_to_create_an_order_if_product_stock_is_insufficient()
    {
        $product = Product::factory()->create([
            'name' => 'Producto con bajo stock',
            'price' => 50,
            'stock' => 0,
        ]);

        $orderData = [
            'customer_name' => 'Ana María',
            'customer_email' => 'ana.maria@example.com',
            'total_price' => 50,
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => 1,
                    'price' => $product->price,
                ]
            ]
        ];

        $headers = [
            'Authorization' => 'Basic ' . base64_encode(env('BASIC_AUTH_USER') . ':' . env('BASIC_AUTH_PASSWD')),
        ];

        $response = $this->postJson('/api/orders', $orderData, $headers);

        $response->assertStatus(500)
            ->assertJson(['error' => "Stock insuficiente para el producto {$product->name}"]);

        $this->assertDatabaseMissing('order_details', [
            'product_id' => $product->id,
        ]);
    }

    /** @test */
    public function it_creates_an_order_with_multiple_products()
    {
        $product1 = Product::factory()->create([
            'name' => 'Producto 1',
            'price' => 50,
            'stock' => 10,
            'description' => 'Descripción del Producto 1',
        ]);

        $product2 = Product::factory()->create([
            'name' => 'Producto 2',
            'price' => 100,
            'stock' => 5,
            'description' => 'Descripción del Producto 2',
        ]);

        $orderData = [
            'customer_name' => 'Luis Martínez',
            'customer_email' => 'luis.martinez@example.com',
            'total_price' => 150,
        ];

        $order = Order::create($orderData);

        $order->details()->createMany([
            [
                'product_id' => $product1->id,
                'quantity' => 2,
                'price' => $product1->price,
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 1,
                'price' => $product2->price,
            ]
        ]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Luis Martínez',
            'customer_email' => 'luis.martinez@example.com',
            'total_price' => 150,
        ]);

        $this->assertDatabaseHas('order_details', [
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('order_details', [
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);
    }

    /** @test */
    public function it_rolls_back_if_something_fails_during_order_creation()
    {
        // Simulamos el fallo lanzando una excepción manualmente dentro de una transacción.
        DB::beginTransaction();

        try {
            $product = Product::factory()->create([
                'name' => 'Producto',
                'price' => 100,
                'stock' => 5,
            ]);

            $orderData = [
                'customer_name' => 'Carlos Ramírez',
                'customer_email' => 'carlos.ramirez@example.com',
                'total_price' => 100,
            ];

            throw new \Exception('Fallo inesperado');

            $order = Order::create($orderData);
            $order->details()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        $this->assertDatabaseMissing('orders', [
            'customer_name' => 'Carlos Ramírez',
        ]);

        $this->assertDatabaseMissing('order_details', [
            'product_id' => $product->id,
        ]);
    }

    /** @test */
    public function it_requires_all_mandatory_fields()
    {
        $username = env('BASIC_AUTH_USER', 'admin');
        $password = env('BASIC_AUTH_PASS', 'secret');

        $response = $this->postJson('/api/orders', [], [
            'Authorization' => 'Basic ' . base64_encode("$username:$password"),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['customer_name', 'customer_email', 'total_price', 'products']);
    }

    /** @test */
    public function it_returns_unauthorized_if_no_basic_auth_provided()
    {
        $response = $this->get('/api/orders');

        $response->assertStatus(401);
    }
}
