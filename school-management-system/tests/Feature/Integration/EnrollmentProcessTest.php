<?php

namespace Tests\Feature\Integration;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class EnrollmentProcessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create subjects for testing
        Subject::factory()->count(5)->create();
        
        // Create classes for testing
        SchoolClass::factory()->count(3)->create();
    }

    public function test_complete_student_enrollment_workflow(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Step 1: Create a student
        $response = $this->post('/admin/students', [
            'name' => 'John Smith',
            'email' => 'john.smith@student.edu',
            'password' => 'password',
            'date_of_birth' => '2010-05-15',
            'gender' => 'male',
            'phone' => '+1234567890',
            'address' => '123 Student Street',
            'student_id' => 'STU2024001',
            'admission_date' => now()->format('Y-m-d'),
            'admission_number' => 'ADM2024001',
            'total_fees' => 5000,
            'blood_group' => 'O+',
            'emergency_contact_name' => 'Jane Smith',
            'emergency_contact_phone' => '+1234567891',
            'emergency_contact_relationship' => 'Mother',
        ]);

        $response->assertRedirect();
        
        // Verify user and student were created
        $user = User::where('email', 'john.smith@student.edu')->first();
        $student = Student::where('user_id', $user->id)->first();
        
        $this->assertNotNull($user);
        $this->assertNotNull($student);
        $this->assertTrue($user->hasRole('Student'));

        // Step 2: Assign student to a class
        $class = SchoolClass::first();
        $response = $this->put("/admin/students/{$student->id}", [
            'school_class_id' => $class->id,
        ]);

        $response->assertRedirect();
        $this->assertEquals($class->id, $student->fresh()->school_class_id);

        // Step 3: Enroll student in subjects
        $subjects = Subject::take(3)->get();
        foreach ($subjects as $subject) {
            $student->enrollInSubject($subject);
        }

        // Verify enrollments
        $this->assertEquals(3, $student->subjects()->count());
        
        // Step 4: Verify complete enrollment
        $student->refresh();
        $this->assertEquals('enrolled', $student->student_status);
        $this->assertNotNull($student->school_class_id);
        $this->assertGreaterThan(0, $student->subjects()->count());
    }

    public function test_student_class_transfer_process(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Create student with initial class
        $originalClass = SchoolClass::factory()->create(['name' => 'Grade 5-A']);
        $newClass = SchoolClass::factory()->create(['name' => 'Grade 5-B']);
        $student = Student::factory()->create(['school_class_id' => $originalClass->id]);

        // Perform transfer
        $student->transferToClass($newClass);

        // Verify transfer
        $this->assertEquals($newClass->id, $student->fresh()->school_class_id);
        $this->assertEquals($newClass->academic_year_id, $student->fresh()->academic_year_id);
    }

    public function test_subject_enrollment_with_prerequisites(): void
    {
        $student = Student::factory()->create();
        
        // Create subjects with prerequisites
        $prerequisite = Subject::factory()->create([
            'name' => 'Algebra I',
            'prerequisites' => null,
        ]);
        
        $advanced = Subject::factory()->create([
            'name' => 'Algebra II',
            'prerequisites' => 'Completion of Algebra I with grade C or better',
        ]);

        // Enroll in prerequisite first
        $student->enrollInSubject($prerequisite);
        
        // Complete prerequisite with good grade
        $student->subjects()->updateExistingPivot($prerequisite->id, [
            'status' => 'completed',
            'grade' => 85,
        ]);

        // Now enroll in advanced subject
        $student->enrollInSubject($advanced);

        // Verify both enrollments
        $this->assertTrue($student->subjects->contains($prerequisite));
        $this->assertTrue($student->subjects->contains($advanced));
    }

    public function test_class_capacity_management(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Create class with small capacity
        $class = SchoolClass::factory()->create(['capacity' => 2]);
        
        // Enroll students up to capacity
        $student1 = Student::factory()->create(['school_class_id' => $class->id]);
        $student2 = Student::factory()->create(['school_class_id' => $class->id]);
        
        // Verify class is at capacity
        $this->assertEquals(2, $class->students()->count());
        
        // Attempt to enroll another student should succeed (business rule: no hard limit)
        $student3 = Student::factory()->create(['school_class_id' => $class->id]);
        $this->assertEquals(3, $class->students()->count());
    }

    public function test_teacher_subject_assignment_process(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Create teacher and subjects
        $teacher = Teacher::factory()->create(['department' => 'Mathematics']);
        $mathSubject = Subject::factory()->create(['department' => 'Mathematics']);
        $scienceSubject = Subject::factory()->create(['department' => 'Science']);

        // Assign compatible subject
        $teacher->assignSubject($mathSubject);
        $this->assertTrue($teacher->subjects->contains($mathSubject));

        // Verify subject assignment in database
        $this->assertDatabaseHas('teacher_subject', [
            'teacher_id' => $teacher->id,
            'subject_id' => $mathSubject->id,
        ]);

        // Test that teacher can teach compatible subjects
        $this->assertTrue($teacher->canTeachSubject($mathSubject));
        $this->assertFalse($teacher->canTeachSubject($scienceSubject));
    }

    public function test_academic_year_transition_process(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Create current and next academic years
        $currentYear = AcademicYear::factory()->current()->create(['name' => '2024-2025']);
        $nextYear = AcademicYear::factory()->create(['name' => '2025-2026', 'is_current' => false]);

        // Create students in current year
        $students = Student::factory()->count(3)->create(['academic_year_id' => $currentYear->id]);

        // Transition to next academic year
        $nextYear->setCurrent();

        // Verify transition
        $this->assertTrue($nextYear->fresh()->is_current);
        $this->assertFalse($currentYear->fresh()->is_current);
        
        // Verify only one current academic year
        $this->assertEquals(1, AcademicYear::where('is_current', true)->count());
    }

    public function test_student_grade_progression(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Create student in Grade 5
        $grade5Class = SchoolClass::factory()->create(['grade_level' => 5]);
        $student = Student::factory()->create(['school_class_id' => $grade5Class->id]);

        // Enroll in subjects and complete with passing grades
        $subjects = Subject::factory()->count(4)->create();
        foreach ($subjects as $subject) {
            $student->enrollInSubject($subject);
            $student->subjects()->updateExistingPivot($subject->id, [
                'status' => 'completed',
                'grade' => 85, // Passing grade
            ]);
        }

        // Create Grade 6 class for promotion
        $grade6Class = SchoolClass::factory()->create(['grade_level' => 6]);

        // Promote student to Grade 6
        $student->transferToClass($grade6Class);

        // Verify promotion
        $this->assertEquals($grade6Class->id, $student->fresh()->school_class_id);
        $this->assertEquals(6, $student->schoolClass->grade_level);
    }

    public function test_fee_payment_workflow(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Create student with unpaid fees
        $student = Student::factory()->create([
            'total_fees' => 5000,
            'fees_paid' => 0,
            'fees_pending' => 5000,
        ]);

        // Verify initial fee status
        $this->assertEquals('Unpaid', $student->fees_status);
        $this->assertFalse($student->hasPaidFees());

        // Process partial payment
        $student->updateFeesPayment(2000);
        $student->refresh();

        $this->assertEquals(2000, $student->fees_paid);
        $this->assertEquals(3000, $student->fees_pending);
        $this->assertEquals('Partial', $student->fees_status);

        // Complete payment
        $student->updateFeesPayment(3000);
        $student->refresh();

        $this->assertEquals(5000, $student->fees_paid);
        $this->assertEquals(0, $student->fees_pending);
        $this->assertEquals('Paid', $student->fees_status);
        $this->assertTrue($student->hasPaidFees());
    }

    public function test_teacher_class_assignment(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Create teacher and class
        $teacher = Teacher::factory()->create();
        $class = SchoolClass::factory()->create(['class_teacher_id' => null]);

        // Assign teacher to class
        $teacher->assignToClass($class);

        // Verify assignment
        $this->assertEquals($teacher->id, $class->fresh()->class_teacher_id);
        $this->assertTrue($teacher->classes->contains($class));
    }

    public function test_student_subject_dropping(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Create student and enroll in subject
        $student = Student::factory()->create();
        $subject = Subject::factory()->create();
        
        $student->enrollInSubject($subject);
        $this->assertEquals('enrolled', $student->subjects()->first()->pivot->status);

        // Drop subject
        $student->dropSubject($subject);
        $this->assertEquals('dropped', $student->subjects()->first()->pivot->status);
    }

    public function test_enrollment_business_rules_validation(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Test age-grade compatibility
        $student = Student::factory()->create();
        $student->user->update(['date_of_birth' => now()->subYears(20)]); // Too old for typical grade

        $elementaryClass = SchoolClass::factory()->create(['grade_level' => 1]);
        
        // This should work as business rules allow flexibility
        $student->update(['school_class_id' => $elementaryClass->id]);
        $this->assertEquals($elementaryClass->id, $student->fresh()->school_class_id);
    }

    public function test_database_transaction_integrity(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        DB::beginTransaction();
        
        try {
            // Create multiple related records
            $user = User::factory()->create();
            $student = Student::factory()->create(['user_id' => $user->id]);
            $subject = Subject::factory()->create();
            
            $student->enrollInSubject($subject);
            
            // Verify records exist within transaction
            $this->assertDatabaseHas('users', ['id' => $user->id]);
            $this->assertDatabaseHas('students', ['id' => $student->id]);
            $this->assertDatabaseHas('student_subject', [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
            ]);
            
            DB::commit();
            
            // Verify records persist after commit
            $this->assertDatabaseHas('users', ['id' => $user->id]);
            $this->assertDatabaseHas('students', ['id' => $student->id]);
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function test_concurrent_enrollment_handling(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Create student and subject
        $student = Student::factory()->create();
        $subject = Subject::factory()->create();

        // Simulate concurrent enrollment attempts
        $student->enrollInSubject($subject);
        $student->enrollInSubject($subject); // Second attempt

        // Should only have one enrollment record
        $enrollmentCount = $student->subjects()->where('subject_id', $subject->id)->count();
        $this->assertEquals(1, $enrollmentCount);
    }

    public function test_bulk_enrollment_operations(): void
    {
        $admin = $this->actingAsRole('Admin');
        
        // Create multiple students and subjects
        $students = Student::factory()->count(10)->create();
        $subjects = Subject::factory()->count(5)->create();

        // Perform bulk enrollment
        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                $student->enrollInSubject($subject);
            }
        }

        // Verify all enrollments
        $totalEnrollments = DB::table('student_subject')->count();
        $this->assertEquals(50, $totalEnrollments); // 10 students × 5 subjects
    }
}