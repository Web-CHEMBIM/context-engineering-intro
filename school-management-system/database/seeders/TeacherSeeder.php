<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Teacher Seeder for School Management System
 * 
 * Creates realistic teacher profiles with proper department distribution
 */
class TeacherSeeder extends Seeder
{
    /**
     * Department distribution for realistic staffing.
     */
    private array $departmentDistribution = [
        'Mathematics' => 1,
        'Language Arts' => 1,
        'Science' => 1,
        'Social Studies' => 1,
        'Physical Education' => 1,
        'Arts' => 1,
        'Technology' => 1,
        'World Languages' => 1,
        'Special Education' => 1,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Teachers...');

        $currentYear = AcademicYear::where('is_current', true)->first();
        if (!$currentYear) {
            $this->command->error('No current academic year found.');
            return;
        }

        $totalCreated = 0;

        foreach ($this->departmentDistribution as $department => $count) {
            $this->command->info("Creating {$count} teachers for {$department} department...");
            
            for ($i = 0; $i < $count; $i++) {
                // Create user account
                $user = User::factory()->teacher()->create([
                    'email' => fake()->unique()->userName() . '@school.edu',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);

                // Assign teacher role
                $user->assignRole('Teacher');

                // Create teacher profile with appropriate specialization
                $teacher = $this->createTeacherForDepartment($user, $department);
                
                $totalCreated++;
            }
        }

        // Create some special teacher types
        $this->createSpecialTeachers();

        // Assign class teachers
        $this->assignClassTeachers();

        $this->command->info("✓ Created {$totalCreated} teachers across all departments");
        $this->command->info('✓ Teacher distribution reflects realistic school staffing ratios');
        $this->command->info('✓ Assigned class teachers to homeroom classes');
    }

    /**
     * Create a teacher for a specific department.
     */
    private function createTeacherForDepartment(User $user, string $department): Teacher
    {
        // Determine if this should be a head of department (1 per department)
        $isHead = Teacher::where('department', $department)
                        ->where('is_head_of_department', true)
                        ->doesntExist();

        if ($isHead) {
            return Teacher::factory()->headOfDepartment()->create([
                'user_id' => $user->id,
                'department' => $department,
            ]);
        }

        // Create different types of teachers based on random selection
        $teacherType = fake()->randomElement(['veteran', 'newTeacher', 'partTime', 'regular']);
        
        return match ($teacherType) {
            'veteran' => Teacher::factory()->veteran()->create([
                'user_id' => $user->id,
                'department' => $department,
            ]),
            'newTeacher' => Teacher::factory()->newTeacher()->create([
                'user_id' => $user->id,
                'department' => $department,
            ]),
            'partTime' => Teacher::factory()->partTime()->create([
                'user_id' => $user->id,
                'department' => $department,
            ]),
            default => Teacher::factory()->create([
                'user_id' => $user->id,
                'department' => $department,
            ]),
        };
    }

    /**
     * Create special teacher roles like principal, counselors, etc.
     */
    private function createSpecialTeachers(): void
    {
        // Principal
        $principalUser = User::factory()->admin()->create([
            'name' => 'Dr. Sarah Principal',
            'email' => 'principal@school.edu',
            'password' => Hash::make('password'),
        ]);
        $principalUser->assignRole('Admin');

        Teacher::factory()->create([
            'user_id' => $principalUser->id,
            'department' => 'Administration',
            'specialization' => 'Educational Leadership',
            'qualification' => 'Ed.D in Educational Leadership; Master of Education in Administration',
            'experience_years' => fake()->numberBetween(15, 25),
            'salary' => fake()->numberBetween(95000, 120000),
            'contract_type' => 'full_time',
            'is_head_of_department' => false,
        ]);

        // Vice Principal
        $vpUser = User::factory()->admin()->create([
            'name' => 'Mr. James VicePrincipal',
            'email' => 'vp@school.edu',
            'password' => Hash::make('password'),
        ]);
        $vpUser->assignRole('Admin');

        Teacher::factory()->create([
            'user_id' => $vpUser->id,
            'department' => 'Administration',
            'specialization' => 'Student Affairs',
            'qualification' => 'Master of Education in Educational Leadership; Bachelor of Arts in Education',
            'experience_years' => fake()->numberBetween(10, 20),
            'salary' => fake()->numberBetween(75000, 90000),
            'contract_type' => 'full_time',
            'is_head_of_department' => false,
        ]);

        // Guidance Counselors
        for ($i = 1; $i <= 3; $i++) {
            $counselorUser = User::factory()->teacher()->create([
                'name' => fake()->name() . ' (Counselor)',
                'email' => "counselor{$i}@school.edu",
                'password' => Hash::make('password'),
            ]);
            $counselorUser->assignRole('Teacher');

            Teacher::factory()->create([
                'user_id' => $counselorUser->id,
                'department' => 'Student Services',
                'specialization' => 'School Counseling',
                'qualification' => 'Master of Education in School Counseling; Licensed Professional Counselor',
                'experience_years' => fake()->numberBetween(5, 15),
                'salary' => fake()->numberBetween(50000, 65000),
                'contract_type' => 'full_time',
                'is_head_of_department' => $i === 1, // First counselor is head
            ]);
        }

        // Librarian
        $librarianUser = User::factory()->teacher()->create([
            'name' => 'Ms. Emma Librarian',
            'email' => 'librarian@school.edu',
            'password' => Hash::make('password'),
        ]);
        $librarianUser->assignRole('Teacher');

        Teacher::factory()->create([
            'user_id' => $librarianUser->id,
            'department' => 'Library Sciences',
            'specialization' => 'Information Sciences',
            'qualification' => 'Master of Library and Information Science; Bachelor of Arts in Literature',
            'experience_years' => fake()->numberBetween(8, 18),
            'salary' => fake()->numberBetween(45000, 58000),
            'contract_type' => 'full_time',
            'is_head_of_department' => true,
        ]);

        // Substitute Teachers
        for ($i = 1; $i <= 5; $i++) {
            $subUser = User::factory()->teacher()->create([
                'email' => "substitute{$i}@school.edu",
                'password' => Hash::make('password'),
            ]);
            $subUser->assignRole('Teacher');

            Teacher::factory()->substitute()->create([
                'user_id' => $subUser->id,
                'department' => fake()->randomElement(['Mathematics', 'Language Arts', 'Science', 'Social Studies']),
            ]);
        }

        $this->command->info('✓ Created administrative and support staff');
    }

    /**
     * Assign homeroom teachers to classes.
     */
    private function assignClassTeachers(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $classes = SchoolClass::where('academic_year_id', $currentYear->id)
                            ->where('is_active', true)
                            ->get();

        // Get available teachers (excluding substitutes and admin)
        $availableTeachers = Teacher::whereHas('user', function($query) {
            $query->where('is_active', true);
        })
        ->where('contract_type', '!=', 'contract')
        ->whereNotIn('department', ['Administration', 'Student Services', 'Library Sciences'])
        ->get();

        $assigned = 0;
        foreach ($classes as $class) {
            // Don't assign teachers to specialized classes that don't need homeroom teachers
            if (in_array($class->section, ['AP', 'SE', 'ELL', 'H'])) {
                continue;
            }

            // Try to match teacher department with grade-appropriate subjects
            $preferredDepartments = $this->getPreferredDepartmentsForGrade($class->grade_level);
            
            $teacher = $availableTeachers
                ->whereIn('department', $preferredDepartments)
                ->whereNull('id') // Not already assigned (we'll track this manually)
                ->first();

            if (!$teacher) {
                // Fallback to any available teacher
                $teacher = $availableTeachers->first();
            }

            if ($teacher) {
                $class->update(['class_teacher_id' => $teacher->id]);
                // Remove from available teachers to avoid double assignment
                $availableTeachers = $availableTeachers->reject(function($t) use ($teacher) {
                    return $t->id === $teacher->id;
                });
                $assigned++;
            }
        }

        $this->command->info("✓ Assigned {$assigned} homeroom teachers to classes");
    }

    /**
     * Get preferred departments for teaching specific grade levels.
     */
    private function getPreferredDepartmentsForGrade(int $grade): array
    {
        return match (true) {
            $grade <= 5 => ['Language Arts', 'Mathematics', 'Science'], // Elementary generalists
            $grade <= 8 => ['Mathematics', 'Language Arts', 'Science', 'Social Studies'], // Middle school
            $grade <= 12 => ['Mathematics', 'Language Arts', 'Science', 'Social Studies', 'Arts'], // High school
            default => ['Language Arts', 'Mathematics']
        };
    }
}