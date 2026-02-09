<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $cushCoffee = Location::where('name', 'Cush Coffee')->first();
        $cushCentral = Location::where('name', 'Cush Central')->first();

        $martin = User::create([
            'name' => 'Martin',
            'email' => 'martin@blagoev.xyz',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'is_staff' => false,
            'is_active' => true,
        ]);
        $martin->locations()->attach([$cushCoffee->id, $cushCentral->id]);

        $dimo = User::create([
            'name' => 'Dimo',
            'email' => 'dimo@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'is_staff' => false,
            'is_active' => true,
        ]);
        $dimo->locations()->attach([$cushCoffee->id, $cushCentral->id]);

        $geri = User::create([
            'name' => 'Geri',
            'email' => 'geri@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'is_staff' => true,
            'is_active' => true,
        ]);
        $geri->locations()->attach($cushCoffee->id);

        $pesho = User::create([
            'name' => 'Pesho',
            'email' => 'pesho@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'is_staff' => true,
            'is_active' => true,
        ]);
        $pesho->locations()->attach($cushCoffee->id);

        User::factory()->count(3)->create();
    }
}
