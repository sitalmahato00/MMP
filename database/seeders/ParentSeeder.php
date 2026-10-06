<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ParentModel;
use Illuminate\Database\Seeder;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        $parents = [
            [
                'name' => 'Ramesh Paudel',
                'email' => 'ramesh.paudel@mtu.edu.np',
                'phone' => '9841234801',
                'occupation' => 'Engineer',
                'relation' => 'Father',
            ],
            [
                'name' => 'Priya Sharma',
                'email' => 'priya.sharma@mtu.edu.np',
                'phone' => '9841234802',
                'occupation' => 'Doctor',
                'relation' => 'Mother',
            ],
            [
                'name' => 'Manoj Niroula',
                'email' => 'manoj.niroula@mtu.edu.np',
                'phone' => '9841234803',
                'occupation' => 'Business',
                'relation' => 'Father',
            ],
            [
                'name' => 'Sushila Baniya',
                'email' => 'sushila.baniya@mtu.edu.np',
                'phone' => '9841234804',
                'occupation' => 'Teacher',
                'relation' => 'Mother',
            ],
            [
                'name' => 'Ahmed Khan',
                'email' => 'ahmed.khan@mtu.edu.np',
                'phone' => '9841234805',
                'occupation' => 'Businessman',
                'relation' => 'Father',
            ],
        ];

        foreach ($parents as $parentData) {
            $user = User::withTrashed()->updateOrCreate(
                ['email' => $parentData['email']],
                [
                    'name' => $parentData['name'],
                    'email' => $parentData['email'],
                    'phone' => $parentData['phone'],
                    'gender' => strpos($parentData['name'], 'a') !== false ? 'Female' : 'Male',
                    'address' => 'Kathmandu, Nepal',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'two_factor_enabled' => false,
                    'two_factor_method' => 'email',
                ]
            );

            if ($user->trashed()) {
                $user->restore();
            }

            $user->syncRoles(['parent']);

            // Create parent profile (ParentModel does not use SoftDeletes)
            ParentModel::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'occupation' => $parentData['occupation'],
                    'relation_to_student' => $parentData['relation'],
                ]
            );
        }

        $this->command->info('Parents seeded successfully.');
    }
}
