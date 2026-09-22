<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RatePlan extends Model
{
    use HasFactory;

    protected $table = 'rate_plans';

    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'name',
        'currency',
        'grace_minutes',
        'daily_max_minor',
        'rounding',
        'rounding_increment_minutes',
        'priority',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_to' => 'datetime',
            'is_active' => 'boolean',
            'daily_max_minor' => 'integer',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(RateRule::class);
    }

    public function parkingSessions(): HasMany
    {
        return $this->hasMany(ParkingSession::class);
    }
}