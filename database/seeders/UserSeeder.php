<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $coffeeCentral = Location::where('name', 'Coffee Central')->first();
        $airportKiosk = Location::where('name', 'Airport Kiosk')->first();

        $dimo = User::create([
            'name' => 'Dimo',
            'email' => 'dimo@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'is_staff' => false,
            'is_active' => true,
        ]);
        $dimo->locations()->attach([$coffeeCentral->id, $airportKiosk->id]);

        $geri = User::create([
            'name' => 'Geri',
            'email' => 'geri@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'is_staff' => true,
            'is_active' => true,
        ]);
        $geri->locations()->attach($coffeeCentral->id);

        $pesho = User::create([
            'name' => 'Pesho',
            'email' => 'pesho@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'is_staff' => true,
            'is_active' => true,
        ]);
        $pesho->locations()->attach($coffeeCentral->id);

        User::factory()->customer()->count(3)->create();
    }
}
