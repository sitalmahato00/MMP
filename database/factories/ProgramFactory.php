<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Diploma in Information Technology',
            'Diploma in Civil Engineering',
            'Diploma in Electrical Engineering', 
            'Diploma in Electronics Engineering',
            'Diploma in Mechanical Engineering',
            'Diploma in Architecture Engineering'
        ]);

        return [
            'department_id' => Department::factory(),
            'code' => 'D' . $this->faker->randomNumber(3),
            'name' => $name,
            'slug' => Str::slug($name),
            'total_semesters' => 6,
            'duration_years' => 3,
            'description' => $this->faker->paragraph(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

