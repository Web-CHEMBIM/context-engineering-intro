<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Student Seeder for School Management System
 * 
 * Creates realistic student population with proper class distribution
 */
class StudentSeeder extends Seeder
{
    /**
     * Target enrollment per grade level.
     */
    private array $enrollmentTargets = [
        1 => 45,  2 => 48,  3 => 50,  4 => 52,  5 => 50,  // Elementary
        6 => 65,  7 => 68,  8 => 70,                       // Middle School
        9 => 95,  10 => 100, 11 => 95, 12 => 85,          // High School
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Students...');

        $currentYear = AcademicYear::where('is_current', true)->first();
        if (!$currentYear) {
            $this->command->error('No current academic year found.');
            return;
        }

        $totalCreated = 0;

        foreach ($this->enrollmentTargets as $grade => $targetEnrollment) {
            $this->command->info("Creating ~{$targetEnrollment} students for Grade {$grade}...");
            
            // Get classes for this grade level
            $classes = SchoolClass::where('academic_year_id', $currentYear->id)
                                ->where('grade_level', $grade)
                                ->where('is_active', true)
                                ->whereIn('section', ['A', 'B', 'C', 'D', 'E']) // Regular sections only
                                ->get();

            if ($classes->isEmpty()) {
                $this->command->warn("No classes found for Grade {$grade}");
                continue;
            }

            // Distribute students across available classes
            $studentsPerClass = (int)ceil($targetEnrollment / $classes->count());
            
            foreach ($classes as $class) {
                $classCapacity = min($studentsPerClass, $class->capacity);
                
                for ($i = 0; $i < $classCapacity; $i++) {
                    // Create user account
                    $user = User::factory()->student()->create([
                        'email' => fake()->unique()->userName() . '@student.school.edu',
                        'password' => Hash::make('password'),
                        'date_of_birth' => $this->generateBirthDateForGrade($grade),
                    ]);

                    // Assign student role
                    $user->assignRole('Student');

                    // Create student profile
                    $studentType = $this->determineStudentType();
                    $student = $this->createStudentOfType($user, $class, $studentType);
                    
                    $totalCreated++;
                }
            }
        }

        // Create some special student types
        $this->createSpecialStudents($currentYear);

        // Enroll students in subjects
        $this->enrollStudentsInSubjects();

        $this->command->info("✓ Created {$totalCreated} students across all grade levels");
        $this->command->info('✓ Students distributed evenly across class sections');
        $this->command->info('✓ Enrolled students in appropriate subjects based on grade level');
    }

    /**
     * Generate appropriate birth date for grade level.
     */
    private function generateBirthDateForGrade(int $grade): \DateTime
    {
        // Typical age calculation: Kindergarten = 5, Grade 1 = 6, etc.
        $typicalAge = 5 + $grade;
        $minAge = $typicalAge - 1;  // Allow 1 year younger
        $maxAge = $typicalAge + 2;  // Allow 2 years older

        $age = fake()->numberBetween($minAge, $maxAge);
        
        return fake()->dateTimeBetween("-{$age} years", "-" . ($age - 1) . " years");
    }

    /**
     * Determine what type of student to create based on realistic distribution.
     */
    private function determineStudentType(): string
    {
        $weights = [
            'enrolled' => 85,        // Most students are regularly enrolled
            'topPerformer' => 8,     // High achievers
            'withPendingFees' => 5,  // Students with fee issues
            'newStudent' => 2,       // Recently transferred/admitted
        ];

        $rand = fake()->numberBetween(1, 100);
        $cumulative = 0;

        foreach ($weights as $type => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $type;
            }
        }

