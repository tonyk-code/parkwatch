<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlateRead extends Model
{
    use HasFactory;

    protected $table = 'plate_reads';

    public $timestamps = false;

    protected $fillable = [
        'camera_id',
        'gate_id',
        'site_id',
        'matched_vehicle_id',
        'matched_session_id',
        'media_id',
        'raw_text',
        'normalised_text',
        'confidence',
        'match_distance',
        'match_status',
        'observed_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:3',
            'observed_at' => 'datetime',
        ];
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function gate(): BelongsTo
    {
        return $this->belongsTo(Gate::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function matchedVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'matched_vehicle_id');
    }

    public function matchedSession(): BelongsTo
    {
        return $this->belongsTo(ParkingSession::class, 'matched_session_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_id');
    }
}