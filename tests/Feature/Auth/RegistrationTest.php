<?php

namespace Tests\Feature\Auth;

use App\Enums\ConfigKey;
use App\Models\Config;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::query()->where('name', ConfigKey::IsEnableRegistration)->update(['value' => 1]);
        Cache::forget('configs');
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }
}
