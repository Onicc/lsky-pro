<?php

namespace App\Http\Requests\Admin;

use App\Enums\StrategyKey;
use App\Http\Requests\FormRequest;
use App\Models\Strategy;
use Illuminate\Validation\Rule;

class StrategyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $existing = $this->isMethod('PUT') ? Strategy::query()->find($this->route('id')) : null;
        $driver = (int) $this->input('key');
        $required = fn (string $name) => [
            Rule::requiredIf($existing?->key !== $driver || ! $existing?->configs->get($name)),
            'nullable', 'string', 'max:30000',
        ];
        $checkUrl = function ($attribute, $value, $fail) {
            $path = Strategy::getRootPath($value);
            if ($path === '' || ! preg_match('/\A[\p{L}\p{N}_-]+\z/u', $path)) {
                $fail('本地策略的访问地址需要一个有效的路径前缀');
            } elseif (in_array($path, [config('app.thumbnail_path'), 'fonts', 'css', 'js', 'media'], true)) {
                $fail('系统保留路径：'.$path);
            }
        };

        $array = [
            'groups' => 'array',
            'groups.*' => 'integer|exists:groups,id',
            'name' => 'required|max:60',
            'intro' => 'max:2000',
            'key' => ['required', 'integer', Rule::in(array_keys(Strategy::DRIVERS))],
            'configs.url' => ['required', 'url:http,https'],
            'configs.queries' => 'nullable|string|max:2048',
            'configs.private_objects' => 'nullable|boolean',
        ];

        return array_merge($array, match((int)$this->input('key')) {
            StrategyKey::Local => [
                'configs.url' => ['required', 'url:http,https', $checkUrl],
                'configs.root' => ['max:1000', function ($attribute, $value, $fail) {
                    if ($value) {
                        if (! is_dir($value)) {
                            return $fail('储存路径不存在');
                        }
                        if (! is_writable($value)) {
                            return $fail('储存路径没有写入权限');
                        }
                        $root = realpath($value);
                        $public = realpath(public_path());
                        $application = realpath(base_path());
                        $storage = realpath(storage_path('app'));
                        if ($root === $application || $root === $public || str_starts_with($public, $root.'/')
                            || str_starts_with($root, $public.'/')
                            || (str_starts_with($root, $application.'/') && ! str_starts_with($root, $storage.'/'))) {
                            $fail('储存路径必须位于公开目录之外');
                        }
                    }
                }],
            ],
            StrategyKey::S3 => [
                'configs.use_path_style_endpoint' => 'nullable|boolean',
                'configs.access_key_id' => $required('access_key_id'),
                'configs.secret_access_key' => $required('secret_access_key'),
                'configs.endpoint' => 'nullable|url:http,https',
                'configs.region' => 'nullable|string|max:100',
                'configs.bucket' => 'required',
            ],
            StrategyKey::Oss => [
                'configs.access_key_id' => $required('access_key_id'),
                'configs.access_key_secret' => $required('access_key_secret'),
                'configs.endpoint' => 'required',
                'configs.bucket' => 'required',
            ],
            StrategyKey::Cos => [
                'configs.app_id' => 'required',
                'configs.secret_id' => $required('secret_id'),
                'configs.secret_key' => $required('secret_key'),
                'configs.region' => 'required',
                'configs.bucket' => 'required',
            ],
            StrategyKey::Kodo => [
                'configs.access_key' => $required('access_key'),
                'configs.secret_key' => $required('secret_key'),
                'configs.bucket' => 'required',
            ],
            StrategyKey::Uss => [
                'configs.service' => 'required',
                'configs.operator' => 'required',
                'configs.password' => $required('password'),
            ],
            StrategyKey::Sftp => [
                'configs.root' => 'required',
                'configs.host' => 'required',
                'configs.port' => 'integer',
                'configs.username' => 'required',
                'configs.password' => 'nullable|string|max:30000',
                'configs.private_key' => 'nullable|string|max:30000',
                'configs.passphrase' => 'nullable|string|max:30000',
                'configs.host_fingerprint' => 'nullable|string|max:128',
                'configs.use_agent' => 'required|boolean',
            ],
            StrategyKey::Ftp => [
                'configs.root' => 'required',
                'configs.host' => 'required',
                'configs.port' => 'integer',
                'configs.username' => 'required',
                'configs.password' => $required('password'),
                'configs.ssl' => 'required|boolean',
                'configs.passive' => 'required|boolean',
            ],
            StrategyKey::Webdav => [
                'configs.base_uri' => 'required|url:http,https',
                'configs.username' => '',
                'configs.password' => 'nullable|string|max:30000',
                'configs.auth_type' => '',
                'configs.prefix' => '',
            ],
            StrategyKey::Minio => [
                'configs.access_key' => $required('access_key'),
                'configs.secret_key' => $required('secret_key'),
                'configs.endpoint' => 'nullable|url:http,https',
                'configs.region' => 'nullable|string|max:100',
                'configs.bucket' => 'required',
                'configs.bucket_endpoint' => 'nullable|boolean',
            ],
            default => [],
        });
    }

    public function attributes()
    {
        $array = [
            'name' => '名称',
            'intro' => '简介',
            'key' => '策略',
            'configs.url' => '访问网址',
            'configs.queries' => 'Url 额外参数',
        ];

        return array_merge($array, match((int)$this->input('key')) {
            StrategyKey::Local => [
                'configs.root' => '根目录路径',
            ],
            StrategyKey::S3 => [
                'configs.access_key_id' => 'AccessKeyId',
                'configs.secret_access_key' => 'SecretAccessKey',
                'configs.endpoint' => '连接地址',
                'configs.region' => '区域',
                'configs.bucket' => '储存桶名称',
            ],
            StrategyKey::Oss => [
                'configs.access_key_id' => 'AccessKeyId',
                'configs.access_key_secret' => 'AccessKeySecret',
                'configs.endpoint' => '地域节点域名',
                'configs.bucket' => 'Bucket 名称',
            ],
            StrategyKey::Cos => [
                'configs.app_id' => 'AppId',
                'configs.secret_id' => 'SecretId',
                'configs.secret_key' => 'SecretKey',
                'configs.region' => '所属地域',
                'configs.bucket' => '储存桶名称',
            ],
            StrategyKey::Kodo => [
                'configs.access_key' => 'AccessKey',
                'configs.secret_key' => 'SecretKey',
                'configs.bucket' => 'Bucket',
            ],
            StrategyKey::Uss => [
                'configs.service' => '服务名称',
                'configs.operator' => '操作员名称',
                'configs.password' => '操作员密码',
            ],
            StrategyKey::Sftp => [
                'configs.root' => '根目录路径',
                'configs.host' => '主机地址',
                'configs.port' => '连接端口',
                'configs.username' => '用户名',
                'configs.password' => '密码',
                'configs.private_key' => '密钥',
                'configs.passphrase' => '密钥口令',
                'configs.use_agent' => '使用代理',
            ],
            StrategyKey::Ftp => [
                'configs.root' => '根目录路径',
                'configs.host' => '主机地址',
                'configs.port' => '连接端口',
                'configs.username' => '用户名',
                'configs.password' => '密码',
                'configs.ssl' => '加密连接',
                'configs.passive' => '被动模式',
            ],
            StrategyKey::Webdav => [
                'configs.base_uri' => '连接地址',
                'configs.username' => '用户名',
                'configs.password' => '密码',
                'configs.auth_type' => '认证方式',
                'configs.prefix' => '前缀',
            ],
            StrategyKey::Minio => [
                'configs.access_key' => 'AccessKey',
                'configs.secret_key' => 'SecretKey',
                'configs.endpoint' => '连接地址',
                'configs.region' => '区域',
                'configs.bucket' => 'Bucket 名称',
                'configs.bucket_endpoint' => 'BucketEndpoint',
            ],
            default => [],
        });
    }
}
