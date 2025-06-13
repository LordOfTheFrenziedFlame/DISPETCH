<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Order;
use App\Models\User;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_multiple_orders(): void
    {
        // Создаем менеджера
        /** @var User $manager */
        $manager = User::factory()->create(['role' => 'manager']);

        // Создаем 5 заказов для теста (100 слишком много для тестов)
        Order::factory()->count(5)->create(['manager_id' => $manager->id]);

        // Проверяем, что в базе данных 5 заказов
        $this->assertDatabaseCount('orders', 5);
        
        // Проверяем, что все заказы принадлежат нашему менеджеру
        $orders = Order::where('manager_id', $manager->id)->get();
        $this->assertCount(5, $orders);
    }

    public function test_order_creation_requires_manager(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Попытка создать заказ без менеджера должна упасть
        Order::create([
            'customer_name' => 'Test Customer',
            'address' => 'Test Address',
            'phone_number' => '+1234567890',
            'email' => 'test@example.com',
            'order_number' => 99999,
            'manager_id' => null, // Это должно вызвать ошибку
        ]);
    }

    public function test_order_has_unique_order_number(): void
    {
        /** @var User $manager */
        $manager = User::factory()->create(['role' => 'manager']);
        
        $order1 = Order::factory()->create([
            'manager_id' => $manager->id,
            'order_number' => 12345
        ]);
        
        // Попытка создать заказ с тем же номером должна упасть
        $this->expectException(\Illuminate\Database\QueryException::class);
        Order::factory()->create([
            'manager_id' => $manager->id,
            'order_number' => 12345
        ]);
    }
}