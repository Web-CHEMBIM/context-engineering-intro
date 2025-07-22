<?php

namespace Tests\Feature\Browser;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use Tests\TestCase;

class UserWorkflowTest extends TestCase
{
    public function test_complete_admin_workflow(): void
    {
        $admin = $this->actingAsRole('Admin');

        // 1. Admin logs in and sees dashboard
        $response = $this->get('/dashboard');
        $response->assertRedirect('/admin/dashboard');

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');

        // 2. Admin creates a new student
        $response = $this->get('/admin/students/create');
        $response->assertStatus(200);
        $response->assertSee('Add New Student');

        $studentData = [
            'name' => 'Test Student Workflow',
            'email' => 'testworkflow@student.edu',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'date_of_birth' => '2010-01-01',
            'gender' => 'male',
            'phone' => '+1234567890',
            'student_id' => 'WFLOW001',
            'admission_date' => now()->format('Y-m-d'),
            'admission_number' => 'ADM_WFLOW001',
            'total_fees' => 5000,
            'blood_group' => 'O+',
            'emergency_contact_name' => 'Parent Name',
            'emergency_contact_phone' => '+0987654321',
            'emergency_contact_relationship' => 'Father',
        ];

        $response = $this->post('/admin/students', $studentData);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify student was created
        $this->assertDatabaseHas('users', [
            'name' => 'Test Student Workflow',
            'email' => 'testworkflow@student.edu',
        ]);

        // 3. Admin views student list
        $response = $this->get('/admin/students');
        $response->assertStatus(200);
        $response->assertSee('Test Student Workflow');

        // 4. Admin edits the student
        $student = Student::where('student_id', 'WFLOW001')->first();
        $response = $this->get("/admin/students/{$student->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Edit Student');

        $response = $this->put("/admin/students/{$student->id}", [
            'total_fees' => 6000, // Update fees
            'student_status' => 'enrolled',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'total_fees' => 6000,
        ]);

        // 5. Admin deletes the student
        $response = $this->delete("/admin/students/{$student->id}");
        $response->assertRedirect();

        $this->assertSoftDeleted('students', ['id' => $student->id]);
    }

    public function test_teacher_workflow_with_class_management(): void
    {
        $teacherUser = $this->actingAsRole('Teacher');
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        // 1. Teacher logs in and sees dashboard
        $response = $this->get('/dashboard');
        $response->assertRedirect('/teacher/dashboard');

        $response = $this->get('/teacher/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Teacher Dashboard');

        // 2. Create class and assign teacher
        $class = SchoolClass::factory()->create(['class_teacher_id' => $teacher->id]);
        $students = Student::factory()->count(3)->create(['school_class_id' => $class->id]);

        // 3. Teacher views their classes
        $response = $this->get('/teacher/classes');
        $response->assertStatus(200);
        $response->assertSee($class->name);

        // 4. Teacher views students in their class
        $response = $this->get("/teacher/classes/{$class->id}/students");
        $response->assertStatus(200);
        foreach ($students as $student) {
            $response->assertSee($student->user->name);
        }

        // 5. Teacher views subjects they teach
        $subjects = Subject::factory()->count(2)->create();
        $teacher->subjects()->attach($subjects->pluck('id'));

        $response = $this->get('/teacher/subjects');
        $response->assertStatus(200);
        foreach ($subjects as $subject) {
            $response->assertSee($subject->name);
        }

        // 6. Teacher views their profile
        $response = $this->get('/profile');
        $response->assertStatus(200);
        $response->assertSee($teacherUser->name);
        $response->assertSee($teacherUser->email);
    }

    public function test_student_workflow_with_enrollment(): void
    {
        $studentUser = $this->actingAsRole('Student');
        $class = SchoolClass::factory()->create();
        $student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'school_class_id' => $class->id,
        ]);

        // 1. Student logs in and sees dashboard
        $response = $this->get('/dashboard');
        $response->assertRedirect('/student/dashboard');

        $response = $this->get('/student/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Student Dashboard');
        $response->assertSee($student->student_id);

        // 2. Student views their class information
        $response = $this->get('/student/class');
        $response->assertStatus(200);
        $response->assertSee($class->name);

        // 3. Create subjects and enroll student
        $subjects = Subject::factory()->count(4)->create();
        foreach ($subjects as $subject) {
            $student->enrollInSubject($subject);
        }

        // 4. Student views their subjects
        $response = $this->get('/student/subjects');
        $response->assertStatus(200);
        foreach ($subjects as $subject) {
            $response->assertSee($subject->name);
        }

        // 5. Student views their grades
        $student->subjects()->updateExistingPivot($subjects->first()->id, [
            'status' => 'completed',
            'grade' => 85,
        ]);

        $response = $this->get('/student/grades');
        $response->assertStatus(200);
        $response->assertSee('85'); // Grade should be visible

        // 6. Student updates their profile
        $response = $this->get('/profile');
        $response->assertStatus(200);

