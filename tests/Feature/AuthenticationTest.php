<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Создаем тестового пользователя
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Test123!@#Strong'),
            'role' => 'manager'
        ]);
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('login'); // Проверяем, что страница содержит форму входа
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Test123!@#Strong',
        ]);

        $response->assertRedirect('/employee/orders');
        $this->assertAuthenticated('employees');
    }

    public function test_user_cannot_login_with_incorrect_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest('employees');
    }

    public function test_rate_limiting_works_on_login(): void
    {
        // Делаем 6 неудачных попыток входа (лимит 5)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6-я попытка должна быть заблокирована
        $response->assertStatus(429); // Too Many Requests
    }

    public function test_authenticated_user_can_logout(): void
    {
        $this->actingAs($this->user, 'employees');
        
        $response = $this->get('/logout');
        
        $response->assertRedirect('/login');
        $this->assertGuest('employees');
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $protectedRoutes = [
            '/employee/orders',
            '/employee/measurements',
            '/employee/productions',
            '/employee/installations',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_authenticated_user_can_access_protected_routes(): void
    {
        $this->actingAs($this->user, 'employees');

        $protectedRoutes = [
            '/employee/orders',
            '/employee/measurements', 
            '/employee/productions',
            '/employee/installations',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_role_based_access_control(): void
    {
        // Тестируем доступ замерщика
        /** @var User $surveyor */
        $surveyor = User::factory()->create(['role' => 'surveyor']);
        $this->actingAs($surveyor, 'employees');

        // Замерщик может получить доступ к замерам
        $response = $this->get('/employee/measurements');
        $response->assertStatus(200);

        // Но не может управлять пользователями
        $response = $this->get('/employee/users');
        $response->assertStatus(302); // Redirect because no permission
    }

    public function test_password_validation_rules(): void
    {
        $this->actingAs($this->user, 'employees');

        // Тест слабого пароля
        $response = $this->post('/employee/users', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'surveyor'
        ]);

        $response->assertSessionHasErrors('password');

        // Тест сильного пароля
        $response = $this->post('/employee/users', [
            'name' => 'Test User',
            'email' => 'newuser@example.com', 
            'password' => 'StrongPass123!@#',
            'password_confirmation' => 'StrongPass123!@#',
            'role' => 'surveyor'
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }
}
