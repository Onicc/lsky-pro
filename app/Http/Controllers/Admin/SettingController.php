<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ConfigKey;
use App\Http\Controllers\Controller;
use App\Mail\Test;
use App\Models\Config;
use App\Support\SecretValue;
use App\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('admin.setting.index', ['configs' => Utils::config()]);
    }

    public function save(Request $request): Response
    {
        try {
            $settings = $request->validate([
                'app_name' => ['sometimes', 'required', 'string', 'max:100'],
                'site_keywords' => ['nullable', 'string', 'max:500'],
                'site_description' => ['nullable', 'string', 'max:2000'],
                'site_notice' => ['nullable', 'string', 'max:10000'],
                'icp_no' => ['nullable', 'string', 'max:100'],
                'is_enable_registration' => ['sometimes', 'boolean'],
                'is_enable_gallery' => ['sometimes', 'boolean'],
                'is_enable_api' => ['sometimes', 'boolean'],
                'is_allow_guest_upload' => ['sometimes', 'boolean'],
                'is_user_need_verify' => ['sometimes', 'boolean'],
                'user_initial_capacity' => ['sometimes', 'numeric', 'min:0', 'max:100000000000'],
                'mail' => ['sometimes', 'array:default,mailers,from'],
                'mail.default' => ['sometimes', 'in:smtp'],
                'mail.mailers' => ['sometimes', 'array:smtp'],
                'mail.mailers.smtp' => ['sometimes', 'array:transport,host,port,username,password,clear_password,encryption,timeout'],
                'mail.mailers.smtp.transport' => ['sometimes', 'in:smtp'],
                'mail.mailers.smtp.host' => ['sometimes', 'required', 'string', 'max:253'],
                'mail.mailers.smtp.port' => ['sometimes', 'required', 'integer', 'between:1,65535'],
                'mail.mailers.smtp.username' => ['nullable', 'string', 'max:256'],
                'mail.mailers.smtp.password' => ['nullable', 'string', 'max:512'],
                'mail.mailers.smtp.clear_password' => ['sometimes', 'boolean'],
                'mail.mailers.smtp.encryption' => ['nullable', 'in:tls,ssl'],
                'mail.mailers.smtp.timeout' => ['nullable', 'integer', 'between:1,120'],
                'mail.from' => ['sometimes', 'array:address,name'],
                'mail.from.address' => ['nullable', 'string', 'max:254', 'email'],
                'mail.from.name' => ['nullable', 'string', 'max:128'],
            ]);
        } catch (ValidationException $e) {
            return $this->fail($e->validator->errors()->first());
        }

        DB::transaction(function () use ($settings): void {
            foreach ($settings as $name => $value) {
                if ($name === ConfigKey::Mail) {
                    $value = $this->saveMailSettings($value);
                }
                Config::query()->where('name', $name)->update(['value' => $value]);
            }
        });
        Cache::forget('configs');

        return $this->success('保存成功');
    }

    public function mailTest(Request $request): Response
    {
        try {
            $validated = $request->validate(['email' => ['bail', 'required', 'string', 'max:254', 'email']]);
            Mail::to($validated['email'])->send(new Test());
        } catch (ValidationException $e) {
            return $this->fail($e->validator->errors()->first());
        } catch (Throwable $e) {
            Log::warning('SMTP test failed', ['exception_type' => $e::class]);

            return $this->fail('测试邮件发送失败，请检查 SMTP 连接与凭据。');
        }

        return $this->success('发送成功');
    }

    private function saveMailSettings(array $value): string
    {
        $previous = Config::query()->where('name', ConfigKey::Mail)->lockForUpdate()->value('value');
        $mail = json_decode($previous ?? '{}', true, flags: JSON_THROW_ON_ERROR);
        $path = 'mailers.smtp.password';
        if (Arr::get($value, 'mailers.smtp.clear_password')) {
            Arr::set($value, $path, null);
        } elseif (! Arr::get($value, $path)) {
            Arr::forget($value, $path);
        }
        Arr::forget($value, 'mailers.smtp.clear_password');
        $mail = array_replace_recursive($mail, $value);
        Arr::set($mail, $path, SecretValue::encrypt(Arr::get($mail, $path)));

        return json_encode($mail, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
