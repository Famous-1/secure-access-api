<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if an admin user already exists
        if (User::where('email', 'admin@secure-access.com')->exists()) {
            $this->command->info('Admin user already exists.');
            return;
        }

        // Check if there's already an admin user
        if (User::where('usertype', 'admin')->exists()) {
            $this->command->info('Admin user already exists.');
            return;
        }

        // Create the admin user
        User::create([
            'firstname' => 'Admin',
            'lastname' => 'User',
            'phone' => '9999999999',
            'email' => 'admin@secure-access.com',
            'password' => Hash::make('admin123'), // Default password
            'usertype' => 'admin',
            'apartment_unit' => 'Admin Office',
            'full_address' => 'Secure Access Estate Management',
            'status' => 'active',
            'email_verified_at' => now()
        ]);

        $this->command->info('Admin user created successfully.');
    }
}