<?php

use App\Casts\StrategyConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('strategies')->select(['id', 'configs'])->chunkById(500, function ($strategies) {
            foreach ($strategies as $strategy) {
                $configs = json_decode($strategy->configs, true, flags: JSON_THROW_ON_ERROR);
                DB::table('strategies')->where('id', $strategy->id)->update([
                    'configs' => StrategyConfig::protect($configs),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('strategies')->select(['id', 'configs'])->chunkById(500, function ($strategies) {
            foreach ($strategies as $strategy) {
                $configs = json_decode($strategy->configs, true, flags: JSON_THROW_ON_ERROR);
                foreach (StrategyConfig::SECRET_FIELDS as $key) {
                    if (isset($configs[$key]['encrypted'])) {
                        $configs[$key] = Crypt::decryptString($configs[$key]['encrypted']);
                    }
                }
                DB::table('strategies')->where('id', $strategy->id)->update([
                    'configs' => json_encode($configs, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                ]);
            }
        });
    }
};
