<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const STANDARD = ['jpeg', 'jpg', 'png', 'gif', 'tif', 'bmp', 'ico', 'psd', 'webp'];

    public function up(): void
    {
        $this->updateFormats(self::STANDARD, [...self::STANDARD, 'heic']);
    }

    public function down(): void
    {
        $this->updateFormats([...self::STANDARD, 'heic'], self::STANDARD);
    }

    private function updateFormats(array $from, array $to): void
    {
        sort($from);
        foreach (DB::table('groups')->select('id', 'configs')->lazyById() as $group) {
            $configs = json_decode($group->configs, true, flags: JSON_THROW_ON_ERROR);
            $formats = $configs['accepted_file_suffixes'] ?? [];
            if (! is_array($formats)) {
                continue;
            }
            sort($formats);
            if ($formats === $from) {
                $configs['accepted_file_suffixes'] = $to;
                DB::table('groups')->where('id', $group->id)->update([
                    'configs' => json_encode($configs, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }
};
