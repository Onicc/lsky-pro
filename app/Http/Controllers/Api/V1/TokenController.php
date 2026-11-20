<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ConfigKey;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TokenController extends Controller
{
    private const ABILITIES = [
        'images:read', 'images:write', 'images:delete',
        'albums:read', 'albums:delete', 'profile:read', 'tokens:manage',
    ];

    public function store(Request $request): Response
    {
        try {
            $validated = $request->validate([
                'email' => ['bail', 'required', 'string', 'max:254', 'email'],
                'password' => ['required', 'string', 'max:1024'],
                'name' => ['nullable', 'string', 'max:80'],
                'abilities' => ['sometimes', 'array', 'min:1', 'max:7'],
                'abilities.*' => ['required', 'distinct', Rule::in(self::ABILITIES)],
            ]);
        } catch (ValidationException $e) {
            return $this->fail($e->validator->errors()->first());
        }

        $user = User::query()->where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)
            || $user->status !== UserStatus::Normal
            || (Utils::config(ConfigKey::IsUserNeedVerify) && ! $user->hasVerifiedEmail())) {
            return $this->fail('The email address or password is incorrect, or the account is unavailable.');
        }

        $expiresAt = now()->addDays(90);
        $token = $user->createToken($validated['name'] ?? 'API', $validated['abilities'] ?? self::ABILITIES, $expiresAt);

        return $this->success('success', ['token' => $token->plainTextToken, 'expires_at' => $expiresAt->toIso8601String()]);
    }

    public function index(Request $request): Response
    {
        $tokens = $request->user()->tokens()->latest('id')->get([
            'id', 'name', 'abilities', 'last_used_at', 'expires_at', 'created_at',
        ]);

        return $this->success('success', $tokens);
    }

    public function destroy(Request $request): Response
    {
        $removed = $request->user()->tokens()->whereKey($request->route('id'))->delete();

        return $removed ? $this->success() : $this->fail('Token not found.')->setStatusCode(404);
    }

    public function clear(Request $request): Response
    {
        $request->user()->tokens()->delete();

        return $this->success();
    }
}
