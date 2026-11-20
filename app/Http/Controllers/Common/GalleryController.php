<?php

namespace App\Http\Controllers\Common;

use App\Enums\ImagePermission;
use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $images = Image::query()
            ->with(['user', 'group', 'strategy'])
            ->whereNotNull('user_id')
            ->where('is_unhealthy', false)
            ->where('permission', ImagePermission::Public)
            ->latest()
            ->orderByDesc('id')
            ->simplePaginate(40);
        return view('common.gallery', compact('images'));
    }
}
