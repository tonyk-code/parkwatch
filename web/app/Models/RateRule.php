<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateRule extends Model
{
    use HasFactory;

    protected $table = 'rate_rules';

    public $timestamps = false;

    protected $fillable = [
        'rate_plan_id',
        'sequence',
        'from_minute',
        'to_minute',
        'unit',
        'price_minor',
        'day_of_week_mask',
        'time_from',
        'time_to',
        'vehicle_type',
        'spot_type',
    ];

    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
        ];
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }
}