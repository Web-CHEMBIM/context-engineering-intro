<?php

namespace Tests\Unit\Models;

use App\Models\Student;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\Subject;
use Tests\TestCase;

class StudentTest extends TestCase
{
    public function test_student_can_be_created_with_factory(): void
    {
        $student = Student::factory()->create();

        $this->assertInstanceOf(Student::class, $student);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'student_id' => $student->student_id,
        ]);
    }

    public function test_student_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $student->user);
        $this->assertEquals($user->id, $student->user->id);
    }

    public function test_student_belongs_to_school_class(): void
    {
        $class = SchoolClass::factory()->create();
        $student = Student::factory()->create(['school_class_id' => $class->id]);

        $this->assertInstanceOf(SchoolClass::class, $student->schoolClass);
        $this->assertEquals($class->id, $student->schoolClass->id);
    }

    public function test_student_belongs_to_academic_year(): void
    {
        $academicYear = AcademicYear::factory()->create();
        $student = Student::factory()->create(['academic_year_id' => $academicYear->id]);

        $this->assertInstanceOf(AcademicYear::class, $student->academicYear);
        $this->assertEquals($academicYear->id, $student->academicYear->id);
    }

    public function test_student_full_name_attribute(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('John Doe', $student->full_name);
    }

    public function test_student_fees_status_attribute(): void
    {
        // Test fully paid
        $paidStudent = Student::factory()->create([
            'total_fees' => 1000,
            'fees_paid' => 1000,
            'fees_pending' => 0,
        ]);
        $this->assertEquals('Paid', $paidStudent->fees_status);

        // Test unpaid
        $unpaidStudent = Student::factory()->create([
            'total_fees' => 1000,
            'fees_paid' => 0,
            'fees_pending' => 1000,
        ]);
        $this->assertEquals('Unpaid', $unpaidStudent->fees_status);

        // Test partial payment
        $partialStudent = Student::factory()->create([
            'total_fees' => 1000,
            'fees_paid' => 500,
            'fees_pending' => 500,
        ]);
        $this->assertEquals('Partial', $partialStudent->fees_status);
    }

    public function test_student_enrollment_status_attribute(): void
    {
        $student = Student::factory()->create(['student_status' => 'enrolled']);
        $this->assertEquals('Enrolled', $student->enrollment_status);

        $student = Student::factory()->create(['student_status' => 'graduated']);
        $this->assertEquals('Graduated', $student->enrollment_status);
    }

    public function test_student_fees_percentage_paid_attribute(): void
    {
        $student = Student::factory()->create([
            'total_fees' => 1000,
            'fees_paid' => 750,
        ]);

        $this->assertEquals(75.0, $student->fees_percentage_paid);
    }

    public function test_student_class_name_attribute(): void
    {
        $class = SchoolClass::factory()->create(['name' => 'Grade 5 - Section A']);
        $student = Student::factory()->create(['school_class_id' => $class->id]);

        $this->assertEquals('Grade 5 - Section A', $student->class_name);
    }

    public function test_student_academic_year_name_attribute(): void
    {
        $academicYear = AcademicYear::factory()->create(['name' => '2024-2025']);
        $student = Student::factory()->create(['academic_year_id' => $academicYear->id]);

        $this->assertEquals('2024-2025', $student->academic_year_name);
    }

    public function test_student_has_paid_fees_method(): void
    {
        $paidStudent = Student::factory()->create(['fees_pending' => 0]);
        $unpaidStudent = Student::factory()->create(['fees_pending' => 100]);

        $this->assertTrue($paidStudent->hasPaidFees());
        $this->assertFalse($unpaidStudent->hasPaidFees());
    }

    public function test_student_enroll_in_subject(): void
    {
        $student = Student::factory()->create();
        $subject = Subject::factory()->create();

        $student->enrollInSubject($subject);

        $this->assertTrue($student->subjects->contains($subject));
        $this->assertDatabaseHas('student_subject', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => 'enrolled',
        ]);
    }

    public function test_student_cannot_enroll_in_same_subject_twice(): void
    {
        $student = Student::factory()->create();
        $subject = Subject::factory()->create();

        // First enrollment
        $student->enrollInSubject($subject);
        
        // Second enrollment attempt
        $student->enrollInSubject($subject);

        // Should still only have one enrollment
        $enrollmentCount = $student->subjects()->where('subject_id', $subject->id)->count();
        $this->assertEquals(1, $enrollmentCount);
    }

    public function test_student_drop_subject(): void
    {
        $student = Student::factory()->create();
        $subject = Subject::factory()->create();

        // First enroll
        $student->enrollInSubject($subject);

        // Then drop
        $student->dropSubject($subject);

        $this->assertDatabaseHas('student_subject', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => 'dropped',
        ]);
    }

    public function test_student_update_fees_payment(): void
    {
        $student = Student::factory()->create([
            'total_fees' => 1000,
            'fees_paid' => 200,
            'fees_pending' => 800,
        ]);

        $student->updateFeesPayment(300);
        $student->refresh();

        $this->assertEquals(500, $student->fees_paid);
        $this->assertEquals(500, $student->fees_pending);
    }

    public function test_student_update_fees_payment_overpayment(): void
    {
        $student = Student::factory()->create([
            'total_fees' => 1000,
            'fees_paid' => 800,
            'fees_pending' => 200,
        ]);

        $student->updateFeesPayment(500); // Pay more than pending
        $student->refresh();

        $this->assertEquals(1300, $student->fees_paid);
        $this->assertEquals(0, $student->fees_pending); // Should not go negative
    }

    public function test_student_transfer_to_class(): void
    {
        $originalClass = SchoolClass::factory()->create();
        $newClass = SchoolClass::factory()->create();
        $student = Student::factory()->create(['school_class_id' => $originalClass->id]);

        $student->transferToClass($newClass);
        $student->refresh();

        $this->assertEquals($newClass->id, $student->school_class_id);
        $this->assertEquals($newClass->academic_year_id, $student->academic_year_id);
    }

    public function test_current_year_scope(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $otherYear = AcademicYear::factory()->create(['is_current' => false]);

        $currentStudent = Student::factory()->create(['academic_year_id' => $currentYear->id]);
        $otherStudent = Student::factory()->create(['academic_year_id' => $otherYear->id]);

        $currentStudents = Student::currentYear()->get();

        $this->assertTrue($currentStudents->contains($currentStudent));
        $this->assertFalse($currentStudents->contains($otherStudent));
    }

    public function test_enrolled_scope(): void
    {
        $enrolledStudent = Student::factory()->create(['student_status' => 'enrolled']);
        $graduatedStudent = Student::factory()->create(['student_status' => 'graduated']);

        $enrolledStudents = Student::enrolled()->get();

        $this->assertTrue($enrolledStudents->contains($enrolledStudent));
        $this->assertFalse($enrolledStudents->contains($graduatedStudent));
    }

    public function test_with_pending_fees_scope(): void
    {
        $studentWithPendingFees = Student::factory()->create(['fees_pending' => 100]);
        $studentWithoutPendingFees = Student::factory()->create(['fees_pending' => 0]);

        $studentsWithPendingFees = Student::withPendingFees()->get();

        $this->assertTrue($studentsWithPendingFees->contains($studentWithPendingFees));
        $this->assertFalse($studentsWithPendingFees->contains($studentWithoutPendingFees));
    }

    public function test_student_factory_states(): void
    {
        $enrolledStudent = Student::factory()->enrolled()->create();
        $topPerformer = Student::factory()->topPerformer()->create();
        $newStudent = Student::factory()->newStudent()->create();
        $graduatedStudent = Student::factory()->graduated()->create();

        $this->assertEquals('enrolled', $enrolledStudent->student_status);
        $this->assertEquals('enrolled', $topPerformer->student_status);
        $this->assertEquals(0, $topPerformer->fees_pending);
        $this->assertEquals('enrolled', $newStudent->student_status);
        $this->assertEquals('graduated', $graduatedStudent->student_status);
        $this->assertEquals(0, $graduatedStudent->fees_pending);
    }

    public function test_automatic_fees_calculation_on_save(): void
    {
        $student = Student::factory()->make([
            'total_fees' => 1000,
            'fees_paid' => 600,
        ]);

        $student->save();

        $this->assertEquals(400, $student->fees_pending);
    }

    public function test_fees_pending_cannot_be_negative(): void
    {
        $student = Student::factory()->create([
            'total_fees' => 1000,
            'fees_paid' => 1200, // More than total
        ]);

        $student->save();

        $this->assertEquals(0, $student->fresh()->fees_pending);
    }
}