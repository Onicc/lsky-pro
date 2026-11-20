<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlbumRequest;
use App\Models\Album;
use App\Models\User;
use App\Services\AlbumService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlbumController extends Controller
{
    public function albums(): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $albums = $user->albums()->latest()->orderByDesc('id')->paginate(40);
        $albums->getCollection()->each(function (Album $album) {
            $album->setVisible(['id', 'name', 'intro', 'image_num']);
        });
        return $this->success('success', compact('albums'));
    }

    public function create(AlbumRequest $request): Response
    {
        /** @var User $user */
        $user = Auth::user();
        DB::transaction(function () use ($user, $request) {
            $owner = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $owner->albums()->create($this->attributes($request));
            $owner->album_num = $owner->albums()->count();
            $owner->saveOrFail();
        });

        return $this->success('创建成功');
    }

    public function update(AlbumRequest $request): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $album = $user->albums()->find($request->route('id'));
        if (is_null($album)) {
            return $this->fail('不存在的相册');
        }
        $album->update($this->attributes($request));
        return $this->success('修改成功');
    }

    private function attributes(AlbumRequest $request): array
    {
        $validated = $request->validated();
        if (array_key_exists('intro', $validated)) {
            $validated['intro'] ??= '';
        }

        return $validated;
    }

    public function delete(Request $request, AlbumService $albums): Response
    {
        return $albums->delete($request->user(), (int) $request->route('id'))
            ? $this->success('删除成功')
            : $this->fail('不存在的相册');
    }
}
