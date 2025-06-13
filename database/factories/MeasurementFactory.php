<?php

namespace Database\Factories;

use App\Models\Measurement;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeasurementFactory extends Factory
{
    protected $model = Measurement::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'surveyor_id' => User::factory()->create(['role' => 'surveyor']),
            'status' => 'pending',
            'measured_at' => null,
            'notes' => $this->faker->sentence,
        ];
    }
} 