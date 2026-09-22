<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SessionEvent extends Model
{
    use HasFactory;

    protected $table = 'session_events';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'event_type',
        'from_status',
        'to_status',
        'payload',
        'actor_type',
        'actor_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ParkingSession::class, 'session_id');
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}