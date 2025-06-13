<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use App\Models\Order;

abstract class TestCase extends BaseTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        
        // Настройка для тестовой среды
        config(['app.env' => 'testing']);
        
        // Отключаем реальные уведомления в тестах
        \Illuminate\Support\Facades\Notification::fake();
        
        // Отключаем реальную отправку email в тестах
        \Illuminate\Support\Facades\Mail::fake();
    }

    /**
     * Создает аутентифицированного пользователя для тестов
     */
    protected function createAuthenticatedUser(string $role = 'manager', array $attributes = []): User
    {
        /** @var User $user */
        $user = User::factory()->create(array_merge([
            'role' => $role
        ], $attributes));
        
        $this->actingAs($user, 'employees');
        
        return $user;
    }

    /**
     * Создает тестовый заказ с минимально необходимыми данными
     */
    protected function createTestOrder(array $attributes = []): Order
    {
        return Order::factory()->create($attributes);
    }

    /**
     * Проверяет, что маршрут защищен аутентификацией
     */
    protected function assertRouteRequiresAuth(string $method, string $route, array $data = []): void
    {
        $response = match(strtoupper($method)) {
            'GET' => $this->get($route),
            'POST' => $this->post($route, $data),
            'PUT' => $this->put($route, $data),
            'PATCH' => $this->patch($route, $data),
            'DELETE' => $this->delete($route),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}")
        };

        $response->assertRedirect('/login');
    }

    /**
     * Проверяет, что пользователь с определенной ролью имеет доступ к маршруту
     */
    protected function assertRoleCanAccess(string $role, string $method, string $route, array $data = []): void
    {
        $user = $this->createAuthenticatedUser($role);
        
        $response = match(strtoupper($method)) {
            'GET' => $this->get($route),
            'POST' => $this->post($route, $data),
            'PUT' => $this->put($route, $data),
            'PATCH' => $this->patch($route, $data),
            'DELETE' => $this->delete($route),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}")
        };

        $this->assertNotEquals(403, $response->status(), "User with role {$role} should have access to {$method} {$route}");
    }

    /**
     * Проверяет, что пользователь с определенной ролью НЕ имеет доступ к маршруту
     */
    protected function assertRoleCannotAccess(string $role, string $method, string $route, array $data = []): void
    {
        $user = $this->createAuthenticatedUser($role);
        
        $response = match(strtoupper($method)) {
            'GET' => $this->get($route),
            'POST' => $this->post($route, $data),
            'PUT' => $this->put($route, $data),
            'PATCH' => $this->patch($route, $data),
            'DELETE' => $this->delete($route),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}")
        };

        $this->assertContains($response->status(), [302, 403], "User with role {$role} should NOT have access to {$method} {$route}");
    }
}
