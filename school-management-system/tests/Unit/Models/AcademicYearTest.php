<?php

namespace Tests\Unit\Models;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    public function test_academic_year_can_be_created_with_factory(): void
    {
        $academicYear = AcademicYear::factory()->create();

        $this->assertInstanceOf(AcademicYear::class, $academicYear);
        $this->assertDatabaseHas('academic_years', [
            'id' => $academicYear->id,
            'name' => $academicYear->name,
        ]);
    }

    public function test_academic_year_name_format(): void
    {
        $academicYear = AcademicYear::factory()->create([
            'name' => '2024-2025'
        ]);

        $this->assertEquals('2024-2025', $academicYear->name);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{4}$/', $academicYear->name);
    }

    public function test_academic_year_date_casting(): void
    {
        $academicYear = AcademicYear::factory()->create([
            'start_date' => '2024-08-15',
            'end_date' => '2025-06-30'
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $academicYear->start_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $academicYear->end_date);
    }

    public function test_set_current_method(): void
    {
        $currentYear = AcademicYear::factory()->current()->create();
        $otherYear = AcademicYear::factory()->create(['is_current' => false]);

        // Set a different year as current
        $otherYear->setCurrent();

        $currentYear->refresh();
        $otherYear->refresh();

        $this->assertFalse($currentYear->is_current);
        $this->assertTrue($otherYear->is_current);
    }

    public function test_current_id_static_method(): void
    {
        $currentYear = AcademicYear::factory()->current()->create();
        AcademicYear::factory()->create(['is_current' => false]);

        $currentId = AcademicYear::currentId();

        $this->assertEquals($currentYear->id, $currentId);
    }

    public function test_current_id_returns_null_when_no_current_year(): void
    {
        AcademicYear::query()->delete(); // Remove all academic years
        AcademicYear::factory()->create(['is_current' => false]);

        $currentId = AcademicYear::currentId();

        $this->assertNull($currentId);
    }

    public function test_is_active_attribute(): void
    {
        $startDate = now()->subMonth();
        $endDate = now()->addMonths(6);

        $academicYear = AcademicYear::factory()->create([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true
        ]);

        $this->assertTrue($academicYear->is_active_period);
    }

    public function test_is_not_active_when_dates_in_past(): void
    {
        $startDate = now()->subYear();
        $endDate = now()->subMonths(6);

        $academicYear = AcademicYear::factory()->create([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true
        ]);

        $this->assertFalse($academicYear->is_active_period);
    }

    public function test_is_not_active_when_dates_in_future(): void
    {
        $startDate = now()->addMonths(6);
        $endDate = now()->addYear();

        $academicYear = AcademicYear::factory()->create([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true
        ]);

        $this->assertFalse($academicYear->is_active_period);
    }

    public function test_academic_year_has_classes_relationship(): void
    {
        $academicYear = AcademicYear::factory()->create();
        $class = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);

        $this->assertTrue($academicYear->schoolClasses->contains($class));
        $this->assertEquals($academicYear->id, $class->academic_year_id);
    }

    public function test_academic_year_has_students_relationship(): void
    {
        $academicYear = AcademicYear::factory()->create();
        $student = Student::factory()->create(['academic_year_id' => $academicYear->id]);

        $this->assertTrue($academicYear->students->contains($student));
        $this->assertEquals($academicYear->id, $student->academic_year_id);
    }

    public function test_current_scope(): void
    {
        $currentYear = AcademicYear::factory()->current()->create();
        $pastYear = AcademicYear::factory()->create(['is_current' => false]);

        $current = AcademicYear::current()->first();

        $this->assertEquals($currentYear->id, $current->id);
        $this->assertNotEquals($pastYear->id, $current->id);
    }

    public function test_active_scope(): void
    {
        $activeYear = AcademicYear::factory()->create(['is_active' => true]);
        $inactiveYear = AcademicYear::factory()->create(['is_active' => false]);

        $activeYears = AcademicYear::active()->get();

        $this->assertTrue($activeYears->contains($activeYear));
        $this->assertFalse($activeYears->contains($inactiveYear));
    }

    public function test_for_year_scope(): void
    {
        $year2024 = AcademicYear::factory()->create(['name' => '2024-2025']);
        $year2025 = AcademicYear::factory()->create(['name' => '2025-2026']);

        $results = AcademicYear::forYear('2024-2025')->get();

        $this->assertTrue($results->contains($year2024));
        $this->assertFalse($results->contains($year2025));
    }

    public function test_holidays_json_casting(): void
    {
        $holidays = [
            'winter_break' => ['2024-12-20', '2025-01-08'],
            'spring_break' => ['2025-03-10', '2025-03-17'],
        ];

        $academicYear = AcademicYear::factory()->create(['holidays' => $holidays]);

        $this->assertEquals($holidays, $academicYear->holidays);
        $this->assertIsArray($academicYear->holidays);
    }

    public function test_academic_year_duration_calculation(): void
    {
        $academicYear = AcademicYear::factory()->create([
            'start_date' => '2024-08-15',
            'end_date' => '2025-06-30'
        ]);

        $duration = $academicYear->start_date->diffInDays($academicYear->end_date);
        
        $this->assertGreaterThan(300, $duration); // Academic year is typically 300+ days
        $this->assertLessThan(370, $duration);
    }

    public function test_only_one_current_academic_year_allowed(): void
    {
        $firstYear = AcademicYear::factory()->current()->create();
        $this->assertTrue($firstYear->is_current);

        // Create another year and set it as current
        $secondYear = AcademicYear::factory()->create(['is_current' => false]);
        $secondYear->setCurrent();

        // Refresh first year from database
        $firstYear->refresh();

        // Only one should be current
        $this->assertFalse($firstYear->is_current);
        $this->assertTrue($secondYear->is_current);

        // Verify in database
        $currentCount = AcademicYear::where('is_current', true)->count();
        $this->assertEquals(1, $currentCount);
    }

    public function test_academic_year_factory_states(): void
    {
        $currentYear = AcademicYear::factory()->current()->create();
        $pastYear = AcademicYear::factory()->past()->create();
        $inactiveYear = AcademicYear::factory()->inactive()->create();

        $this->assertTrue($currentYear->is_current);
        $this->assertFalse($pastYear->is_current);
        $this->assertFalse($inactiveYear->is_active);
    }
}