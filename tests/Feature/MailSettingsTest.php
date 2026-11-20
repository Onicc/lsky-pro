<?php

namespace Tests\Feature;

use App\Enums\ConfigKey;
use App\Models\Config;
use App\Models\User;
use App\Utils;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    public function test_password_is_encrypted_and_blank_edits_preserve_it(): void
    {
        $admin = User::factory()->create(['is_adminer' => true]);
        $password = 'enc:v1:sample-secret-only';
        $this->actingAs($admin)->putJson('/admin/settings/save', ['mail' => ['mailers' => ['smtp' => [
            'password' => $password,
        ]]]])->assertOk()->assertJsonPath('status', true);

        $stored = Config::query()->where('name', ConfigKey::Mail)->value('value');
        $this->assertStringNotContainsString($password, $stored);
        $this->assertSame($password, data_get(Utils::config(ConfigKey::Mail), 'mailers.smtp.password'));

        $this->putJson('/admin/settings/save', ['mail' => ['mailers' => ['smtp' => [
            'password' => '', 'host' => 'smtp.example.test',
        ]]]])->assertOk()->assertJsonPath('status', true);
        $this->assertSame($password, data_get(Utils::config(ConfigKey::Mail), 'mailers.smtp.password'));

        $this->putJson('/admin/settings/save', ['mail' => ['mailers' => ['smtp' => [
            'clear_password' => true,
        ]]]])->assertOk()->assertJsonPath('status', true);
        $this->assertNull(data_get(Utils::config(ConfigKey::Mail), 'mailers.smtp.password'));
    }
}
