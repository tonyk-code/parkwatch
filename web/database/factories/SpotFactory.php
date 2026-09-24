<?php

namespace Database\Factories;

use App\Models\Spot;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Spot>
 */
class SpotFactory extends Factory
{
    protected $model = Spot::class;

    public function definition(): array
    {
        return [
            'zone_id' => Zone::factory(),
            'camera_id' => null,
            'code' => fake()->unique()->lexify('SPOT??'),
            'spot_type' => 'standard',
            'polygon' => [
                ['x' => 0, 'y' => 0],
                ['x' => 1, 'y' => 0],
                ['x' => 1, 'y' => 1],
                ['x' => 0, 'y' => 1],
            ],
            'polygon_version' => 1,
            'display_x' => 0,
            'display_y' => 0,
            'is_bookable' => true,
            'is_active' => true,
        ];
    }
}