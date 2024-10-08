<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'customer_name' => $this->faker->name,
            'customer_email' => $this->faker->email,
            'total_price' => $this->faker->randomFloat(2, 50, 500),
        ];
    }
}
