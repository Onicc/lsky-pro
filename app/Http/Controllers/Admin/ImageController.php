<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ImagePermission;
use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ImageController extends Controller
{
    public function index(Request $request): View
    {
        $input = $request->query('keywords');
        $keywords = is_string($input) ? Str::limit(trim($input), 500, '') : '';
        $images = Image::query()->with(['user' => function (BelongsTo $belongsTo) {
            $belongsTo->withSum('images', 'size');
        }, 'album', 'group', 'strategy'])->when($keywords, function (Builder $builder, $keywords) {
            $words = [];
            $qualifiers = [
                'name:', 'album:', 'group:', 'strategy:', 'email:', 'extension:', 'md5:', 'sha1:', 'ip:', 'is:', 'order:',
            ];
            collect(array_filter(explode(' ', $keywords)))->filter(function ($keyword) use ($qualifiers, &$words) {
                if (Str::startsWith($keyword, $qualifiers)) {
                    return true;
                }
                $words[] = $keyword;
                return false;
            })->each(function ($filter) use ($builder) {
                match ($filter) {
                    'is:public' => $builder->where('permission', ImagePermission::Public),
                    'is:link' => $builder->where('permission', ImagePermission::LinkOnly),
                    'is:private' => $builder->where('permission', ImagePermission::Private),
                    'is:unhealthy' => $builder->where('is_unhealthy', 1),
                    'is:guest' => $builder->whereNull('user_id'),
                    'is:adminer' => $builder->whereHas('user', fn (Builder $builder) => $builder->where('is_adminer', 1)),
                    'order:earliest' => $builder->orderBy('created_at'),
                    'order:utmost' => $builder->orderByDesc('size'),
                    'order:least' => $builder->orderBy('size'),
                    default => 0,
                };

                [$qualifier, $value] = explode(':', $filter, 2);

                if ($value) {
                    $callback = fn (Builder $builder) => $builder->where('name', $value);
                    match ($qualifier) {
                        'name' => $builder->whereHas('user', $callback),
                        'album' => $builder->whereHas('album', $callback),
                        'group' => $builder->whereHas('group', $callback),
                        'strategy' => $builder->whereHas('strategy', $callback),
                        'email' => $builder->whereHas('user', fn (Builder $builder) => $builder->where('email', $value)),
                        'extension' => $builder->where('extension', $value),
                        'md5' => $builder->where('md5', $value),
                        'sha1' => $builder->where('sha1', $value),
                        'ip' => $builder->where('uploaded_ip', $value),
                        default => 0
                    };
                }
            });

            foreach ($words as $word) {
                $builder->where(fn (Builder $match) => $match->where('name', 'like', "%{$word}%")
                    ->orWhere('origin_name', 'like', "%{$word}%")
                    ->orWhere('alias_name', 'like', "%{$word}%"));
            }
        })->latest()->orderByDesc('id')->paginate(40);
        $images->getCollection()->each(function (Image $image) {
            $image->append(['url', 'pathname', 'thumb_url']);
            $image->album?->setVisible(['name']);
            $image->group?->setVisible(['name']);
            $image->strategy?->setVisible(['name']);
        });

        $images->appends(compact('keywords'));

        return view('admin.image.index', compact('images'));
    }

    public function delete(Request $request, UserService $images): Response
    {
        $deleted = $images->deleteImages([(int) $request->route('id')]);

        return $deleted
            ? $this->success('删除成功，储存对象待清理', ['deleted' => $deleted])
            : $this->fail('未找到需要删除的图片')->setStatusCode(404);
    }
}
