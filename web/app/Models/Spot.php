<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Spot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'zone_id',
        'camera_id',
        'code',
        'spot_type',
        'polygon',
        'polygon_version',
        'display_x',
        'display_y',
        'is_bookable',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'polygon' => 'array',
            'display_x' => 'decimal:3',
            'display_y' => 'decimal:3',
            'is_bookable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function state(): HasOne
    {
        return $this->hasOne(SpotState::class);
    }

    public function parkingSessions(): HasMany
    {
        return $this->hasMany(ParkingSession::class);
    }

    public function detectionEvents(): HasMany
    {
        return $this->hasMany(DetectionEvent::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }
}