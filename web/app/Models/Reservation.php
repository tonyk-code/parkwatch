<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'zone_id',
        'spot_id',
        'user_id',
        'vehicle_id',
        'payment_id',
        'session_id',
        'starts_at',
        'expires_at',
        'status',
        'amount_minor',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'amount_minor' => 'integer',
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

    public function spot(): BelongsTo
    {
        return $this->belongsTo(Spot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ParkingSession::class);
    }

    public function spotState(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SpotState::class, 'reservation_id');
    }
}