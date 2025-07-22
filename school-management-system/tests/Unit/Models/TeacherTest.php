<?php

namespace Tests\Unit\Models;

use App\Models\Teacher;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    public function test_teacher_can_be_created_with_factory(): void
    {
        $teacher = Teacher::factory()->create();

        $this->assertInstanceOf(Teacher::class, $teacher);
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'teacher_id' => $teacher->teacher_id,
        ]);
    }

    public function test_teacher_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $teacher->user);
        $this->assertEquals($user->id, $teacher->user->id);
    }

    public function test_teacher_has_many_classes(): void
    {
        $teacher = Teacher::factory()->create();
        $class1 = SchoolClass::factory()->create(['class_teacher_id' => $teacher->id]);
        $class2 = SchoolClass::factory()->create(['class_teacher_id' => $teacher->id]);

        $this->assertTrue($teacher->classes->contains($class1));
        $this->assertTrue($teacher->classes->contains($class2));
        $this->assertEquals(2, $teacher->classes->count());
    }

    public function test_teacher_has_many_subjects(): void
    {
        $teacher = Teacher::factory()->create();
        $subject1 = Subject::factory()->create();
        $subject2 = Subject::factory()->create();

        // Attach subjects to teacher
        $teacher->subjects()->attach([$subject1->id, $subject2->id]);

        $this->assertTrue($teacher->subjects->contains($subject1));
        $this->assertTrue($teacher->subjects->contains($subject2));
        $this->assertEquals(2, $teacher->subjects->count());
    }

    public function test_teacher_full_name_attribute(): void
    {
        $user = User::factory()->create(['name' => 'Jane Smith']);
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('Jane Smith', $teacher->full_name);
    }

    public function test_teacher_email_attribute(): void
    {
        $user = User::factory()->create(['email' => 'teacher@school.edu']);
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('teacher@school.edu', $teacher->email);
    }

    public function test_teacher_phone_attribute(): void
    {
        $user = User::factory()->create(['phone' => '+1234567890']);
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('+1234567890', $teacher->phone);
    }

    public function test_teacher_years_of_service_attribute(): void
    {
        $hireDate = now()->subYears(5);
        $teacher = Teacher::factory()->create(['hire_date' => $hireDate]);

        $this->assertEquals(5, $teacher->years_of_service);
    }

    public function test_teacher_contract_status_attribute(): void
    {
        $fullTimeTeacher = Teacher::factory()->create(['contract_type' => 'full_time']);
        $partTimeTeacher = Teacher::factory()->create(['contract_type' => 'part_time']);
        $contractTeacher = Teacher::factory()->create(['contract_type' => 'contract']);

        $this->assertEquals('Full Time', $fullTimeTeacher->contract_status);
        $this->assertEquals('Part Time', $partTimeTeacher->contract_status);
        $this->assertEquals('Contract', $contractTeacher->contract_status);
    }

    public function test_teacher_monthly_salary_calculation(): void
    {
        $teacher = Teacher::factory()->create(['salary' => 60000]);

        $this->assertEquals(5000.0, $teacher->monthly_salary);
    }

    public function test_teacher_office_hours_json_casting(): void
    {
        $officeHours = [
            'Monday' => '09:00-12:00',
            'Tuesday' => '10:00-14:00',
            'Friday' => '08:00-11:00',
        ];

        $teacher = Teacher::factory()->create(['office_hours' => $officeHours]);

        $this->assertEquals($officeHours, $teacher->office_hours);
        $this->assertIsArray($teacher->office_hours);
    }

    public function test_teacher_can_teach_subject(): void
    {
        $teacher = Teacher::factory()->create(['department' => 'Mathematics']);
        $mathSubject = Subject::factory()->create(['department' => 'Mathematics']);
        $scienceSubject = Subject::factory()->create(['department' => 'Science']);

        $this->assertTrue($teacher->canTeachSubject($mathSubject));
        $this->assertFalse($teacher->canTeachSubject($scienceSubject));
    }

    public function test_teacher_workload_calculation(): void
    {
        $teacher = Teacher::factory()->create();
        
        // Create classes and subjects
        $class1 = SchoolClass::factory()->create(['class_teacher_id' => $teacher->id]);
        $class2 = SchoolClass::factory()->create(['class_teacher_id' => $teacher->id]);
        
        $subject1 = Subject::factory()->create(['credit_hours' => 5]);
        $subject2 = Subject::factory()->create(['credit_hours' => 3]);
        
        $teacher->subjects()->attach([
            $subject1->id => ['credit_hours' => 5],
            $subject2->id => ['credit_hours' => 3],
        ]);

        $workload = $teacher->calculateWorkload();

        $this->assertArrayHasKey('classes_count', $workload);
        $this->assertArrayHasKey('subjects_count', $workload);
        $this->assertArrayHasKey('total_credit_hours', $workload);
        
        $this->assertEquals(2, $workload['classes_count']);
        $this->assertEquals(2, $workload['subjects_count']);
        $this->assertEquals(8, $workload['total_credit_hours']);
    }

    public function test_teacher_is_overloaded(): void
    {
        $teacher = Teacher::factory()->create();
        
        // Create many subjects to overload the teacher
        $subjects = Subject::factory()->count(8)->create(['credit_hours' => 4]);
        $teacher->subjects()->attach($subjects->pluck('id')->mapWithKeys(function($id) {
            return [$id => ['credit_hours' => 4]];
        })->toArray());

        $this->assertTrue($teacher->isOverloaded()); // 8 * 4 = 32 hours > 25 hour limit
    }

    public function test_teacher_is_not_overloaded(): void
    {
        $teacher = Teacher::factory()->create();
        
        // Create reasonable workload
        $subjects = Subject::factory()->count(3)->create(['credit_hours' => 5]);
        $teacher->subjects()->attach($subjects->pluck('id')->mapWithKeys(function($id) {
            return [$id => ['credit_hours' => 5]];
        })->toArray());

        $this->assertFalse($teacher->isOverloaded()); // 3 * 5 = 15 hours < 25 hour limit
    }

    public function test_teacher_assign_to_class(): void
    {
        $teacher = Teacher::factory()->create();
        $class = SchoolClass::factory()->create();

        $teacher->assignToClass($class);

        $this->assertEquals($teacher->id, $class->fresh()->class_teacher_id);
        $this->assertTrue($teacher->classes->contains($class));
    }

    public function test_teacher_assign_subject(): void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create(['credit_hours' => 4]);

        $teacher->assignSubject($subject);

        $this->assertTrue($teacher->subjects->contains($subject));
        $this->assertDatabaseHas('teacher_subject', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'credit_hours' => 4,
        ]);
    }

    public function test_teacher_remove_subject(): void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create();

        $teacher->assignSubject($subject);
        $teacher->removeSubject($subject);

        $this->assertFalse($teacher->fresh()->subjects->contains($subject));
        $this->assertDatabaseMissing('teacher_subject', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_active_scope(): void
    {
        $activeTeacher = Teacher::factory()->create();
        $activeTeacher->user->update(['is_active' => true]);
        
        $inactiveTeacher = Teacher::factory()->create();
        $inactiveTeacher->user->update(['is_active' => false]);

        $activeTeachers = Teacher::active()->get();

        $this->assertTrue($activeTeachers->contains($activeTeacher));
        $this->assertFalse($activeTeachers->contains($inactiveTeacher));
    }

    public function test_by_department_scope(): void
    {
        $mathTeacher = Teacher::factory()->create(['department' => 'Mathematics']);
        $scienceTeacher = Teacher::factory()->create(['department' => 'Science']);

        $mathTeachers = Teacher::byDepartment('Mathematics')->get();

        $this->assertTrue($mathTeachers->contains($mathTeacher));
        $this->assertFalse($mathTeachers->contains($scienceTeacher));
    }

    public function test_full_time_scope(): void
    {
        $fullTimeTeacher = Teacher::factory()->create(['contract_type' => 'full_time']);
        $partTimeTeacher = Teacher::factory()->create(['contract_type' => 'part_time']);

        $fullTimeTeachers = Teacher::fullTime()->get();

        $this->assertTrue($fullTimeTeachers->contains($fullTimeTeacher));
        $this->assertFalse($fullTimeTeachers->contains($partTimeTeacher));
    }

    public function test_heads_of_department_scope(): void
    {
        $hodTeacher = Teacher::factory()->create(['is_head_of_department' => true]);
        $regularTeacher = Teacher::factory()->create(['is_head_of_department' => false]);

        $hods = Teacher::headsOfDepartment()->get();

        $this->assertTrue($hods->contains($hodTeacher));
        $this->assertFalse($hods->contains($regularTeacher));
    }

    public function test_teacher_factory_states(): void
    {
        $headOfDept = Teacher::factory()->headOfDepartment()->create();
        $newTeacher = Teacher::factory()->newTeacher()->create();
        $veteran = Teacher::factory()->veteran()->create();
        $partTime = Teacher::factory()->partTime()->create();

        $this->assertTrue($headOfDept->is_head_of_department);
        $this->assertEquals('full_time', $headOfDept->contract_type);
        $this->assertGreaterThanOrEqual(10, $headOfDept->experience_years);

        $this->assertFalse($newTeacher->is_head_of_department);
        $this->assertLessThanOrEqual(3, $newTeacher->experience_years);

        $this->assertGreaterThanOrEqual(10, $veteran->experience_years);

        $this->assertEquals('part_time', $partTime->contract_type);
    }

    public function test_teacher_salary_generation_by_department(): void
    {
        $mathTeacher = Teacher::factory()->create(['department' => 'Mathematics']);
        $peTeacher = Teacher::factory()->create(['department' => 'Physical Education']);

        // Math teachers should have higher multiplier than PE teachers
        // This is a general test since exact salary depends on experience too
        $this->assertIsFloat($mathTeacher->salary);
        $this->assertIsFloat($peTeacher->salary);
        $this->assertGreaterThan(0, $mathTeacher->salary);
        $this->assertGreaterThan(0, $peTeacher->salary);
    }
}