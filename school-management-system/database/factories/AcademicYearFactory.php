<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2020, 2025);
        $endYear = $startYear + 1;
        $startDate = "{$startYear}-08-15"; // Mid August
        $endDate = "{$endYear}-06-30";   // End of June
        
        return [
            'name' => "{$startYear}-{$endYear}",
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_current' => false, // Will be set explicitly for current year
            'semester_count' => fake()->numberBetween(2, 4),
            'total_weeks' => fake()->numberBetween(38, 42),
            'holidays' => json_encode([
                'thanksgiving_break' => ['2024-11-25', '2024-11-29'],
                'winter_break' => ['2024-12-20', '2025-01-08'],
                'spring_break' => ['2025-03-10', '2025-03-17'],
                'summer_break' => ['2025-06-15', '2025-08-15'],
            ]),
            'description' => fake()->optional(0.7)->sentence(10),
            'is_active' => true,
        ];
    }

    /**
     * Create the current academic year.
     */
    public function current(): static
    {
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;
        
        return $this->state([
            'name' => "{$currentYear}-{$nextYear}",
            'start_date' => "{$currentYear}-08-15",
            'end_date' => "{$nextYear}-06-30",
            'is_current' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Create a past academic year.
     */
    public function past(): static
    {
        $year = fake()->numberBetween(2018, 2023);
        
        return $this->state([
            'name' => "{$year}-" . ($year + 1),
            'start_date' => "{$year}-08-15",
            'end_date' => ($year + 1) . "-06-30",
            'is_current' => false,
        ]);
    }

    /**
     * Create an inactive academic year.
     */
    public function inactive(): static
    {
        return $this->state([
            'is_active' => false,
            'is_current' => false,
        ]);
    }
}