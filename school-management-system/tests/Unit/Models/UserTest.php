<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    public function test_user_can_be_created_with_factory(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function test_user_password_is_hashed(): void
    {
        $password = 'test-password';
        $user = User::factory()->create(['password' => $password]);

        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function test_user_role_checking_methods(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $admin = $this->createAdmin();
        $teacher = $this->createTeacher();
        $student = $this->createStudent();

        // Test SuperAdmin methods
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($superAdmin->isAdmin());
        $this->assertFalse($superAdmin->isTeacher());
        $this->assertFalse($superAdmin->isStudent());

        // Test Admin methods
        $this->assertFalse($admin->isSuperAdmin());
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isTeacher());
        $this->assertFalse($admin->isStudent());

        // Test Teacher methods
        $this->assertFalse($teacher->isSuperAdmin());
        $this->assertFalse($teacher->isAdmin());
        $this->assertTrue($teacher->isTeacher());
        $this->assertFalse($teacher->isStudent());

        // Test Student methods
        $this->assertFalse($student->isSuperAdmin());
        $this->assertFalse($student->isAdmin());
        $this->assertFalse($student->isTeacher());
        $this->assertTrue($student->isStudent());
    }

    public function test_user_can_have_student_relationship(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Student::class, $user->fresh()->student);
        $this->assertEquals($student->id, $user->student->id);
    }

    public function test_user_can_have_teacher_relationship(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Teacher::class, $user->fresh()->teacher);
        $this->assertEquals($teacher->id, $user->teacher->id);
    }

    public function test_user_profile_photo_url_attribute(): void
    {
        $user = User::factory()->create(['profile_photo' => 'photos/test.jpg']);
        
        $this->assertStringContainsString('photos/test.jpg', $user->profile_photo_url);
    }

    public function test_user_profile_photo_url_default(): void
    {
        $user = User::factory()->create(['profile_photo' => null]);
        
        $this->assertStringContainsString('default-avatar.png', $user->profile_photo_url);
    }

    public function test_user_full_contact_attribute(): void
    {
        $user = User::factory()->create([
            'phone' => '+1234567890',
            'email' => 'test@example.com'
        ]);

        $expected = 'test@example.com | +1234567890';
        $this->assertEquals($expected, $user->full_contact);
    }

    public function test_user_full_contact_without_phone(): void
    {
        $user = User::factory()->create([
            'phone' => null,
            'email' => 'test@example.com'
        ]);

        $this->assertEquals('test@example.com', $user->full_contact);
    }

    public function test_user_age_calculation(): void
    {
        $birthDate = now()->subYears(25)->format('Y-m-d');
        $user = User::factory()->create(['date_of_birth' => $birthDate]);

        $this->assertEquals(25, $user->age);
    }

    public function test_user_age_without_birth_date(): void
    {
        $user = User::factory()->create(['date_of_birth' => null]);

        $this->assertNull($user->age);
    }

    public function test_active_scope(): void
    {
        $activeUser = User::factory()->create(['is_active' => true]);
        $inactiveUser = User::factory()->create(['is_active' => false]);

        $activeUsers = User::active()->get();

        $this->assertTrue($activeUsers->contains($activeUser));
        $this->assertFalse($activeUsers->contains($inactiveUser));
    }

    public function test_inactive_scope(): void
    {
        $activeUser = User::factory()->create(['is_active' => true]);
        $inactiveUser = User::factory()->create(['is_active' => false]);

        $inactiveUsers = User::inactive()->get();

        $this->assertFalse($inactiveUsers->contains($activeUser));
        $this->assertTrue($inactiveUsers->contains($inactiveUser));
    }

    public function test_by_role_scope(): void
    {
        $teacher = $this->createTeacher();
        $student = $this->createStudent();

        $teachers = User::byRole('Teacher')->get();
        $students = User::byRole('Student')->get();

        $this->assertTrue($teachers->contains($teacher));
        $this->assertFalse($teachers->contains($student));
        $this->assertTrue($students->contains($student));
        $this->assertFalse($students->contains($teacher));
    }

    public function test_user_factory_creates_different_user_types(): void
    {
        $studentUser = User::factory()->student()->create();
        $teacherUser = User::factory()->teacher()->create();
        $adminUser = User::factory()->admin()->create();

        // Verify age ranges are appropriate for roles
        $this->assertGreaterThanOrEqual(5, $studentUser->age);
        $this->assertLessThanOrEqual(25, $studentUser->age);

        $this->assertGreaterThanOrEqual(22, $teacherUser->age);
        $this->assertLessThanOrEqual(65, $teacherUser->age);

        $this->assertGreaterThanOrEqual(25, $adminUser->age);
        $this->assertLessThanOrEqual(60, $adminUser->age);
    }

    public function test_user_mass_assignment_protection(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\MassAssignmentException::class);
        
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'id' => 999999, // This should not be mass assignable
        ]);
    }

    public function test_user_email_must_be_unique(): void
    {
        $email = 'duplicate@example.com';
        User::factory()->create(['email' => $email]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => $email]);
    }
}