<?php

namespace Database\Factories;

use App\Models\RatePlan;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RatePlan>
 */
class RatePlanFactory extends Factory
{
    protected $model = RatePlan::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => 'Standard Parking',
            'currency' => 'ETB',
            'grace_minutes' => 15,
            'daily_max_minor' => 10000,
            'rounding' => 'up',
            'rounding_increment_minutes' => 60,
            'priority' => 0,
            'valid_from' => now(),
            'valid_to' => null,
            'is_active' => true,
        ];
    }
}