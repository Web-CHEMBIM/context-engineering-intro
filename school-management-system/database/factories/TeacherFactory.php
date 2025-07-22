<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Common departments and their specializations.
     */
    protected static array $departments = [
        'Mathematics' => ['Algebra', 'Geometry', 'Calculus', 'Statistics', 'Applied Mathematics'],
        'Science' => ['Biology', 'Chemistry', 'Physics', 'Environmental Science', 'Earth Science'],
        'Language Arts' => ['Literature', 'Writing', 'Reading', 'Grammar', 'Creative Writing'],
        'Social Studies' => ['History', 'Geography', 'Civics', 'Economics', 'Psychology'],
        'Physical Education' => ['Sports', 'Health', 'Fitness', 'Recreation', 'Athletics'],
        'Arts' => ['Visual Arts', 'Music', 'Drama', 'Dance', 'Digital Arts'],
        'Technology' => ['Computer Science', 'Information Technology', 'Digital Media', 'Programming'],
        'World Languages' => ['Spanish', 'French', 'German', 'Mandarin', 'ESL'],
        'Special Education' => ['Learning Support', 'Behavioral Support', 'Speech Therapy', 'Occupational Therapy'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $department = fake()->randomKey(self::$departments);
        $specializations = self::$departments[$department];
        $specialization = fake()->randomElement($specializations);
        
        $hireDate = fake()->dateTimeBetween('-20 years', '-6 months');
        $experienceYears = fake()->numberBetween(0, max(0, now()->diffInYears($hireDate) + fake()->numberBetween(0, 5)));
        
        return [
            'user_id' => User::factory()->teacher(),
            'teacher_id' => $this->generateTeacherId(),
            'employee_id' => fake()->unique()->numerify('EMP###'),
            'department' => $department,
            'specialization' => $specialization,
            'qualification' => $this->generateQualification($department),
            'experience_years' => $experienceYears,
            'hire_date' => $hireDate,
            'salary' => $this->generateSalary($experienceYears, $department),
            'contract_type' => fake()->randomElement(['full_time', 'part_time', 'contract']),
            'is_head_of_department' => fake()->boolean(10), // 10% chance
            'office_hours' => json_encode($this->generateOfficeHours()),
        ];
    }

    /**
     * Generate a unique teacher ID.
     */
    protected function generateTeacherId(): string
    {
        $prefix = 'T';
        $year = fake()->numberBetween(20, 25);
        $number = fake()->unique()->numberBetween(100, 999);
        
        return $prefix . $year . $number;
    }

    /**
     * Generate realistic qualification based on department.
     */
    protected function generateQualification(string $department): string
    {
        $degrees = ['Bachelor of Education', 'Bachelor of Arts', 'Bachelor of Science', 'Master of Education', 'Master of Arts', 'Master of Science'];
        $primaryDegree = fake()->randomElement($degrees);
        
        $qualifications = [$primaryDegree . ' in ' . $department];
        
        // Add additional qualifications
        if (fake()->boolean(30)) {
            $additionalDegrees = ['Teaching Certificate', 'TESOL Certificate', 'Special Education Certificate'];
            $qualifications[] = fake()->randomElement($additionalDegrees);
        }
        
        // Add advanced degrees for some teachers
        if (fake()->boolean(20)) {
            $advancedDegrees = ['Master of Education', 'Ed.D in Educational Leadership', 'PhD in ' . $department];
            $qualifications[] = fake()->randomElement($advancedDegrees);
        }
        
        return implode('; ', $qualifications);
    }

    /**
     * Generate salary based on experience and department.
     */
    protected function generateSalary(int $experienceYears, string $department): float
    {
        // Base salary
        $baseSalary = 40000;
        
        // Experience bonus
        $experienceBonus = $experienceYears * 1500;
        
        // Department multiplier
        $departmentMultipliers = [
            'Mathematics' => 1.1,
            'Science' => 1.1,
            'Technology' => 1.15,
            'Special Education' => 1.05,
            'World Languages' => 1.0,
            'Physical Education' => 0.95,
            'Arts' => 0.95,
        ];
        
        $multiplier = $departmentMultipliers[$department] ?? 1.0;
        
        return round(($baseSalary + $experienceBonus) * $multiplier, 2);
    }

    /**
     * Generate office hours schedule.
     */
    protected function generateOfficeHours(): array
    {
        $days = fake()->randomElements(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], fake()->numberBetween(2, 4));
        $officeHours = [];
        
        foreach ($days as $day) {
            $startHour = fake()->numberBetween(8, 14);
            $endHour = $startHour + fake()->numberBetween(1, 3);
            
            $officeHours[$day] = sprintf('%02d:00-%02d:00', $startHour, $endHour);
        }
        
        return $officeHours;
    }

    /**
     * Create a head of department.
     */
    public function headOfDepartment(): static
    {
        return $this->state([
            'is_head_of_department' => true,
            'experience_years' => fake()->numberBetween(10, 25),
            'contract_type' => 'full_time',
            'qualification' => function (array $attributes) {
                return 'Master of Education in ' . $attributes['department'] . '; Educational Leadership Certificate';
            },
        ]);
    }

    /**
     * Create a new teacher with less experience.
     */
    public function newTeacher(): static
    {
        $hireDate = fake()->dateTimeBetween('-2 years', 'now');
        
        return $this->state([
            'hire_date' => $hireDate,
            'experience_years' => fake()->numberBetween(0, 3),
            'is_head_of_department' => false,
        ]);
    }

    /**
     * Create a veteran teacher.
     */
    public function veteran(): static
    {
        $hireDate = fake()->dateTimeBetween('-30 years', '-10 years');
        
        return $this->state([
            'hire_date' => $hireDate,
            'experience_years' => fake()->numberBetween(10, 30),
            'qualification' => function (array $attributes) {
                return 'Master of Education in ' . $attributes['department'] . '; National Board Certification';
            },
        ]);
    }

    /**
     * Create a part-time teacher.
     */
    public function partTime(): static
    {
        return $this->state([
            'contract_type' => 'part_time',
            'salary' => function (array $attributes) {
                return $attributes['salary'] * 0.6; // 60% of full-time salary
            },
        ]);
    }

    /**
     * Create a substitute/contract teacher.
     */
    public function substitute(): static
    {
        return $this->state([
            'contract_type' => 'contract',
            'salary' => fake()->numberBetween(25000, 35000),
            'experience_years' => fake()->numberBetween(0, 5),
        ]);
    }
}