<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Image;
use App\Models\MediaDeletion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function deleteImages(array $keys, ?User $user = null, string $field = 'id'): int
    {
        return DB::transaction(function () use ($keys, $user, $field) {
            $images = Image::query()
                ->when($user, fn (Builder $query, User $owner) => $query->where('user_id', $owner->id))
                ->whereIn($field, $keys)->orderBy('id')->lockForUpdate()->get();

            foreach ($images as $image) {
                MediaDeletion::query()->create([
                    'strategy_id' => $image->strategy_id,
                    'image_key' => $image->key,
                    'path' => $image->path,
                    'name' => $image->name,
                    'object_managed' => $image->object_managed,
                    'available_at' => now(),
                ]);

                $image->delete();
            }

            foreach ($images->whereNotNull('album_id')->countBy('album_id') as $albumId => $count) {
                Album::query()->whereKey($albumId)->where('image_num', '>=', $count)->decrement('image_num', $count);
            }
            foreach ($images->whereNotNull('user_id')->countBy('user_id') as $ownerId => $count) {
                User::query()->whereKey($ownerId)->where('image_num', '>=', $count)->decrement('image_num', $count);
            }

            return $images->count();
        }, 3);
    }
}
