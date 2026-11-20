<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConsoleController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $now = now();
        $yesterday = $today->copy()->subDay();
        $numbers = [
            'today' => Image::query()->whereBetween('created_at', [$today, $now])->count(),
            'yesterday' => Image::query()->where('created_at', '>=', $yesterday)->where('created_at', '<', $today)->count(),
            'week' => Image::query()->whereBetween('created_at', [$today->copy()->startOfWeek(), $now])->count(),
            'month' => Image::query()->whereBetween('created_at', [$today->copy()->startOfMonth(), $now])->count(),
        ];

        $start = $today->copy()->subDays(29);
        $dates = array_map(fn (int $offset): string => $start->copy()->addDays($offset)->toDateString(), range(0, 29));
        $day = DB::getDriverName() === 'sqlsrv' ? 'CAST(created_at AS DATE)' : 'DATE(created_at)';
        $uploads = Image::query()->whereBetween('created_at', [$start, $now])
            ->selectRaw("$day AS chart_day")
            ->selectRaw('SUM(CASE WHEN user_id IS NULL THEN 1 ELSE 0 END) AS guest_total')
            ->selectRaw('SUM(CASE WHEN user_id IS NOT NULL THEN 1 ELSE 0 END) AS user_total')
            ->groupByRaw($day)->get()->keyBy('chart_day');
        $registrations = User::query()->whereBetween('created_at', [$start, $now])
            ->selectRaw("$day AS chart_day, COUNT(*) AS total")
            ->groupByRaw($day)->pluck('total', 'chart_day');

        $fields = ['游客上传', '用户上传', '新用户'];
        $datasets = [
            ['name' => $fields[0], 'type' => 'line', 'data' => array_map(fn ($date) => (int) ($uploads[$date]->guest_total ?? 0), $dates)],
            ['name' => $fields[1], 'type' => 'line', 'data' => array_map(fn ($date) => (int) ($uploads[$date]->user_total ?? 0), $dates)],
            ['name' => $fields[2], 'type' => 'line', 'data' => array_map(fn ($date) => (int) ($registrations[$date] ?? 0), $dates)],
        ];

        return view('admin.console.index', compact('fields', 'numbers', 'dates', 'datasets'));
    }
}
