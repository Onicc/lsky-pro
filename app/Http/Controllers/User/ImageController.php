<?php

namespace App\Http\Controllers\User;

use App\Enums\ImagePermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageRenameRequest;
use App\Models\Album;
use App\Models\Image;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class ImageController extends Controller
{
    public function index(): View
    {
        return view('user.images');
    }

    public function images(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $images = $user->images()->filter($request)->with('group', 'strategy')->paginate(40);
        $images->getCollection()->each(function (Image $image) {
            // 图片宽高过小会导致前端排版异常
            $image->width = max($image->width, 200);
            $image->height = max($image->height, 200);

            $image->human_date = $image->created_at->diffForHumans();
            $image->date = $image->created_at->format('Y-m-d H:i:s');
            $image->append(['url', 'thumb_url', 'filename', 'links'])->setVisible([
                'id', 'filename', 'url', 'thumb_url', 'human_date', 'date', 'size', 'width', 'height', 'links'
            ]);
        });
        return $this->success('success', compact('images'));
    }

    public function image(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Image $image */
        if (!$image = $user->images()->find($request->route('id'))) {
            return $this->fail('未找到该图片');
        }
        $image->strategy?->setVisible(['name']);
        $image->album?->setVisible(['name']);
        $image->append(['url', 'thumb_url', 'filename', 'links'])->setVisible([
            'id', 'filename', 'origin_name', 'url', 'thumb_url', 'width', 'height', 'size', 'mimetype', 'md5', 'sha1',
            'permission', 'strategy', 'album', 'uploaded_ip', 'links', 'created_at'
        ]);
        return $this->success('success', compact('image'));
    }

    public function permission(Request $request): Response
    {
        $user = $request->user();
        $permissions = [
            'public' => ImagePermission::Public,
            'private' => ImagePermission::Private,
            'link' => ImagePermission::LinkOnly,
        ];
        $permission = $request->input('permission');
        $ids = $this->imageIds($request->input('ids'));

        if (! is_string($permission) || ! isset($permissions[$permission]) || ! $ids) {
            return $this->fail('未选择有效的图片或访问权限');
        }

        return DB::transaction(function () use ($user, $ids, $permissions, $permission) {
            $images = $user->images()->with('strategy')->whereKey($ids)->orderBy('id')->lockForUpdate()->get();

            if ($images->count() !== count($ids)) {
                return $this->fail('所选图片已不存在');
            }
            if ($permission === 'private' && $images->contains(fn (Image $image) => ! $image->supportsPrivateMedia())) {
                return $this->fail('所选图片的储存位置仍可匿名读取，请先迁移到私有储存');
            }

            $user->images()->whereKey($ids)->update([
                'permission' => $permissions[$permission],
                'share_version' => bin2hex(random_bytes(16)),
            ]);

            return $this->success('设置成功');
        });
    }

    public function share(Request $request, int $id): Response
    {
        $image = $request->user()->images()->findOrFail($id);
        if ($image->is_unhealthy || ! $image->strategy_id || $image->permission !== ImagePermission::Private) {
            return $this->fail('仅私有图片支持临时分享，请复制其他图片的直接链接');
        }

        $expiresAt = now()->addMinutes(10);
        $link = URL::temporarySignedRoute('media.original', $expiresAt, [
            'key' => $image->key,
            'extension' => $image->extension,
            'version' => $image->share_version,
        ]);

        return $this->success('分享链接在 10 分钟内有效', ['url' => $link, 'expires_at' => $expiresAt->toIso8601String()]);
    }

    public function rename(ImageRenameRequest $request): Response
    {
        $image = $request->user()->images()->find($request->input('id'));

        if (! $image) {
            return $this->fail('未找到该图片');
        }

        $image->alias_name = $request->input('name');
        $image->save();

        return $this->success('重命名成功', $image->only('id', 'filename'));
    }

    public function movement(Request $request): Response
    {
        $user = $request->user();
        $ids = $this->imageIds($request->input('selected'));
        $destination = $request->input('id');

        if (! $ids || ($destination && (! is_scalar($destination) || ! ctype_digit((string) $destination)))) {
            return $this->fail('所选图片或相册无效');
        }

        return DB::transaction(function () use ($user, $ids, $destination) {
            $album = $destination ? $user->albums()->whereKey($destination)->lockForUpdate()->first() : null;
            if ($destination && ! $album) {
                return $this->fail('目标相册已不存在');
            }

            $images = $user->images()->whereKey($ids)->orderBy('id')->lockForUpdate()->get(['id', 'album_id']);
            if ($images->count() !== count($ids)) {
                return $this->fail('所选图片已不存在');
            }

            $albumIds = $images->pluck('album_id')->push($album?->id)->filter()->unique()->sort()->all();
            $user->images()->whereKey($ids)->update(['album_id' => $album?->id]);

            $user->albums()->whereKey($albumIds)->withCount('images')->get()->each(function (Album $album) {
                $album->update(['image_num' => $album->images_count]);
            });

            return $this->success('移动成功');
        }, 3);
    }

    public function delete(Request $request, UserService $users): Response
    {
        $ids = $this->imageIds($request->input('ids', $request->all()));
        if (! $ids) {
            return $this->fail('请选择需要删除的图片');
        }

        $deleted = $users->deleteImages($ids, $request->user());
        if (! $deleted) {
            return $this->fail('未找到需要删除的图片');
        }

        return $this->success('删除成功，储存对象待清理', ['deleted' => $deleted]);
    }

    private function imageIds(mixed $input): array
    {
        if (! is_array($input) || count($input) > 500) {
            return [];
        }

        foreach ($input as $id) {
            if (! is_scalar($id) || ! ctype_digit((string) $id) || (int) $id < 1) {
                return [];
            }
        }

        return array_values(array_unique(array_map('intval', $input)));
    }
}
