<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Information Technology',
            'Civil Engineering', 
            'Electrical Engineering',
            'Electronics Engineering',
            'Mechanical Engineering',
            'Architecture Engineering'
        ]);

        return [
            'code' => 'D' . $this->faker->randomNumber(2),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(10),
            'photo' => null,
            'syllabus' => null,
            'seat_capacity' => $this->faker->numberBetween(30, 60),
            'hod_id' => User::factory()->createQuietly(['email' => $this->faker->safeEmail()]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

