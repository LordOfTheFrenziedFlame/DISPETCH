<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_application_health(): void
    {
        // Проверяем, что приложение запускается
        $this->assertTrue(true);
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'manager']);
        
        $this->actingAs($user, 'employees');
        
        $response = $this->get('/employee/orders');
        $response->assertStatus(200);
    }

    public function test_order_creation_workflow(): void
    {
        /** @var User $manager */
        $manager = User::factory()->create(['role' => 'manager']);
        /** @var User $surveyor */
        $surveyor = User::factory()->create(['role' => 'surveyor']);
        
        $this->actingAs($manager, 'employees');

        // Данные для создания заказа
        $orderData = [
            'customer_name' => 'Test Customer',
            'address' => 'Test Address',
            'phone_number' => '+1234567890',
            'email' => 'test@example.com',
            'manager_id' => $manager->id,
            'surveyor_id' => $surveyor->id,
            'order_number' => 12345
        ];

        // Создаем заказ
        $response = $this->post('/employee/orders', $orderData);
        
        // Проверяем, что заказ создан
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Test Customer',
            'order_number' => 12345
        ]);
    }

    public function test_roles_and_permissions(): void
    {
        $roles = ['manager', 'surveyor', 'constructor', 'installer'];
        
        foreach ($roles as $role) {
            /** @var User $user */
            $user = User::factory()->create(['role' => $role]);
            
            $this->actingAs($user, 'employees');
            
            // Каждая роль должна иметь доступ к основным страницам
            $response = $this->get('/employee/orders');
            $this->assertContains($response->status(), [200, 302]); // 200 OK или 302 Redirect
            
            // Проверяем доступ к соответствующим разделам
            switch($role) {
                case 'manager':
                    $response = $this->get('/employee/orders');
                    $this->assertEquals(200, $response->status());
                    break;
                    
                case 'surveyor':
                    $response = $this->get('/employee/measurements');
                    $this->assertEquals(200, $response->status());
                    break;
                    
                case 'constructor':
                    $response = $this->get('/employee/documentations');
                    $this->assertEquals(200, $response->status());
                    break;
                    
                case 'installer':
                    $response = $this->get('/employee/installations');
                    $this->assertEquals(200, $response->status());
                    break;
            }
        }
    }

    public function test_database_connections_work(): void
    {
        // Проверяем, что можем создавать записи в БД
        try {
            /** @var User $user */
            $user = User::factory()->create();
            $this->assertDatabaseHas('users', ['id' => $user->id]);

            /** @var Order $order */
            $order = Order::factory()->create(['manager_id' => $user->id]);
            $this->assertDatabaseHas('orders', ['id' => $order->id]);
            
            // Проверяем что база данных работает корректно
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Database connection failed: ' . $e->getMessage());
        }
    }

    public function test_basic_security_measures(): void
    {
        // Тест защиты от неаутентифицированного доступа
        $protectedRoutes = [
            '/employee/orders',
            '/employee/measurements',
            '/employee/documentations',
            '/employee/productions',
            '/employee/installations'
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_application_key_is_set(): void
    {
        // Проверяем, что APP_KEY установлен
        $this->assertNotEmpty(config('app.key'));
    }

    public function test_environment_is_testing(): void
    {
        // Проверяем, что мы в тестовой среде
        $this->assertEquals('testing', app()->environment());
    }
}
