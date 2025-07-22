<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Database Seeder for School Management System
 * 
 * Seeds the database with initial roles, permissions, and users
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Roles and Permissions first
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Create Academic Year
        $this->createAcademicYear();

        // Create Default Users
        $this->createDefaultUsers();

        $this->command->info('Database seeded successfully!');
    }

    /**
     * Create the current academic year.
     */
    private function createAcademicYear(): void
    {
        AcademicYear::firstOrCreate(
            ['name' => '2024-2025'],
            [
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
                'is_current' => true,
                'is_active' => true,
                'description' => 'Academic Year 2024-2025'
            ]
        );

        $this->command->info('Academic year created successfully.');
    }

    /**
     * Create default users for each role.
     */
    private function createDefaultUsers(): void
    {
        // Create SuperAdmin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@school.edu'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'phone' => '+1234567890',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('SuperAdmin');

        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@school.edu'],
            [
                'name' => 'School Administrator',
                'password' => Hash::make('password'),
                'phone' => '+1234567891',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Admin');

        // Create Sample Teacher
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@school.edu'],
            [
                'name' => 'John Teacher',
                'password' => Hash::make('password'),
                'phone' => '+1234567892',
                'date_of_birth' => '1985-06-15',
                'gender' => 'male',
                'address' => '123 Teacher Street, Education City',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $teacher->assignRole('Teacher');

        // Create Sample Student
        $student = User::firstOrCreate(
            ['email' => 'student@school.edu'],
            [
                'name' => 'Jane Student',
                'password' => Hash::make('password'),
                'phone' => '+1234567893',
                'date_of_birth' => '2008-03-20',
                'gender' => 'female',
                'address' => '456 Student Lane, Learning Town',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $student->assignRole('Student');

        $this->command->info('Default users created successfully.');
        $this->command->info('');
        $this->command->info('Default Login Credentials:');
        $this->command->info('SuperAdmin: superadmin@school.edu / password');
        $this->command->info('Admin: admin@school.edu / password');  
        $this->command->info('Teacher: teacher@school.edu / password');
        $this->command->info('Student: student@school.edu / password');
    }
}
