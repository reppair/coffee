<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\User;
use Database\Seeders\LocationSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Create a new admin user with a random password';

    public function handle(): int
    {
        if (Location::count() === 0) {
            warning('No locations found. Seeding locations...');
            $this->call('db:seed', ['--class' => LocationSeeder::class]);
        }

        $name = text(
            label: 'What is the user\'s name?',
            placeholder: 'E.g. John Doe',
            required: true,
        );

        $email = text(
            label: 'What is their email address?',
            placeholder: 'E.g. john@example.com',
            required: true,
            validate: ['email' => 'email|unique:users,email'],
        );

        $password = Str::random(32);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_admin' => true,
            'is_active' => true,
        ]);

        $locations = Location::all();
        $user->locations()->attach($locations);

        info('Admin user created successfully.');
        info("Assigned to {$locations->count()} location(s).");
        outro("Password: {$password}");

        return self::SUCCESS;
    }
}
