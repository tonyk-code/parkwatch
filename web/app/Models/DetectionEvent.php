<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetectionEvent extends Model
{
    use HasFactory;

    protected $table = 'detection_events';

    public $timestamps = false;

    protected $fillable = [
        'event_uuid',
        'camera_id',
        'spot_id',
        'site_id',
        'event_type',
        'payload',
        'confidence',
        'polygon_version',
        'frame_ref',
        'observed_at',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'confidence' => 'decimal:3',
            'observed_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function spot(): BelongsTo
    {
        return $this->belongsTo(Spot::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}