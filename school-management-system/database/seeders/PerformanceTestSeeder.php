<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Performance Test Seeder for School Management System
 * 
 * Creates large datasets for performance testing and load testing
 */
class PerformanceTestSeeder extends Seeder
{
    /**
     * Run the database seeds for performance testing.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating performance test data...');
        
        if (!$this->command->confirm('This will create large amounts of test data. Continue?')) {
            $this->command->info('Performance test seeding cancelled.');
            return;
        }

        $this->createLargeStudentDataset();
        $this->createSubjectEnrollments();
        $this->createHistoricalData();
        $this->updateStatistics();

        $this->command->info('✅ Performance test data created successfully!');
    }

    /**
     * Create a large student dataset for testing.
     */
    private function createLargeStudentDataset(): void
    {
        $this->command->info('Creating large student dataset...');

        $currentYear = AcademicYear::where('is_current', true)->first();
        $classes = SchoolClass::where('academic_year_id', $currentYear->id)
                            ->where('is_active', true)
                            ->get();

        // Create 2000+ additional students
        $batchSize = 100;
        $totalStudents = 2500;
        $created = 0;

        for ($batch = 0; $batch < ceil($totalStudents / $batchSize); $batch++) {
            $studentsInBatch = min($batchSize, $totalStudents - ($batch * $batchSize));
            
            $users = [];
            $students = [];

            for ($i = 0; $i < $studentsInBatch; $i++) {
                $class = $classes->random();
                
                $users[] = [
                    'name' => fake()->name(),
                    'email' => fake()->unique()->userName() . '@bulk.student.edu',
                    'email_verified_at' => fake()->optional(0.7)->dateTimeBetween('-1 year'),
                    'password' => bcrypt('password'),
                    'phone' => fake()->optional(0.6)->numerify('+1##########'),
                    'date_of_birth' => fake()->dateTimeBetween('-25 years', '-5 years'),
                    'gender' => fake()->randomElement(['male', 'female', 'other']),
                    'address' => fake()->optional(0.5)->address(),
                    'is_active' => fake()->boolean(95),
                    'last_login_at' => fake()->optional(0.8)->dateTimeBetween('-30 days'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert users
            $userIds = DB::table('users')->insertGetId($users);
            
            // Create corresponding students
            for ($i = 0; $i < $studentsInBatch; $i++) {
                $class = $classes->random();
                $totalFees = fake()->numberBetween(3000, 8000);
                $feesPaid = fake()->numberBetween(0, $totalFees);
                
                $students[] = [
                    'user_id' => $userIds + $i,
                    'student_id' => 'BULK' . fake()->unique()->numberBetween(100000, 999999),
                    'roll_number' => fake()->unique()->numberBetween(10000, 99999),
                    'admission_date' => fake()->dateTimeBetween('-3 years', '-1 month'),
                    'admission_number' => 'ADM' . fake()->unique()->numberBetween(100000, 999999),
                    'school_class_id' => $class->id,
                    'academic_year_id' => $currentYear->id,
                    'student_status' => fake()->randomElement(['enrolled', 'enrolled', 'enrolled', 'transferred']),
                    'blood_group' => fake()->randomElement(['O+', 'A+', 'B+', 'AB+', 'O-', 'A-', 'B-', 'AB-']),
                    'medical_conditions' => fake()->optional(0.3)->randomElement(['None', 'Asthma', 'Allergies', 'ADHD']),
                    'emergency_contact_name' => fake()->name(),
                    'emergency_contact_phone' => fake()->numerify('+1##########'),
                    'emergency_contact_relationship' => fake()->randomElement(['Mother', 'Father', 'Guardian']),
                    'total_fees' => $totalFees,
                    'fees_paid' => $feesPaid,
                    'fees_pending' => max(0, $totalFees - $feesPaid),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert students
            DB::table('students')->insert($students);
            
            $created += $studentsInBatch;
            $this->command->info("Created {$created} / {$totalStudents} students...");
        }

        $this->command->info("✓ Created {$totalStudents} additional students for performance testing");
    }

    /**
     * Create massive subject enrollments for testing.
     */
    private function createSubjectEnrollments(): void
    {
        $this->command->info('Creating subject enrollments...');

        $currentYear = AcademicYear::where('is_current', true)->first();
        $students = Student::where('academic_year_id', $currentYear->id)
                          ->where('student_status', 'enrolled')
                          ->with('schoolClass')
                          ->get();

        $subjects = Subject::where('is_active', true)->get();
        
        $enrollments = [];
        $enrollmentCount = 0;
        $batchSize = 1000;

        foreach ($students as $student) {
            $gradeLevel = $student->schoolClass->grade_level;
            $gradeSubjects = $subjects->filter(function($subject) use ($gradeLevel) {
                $gradeLevels = json_decode($subject->grade_levels, true);
                return in_array($gradeLevel, $gradeLevels);
            });

            foreach ($gradeSubjects as $subject) {
                // Enroll in mandatory subjects, and randomly in electives
                if ($subject->is_mandatory || fake()->boolean(70)) {
                    $enrollments[] = [
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $currentYear->id,
                        'enrollment_date' => fake()->dateTimeBetween('-6 months', 'now'),
                        'status' => fake()->randomElement(['enrolled', 'enrolled', 'enrolled', 'completed']),
                        'grade' => fake()->optional(0.6)->numberBetween(60, 100),
                        'remarks' => fake()->optional(0.2)->sentence(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $enrollmentCount++;

                    // Bulk insert when batch is full
                    if (count($enrollments) >= $batchSize) {
                        DB::table('student_subject')->insert($enrollments);
                        $enrollments = [];
                        $this->command->info("Processed {$enrollmentCount} enrollments...");
                    }
                }
            }
        }

        // Insert remaining enrollments
        if (!empty($enrollments)) {
            DB::table('student_subject')->insert($enrollments);
        }

        $this->command->info("✓ Created {$enrollmentCount} subject enrollments");
    }

    /**
     * Create historical academic data for multiple years.
     */
    private function createHistoricalData(): void
    {
        $this->command->info('Creating historical academic data...');

        $pastYears = AcademicYear::where('is_current', false)
                               ->where('name', 'like', '202%')
                               ->get();

        foreach ($pastYears as $year) {
            // Create some graduated students for each past year
            $graduatedCount = fake()->numberBetween(80, 120);
            
            $users = [];
            $students = [];

            for ($i = 0; $i < $graduatedCount; $i++) {
                $users[] = [
                    'name' => fake()->name(),
                    'email' => fake()->unique()->userName() . "@alumni{$year->name}.edu",
                    'email_verified_at' => fake()->dateTimeBetween($year->start_date, $year->end_date),
                    'password' => bcrypt('password'),
                    'phone' => fake()->optional(0.6)->numerify('+1##########'),
                    'date_of_birth' => fake()->dateTimeBetween('-25 years', '-18 years'),
                    'gender' => fake()->randomElement(['male', 'female', 'other']),
                    'address' => fake()->optional(0.5)->address(),
                    'is_active' => fake()->boolean(30), // Many alumni are inactive
                    'last_login_at' => fake()->optional(0.2)->dateTimeBetween($year->end_date, 'now'),
                    'created_at' => fake()->dateTimeBetween($year->start_date, $year->end_date),
                    'updated_at' => fake()->dateTimeBetween($year->start_date, $year->end_date),
                ];
            }

            // Bulk insert historical users
            $firstUserId = DB::table('users')->insertGetId($users[0]);
            if (count($users) > 1) {
                DB::table('users')->insert(array_slice($users, 1));
            }

            // Create historical students
            for ($i = 0; $i < $graduatedCount; $i++) {
                $totalFees = fake()->numberBetween(3000, 7000); // Historical fees were lower
                
                $students[] = [
                    'user_id' => $firstUserId + $i,
                    'student_id' => 'HIST' . $year->name . fake()->unique()->numberBetween(1000, 9999),
                    'roll_number' => fake()->unique()->numberBetween(10000, 99999),
                    'admission_date' => fake()->dateTimeBetween($year->start_date, $year->end_date),
                    'admission_number' => 'ADM' . substr($year->name, 0, 2) . fake()->unique()->numberBetween(1000, 9999),
                    'school_class_id' => null, // Historical classes may not exist
                    'academic_year_id' => $year->id,
                    'student_status' => fake()->randomElement(['graduated', 'graduated', 'transferred']),
                    'blood_group' => fake()->randomElement(['O+', 'A+', 'B+', 'AB+']),
                    'medical_conditions' => fake()->optional(0.2)->randomElement(['None', 'Asthma', 'Allergies']),
                    'emergency_contact_name' => fake()->name(),
                    'emergency_contact_phone' => fake()->numerify('+1##########'),
                    'emergency_contact_relationship' => fake()->randomElement(['Mother', 'Father', 'Guardian']),
                    'total_fees' => $totalFees,
                    'fees_paid' => $totalFees, // All graduated students have paid fees
                    'fees_pending' => 0,
                    'created_at' => fake()->dateTimeBetween($year->start_date, $year->end_date),
                    'updated_at' => $year->end_date,
                ];
            }

            DB::table('students')->insert($students);
            
            $this->command->info("✓ Created {$graduatedCount} historical students for {$year->name}");
        }
    }

    /**
     * Update various statistics for realistic data distribution.
     */
    private function updateStatistics(): void
    {
        $this->command->info('Updating statistics and relationships...');

        // Update some students to have realistic grade distributions
        $completedEnrollments = DB::table('student_subject')
                                 ->where('status', 'completed')
                                 ->whereNull('grade')
                                 ->get();

        foreach ($completedEnrollments->chunk(500) as $chunk) {
            $updates = [];
            foreach ($chunk as $enrollment) {
                $updates[] = [
                    'student_id' => $enrollment->student_id,
                    'subject_id' => $enrollment->subject_id,
                    'grade' => $this->generateRealisticGrade(),
                ];
            }

            // Update grades in batches
            foreach ($updates as $update) {
                DB::table('student_subject')
                  ->where('student_id', $update['student_id'])
                  ->where('subject_id', $update['subject_id'])
                  ->update(['grade' => $update['grade']]);
            }
        }

        $this->command->info('✓ Updated statistical distributions');
    }

    /**
     * Generate realistic grade distribution (bell curve).
     */
    private function generateRealisticGrade(): int
    {
        // Create a realistic grade distribution
        $rand = fake()->numberBetween(1, 100);
        
        return match (true) {
            $rand <= 5 => fake()->numberBetween(0, 59),    // 5% fail
            $rand <= 20 => fake()->numberBetween(60, 69),  // 15% D range
            $rand <= 50 => fake()->numberBetween(70, 79),  // 30% C range
            $rand <= 80 => fake()->numberBetween(80, 89),  // 30% B range
            default => fake()->numberBetween(90, 100),     // 20% A range
        };
    }
}