<?php

namespace App\Console\Commands;

use App\Enums\ConfigKey;
use App\Enums\UserStatus;
use App\Models\Config;
use App\Models\User;
use App\Support\Installation;
use Database\Seeders\InstallSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Throwable;

class Install extends Command
{
    protected $signature = 'lsky:install {--adopt : Attach an existing Lsky Pro database and apply pending migrations}';

    protected $description = 'Initialize an empty database and create the first administrator';

    public function handle(): int
    {
        $marker = Installation::marker();
        $lock = @fopen($marker.'.mutex', 'c');

        if ($lock === false) {
            $this->error('Could not open the installation lock. Check storage/app permissions.');

            return self::FAILURE;
        }

        try {
            if (! flock($lock, LOCK_EX | LOCK_NB)) {
                $this->error('Another installation is in progress.');

                return self::FAILURE;
            }

            if (! $this->option('adopt') && Installation::exists()) {
                $this->info('Lsky Pro is already installed.');

                return self::SUCCESS;
            }

            // Resolve the encrypter before changing the database so an invalid key cannot leave a partial installation.
            app('encrypter');

            if ($this->option('adopt')) {
                if (! $this->hasExistingInstallation()) {
                    $this->error('The configured database lacks a complete Lsky Pro installation with an administrator.');

                    return self::FAILURE;
                }
                if (! $this->migrate()) {
                    return self::FAILURE;
                }
            } elseif (! $this->initializeDatabase($marker.'.pending')) {
                return self::FAILURE;
            }

            $storageRoot = config('image.private_root');
            if (! is_dir($storageRoot) && ! @mkdir($storageRoot, 0750, true) && ! is_dir($storageRoot)) {
                $this->error('Could not create the private media directory. Check storage/app permissions.');

                return self::FAILURE;
            }

            File::replace($marker, 'installed'.PHP_EOL);
            if (is_file($marker.'.pending')) {
                File::delete($marker.'.pending');
            }
            $this->info('Lsky Pro is ready.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            report($e);
            $this->error('Installation failed. Check the application log and your database and storage settings.');

            return self::FAILURE;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function initializeDatabase(string $pending): bool
    {
        $email = config('lsky.install.admin_email');
        $password = config('lsky.install.admin_password');
        $validator = Validator::make(compact('email', 'password'), [
            'email' => ['required', 'string', 'max:254', 'email'],
            'password' => ['required', 'string', 'min:12'],
        ]);
        if ($validator->fails() || strlen($password) > 72) {
            $this->error('Set LSKY_ADMIN_EMAIL and LSKY_ADMIN_PASSWORD (12–72 bytes).');

            return false;
        }

        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        $identity = hash('sha256', implode('|', [
            $connection->getDriverName(), $connection->getConfig('host'), $connection->getConfig('port'), $database,
        ]));

        if ($connection->getDriverName() === 'sqlite') {
            if ($database === ':memory:') {
                $this->error('Installation requires a persistent SQLite database path.');

                return false;
            }
            if (! is_file($database)) {
                if (! is_dir(dirname($database)) || ! touch($database)) {
                    $this->error('Could not create the configured SQLite database file.');

                    return false;
                }
                @chmod($database, 0600);
            }
        }

        if (is_file($pending)) {
            if (trim(File::get($pending)) !== $identity) {
                $this->error('The previous installation was started with a different database.');

                return false;
            }
        } elseif (Schema::getTables() !== []) {
            $this->error('Installation requires an empty database. Use --adopt to attach an existing Lsky Pro installation.');

            return false;
        } else {
            File::replace($pending, $identity.PHP_EOL);
        }

        if (! $this->migrate()) {
            return false;
        }

        if (! $this->hasExistingInstallation()) {
            DB::transaction(function () use ($email, $password) {
                $this->laravel->make(InstallSeeder::class)->run();

                $admin = new User([
                    'name' => '管理员',
                    'email' => $email,
                    'password' => Hash::make($password),
                    'status' => UserStatus::Normal,
                ]);
                $admin->is_adminer = true;
                $admin->email_verified_at = now();
                $admin->saveOrFail();
            });
        }

        return true;
    }

    private function migrate(): bool
    {
        return $this->call('migrate', ['--force' => true]) === self::SUCCESS;
    }

    private function hasExistingInstallation(): bool
    {
        return Schema::hasTable('migrations') && Schema::hasTable('configs') && Schema::hasTable('users')
            && Config::query()->where('name', ConfigKey::AppVersion)->exists()
            && User::query()->where('is_adminer', true)->exists();
    }
}
