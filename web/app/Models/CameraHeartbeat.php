<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CameraHeartbeat extends Model
{
    use HasFactory;

    protected $table = 'camera_heartbeats';

    public $timestamps = false;

    protected $fillable = [
        'camera_id',
        'reported_at',
        'fps_actual',
        'frames_processed',
        'buffer_depth',
        'drift_score',
        'worker_version',
    ];

    protected function casts(): array
    {
        return [
            'fps_actual' => 'decimal:2',
            'reported_at' => 'datetime',
            'drift_score' => 'decimal:4',
        ];
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }
}