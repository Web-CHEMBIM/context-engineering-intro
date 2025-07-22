<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

/**
 * Academic Year Seeder for School Management System
 * 
 * Creates multiple academic years for historical data and testing
 */
class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Academic Years...');

        // Create past academic years
        AcademicYear::factory()->past()->create([
            'name' => '2021-2022',
            'start_date' => '2021-08-15',
            'end_date' => '2022-06-30',
        ]);

        AcademicYear::factory()->past()->create([
            'name' => '2022-2023',
            'start_date' => '2022-08-15',
            'end_date' => '2023-06-30',
        ]);

        AcademicYear::factory()->past()->create([
            'name' => '2023-2024',
            'start_date' => '2023-08-15',
            'end_date' => '2024-06-30',
        ]);

        // Create current academic year
        $currentYear = AcademicYear::factory()->current()->create([
            'name' => '2024-2025',
            'start_date' => '2024-08-15',
            'end_date' => '2025-06-30',
            'description' => 'Current Academic Year 2024-2025',
        ]);

        // Create future academic year for planning
        AcademicYear::factory()->create([
            'name' => '2025-2026',
            'start_date' => '2025-08-15',
            'end_date' => '2026-06-30',
            'is_current' => false,
            'description' => 'Upcoming Academic Year 2025-2026',
        ]);

        $this->command->info('✓ Created 5 Academic Years (2021-2026)');
        $this->command->info("✓ Current Academic Year: {$currentYear->name}");
    }
}