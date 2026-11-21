<?php

namespace App\Models;

use App\Enums\ImagePermission;
use App\Services\ImageService;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use League\Flysystem\Filesystem;

/**
 * @property int $id
 * @property int $user_id
 * @property int $album_id
 * @property int $group_id
 * @property int $strategy_id
 * @property string $key
 * @property string $path
 * @property string $name
 * @property string $pathname
 * @property string $origin_name
 * @property string $alias_name
 * @property string $filename
 * @property float $size
 * @property string $mimetype
 * @property string $extension
 * @property string $md5
 * @property string $sha1
 * @property integer $width
 * @property integer $height
 * @property string $url
 * @property string $thumb_url
 * @property Collection $links
 * @property int $permission
 * @property boolean $is_unhealthy
 * @property string $uploaded_ip
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property-read User $user
 * @property-read Album $album
 * @property-read Group $group
 * @property-read Strategy $strategy
 */
class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'path',
        'name',
        'origin_name',
        'alias_name',
        'size',
        'mimetype',
        'extension',
        'md5',
        'sha1',
        'sha256',
        'width',
        'height',
        'permission',
        'is_unhealthy',
        'uploaded_ip',
    ];

    protected $hidden = [
        'user_id',
        'album_id',
        'group_id',
        'strategy_id',
        'idempotency_key',
        'request_fingerprint',
        'thumbnail_pending_at',
        'thumbnail_attempts',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'album_id' => 'integer',
        'group_id' => 'integer',
        'strategy_id' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'size' => 'float',
        'is_unhealthy' => 'bool',
        'permission' => 'integer',
        'object_managed' => 'boolean',
        'thumbnail_pending_at' => 'datetime',
        'thumbnail_attempts' => 'integer',
    ];

    protected ?Filesystem $filesystem = null;

    protected static function booted()
    {
        static::creating(function (self $image) {
            $image->key ??= bin2hex(random_bytes(16));
        });
    }

    public function scopeFilter(Builder $builder, Request $request)
    {
        return $builder->when($request->query('order') ?: 'newest', function (Builder $builder, $order) {
            switch ($order) {
                case 'earliest':
                    $builder->orderBy('created_at');
                    break;
                case 'utmost':
                    $builder->orderByDesc('size');
                    break;
                case 'least':
                    $builder->orderBy('size');
                    break;
                default:
                    $builder->latest();
            }
        })->when($request->query('permission') ?: 'all', function (Builder $builder, $permission) {
            switch ($permission) {
                case 'public':
                    $builder->where('permission', ImagePermission::Public);
                    break;
                case 'link':
                    $builder->where('permission', ImagePermission::LinkOnly);
                    break;
                case 'private':
                    $builder->where('permission', ImagePermission::Private);
                    break;
            }
        })->when(is_string($request->query('keyword')) ? $request->query('keyword') : null, function (Builder $builder, $keyword) {
            $builder->where(function (Builder $query) use ($keyword) {
                $query->where('origin_name', 'like', "%{$keyword}%")
                    ->orWhere('alias_name', 'like', "%{$keyword}%");
            });
        })->when((int) $request->query('album_id'), function (Builder $builder, $albumId) {
            $builder->where('album_id', $albumId);
        }, function (Builder $builder) {
            $builder->whereNull('album_id');
        })->orderByDesc('id');
    }

    public function filename(): Attribute
    {
        return new Attribute(fn() => $this->alias_name ?: $this->origin_name);
    }

    public function pathname(): Attribute
    {
        $path = $this->path ? "{$this->path}/" : '';
        return new Attribute(fn() => "{$path}{$this->name}");
    }

    public function url(): Attribute
    {
        return new Attribute(fn () => route('media.original', [
            'key' => $this->key,
            'extension' => $this->extension,
        ]));
    }

    public function thumbUrl(): Attribute
    {
        return new Attribute(fn () => route('media.thumbnail', ['key' => $this->key]));
    }

    public function links(): Attribute
    {
        return new Attribute(function () {
            $url = $this->url;
            $name = $this->origin_name;
            $html = '<img src="'.e($url).'" alt="'.e($name).'" title="'.e($name).'" />';
            $markdownName = str_replace(['\\', '[', ']', "\r", "\n"], ['\\\\', '\\[', '\\]', ' ', ' '], $name);
            $markdownUrl = str_replace(['(', ')', ' '], ['%28', '%29', '%20'], $url);

            return collect([
                'url' => $url,
                'html' => e($html),
                'bbcode' => "[img]{$url}[/img]",
                'markdown' => "![{$markdownName}]({$markdownUrl})",
                'markdown_with_link' => "[![{$markdownName}]({$markdownUrl})]({$markdownUrl})",
                'thumbnail_url' => $this->thumb_url,
            ]);
        });
    }

    public function filesystem(): Filesystem
    {
        if (! $this->strategy) {
            throw new NotFoundHttpException();
        }

        return $this->filesystem ??= new Filesystem(
            app(ImageService::class)->getAdapter($this->strategy, true),
        );
    }

    public function storagePath(): string
    {
        return '_lsky-media/v1/'.$this->pathname;
    }

    public function supportsPrivateMedia(): bool
    {
        return $this->strategy?->supportsPrivateMedia() && $this->object_managed;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class, 'album_id', 'id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class, 'strategy_id', 'id');
    }

    public function thumbnailPath(): string
    {
        return config('image.private_root').'/thumbnails/'.$this->key.'.webp';
    }
}
