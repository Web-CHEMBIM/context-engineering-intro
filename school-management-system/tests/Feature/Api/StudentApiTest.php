<?php

namespace Tests\Feature\Api;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentApiTest extends TestCase
{
    public function test_admin_can_list_students(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        // Create test students
        Student::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/students');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'student_id',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'school_class',
                    'academic_year',
                    'student_status',
                    'fees_status',
                    'created_at',
                ]
            ],
            'links',
            'meta',
        ]);
    }

    public function test_teacher_can_view_students_in_their_class(): void
    {
        $teacher = $this->createTeacher();
        Sanctum::actingAs($teacher);

        // Create class with teacher
        $class = SchoolClass::factory()->create(['class_teacher_id' => $teacher->teacher->id]);
        $student = Student::factory()->create(['school_class_id' => $class->id]);

        $response = $this->getJson('/api/v1/students');

        $response->assertStatus(200);
        // Should contain students from teacher's classes
    }

    public function test_student_cannot_list_all_students(): void
    {
        $student = $this->createStudent();
        Sanctum::actingAs($student);

        $response = $this->getJson('/api/v1/students');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_student(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $class = SchoolClass::factory()->create();

        $studentData = [
            'name' => 'New Student',
            'email' => 'newstudent@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'date_of_birth' => '2010-01-01',
            'gender' => 'female',
            'student_id' => 'STU2024001',
            'admission_date' => now()->format('Y-m-d'),
            'admission_number' => 'ADM2024001',
            'school_class_id' => $class->id,
            'total_fees' => 5000,
            'blood_group' => 'A+',
            'emergency_contact_name' => 'Parent Name',
            'emergency_contact_phone' => '+1234567890',
            'emergency_contact_relationship' => 'Mother',
        ];

        $response = $this->postJson('/api/v1/students', $studentData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'student_id',
                'user',
                'student_status',
            ],
            'message',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New Student',
            'email' => 'newstudent@test.com',
        ]);

        $this->assertDatabaseHas('students', [
            'student_id' => 'STU2024001',
        ]);
    }

    public function test_student_creation_validation(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/students', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name',
            'email',
            'password',
            'date_of_birth',
            'student_id',
            'admission_date',
        ]);
    }

    public function test_duplicate_student_id_validation(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $existingStudent = Student::factory()->create(['student_id' => 'STU001']);

        $studentData = [
            'name' => 'Duplicate Student',
            'email' => 'duplicate@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'date_of_birth' => '2010-01-01',
            'student_id' => 'STU001', // Duplicate
            'admission_date' => now()->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/v1/students', $studentData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['student_id']);
    }

    public function test_admin_can_update_student(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $student = Student::factory()->create();
        $newClass = SchoolClass::factory()->create();

        $updateData = [
            'school_class_id' => $newClass->id,
            'total_fees' => 6000,
            'student_status' => 'enrolled',
        ];

        $response = $this->putJson("/api/v1/students/{$student->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonPath('data.total_fees', '6000.00');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'school_class_id' => $newClass->id,
            'total_fees' => 6000,
        ]);
    }

    public function test_student_can_view_own_profile(): void
    {
        $studentUser = $this->createStudent();
        $student = Student::factory()->create(['user_id' => $studentUser->id]);
        Sanctum::actingAs($studentUser);

        $response = $this->getJson("/api/v1/students/{$student->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $student->id);
    }

    public function test_student_cannot_view_other_students(): void
    {
        $studentUser = $this->createStudent();
        $otherStudent = Student::factory()->create();
        Sanctum::actingAs($studentUser);

        $response = $this->getJson("/api/v1/students/{$otherStudent->id}");

        $response->assertStatus(403);
    }

    public function test_api_student_search_by_name(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $user1 = User::factory()->create(['name' => 'John Smith']);
        $user2 = User::factory()->create(['name' => 'Jane Doe']);
        Student::factory()->create(['user_id' => $user1->id]);
        Student::factory()->create(['user_id' => $user2->id]);

        $response = $this->getJson('/api/v1/students?search=John');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.user.name', 'John Smith');
    }

    public function test_api_student_filter_by_class(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $class = SchoolClass::factory()->create(['name' => 'Grade 5-A']);
        $student = Student::factory()->create(['school_class_id' => $class->id]);
        Student::factory()->create(); // Different class

        $response = $this->getJson("/api/v1/students?class_id={$class->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.school_class.id', $class->id);
    }

    public function test_api_student_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        Student::factory()->create(['student_status' => 'enrolled']);
        Student::factory()->create(['student_status' => 'graduated']);

        $response = $this->getJson('/api/v1/students?status=enrolled');

        $response->assertStatus(200);
        
        $statuses = collect($response->json('data'))->pluck('student_status')->unique();
        $this->assertEquals(['enrolled'], $statuses->toArray());
    }

    public function test_api_student_enrollment_management(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $student = Student::factory()->create();
        $subject = Subject::factory()->create();

        $response = $this->postJson("/api/v1/students/{$student->id}/subjects", [
            'subject_id' => $subject->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Student enrolled in subject successfully']);

        $this->assertDatabaseHas('student_subject', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => 'enrolled',
        ]);
    }

    public function test_api_student_subject_unenrollment(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $student = Student::factory()->create();
        $subject = Subject::factory()->create();
        $student->enrollInSubject($subject);

        $response = $this->deleteJson("/api/v1/students/{$student->id}/subjects/{$subject->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Student unenrolled from subject successfully']);

        $this->assertDatabaseHas('student_subject', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => 'dropped',
        ]);
    }

    public function test_api_student_fee_payment_update(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $student = Student::factory()->create([
            'total_fees' => 5000,
            'fees_paid' => 2000,
            'fees_pending' => 3000,
        ]);

        $response = $this->postJson("/api/v1/students/{$student->id}/payments", [
            'amount' => 1500,
            'payment_method' => 'cash',
            'receipt_number' => 'REC001',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.fees_paid', '3500.00');
        $response->assertJsonPath('data.fees_pending', '1500.00');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'fees_paid' => 3500,
            'fees_pending' => 1500,
        ]);
    }

    public function test_api_student_transfer_to_class(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $student = Student::factory()->create();
        $newClass = SchoolClass::factory()->create();

        $response = $this->postJson("/api/v1/students/{$student->id}/transfer", [
            'school_class_id' => $newClass->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.school_class.id', $newClass->id);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'school_class_id' => $newClass->id,
        ]);
    }

    public function test_api_bulk_student_operations(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $students = Student::factory()->count(3)->create();
        $studentIds = $students->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/students/bulk-update', [
            'ids' => $studentIds,
            'student_status' => 'graduated',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.updated_count', 3);

        foreach ($studentIds as $studentId) {
            $this->assertDatabaseHas('students', [
                'id' => $studentId,
                'student_status' => 'graduated',
            ]);
        }
    }

    public function test_api_student_performance_data(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $student = Student::factory()->create();
        $subject = Subject::factory()->create();
        
        $student->enrollInSubject($subject);
        $student->subjects()->updateExistingPivot($subject->id, [
            'status' => 'completed',
            'grade' => 85,
        ]);

        $response = $this->getJson("/api/v1/students/{$student->id}/performance");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'average_grade',
                'completed_subjects',
                'enrolled_subjects',
                'performance_status',
                'subjects' => [
                    '*' => [
                        'name',
                        'grade',
                        'status',
                    ]
                ]
            ]
        ]);
    }

    public function test_api_student_attendance_tracking(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $student = Student::factory()->create();

        $response = $this->getJson("/api/v1/students/{$student->id}/attendance");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'total_days',
                'present_days',
                'absent_days',
                'attendance_percentage',
                'recent_attendance',
            ]
        ]);
    }

    public function test_api_student_export_functionality(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        Student::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/students/export?format=json');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'student_id',
                    'name',
                    'email',
                    'class',
                    'status',
                    'fees_status',
                ]
            ],
            'meta' => [
                'total_records',
                'exported_at',
                'format',
            ]
        ]);
    }
}