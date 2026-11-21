<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckIsInstalled;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_an_uninstalled_site_exposes_read_only_setup_instructions(): void
    {
        $this->withMiddleware(CheckIsInstalled::class);
        $this->get('/')->assertRedirect('/install');
        $this->get('/install')->assertOk()->assertSee('php artisan lsky:install');
        $this->post('/install')->assertMethodNotAllowed();
        $this->getJson('/up')->assertStatus(503)->assertJsonPath('status', 'unavailable')
            ->assertJsonStructure(['status', 'checks' => ['database', 'migrations', 'storage']]);
    }
}
