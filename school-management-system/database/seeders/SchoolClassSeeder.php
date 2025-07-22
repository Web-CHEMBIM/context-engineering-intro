<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

/**
 * School Class Seeder for School Management System
 * 
 * Creates realistic class structure for K-12 education
 */
class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding School Classes...');

        $currentYear = AcademicYear::where('is_current', true)->first();
        $pastYear = AcademicYear::where('name', '2023-2024')->first();
        
        if (!$currentYear) {
            $this->command->error('No current academic year found. Run AcademicYearSeeder first.');
            return;
        }

        $created = 0;

        // Elementary Classes (Grades K-5) - 2 sections each
        for ($grade = 1; $grade <= 5; $grade++) {
            foreach (['A', 'B'] as $section) {
                $class = SchoolClass::factory()->elementary()->create([
                    'name' => "Grade {$grade} - Section {$section}",
                    'grade_level' => (string)$grade,
                    'section' => $section,
                    'academic_year_id' => $currentYear->id,
                    'capacity' => $this->getCapacityForGrade($grade),
                ]);
                $created++;

                // Create some past year classes for historical data
                if ($pastYear) {
                    SchoolClass::factory()->elementary()->create([
                        'name' => "Grade {$grade} - Section {$section}",
                        'grade_level' => (string)$grade,
                        'section' => $section,
                        'academic_year_id' => $pastYear->id,
                        'capacity' => $this->getCapacityForGrade($grade),
                        'is_active' => false, // Past year classes are inactive
                    ]);
                    $created++;
                }
            }
        }

        // Middle School Classes (Grades 6-8) - 3 sections each
        for ($grade = 6; $grade <= 8; $grade++) {
            foreach (['A', 'B', 'C'] as $section) {
                $class = SchoolClass::factory()->middleSchool()->create([
                    'name' => "Grade {$grade} - Section {$section}",
                    'grade_level' => $grade,
                    'section' => $section,
                    'academic_year_id' => $currentYear->id,
                    'capacity' => $this->getCapacityForGrade($grade),
                    'room_number' => "M{$grade}{$section}",
                ]);
                $created++;

                // Past year classes
                if ($pastYear) {
                    SchoolClass::factory()->middleSchool()->create([
                        'name' => "Grade {$grade} - Section {$section}",
                        'grade_level' => $grade,
                        'section' => $section,
                        'academic_year_id' => $pastYear->id,
                        'capacity' => $this->getCapacityForGrade($grade),
                        'room_number' => "M{$grade}{$section}",
                        'is_active' => false,
                    ]);
                    $created++;
                }
            }
        }

        // High School Classes (Grades 9-12) - 4 sections each
        for ($grade = 9; $grade <= 12; $grade++) {
            foreach (['A', 'B', 'C', 'D'] as $section) {
                $class = SchoolClass::factory()->highSchool()->create([
                    'name' => "Grade {$grade} - Section {$section}",
                    'grade_level' => $grade,
                    'section' => $section,
                    'academic_year_id' => $currentYear->id,
                    'capacity' => $this->getCapacityForGrade($grade),
                    'room_number' => "H{$grade}{$section}",
                ]);
                $created++;

                // Past year classes
                if ($pastYear) {
                    SchoolClass::factory()->highSchool()->create([
                        'name' => "Grade {$grade} - Section {$section}",
                        'grade_level' => $grade,
                        'section' => $section,
                        'academic_year_id' => $pastYear->id,
                        'capacity' => $this->getCapacityForGrade($grade),
                        'room_number' => "H{$grade}{$section}",
                        'is_active' => false,
                    ]);
                    $created++;
                }
            }
        }

        // Create some specialized classes
        $this->createSpecializedClasses($currentYear, $pastYear);

        $this->command->info("✓ Created {$created} School Classes");
        $this->command->info('✓ Class structure: Elementary (K-5), Middle School (6-8), High School (9-12)');
        $this->command->info('✓ Multiple sections per grade level for realistic enrollment');
    }

    /**
     * Get appropriate capacity based on grade level.
     */
    private function getCapacityForGrade(int $grade): int
    {
        return match (true) {
            $grade <= 5 => fake()->numberBetween(18, 24),   // Elementary: smaller classes
            $grade <= 8 => fake()->numberBetween(22, 28),   // Middle: medium classes
            $grade <= 12 => fake()->numberBetween(25, 32),  // High school: larger classes
            default => 25
        };
    }

    /**
     * Create specialized classes like honors, AP, special education.
     */
    private function createSpecializedClasses(AcademicYear $currentYear, ?AcademicYear $pastYear): void
    {
        $specialClasses = [
            // High School Honors Classes
            ['name' => 'Honors English - Grade 9', 'grade_level' => 9, 'section' => 'H', 'room' => 'H9H'],
            ['name' => 'Honors Mathematics - Grade 10', 'grade_level' => 10, 'section' => 'H', 'room' => 'H10H'],
            ['name' => 'AP Biology - Grade 11', 'grade_level' => 11, 'section' => 'AP', 'room' => 'S11A'],
            ['name' => 'AP Chemistry - Grade 12', 'grade_level' => 12, 'section' => 'AP', 'room' => 'S12A'],
            ['name' => 'AP Calculus - Grade 12', 'grade_level' => 12, 'section' => 'AP', 'room' => 'M12A'],
            
            // Special Education Classes
            ['name' => 'Special Education - Elementary', 'grade_level' => 3, 'section' => 'SE', 'room' => 'SE1'],
            ['name' => 'Special Education - Middle', 'grade_level' => 7, 'section' => 'SE', 'room' => 'SE2'],
            ['name' => 'Special Education - High School', 'grade_level' => 10, 'section' => 'SE', 'room' => 'SE3'],
            
            // ELL (English Language Learner) Classes
            ['name' => 'ELL Elementary Support', 'grade_level' => 4, 'section' => 'ELL', 'room' => 'ELL1'],
            ['name' => 'ELL Middle School Support', 'grade_level' => 7, 'section' => 'ELL', 'room' => 'ELL2'],
            ['name' => 'ELL High School Support', 'grade_level' => 10, 'section' => 'ELL', 'room' => 'ELL3'],
        ];

        foreach ($specialClasses as $classData) {
            // Current year
            SchoolClass::factory()->create([
                'name' => $classData['name'],
                'grade_level' => $classData['grade_level'],
                'section' => $classData['section'],
                'academic_year_id' => $currentYear->id,
                'capacity' => $classData['section'] === 'SE' ? 12 : 20, // Smaller capacity for special needs
                'room_number' => $classData['room'],
                'is_active' => true,
            ]);

            // Past year
            if ($pastYear) {
                SchoolClass::factory()->create([
                    'name' => $classData['name'],
                    'grade_level' => $classData['grade_level'],
                    'section' => $classData['section'],
                    'academic_year_id' => $pastYear->id,
                    'capacity' => $classData['section'] === 'SE' ? 12 : 20,
                    'room_number' => $classData['room'],
                    'is_active' => false,
                ]);
            }
        }

        $this->command->info('✓ Created specialized classes: Honors, AP, Special Education, ELL');
    }
}