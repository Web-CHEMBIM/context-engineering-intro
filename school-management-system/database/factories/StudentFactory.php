<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Common blood groups with realistic distribution.
     */
    protected static array $bloodGroups = [
        'O+' => 35, 'A+' => 30, 'B+' => 20, 'AB+' => 8,
        'O-' => 4, 'A-' => 2, 'B-' => 1, 'AB-' => 0.5
    ];

    /**
     * Common medical conditions for students.
     */
    protected static array $medicalConditions = [
        'None',
        'Asthma',
        'Allergies (Food)',
        'Allergies (Environmental)', 
        'ADHD',
        'Diabetes Type 1',
        'Epilepsy',
        'Hearing Impairment',
        'Vision Impairment',
        'Learning Disability',
    ];

    /**
     * Emergency contact relationships.
     */
    protected static array $emergencyRelationships = [
        'Mother', 'Father', 'Guardian', 'Grandmother', 'Grandfather',
        'Aunt', 'Uncle', 'Older Sibling', 'Family Friend'
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $admissionDate = fake()->dateTimeBetween('-5 years', '-3 months');
        $gradeLevel = fake()->numberBetween(1, 12);
        
        // Generate fees based on grade level
        $totalFees = $this->generateFeesForGrade($gradeLevel);
        $feesPaid = fake()->numberBetween(0, $totalFees);
        $feesPending = max(0, $totalFees - $feesPaid);

        return [
            'user_id' => User::factory()->student(),
            'student_id' => $this->generateStudentId(),
            'roll_number' => fake()->unique()->numberBetween(1001, 9999),
            'admission_date' => $admissionDate,
            'admission_number' => $this->generateAdmissionNumber($admissionDate),
            'school_class_id' => null, // Will be assigned based on grade level
            'academic_year_id' => AcademicYear::factory(),
            'student_status' => fake()->randomElement(['enrolled', 'transferred', 'graduated', 'suspended']),
            'blood_group' => $this->generateBloodGroup(),
            'medical_conditions' => $this->generateMedicalConditions(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->numerify('+1##########'),
            'emergency_contact_relationship' => fake()->randomElement(self::$emergencyRelationships),
            'total_fees' => $totalFees,
            'fees_paid' => $feesPaid,
            'fees_pending' => $feesPending,
        ];
    }

    /**
     * Generate a unique student ID.
     */
    protected function generateStudentId(): string
    {
        $year = fake()->numberBetween(20, 25);
        $sequence = fake()->unique()->numberBetween(1000, 9999);
        
        return "STU{$year}{$sequence}";
    }

    /**
     * Generate admission number based on admission date.
     */
    protected function generateAdmissionNumber(\DateTime $admissionDate): string
    {
        $year = $admissionDate->format('y');
        $sequence = fake()->unique()->numberBetween(100, 999);
        
        return "ADM{$year}{$sequence}";
    }

    /**
     * Generate blood group with realistic distribution.
     */
    protected function generateBloodGroup(): string
    {
        $rand = fake()->numberBetween(1, 1000);
        $cumulative = 0;
        
        foreach (self::$bloodGroups as $bloodGroup => $percentage) {
            $cumulative += $percentage * 10; // Convert to per-thousand
            if ($rand <= $cumulative) {
                return $bloodGroup;
            }
        }
        
        return 'O+'; // Fallback
    }

    /**
     * Generate medical conditions (could be multiple or none).
     */
    protected function generateMedicalConditions(): ?string
    {
        // 70% chance of no medical conditions
        if (fake()->boolean(70)) {
            return 'None';
        }
        
        // 20% chance of one condition, 10% chance of multiple
        $conditionCount = fake()->boolean(20) ? 2 : 1;
        $conditions = fake()->randomElements(
            array_filter(self::$medicalConditions, fn($c) => $c !== 'None'),
            $conditionCount
        );
        
        return implode(', ', $conditions);
    }

    /**
     * Generate appropriate fees based on grade level.
     */
    protected function generateFeesForGrade(int $gradeLevel): float
    {
        // Higher grades typically cost more
        $baseFee = match (true) {
            $gradeLevel <= 5 => fake()->numberBetween(3000, 4500),  // Elementary
            $gradeLevel <= 8 => fake()->numberBetween(4000, 5500),  // Middle School
            $gradeLevel <= 12 => fake()->numberBetween(5000, 7000), // High School
            default => 5000
        };
        
        // Add some variation
        $variation = fake()->numberBetween(-500, 1000);
        
        return max(2000, $baseFee + $variation);
    }

    /**
     * Create an enrolled student.
     */
    public function enrolled(): static
    {
        return $this->state([
            'student_status' => 'enrolled',
            'fees_paid' => function (array $attributes) {
                // Enrolled students are more likely to have paid fees
                return fake()->numberBetween(
                    (int)($attributes['total_fees'] * 0.5),
                    $attributes['total_fees']
                );
            },
        ]);
    }

    /**
     * Create a top performing student.
     */
    public function topPerformer(): static
    {
        return $this->state([
            'student_status' => 'enrolled',
            'medical_conditions' => 'None',
            'fees_pending' => 0, // Top performers typically have paid fees
        ]);
    }

    /**
     * Create a student with pending fees.
     */
    public function withPendingFees(): static
    {
        return $this->state([
            'student_status' => 'enrolled',
            'fees_paid' => function (array $attributes) {
                // Pay only 20-60% of fees
                return (int)($attributes['total_fees'] * fake()->numberBetween(20, 60) / 100);
            },
        ]);
    }

    /**
     * Create a new student (recently admitted).
     */
    public function newStudent(): static
    {
        $recentAdmission = fake()->dateTimeBetween('-6 months', 'now');
        
        return $this->state([
            'admission_date' => $recentAdmission,
            'student_status' => 'enrolled',
            'admission_number' => $this->generateAdmissionNumber($recentAdmission),
        ]);
    }

    /**
     * Create a graduated student.
     */
    public function graduated(): static
    {
        return $this->state([
            'student_status' => 'graduated',
            'fees_pending' => 0, // Graduated students have cleared all fees
            'academic_year_id' => AcademicYear::factory()->past(),
        ]);
    }

    /**
     * Create a student for a specific grade level.
     */
    public function forGrade(int $gradeLevel): static
    {
        return $this->state([
            'total_fees' => $this->generateFeesForGrade($gradeLevel),
            'user_id' => User::factory()->student()->state([
                'date_of_birth' => $this->generateBirthDateForGrade($gradeLevel),
            ]),
        ]);
    }

    /**
     * Generate appropriate birth date for a grade level.
     */
    protected function generateBirthDateForGrade(int $gradeLevel): \DateTime
    {
        // Typical age for grade level (grade 1 = age 6, grade 12 = age 17-18)
        $typicalAge = 5 + $gradeLevel;
        $minAge = max(5, $typicalAge - 2);  // Allow 2 years younger
        $maxAge = $typicalAge + 3;         // Allow 3 years older
        
        $age = fake()->numberBetween($minAge, $maxAge);
        
        return fake()->dateTimeBetween("-{$age} years", "-" . ($age - 1) . " years");
    }

    /**
     * Create a student with special needs.
     */
    public function specialNeeds(): static
    {
        $specialConditions = [
            'Learning Disability',
            'ADHD', 
            'Autism Spectrum Disorder',
            'Hearing Impairment',
            'Vision Impairment',
            'Physical Disability'
        ];
        
        return $this->state([
            'student_status' => 'enrolled',
            'medical_conditions' => fake()->randomElement($specialConditions),
        ]);
    }

    /**
     * Create an international student.
     */
    public function international(): static
    {
        return $this->state([
            'student_status' => 'enrolled',
            'total_fees' => function (array $attributes) {
                $totalFees = $attributes['total_fees'] ?? null;
                if ($totalFees instanceof \Closure) {
                    $totalFees = $totalFees($attributes);
                }
                if (!is_numeric($totalFees)) {
                    $totalFees = 5000; // fallback default
                }
                return $totalFees * fake()->numberBetween(120, 180) / 100;
            },
            'user_id' => User::factory()->student()->state([
                'address' => fake()->address() . ', ' . fake()->country(),
            ]),
        ]);
    }

    /**
     * Create a student for a specific class.
     */
    public function forClass(SchoolClass $class): static
    {
        return $this->state([
            'school_class_id' => $class->id,
            'academic_year_id' => $class->academic_year_id,
            'user_id' => User::factory()->student()->state([
                'date_of_birth' => $this->generateBirthDateForGrade($class->grade_level),
            ]),
            'total_fees' => $this->generateFeesForGrade($class->grade_level),
        ]);
    }

    /**
     * Create a student for a specific academic year.
     */
    public function forAcademicYear(AcademicYear $academicYear): static
    {
        return $this->state([
            'academic_year_id' => $academicYear->id,
            'admission_date' => fake()->dateTimeBetween(
                $academicYear->start_date,
                min(now(), $academicYear->end_date)
            ),
        ]);
    }
}