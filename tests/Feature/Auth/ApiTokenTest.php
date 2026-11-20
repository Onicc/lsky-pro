<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    public function test_scoped_device_token_has_expiration_and_only_granted_abilities(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/v1/tokens', [
            'email' => $user->email,
            'password' => 'password',
            'name' => '图库读取',
            'abilities' => ['images:read'],
        ])->assertOk()->assertJsonPath('status', true);

        $token = $response->json('data.token');
        $this->assertNotNull($response->json('data.expires_at'));
        $this->withToken($token)->getJson('/api/v1/images')->assertOk();
        $this->deleteJson('/api/v1/images/any-image')->assertForbidden();
    }

    public function test_frozen_accounts_cannot_issue_or_use_personal_tokens(): void
    {
        $user = User::factory()->create(['status' => UserStatus::Frozen]);
        $this->postJson('/api/v1/tokens', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()->assertJsonPath('status', false);

        $token = $user->createToken('already-issued')->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/profile')->assertForbidden();
    }

    public function test_expired_tokens_are_rejected(): void
    {
        $user = User::factory()->create();
        $expired = $user->createToken('expired', ['profile:read'], now()->subMinute())->plainTextToken;

        $this->withToken($expired)->getJson('/api/v1/profile')->assertUnauthorized();
    }

    public function test_password_reset_revokes_existing_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('one');
        $user->createToken('two');
        event(new PasswordReset($user));

        $this->assertSame(0, $user->tokens()->count());
    }
}
