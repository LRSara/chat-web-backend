<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'nick',
        'connected_at',
        'disconnected_at',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function scopeOnline($query)
    {
        return $query->whereNull('disconnected_at');
    }
}
