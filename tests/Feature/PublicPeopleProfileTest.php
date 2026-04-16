<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPeopleProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_hod_profile_page_shows_department_and_contact_details(): void
    {
        $hod = User::factory()->create([
            'name' => 'Dr. Head Officer',
            'email' => 'hod@example.test',
            'phone' => '+977-9811111111',
            'address' => 'Budhiganga-4, Morang',
            'gender' => 'male',
            'dob' => Carbon::parse('1980-05-10'),
            'is_active' => true,
        ]);

        $department = Department::create([
            'name' => 'Civil Engineering',
            'code' => 'CE',
            'slug' => 'civil-engineering',
            'description' => 'Civil engineering department description.',
            'seat_capacity' => 40,
            'hod_id' => $hod->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('public.people.profile', ['type' => 'hod', 'id' => $department->id]));

        $response->assertOk();
        $response->assertSeeText('Head of Department');
        $response->assertSeeText('Dr. Head Officer');
        $response->assertSeeText('Civil Engineering');
        $response->assertSeeText('hod@example.test');
        $response->assertSeeText('Budhiganga-4, Morang');
    }

    public function test_teacher_profile_page_shows_professional_details(): void
    {
        $department = Department::create([
            'name' => 'Electrical Engineering',
            'code' => 'EE',
            'slug' => 'electrical-engineering',
            'description' => 'Electrical engineering department description.',
            'seat_capacity' => 40,
            'is_active' => true,
        ]);

        $teacherUser = User::factory()->create([
            'name' => 'Er. Teacher One',
            'email' => 'teacher@example.test',
            'phone' => '+977-9812222222',
            'address' => 'Dharan-7, Sunsari',
            'gender' => 'female',
            'dob' => Carbon::parse('1990-02-14'),
            'is_active' => true,
        ]);

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'department_id' => $department->id,
            'employee_id' => 'T-001',
            'designation' => 'Lecturer',
            'qualification' => 'BTech in Electrical Engineering',
            'specialization' => 'Power Systems',
            'join_date' => Carbon::parse('2018-07-15'),
            'employment_type' => 'permanent',
            'is_active' => true,
        ]);

        $response = $this->get(route('public.people.profile', ['type' => 'teacher', 'id' => $teacher->id]));

        $response->assertOk();
        $response->assertSeeText('Teacher');
        $response->assertSeeText('Er. Teacher One');
        $response->assertSeeText('Lecturer');
        $response->assertSeeText('T-001');
        $response->assertSeeText('Power Systems');
        $response->assertSeeText('teacher@example.test');
    }

    public function test_staff_profile_page_shows_address_and_role_details(): void
    {
        $department = Department::create([
            'name' => 'Computer Science & Information Technology',
            'code' => 'CSIT',
            'slug' => 'computer-science-information-technology',
            'description' => 'CSIT department description.',
            'seat_capacity' => 48,
            'is_active' => true,
        ]);

        $staffUser = User::factory()->create([
            'name' => 'Lab Assist',
            'email' => 'staff@example.test',
            'phone' => '+977-9813333333',
            'address' => 'Itahari-2, Sunsari',
            'gender' => 'male',
            'dob' => Carbon::parse('1992-11-20'),
            'is_active' => true,
        ]);

        $staff = Staff::create([
            'user_id' => $staffUser->id,
            'name' => 'Lab Assist',
            'designation' => 'Lab Technician',
            'department' => $department->name,
            'email' => 'staff@example.test',
            'phone' => '+977-9813333333',
            'order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('public.people.profile', ['type' => 'staff', 'id' => $staff->id]));

        $response->assertOk();
        $response->assertSeeText('Lab Technician');
        $response->assertSeeText('Lab Assist');
        $response->assertSeeText('staff@example.test');
        $response->assertSeeText('Itahari-2, Sunsari');
        $response->assertSeeText('Computer Science & Information Technology');
    }
}
