<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionValidation extends Model
{
    use HasFactory;

    protected $table = 'session_validations';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'validation_id',
        'applied_minor',
        'applied_by',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'applied_minor' => 'integer',
            'applied_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ParkingSession::class);
    }

    public function validation(): BelongsTo
    {
        return $this->belongsTo(Validation::class);
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}