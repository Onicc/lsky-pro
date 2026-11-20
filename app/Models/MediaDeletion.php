<?php

namespace App\Models;

class MediaDeletion extends Model
{
    protected $fillable = [
        'strategy_id', 'image_key', 'path', 'name', 'object_managed', 'attempts', 'available_at',
    ];

    protected $casts = [
        'object_managed' => 'boolean',
        'available_at' => 'datetime',
    ];
}
