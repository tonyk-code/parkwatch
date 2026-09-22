<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Camera extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'zone_id',
        'gate_id',
        'name',
        'stream_url',
        'stream_protocol',
        'resolution_w',
        'resolution_h',
        'fps_target',
        'purpose',
        'reference_frame_path',
        'calibrated_at',
        'calibrated_by',
        'status',
        'last_heartbeat_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'calibrated_at' => 'datetime',
            'last_heartbeat_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function gate(): BelongsTo
    {
        return $this->belongsTo(Gate::class);
    }

    public function spots(): HasMany
    {
        return $this->hasMany(Spot::class);
    }

    public function detectionEvents(): HasMany
    {
        return $this->hasMany(DetectionEvent::class);
    }

    public function ingestReceipts(): HasMany
    {
        return $this->hasMany(IngestReceipt::class);
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(CameraHeartbeat::class);
    }

    public function plateReads(): HasMany
    {
        return $this->hasMany(PlateRead::class);
    }
}