        return 'enrolled';
    }

    /**
     * Create a student of specific type.
     */
    private function createStudentOfType(User $user, SchoolClass $class, string $type): Student
    {
        $baseData = [
            'user_id' => $user->id,
            'school_class_id' => $class->id,
            'academic_year_id' => $class->academic_year_id,
        ];

        return match ($type) {
            'topPerformer' => Student::factory()->topPerformer()->create($baseData),
            'withPendingFees' => Student::factory()->withPendingFees()->create($baseData),
            'newStudent' => Student::factory()->newStudent()->create($baseData),
            default => Student::factory()->enrolled()->create($baseData),
        };
    }

    /**
     * Create special student populations.
     */
    private function createSpecialStudents(AcademicYear $currentYear): void
    {
        // International Students (3-5 per grade 9-12)
        for ($grade = 9; $grade <= 12; $grade++) {
            $classes = SchoolClass::where('academic_year_id', $currentYear->id)
                                ->where('grade_level', $grade)
                                ->where('is_active', true)
                                ->take(2)
                                ->get();

            foreach ($classes as $class) {
                for ($i = 0; $i < fake()->numberBetween(2, 4); $i++) {
                    $user = User::factory()->student()->create([
                        'email' => fake()->unique()->userName() . '@international.school.edu',
                        'password' => Hash::make('password'),
                        'date_of_birth' => $this->generateBirthDateForGrade($grade),
                        'address' => fake()->address() . ', ' . fake()->country(),
                    ]);

                    $user->assignRole('Student');

                    Student::factory()->international()->create([
                        'user_id' => $user->id,
                        'school_class_id' => $class->id,
                        'academic_year_id' => $currentYear->id,
                    ]);
                }
            }
        }

        // Special Needs Students
        $specialClasses = SchoolClass::where('academic_year_id', $currentYear->id)
                                   ->where('section', 'SE')
                                   ->get();

        foreach ($specialClasses as $class) {
            for ($i = 0; $i < fake()->numberBetween(8, 12); $i++) {
                $user = User::factory()->student()->create([
                    'email' => fake()->unique()->userName() . '@student.school.edu',
                    'password' => Hash::make('password'),
                    'date_of_birth' => $this->generateBirthDateForGrade($class->grade_level),
                ]);

                $user->assignRole('Student');

                Student::factory()->specialNeeds()->create([
                    'user_id' => $user->id,
                    'school_class_id' => $class->id,
                    'academic_year_id' => $currentYear->id,
                ]);
            }
        }

        // ELL (English Language Learner) Students
        $ellClasses = SchoolClass::where('academic_year_id', $currentYear->id)
                                ->where('section', 'ELL')
                                ->get();

        foreach ($ellClasses as $class) {
            for ($i = 0; $i < fake()->numberBetween(15, 20); $i++) {
                $user = User::factory()->student()->create([
                    'email' => fake()->unique()->userName() . '@student.school.edu',
                    'password' => Hash::make('password'),
                    'date_of_birth' => $this->generateBirthDateForGrade($class->grade_level),
                ]);

                $user->assignRole('Student');

                Student::factory()->create([
                    'user_id' => $user->id,
                    'school_class_id' => $class->id,
                    'academic_year_id' => $currentYear->id,
                    'student_status' => 'enrolled',
                    'medical_conditions' => 'ELL Support Required',
                ]);
            }
        }

        $this->command->info('✓ Created special student populations: International, Special Needs, ELL');
    }

    /**
     * Enroll students in appropriate subjects based on their grade level.
     */
    private function enrollStudentsInSubjects(): void
    {
        $this->command->info('Enrolling students in subjects...');

        $currentYear = AcademicYear::where('is_current', true)->first();
        $students = Student::where('academic_year_id', $currentYear->id)
                          ->where('student_status', 'enrolled')
                          ->with('schoolClass')
                          ->get();

        $enrolled = 0;
        foreach ($students as $student) {
            $gradeLevel = $student->schoolClass->grade_level;
            $subjects = $this->getSubjectsForGrade($gradeLevel);

            foreach ($subjects as $subject) {
                // Mandatory subjects - enroll all students
                if ($subject->is_mandatory) {
                    $student->enrollInSubject($subject);
                    $enrolled++;
                } else {
                    // Electives - enroll some students (60-80% participation)
                    if (fake()->boolean(fake()->numberBetween(60, 80))) {
                        $student->enrollInSubject($subject);
                        $enrolled++;
                    }
                }
            }
        }

        $this->command->info("✓ Completed {$enrolled} subject enrollments");
    }

    /**
     * Get appropriate subjects for a grade level.
     */
    private function getSubjectsForGrade(int $gradeLevel): \Illuminate\Database\Eloquent\Collection
    {
        return Subject::where('is_active', true)
                     ->where(function($query) use ($gradeLevel) {
                         $query->whereRaw("JSON_CONTAINS(grade_levels, '\"$gradeLevel\"')");
                     })
                     ->get();
    }
}