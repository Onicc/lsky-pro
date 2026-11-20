<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AlbumService
{
    public function delete(User $user, int $id): bool
    {
        return DB::transaction(function () use ($user, $id): bool {
            $owner = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $album = $owner->albums()->whereKey($id)->lockForUpdate()->first();
            if ($album === null) {
                return false;
            }

            $album->images()->update(['album_id' => null]);
            $album->deleteOrFail();
            $owner->album_num = $owner->albums()->count();
            $owner->saveOrFail();

            return true;
        }, 3);
    }
}
