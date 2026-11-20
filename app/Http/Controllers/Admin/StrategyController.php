<?php

namespace App\Http\Controllers\Admin;

use App\Casts\StrategyConfig;
use App\Http\Controllers\Controller;
use App\Models\MediaDeletion;
use App\Models\MediaWrite;
use App\Http\Requests\Admin\StrategyRequest;
use App\Models\Strategy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class StrategyController extends Controller
{
    public function index(Request $request): View
    {
        $keywords = $request->query('keywords');
        $strategies = Strategy::query()->when($keywords, function (Builder $builder, $keywords) {
            $builder->where('name', 'like', "%{$keywords}%")->orWhere('intro', 'like', "%{$keywords}%");
        })->withCount('images')->withSum('images', 'size')->latest()->paginate();

        $strategies->appends(compact('keywords'));

        return view('admin.strategy.index', compact('strategies'));
    }

    public function add(): View
    {
        return view('admin.strategy.add');
    }

    public function edit(Request $request): View
    {
        /** @var Strategy $strategy */
        $strategy = Strategy::query()->findOrFail($request->route('id'));
        return view('admin.strategy.edit', compact('strategy'));
    }

    public function create(StrategyRequest $request): Response
    {
        $validated = $request->validated();
        $strategy = new Strategy($validated);
        DB::transaction(function () use ($strategy, $validated) {
            $strategy->save();
            $strategy->groups()->attach($validated['groups'] ?? []);
        });
        return $this->success('创建成功');
    }

    public function update(StrategyRequest $request): Response
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            $strategy = Strategy::query()->whereKey($request->route('id'))->lockForUpdate()->firstOrFail();
            $oldDriver = $strategy->key;
            $oldLocation = $strategy->storageLocation();
            $oldPrivacy = $strategy->supportsPrivateMedia();
            $configs = $validated['configs'] ?? [];

            if ($oldDriver === (int) $validated['key']) {
                foreach (StrategyConfig::SECRET_FIELDS as $name) {
                    if (! filled($configs[$name] ?? null) && $strategy->configs->has($name)) {
                        $configs[$name] = $strategy->configs->get($name);
                    }
                }
            }

            $strategy->fill(Arr::except($validated, 'configs'));
            $strategy->configs = $configs;
            if ($strategy->key !== $oldDriver || $strategy->storageLocation() !== $oldLocation
                || ($oldPrivacy && ! $strategy->supportsPrivateMedia())) {
                if ($this->hasMedia($strategy)) {
                    return $this->fail('该策略仍有图片或待清理文件，请先完成数据迁移');
                }
            }

            $strategy->save();
            $strategy->groups()->sync($validated['groups'] ?? []);

            return $this->success('保存成功');
        });
    }

    public function delete(Request $request): Response
    {
        return DB::transaction(function () use ($request) {
            $strategy = Strategy::query()->whereKey($request->route('id'))->lockForUpdate()->firstOrFail();

            if ($this->hasMedia($strategy)) {
                return $this->fail('该策略仍有图片或待清理文件，请先完成数据迁移');
            }

            $strategy->delete();

            return $this->success('删除成功');
        });
    }
    private function hasMedia(Strategy $strategy): bool
    {
        return $strategy->images()->exists()
            || MediaDeletion::query()->where('strategy_id', $strategy->id)->exists()
            || MediaWrite::query()->where('strategy_id', $strategy->id)->exists();
    }
}
