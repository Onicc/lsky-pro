<?php

namespace Tests;

use App\Http\Middleware\CheckIsInstalled;
use Database\Seeders\InstallSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(CheckIsInstalled::class);
        $this->withoutVite();
        $this->seed(InstallSeeder::class);
    }
}
