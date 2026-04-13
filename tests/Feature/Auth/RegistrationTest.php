<?php

namespace Tests\Feature\Auth;

use App\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $level = UserLevel::create([
            'name' => 'Administrador',
        ]);

        $response = $this->withCsrfToken()->post('/register', [
            '_token' => 'test-csrf-token',
            'user_level_id' => $level->id,
            'document_type' => 'DNI',
            'document_number' => '12345678',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '600000000',
            'address' => 'Barcelona',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
