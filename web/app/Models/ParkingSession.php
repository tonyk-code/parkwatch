<?php

namespace App\Models;

use App\Enums\SessionMode;
use App\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingSession extends Model
{
    use HasFactory;

    protected $table = 'parking_sessions';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'zone_id',
        'spot_id',
        'vehicle_id',
        'user_id',
        'rate_plan_id',
        'permit_id',
        'entry_gate_id',
        'exit_gate_id',
        'reference_code',
        'entered_at',
        'exited_at',
        'status',
        'session_mode',
        'amount_due',
        'amount_paid',
        'amount_waived',
        'currency',
        'entry_plate_read',
        'exit_plate_read',
        'entry_media_id',
        'exit_media_id',
        'opened_by',
        'closed_by',
        'closed_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => SessionStatus::class,
            'session_mode' => SessionMode::class,
            'entered_at' => 'datetime',
            'exited_at' => 'datetime',
            'amount_due' => 'integer',
            'amount_paid' => 'integer',
            'amount_waived' => 'integer',
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

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function permit(): BelongsTo
    {
        return $this->belongsTo(Permit::class);
    }

    public function entryGate(): BelongsTo
    {
        return $this->belongsTo(Gate::class, 'entry_gate_id');
    }

    public function exitGate(): BelongsTo
    {
        return $this->belongsTo(Gate::class, 'exit_gate_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(SessionEvent::class, 'session_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'session_id');
    }

    public function plateReads(): HasMany
    {
        return $this->hasMany(PlateRead::class, 'matched_session_id');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'session_id');
    }

    public function spotState(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SpotState::class, 'session_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'session_id');
    }

    public function entryMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'entry_media_id');
    }

    public function exitMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'exit_media_id');
    }
}