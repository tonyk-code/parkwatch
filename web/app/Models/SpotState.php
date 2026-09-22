<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotState extends Model
{
    use HasFactory;

    protected $table = 'spot_states';

    protected $primaryKey = 'spot_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'spot_id',
        'session_id',
        'reservation_id',
        'camera_id',
        'status',
        'confidence',
        'source',
        'manual_override',
        'override_by',
        'override_reason',
        'override_until',
        'polygon_version',
        'last_changed_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:3',
            'manual_override' => 'boolean',
            'override_until' => 'datetime',
            'last_changed_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function spot(): BelongsTo
    {
        return $this->belongsTo(Spot::class, 'spot_id');
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ParkingSession::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}