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
                'name' => 'Coffee Central',
                'address' => '123 Main Street, Downtown',
                'phone' => '+1 (555) 123-4567',
                'is_active' => true,
            ],
            [
                'name' => 'Airport Kiosk',
                'address' => 'Terminal 2, International Airport',
                'phone' => '+1 (555) 987-6543',
                'is_active' => false,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
