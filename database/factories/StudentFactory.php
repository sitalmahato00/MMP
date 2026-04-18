<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'academic_session_id' => AcademicSession::factory(),
            'department_id' => Department::factory(),
            'program_id' => Program::factory(),
            'roll_number' => 'D' . fake()->randomNumber(3) . '-' . fake()->numberBetween(80,81) . '-' . fake()->numberBetween(1,40),
            'registration_number' => 'MMP-' . fake()->randomNumber(3) . '-' . fake()->numberBetween(001,999),
            'current_semester' => fake()->numberBetween(1,6),
            'section' => fake()->randomElement(['A', 'B']),
            'batch' => fake()->numerify('20#1'),
            'admission_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'guardian_name' => fake()->name(),
            'guardian_phone' => '98' . fake()->numerify('########'),
            'blood_group' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'status' => 'active',
            'is_archived' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

