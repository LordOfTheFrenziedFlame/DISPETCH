<?php

namespace Database\Factories;

use App\Models\Production;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionFactory extends Factory
{
    protected $model = Production::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'notes' => $this->faker->sentence,
            'completed_at' => null,
        ];
    }
} 