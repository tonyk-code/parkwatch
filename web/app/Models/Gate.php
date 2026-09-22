<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gate extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'camera_id',
        'name',
        'direction',
        'has_barrier',
        'barrier_endpoint',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'has_barrier' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function entrySessions(): HasMany
    {
        return $this->hasMany(
            ParkingSession::class,
            'entry_gate_id'
        );
    }

    public function exitSessions(): HasMany
    {
        return $this->hasMany(
            ParkingSession::class,
            'exit_gate_id'
        );
    }

    public function plateReads(): HasMany
    {
        return $this->hasMany(PlateRead::class);
    }
}