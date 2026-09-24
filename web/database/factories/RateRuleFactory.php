<?php

namespace Database\Factories;

use App\Models\RatePlan;
use App\Models\RateRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RateRule>
 */
class RateRuleFactory extends Factory
{
    protected $model = RateRule::class;

    public function definition(): array
    {
        return [
            'rate_plan_id' => RatePlan::factory(),
            'sequence' => 1,
            'from_minute' => 0,
            'to_minute' => 60,
            'unit' => 'hour',
            'price_minor' => 2000,
            'day_of_week_mask' => 127,
            'time_from' => null,
            'time_to' => null,
            'vehicle_type' => null,
            'spot_type' => null,
        ];
    }
}