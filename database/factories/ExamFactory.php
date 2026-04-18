<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exam>
 */
class ExamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['First Internal', 'Mid Term', 'Final Exam', 'Second Internal']),
            'academic_session_id' => AcademicSession::factory(),
            'department_id' => Department::factory(),
            'type' => fake()->randomElement(['assessment', 'term', 'final']),
            'start_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => fake()->randomElement(['scheduled', 'ongoing', 'completed', 'results_published']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

