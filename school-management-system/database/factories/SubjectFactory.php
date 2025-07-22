<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * List of realistic subjects with their departments and grade levels.
     */
    protected static array $subjects = [
        ['name' => 'Mathematics', 'department' => 'Mathematics', 'grades' => [1,2,3,4,5,6,7,8,9,10,11,12], 'mandatory' => true],
        ['name' => 'English Language Arts', 'department' => 'Language Arts', 'grades' => [1,2,3,4,5,6,7,8,9,10,11,12], 'mandatory' => true],
        ['name' => 'Science', 'department' => 'Science', 'grades' => [1,2,3,4,5,6], 'mandatory' => true],
        ['name' => 'Biology', 'department' => 'Science', 'grades' => [9,10,11,12], 'mandatory' => true],
        ['name' => 'Chemistry', 'department' => 'Science', 'grades' => [10,11,12], 'mandatory' => true],
        ['name' => 'Physics', 'department' => 'Science', 'grades' => [10,11,12], 'mandatory' => true],
        ['name' => 'History', 'department' => 'Social Studies', 'grades' => [6,7,8,9,10,11,12], 'mandatory' => true],
        ['name' => 'Geography', 'department' => 'Social Studies', 'grades' => [6,7,8,9], 'mandatory' => false],
        ['name' => 'Physical Education', 'department' => 'Physical Education', 'grades' => [1,2,3,4,5,6,7,8,9,10,11,12], 'mandatory' => true],
        ['name' => 'Art', 'department' => 'Arts', 'grades' => [1,2,3,4,5,6,7,8,9,10,11,12], 'mandatory' => false],
        ['name' => 'Music', 'department' => 'Arts', 'grades' => [1,2,3,4,5,6,7,8,9,10,11,12], 'mandatory' => false],
        ['name' => 'Computer Science', 'department' => 'Technology', 'grades' => [7,8,9,10,11,12], 'mandatory' => false],
        ['name' => 'Spanish', 'department' => 'World Languages', 'grades' => [7,8,9,10,11,12], 'mandatory' => false],
        ['name' => 'French', 'department' => 'World Languages', 'grades' => [7,8,9,10,11,12], 'mandatory' => false],
        ['name' => 'Drama', 'department' => 'Arts', 'grades' => [9,10,11,12], 'mandatory' => false],
        ['name' => 'Economics', 'department' => 'Social Studies', 'grades' => [11,12], 'mandatory' => false],
        ['name' => 'Psychology', 'department' => 'Social Studies', 'grades' => [11,12], 'mandatory' => false],
        ['name' => 'Statistics', 'department' => 'Mathematics', 'grades' => [11,12], 'mandatory' => false],
        ['name' => 'Calculus', 'department' => 'Mathematics', 'grades' => [12], 'mandatory' => false],
        ['name' => 'Environmental Science', 'department' => 'Science', 'grades' => [10,11,12], 'mandatory' => false],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Select a random subject from our predefined list
        $subject = fake()->randomElement(self::$subjects);
        
        // Generate a subject code based on the name
        $code = strtoupper(substr($subject['name'], 0, 3)) . fake()->numberBetween(100, 999);
        
        return [
            'name' => $subject['name'],
            'code' => $code,
            'description' => fake()->optional(0.8)->paragraph(2),
            'credit_hours' => $this->getCreditHours($subject),
            'department' => $subject['department'],
            'grade_levels' => json_encode($subject['grades']),
            'is_mandatory' => $subject['mandatory'],
            'prerequisites' => fake()->optional(0.3)->sentence(),
            'syllabus_file' => null,
            'is_active' => fake()->boolean(95),
        ];
    }

    /**
     * Get appropriate credit hours based on subject type.
     */
    protected function getCreditHours(array $subject): int
    {
        // Core subjects get more credit hours
        if ($subject['mandatory']) {
            return fake()->numberBetween(4, 6);
        }
        
        // Electives get fewer credit hours
        return fake()->numberBetween(2, 4);
    }

    /**
     * Create a core/mandatory subject.
     */
    public function mandatory(): static
    {
        $coreSubjects = array_filter(self::$subjects, fn($s) => $s['mandatory']);
        $subject = fake()->randomElement($coreSubjects);
        
        return $this->state([
            'name' => $subject['name'],
            'department' => $subject['department'],
            'grade_levels' => json_encode($subject['grades']),
            'is_mandatory' => true,
            'credit_hours' => fake()->numberBetween(4, 6),
        ]);
    }

    /**
     * Create an elective subject.
     */
    public function elective(): static
    {
        $electives = array_filter(self::$subjects, fn($s) => !$s['mandatory']);
        $subject = fake()->randomElement($electives);
        
        return $this->state([
            'name' => $subject['name'],
            'department' => $subject['department'],
            'grade_levels' => json_encode($subject['grades']),
            'is_mandatory' => false,
            'credit_hours' => fake()->numberBetween(2, 4),
        ]);
    }

    /**
     * Create a high school level subject.
     */
    public function highSchool(): static
    {
        $hsSubjects = array_filter(self::$subjects, fn($s) => max($s['grades']) >= 9);
        $subject = fake()->randomElement($hsSubjects);
        
        return $this->state([
            'grade_levels' => json_encode(array_filter($subject['grades'], fn($g) => $g >= 9)),
        ]);
    }

    /**
     * Create an elementary level subject.
     */
    public function elementary(): static
    {
        $elemSubjects = array_filter(self::$subjects, fn($s) => min($s['grades']) <= 5);
        $subject = fake()->randomElement($elemSubjects);
        
        return $this->state([
            'grade_levels' => json_encode(array_filter($subject['grades'], fn($g) => $g <= 5)),
        ]);
    }
}