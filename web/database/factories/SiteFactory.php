<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->company() . ' Parking',
            'code' => strtoupper(fake()->unique()->lexify('SITE??')),
            'address' => fake()->address(),
            'latitude' => 9.0192,
            'longitude' => 38.7525,
            'timezone' => 'Africa/Addis_Ababa',
            'total_capacity' => 0,
            'session_mode' => 'spot',
            'occupancy_enter_seconds' => 10,
            'occupancy_exit_seconds' => 45,
            'opens_at' => null,
            'closes_at' => null,
            'is_24_hours' => true,
            'is_active' => true,
            'settings' => [],
        ];
    }
}