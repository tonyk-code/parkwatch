<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Zone>
 */
class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => 'Zone ' . fake()->unique()->randomLetter(),
            'code' => fake()->unique()->lexify('ZONE??'),
            'floor_label' => 'Level ' . fake()->numberBetween(1, 3),
            'capacity' => 20,
            'display_order' => 0,
            'map_image_path' => null,
            'is_active' => true,
        ];
    }
}