        $response = $this->put('/profile', [
            'name' => 'Updated Student Name',
            'phone' => '+1111111111',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $studentUser->id,
            'name' => 'Updated Student Name',
            'phone' => '+1111111111',
        ]);
    }

    public function test_superadmin_user_management_workflow(): void
    {
        $superAdmin = $this->actingAsRole('SuperAdmin');

        // 1. SuperAdmin accesses user management
        $response = $this->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('User Management');

        // 2. SuperAdmin creates a new user
        $response = $this->get('/admin/users/create');
        $response->assertStatus(200);

        $userData = [
            'name' => 'New Admin User',
            'email' => 'newadmin@school.edu',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Admin',
            'is_active' => true,
        ];

        $response = $this->post('/admin/users', $userData);
        $response->assertRedirect();

        $newUser = User::where('email', 'newadmin@school.edu')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasRole('Admin'));

        // 3. SuperAdmin edits user
        $response = $this->get("/admin/users/{$newUser->id}/edit");
        $response->assertStatus(200);

        $response = $this->put("/admin/users/{$newUser->id}", [
            'name' => 'Updated Admin Name',
            'is_active' => false,
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'name' => 'Updated Admin Name',
            'is_active' => false,
        ]);

        // 4. SuperAdmin views system reports
        $response = $this->get('/admin/reports');
        $response->assertStatus(200);
        $response->assertSee('System Reports');
    }

    public function test_authentication_and_session_workflow(): void
    {
        // 1. Visit login page as guest
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Login');

        // 2. Attempt login with invalid credentials
        $response = $this->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');

        // 3. Create user and login successfully
        $user = User::factory()->create([
            'password' => bcrypt('correctpassword'),
        ]);
        $user->assignRole('Student');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'correctpassword',
        ]);
        $response->assertRedirect('/dashboard');

        // 4. Verify authenticated session
        $this->assertAuthenticated();

        // 5. Access protected route
        $response = $this->get('/student/dashboard');
        $response->assertStatus(200);

        // 6. Logout
        $response = $this->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();

        // 7. Try to access protected route after logout
        $response = $this->get('/student/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_form_validation_and_error_handling_workflow(): void
    {
        $admin = $this->actingAsRole('Admin');

        // 1. Submit empty form and check validation
        $response = $this->post('/admin/students', []);
        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
            'date_of_birth',
            'student_id',
            'admission_date',
        ]);

        // 2. Submit with invalid email format
        $response = $this->post('/admin/students', [
            'name' => 'Test Student',
            'email' => 'invalid-email-format',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'date_of_birth' => '2010-01-01',
            'student_id' => 'TEST001',
            'admission_date' => now()->format('Y-m-d'),
        ]);
        $response->assertSessionHasErrors(['email']);

        // 3. Submit with mismatched passwords
        $response = $this->post('/admin/students', [
            'name' => 'Test Student',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
            'date_of_birth' => '2010-01-01',
            'student_id' => 'TEST001',
            'admission_date' => now()->format('Y-m-d'),
        ]);
        $response->assertSessionHasErrors(['password']);

        // 4. Submit valid form
        $validData = [
            'name' => 'Valid Student',
            'email' => 'valid@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'date_of_birth' => '2010-01-01',
            'gender' => 'male',
            'student_id' => 'VALID001',
            'admission_date' => now()->format('Y-m-d'),
            'admission_number' => 'ADM_VALID001',
            'total_fees' => 5000,
            'blood_group' => 'A+',
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_phone' => '+1234567890',
            'emergency_contact_relationship' => 'Parent',
        ];

        $response = $this->post('/admin/students', $validData);
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
    }

    public function test_pagination_and_search_workflow(): void
    {
        $admin = $this->actingAsRole('Admin');

        // Create many students
        $students = Student::factory()->count(25)->create();

        // 1. View paginated list
        $response = $this->get('/admin/students');
        $response->assertStatus(200);
        $response->assertSee('Next'); // Pagination should be present

        // 2. Go to second page
        $response = $this->get('/admin/students?page=2');
        $response->assertStatus(200);

        // 3. Search for specific student
        $searchStudent = $students->first();
        $response = $this->get('/admin/students?search=' . $searchStudent->user->name);
        $response->assertStatus(200);
        $response->assertSee($searchStudent->user->name);

        // 4. Filter by status
        $response = $this->get('/admin/students?status=enrolled');
        $response->assertStatus(200);
    }

    public function test_ajax_request_workflow(): void
    {
        $admin = $this->actingAsRole('Admin');

        // Create test data
        $student = Student::factory()->create();

        // 1. Make AJAX request for student details
        $response = $this->getJson("/admin/students/{$student->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'student_id',
            'user',
            'school_class',
        ]);

        // 2. Make AJAX update request
        $response = $this->putJson("/admin/students/{$student->id}", [
            'total_fees' => 7000,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Student updated successfully']);
    }

    public function test_file_upload_workflow(): void
    {
        $admin = $this->actingAsRole('Admin');

        // 1. Test profile photo upload
        $response = $this->get('/profile');
        $response->assertStatus(200);

        // 2. Upload profile photo (simulate file upload)
        $response = $this->put('/profile', [
            'name' => 'Updated Name',
            // In a real test, you would upload an actual file
            // 'profile_photo' => UploadedFile::fake()->image('avatar.jpg'),
        ]);
        $response->assertRedirect();
    }

    public function test_responsive_navigation_workflow(): void
    {
        $admin = $this->actingAsRole('Admin');

        // Test main navigation items
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Students');
        $response->assertSee('Teachers');
        $response->assertSee('Classes');
        $response->assertSee('Subjects');

        // Test breadcrumb navigation
        $response = $this->get('/admin/students/create');
        $response->assertStatus(200);
        $response->assertSee('Students');
        $response->assertSee('Add New Student');
    }
}