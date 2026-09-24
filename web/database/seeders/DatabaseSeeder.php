<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\RatePlan;
use App\Models\RateRule;
use App\Models\Site;
use App\Models\Spot;
use App\Models\User;
use App\Models\Zone;
use App\Models\StaffAssignment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'ParkWatch Demo Organization',
            'slug' => 'parkwatch-demo',
            'contact_email' => 'demo@parkwatch.local',
            'contact_phone' => '+251900000000',
            'timezone' => 'Africa/Addis_Ababa',
            'currency' => 'ETB',
        ]);

        $site = Site::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Bole Parking',
            'code' => 'BOLE',
            'address' => 'Bole, Addis Ababa',
            'total_capacity' => 60,
            'timezone' => 'Africa/Addis_Ababa',
        ]);

        $zoneNames = [
            ['name' => 'Zone A', 'code' => 'A', 'floor_label' => 'Level 1'],
            ['name' => 'Zone B', 'code' => 'B', 'floor_label' => 'Level 2'],
            ['name' => 'Zone C', 'code' => 'C', 'floor_label' => 'Level 3'],
        ];

        foreach ($zoneNames as $index => $zoneData) {
            $zone = Zone::factory()->create([
                'site_id' => $site->id,
                'name' => $zoneData['name'],
                'code' => $zoneData['code'],
                'floor_label' => $zoneData['floor_label'],
                'capacity' => 20,
                'display_order' => $index + 1,
            ]);

            for ($spotNumber = 1; $spotNumber <= 20; $spotNumber++) {
                Spot::factory()->create([
                    'zone_id' => $zone->id,
                    'code' => sprintf('%s-%02d', $zoneData['code'], $spotNumber),
                    'display_x' => (($spotNumber - 1) % 5) * 10,
                    'display_y' => intdiv($spotNumber - 1, 5) * 10,
                ]);
            }
        }

        $userData = [
            [
                'full_name' => 'ParkWatch Owner',
                'email' => 'owner@parkwatch.local',
                'user_type' => 'owner',
            ],
            [
                'full_name' => 'Site Manager',
                'email' => 'manager@parkwatch.local',
                'user_type' => 'staff',
            ],
            [
                'full_name' => 'Attendant One',
                'email' => 'attendant1@parkwatch.local',
                'user_type' => 'staff',
            ],
            [
                'full_name' => 'Attendant Two',
                'email' => 'attendant2@parkwatch.local',
                'user_type' => 'staff',
            ],
            [
                'full_name' => 'Demo Driver',
                'email' => 'driver@parkwatch.local',
                'user_type' => 'customer',
            ],
        ];

        foreach ($userData as $data) {
            User::factory()->create([
                'organization_id' => $organization->id,
                ...$data,
            ]);
        }

        $manager = User::where('email', 'manager@parkwatch.local')->first();
        $attendantOne = User::where('email', 'attendant1@parkwatch.local')->first();
        $attendantTwo = User::where('email', 'attendant2@parkwatch.local')->first();

        StaffAssignment::create([
            'user_id' => $manager->id,
            'site_id' => $site->id,
            'role_name' => 'manager',
            'starts_at' => now(),
            'ends_at' => null,
            'is_active' => true,
        ]);

        StaffAssignment::create([
            'user_id' => $attendantOne->id,
            'site_id' => $site->id,
            'role_name' => 'attendant',
            'starts_at' => now(),
            'ends_at' => null,
            'is_active' => true,
        ]);

        StaffAssignment::create([
            'user_id' => $attendantTwo->id,
            'site_id' => $site->id,
            'role_name' => 'attendant',
            'starts_at' => now(),
            'ends_at' => null,
            'is_active' => true,
        ]);

        $ratePlan = RatePlan::factory()->create([
            'site_id' => $site->id,
            'name' => 'Standard Parking',
            'currency' => 'ETB',
        ]);

        RateRule::factory()->create([
            'rate_plan_id' => $ratePlan->id,
        ]);
    }
}