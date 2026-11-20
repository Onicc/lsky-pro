<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $keywords = $request->query('keywords');
        $users = User::query()->when($status > -1, function (Builder $builder) use ($status) {
            $builder->where('status', $status);
        })->when($keywords, function (Builder $builder, $keywords) {
            $builder->where('name', 'like', "%{$keywords}%")->orWhere('email', 'like', "%{$keywords}%");
        })->with('group')->withSum('images', 'size')->latest()->paginate();
        $users->getCollection()->each(function (User $user) {
            $user->group->setVisible(['name']);
        });
        $statuses = [-1 => '全部', 1 => '正常', 0 => '冻结'];

        $users->appends(compact('status', 'keywords'));

        return view('admin.user.index', compact('users', 'statuses'));
    }

    public function edit(Request $request): View
    {
        $user = User::query()->findOrFail($request->route('id'));
        return view('admin.user.edit', compact('user'));
    }

    public function update(UserRequest $request): Response
    {
        /** @var User $user */
        $user = User::query()->findOrFail($request->route('id'));
        $validated = $request->validated();

        $passwordChanged = ! empty($validated['password']);
        if ($passwordChanged) {
            $user->forceFill([
                'password' => Hash::make($validated['password']),
                'remember_token' => Str::random(60),
            ]);

        }

        unset($validated['password']);
        $user->fill($validated);
        $user->group_id = $validated['group_id'];

        DB::transaction(function () use ($user, $passwordChanged): void {
            $user->saveOrFail();
            if ($passwordChanged) {
                event(new PasswordReset($user));
            } elseif ($user->wasChanged('status') && $user->status !== UserStatus::Normal) {
                $user->tokens()->delete();
            }
        });
        return $this->success('保存成功');
    }

    public function delete(Request $request): Response
    {
        /** @var User $user */
        if ($user = User::query()->find($request->route('id'))) {
            DB::transaction(function () use ($user) {
                $user->images()->update(['user_id' => null]);
                $user->albums()->delete();
                $user->tokens()->delete();
                $user->delete();
            });
        }
        return $this->success('删除成功');
    }
}
