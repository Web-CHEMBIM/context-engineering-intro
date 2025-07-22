<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gradeLevel = fake()->numberBetween(1, 12);
        $section = fake()->randomElement(['A', 'B', 'C', 'D', 'E']);
        
        return [
            'name' => "Grade {$gradeLevel} - Section {$section}",
            'grade_level' => (string)$gradeLevel,
            'section' => $section,
            'academic_year_id' => AcademicYear::factory(),
            'capacity' => $this->getCapacityForGrade($gradeLevel),
            'description' => fake()->optional(0.3)->sentence(),
            'is_active' => fake()->boolean(95),
        ];
    }

    /**
     * Get appropriate capacity based on grade level.
     */
    protected function getCapacityForGrade(int $gradeLevel): int
    {
        // Younger grades typically have smaller class sizes
        if ($gradeLevel <= 3) {
            return fake()->numberBetween(15, 22);
        } elseif ($gradeLevel <= 8) {
            return fake()->numberBetween(20, 28);
        } else {
            return fake()->numberBetween(25, 35);
        }
    }

    /**
     * Generate a realistic class schedule.
     */
    protected function generateSchedule(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $schedule = [];
        
        foreach ($days as $day) {
            $schedule[$day] = [
                'start_time' => '08:00',
                'end_time' => $this->getEndTimeForGrade(),
                'periods' => fake()->numberBetween(6, 8),
                'lunch_break' => '12:00-12:45',
            ];
        }
        
        return $schedule;
    }

    /**
     * Get appropriate end time based on grade level.
     */
    protected function getEndTimeForGrade(): string
    {
        // Younger students have shorter days
        $gradeLevels = [
            1 => '14:30', 2 => '14:30', 3 => '14:45',
            4 => '15:00', 5 => '15:00', 6 => '15:15',
            7 => '15:15', 8 => '15:30', 9 => '15:30',
            10 => '15:45', 11 => '15:45', 12 => '16:00'
        ];
        
        return $gradeLevels[fake()->numberBetween(1, 12)] ?? '15:30';
    }

    /**
     * Create an elementary class (grades 1-5).
     */
    public function elementary(): static
    {
        $gradeLevel = fake()->numberBetween(1, 5);
        
        return $this->state([
            'grade_level' => (string)$gradeLevel,
            'name' => "Grade {$gradeLevel} - Section " . fake()->randomElement(['A', 'B', 'C']),
            'capacity' => fake()->numberBetween(15, 25),
        ]);
    }

    /**
     * Create a middle school class (grades 6-8).
     */
    public function middleSchool(): static
    {
        $gradeLevel = fake()->numberBetween(6, 8);
        
        return $this->state([
            'grade_level' => (string)$gradeLevel,
            'name' => "Grade {$gradeLevel} - Section " . fake()->randomElement(['A', 'B', 'C', 'D']),
            'capacity' => fake()->numberBetween(20, 28),
        ]);
    }

    /**
     * Create a high school class (grades 9-12).
     */
    public function highSchool(): static
    {
        $gradeLevel = fake()->numberBetween(9, 12);
        
        return $this->state([
            'grade_level' => (string)$gradeLevel,
            'name' => "Grade {$gradeLevel} - Section " . fake()->randomElement(['A', 'B', 'C', 'D', 'E']),
            'capacity' => fake()->numberBetween(25, 35),
        ]);
    }

    /**
     * Create a class with a specific teacher.
     * Note: Teacher assignment handled separately via pivot table
     */
    public function withTeacher(?Teacher $teacher = null): static
    {
        return $this->state([
            // Teacher assignment will be handled via the teacher_class pivot table
        ]);
    }

    /**
     * Create a class for a specific academic year.
     */
    public function forAcademicYear(AcademicYear $academicYear): static
    {
        return $this->state([
            'academic_year_id' => $academicYear->id,
        ]);
    }

    /**
     * Create a full capacity class.
     */
    public function fullCapacity(): static
    {
        return $this->afterCreating(function ($class) {
            // This would be handled by the seeder to create students up to capacity
        });
    }
}