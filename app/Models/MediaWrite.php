<?php

namespace App\Models;

class MediaWrite extends Model
{
    protected $fillable = [
        'user_id', 'strategy_id', 'image_key', 'path', 'name', 'size',
        'idempotency_key', 'request_fingerprint', 'write_attempted', 'expires_at',
    ];

    protected $casts = [
        'write_attempted' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function retire(): void
    {
        if ($this->write_attempted) {
            MediaDeletion::query()->firstOrCreate(['image_key' => $this->image_key], [
                'strategy_id' => $this->strategy_id,
                'path' => $this->path,
                'name' => $this->name,
                'object_managed' => true,
                'available_at' => now(),
            ]);
        }

        $this->delete();
    }
}
