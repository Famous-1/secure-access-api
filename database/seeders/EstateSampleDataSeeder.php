<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VisitorCode;
use App\Models\Complaint;
use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EstateSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create sample residents
        $residents = [
            [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '1111111111',
                'apartment_unit' => 'Apt 101, Block A',
                'full_address' => '123 Main Street, City, State 12345',
                'usertype' => 'user',
                'status' => 'active'
            ],
            [
                'firstname' => 'Jane',
                'lastname' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '1111111112',
                'apartment_unit' => 'Apt 205, Block B',
                'full_address' => '456 Oak Avenue, City, State 12345',
                'usertype' => 'user',
                'status' => 'active'
            ],
            [
                'firstname' => 'Mike',
                'lastname' => 'Johnson',
                'email' => 'mike.johnson@example.com',
                'phone' => '1111111113',
                'apartment_unit' => 'Apt 302, Block C',
                'full_address' => '789 Pine Road, City, State 12345',
                'usertype' => 'user',
                'status' => 'active'
            ]
        ];

        $createdUsers = [];
        foreach ($residents as $residentData) {
            $user = User::create([
                ...$residentData,
                'password' => Hash::make('password123'),
                'email_verified_at' => now()
            ]);
            $createdUsers[] = $user;
        }

        // Create sample maintainer
        $maintainer = User::create([
            'firstname' => 'Bob',
            'lastname' => 'Wilson',
            'email' => 'bob.wilson@example.com',
            'phone' => '1111111114',
            'apartment_unit' => 'Maintenance Office',
            'full_address' => 'Maintenance Building, Estate Complex',
            'usertype' => 'user',
            'status' => 'active',
            'password' => Hash::make('password123'),
            'email_verified_at' => now()
        ]);

        // Create sample visitor codes
        $visitorCodes = [
            [
                'user_id' => $createdUsers[0]->id,
                'visitor_name' => 'Alice Brown',
                'phone_number' => '9876543210',
                'destination' => 'Apt 101, Block A',
                'number_of_visitors' => 2,
                'code' => 'ABC123',
                'expires_at' => now()->addHours(4),
                'additional_notes' => 'Family visit',
                'status' => 'active'
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'visitor_name' => 'Charlie Davis',
                'phone_number' => '9876543211',
                'destination' => 'Apt 205, Block B',
                'number_of_visitors' => 1,
                'code' => 'DEF456',
                'expires_at' => now()->addHours(2),
                'additional_notes' => 'Delivery person',
                'status' => 'used',
                'verified_by' => $maintainer->id,
                'verified_at' => now()->subHour()
            ]
        ];

        foreach ($visitorCodes as $codeData) {
            VisitorCode::create($codeData);
        }

        // Create sample complaints
        $complaints = [
            [
                'user_id' => $createdUsers[0]->id,
                'type' => 'complaint',
                'category' => 'Maintenance',
                'severity' => 'medium',
                'title' => 'Broken Elevator',
                'description' => 'The elevator in Block A has been out of order for 2 days. Please fix it as soon as possible.',
                'status' => 'pending'
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'type' => 'suggestion',
                'category' => 'Security',
                'severity' => 'low',
                'title' => 'Install Security Cameras',
                'description' => 'I suggest installing security cameras in the parking area for better safety.',
                'status' => 'in_progress'
            ],
            [
                'user_id' => $createdUsers[2]->id,
                'type' => 'complaint',
                'category' => 'Noise',
                'severity' => 'high',
                'title' => 'Loud Music at Night',
                'description' => 'There is loud music coming from the apartment above mine every night after 10 PM.',
                'status' => 'resolved',
                'resolved_at' => now()->subDay(),
                'resolved_by' => $maintainer->id,
                'admin_notes' => 'Issue resolved by speaking with the tenant.'
            ]
        ];

        foreach ($complaints as $complaintData) {
            Complaint::create($complaintData);
        }

        // Create sample activities
        $activities = [
            [
                'user_id' => $createdUsers[0]->id,
                'action' => 'visitor_code_created',
                'description' => 'Generated visitor code for Alice Brown',
                'related_type' => 'App\Models\VisitorCode',
                'related_id' => 1,
                'metadata' => ['visitor_name' => 'Alice Brown', 'code' => 'ABC123']
            ],
            [
                'user_id' => $maintainer->id,
                'action' => 'visitor_code_verified',
                'description' => 'Verified visitor code for Charlie Davis',
                'related_type' => 'App\Models\VisitorCode',
                'related_id' => 2,
                'metadata' => ['visitor_name' => 'Charlie Davis', 'code' => 'DEF456']
            ],
            [
                'user_id' => $createdUsers[0]->id,
                'action' => 'complaint_created',
                'description' => 'Submitted complaint: Broken Elevator',
                'related_type' => 'App\Models\Complaint',
                'related_id' => 1,
                'metadata' => ['type' => 'complaint', 'title' => 'Broken Elevator']
            ]
        ];

        foreach ($activities as $activityData) {
            Activity::create($activityData);
        }

        $this->command->info('Estate sample data created successfully.');
    }
}