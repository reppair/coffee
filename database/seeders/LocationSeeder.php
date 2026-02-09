<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Cush Coffee',
                'address' => 'Kazbek, Manastirski Livadi',
                'phone' => '+1 (555) 123-4567',
                'is_active' => true,
            ],
            [
                'name' => 'Cush Central',
                'address' => 'Tzar Simeon',
                'phone' => '+1 (555) 987-6543',
                'is_active' => false,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
