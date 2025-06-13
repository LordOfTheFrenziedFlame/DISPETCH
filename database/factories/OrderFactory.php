<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'customer_name' => $this->faker->name,
            'address' => $this->faker->address,
            'phone_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'manager_id' => User::factory(),
            'meeting_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'order_number' => $this->faker->unique()->numberBetween(100000, 999999),
            // Добавьте другие поля, если необходимо
        ];
    }
}
