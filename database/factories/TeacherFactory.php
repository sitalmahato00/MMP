<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'employee_id' => 'T-' . fake()->randomNumber(3),
            'designation' => fake()->randomElement(['Lecturer', 'Instructor', 'Assistant Lecturer']),
            'qualification' => fake()->randomElement(['B.E.', 'B.Tech', 'M.Sc.', 'M.E.']),
            'specialization' => fake()->sentence(3),
            'join_date' => fake()->dateTimeBetween('-10 years', '-1 year'),
            'employment_type' => fake()->randomElement(['permanent', 'contract', 'visiting']),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

