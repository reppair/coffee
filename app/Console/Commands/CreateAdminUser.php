<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\User;
use Database\Seeders\LocationSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
        {name? : The name of the admin user}
        {email? : The email address of the admin user}';

    protected $description = 'Create a new admin user with a random password';

    public function handle(): int
    {
        if (Location::count() === 0) {
            warning('No locations found. Seeding locations...');
            $this->call('db:seed', ['--class' => LocationSeeder::class, '--force' => true]);
        }

        $name = $this->argument('name') ?? text(
            label: 'What is the user\'s name?',
            placeholder: 'E.g. John Doe',
            required: true,
        );

        $email = $this->argument('email') ?? text(
            label: 'What is their email address?',
            placeholder: 'E.g. john@example.com',
            required: true,
            validate: ['email' => 'email|unique:users,email'],
        );

        if ($this->argument('name') || $this->argument('email')) {
            $validator = Validator::make(
                ['name' => $name, 'email' => $email],
                array_filter([
                    'name' => $this->argument('name') ? 'required|string|max:255' : null,
                    'email' => $this->argument('email') ? 'required|email|unique:users,email' : null,
                ]),
            );

            if ($validator->fails()) {
                error($validator->errors()->first());

                return self::FAILURE;
            }
        }

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
