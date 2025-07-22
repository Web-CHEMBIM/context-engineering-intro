<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Role and Permission Seeder for School Management System
 * 
 * Creates the four main roles and their associated permissions:
 * - SuperAdmin: Complete system control
 * - Admin: Academic administration
 * - Teacher: Limited teaching functionality
 * - Student: Read-only student access
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $this->createRoles();
        
        // Create Permissions
        $this->createPermissions();
        
        // Assign Permissions to Roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Create the four main roles for the school management system.
     */
    private function createRoles(): void
    {
        $roles = [
            [
                'name' => 'SuperAdmin',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Admin', 
                'guard_name' => 'web'
            ],
            [
                'name' => 'Teacher',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Student',
                'guard_name' => 'web'
            ]
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate($role);
        }

        $this->command->info('Roles created successfully.');
    }

    /**
     * Create permissions for all academic entities and system functions.
     */
    private function createPermissions(): void
    {
        $permissions = [
            // User Management Permissions
            'create-users',
            'view-users', 
            'edit-users',
            'delete-users',
            'manage-user-roles',

            // Student Management Permissions
            'create-students',
            'view-students',
            'edit-students', 
            'delete-students',
            'enroll-students',
            'transfer-students',
            'view-student-details',
            'manage-student-grades',

            // Teacher Management Permissions
            'create-teachers',
            'view-teachers',
            'edit-teachers',
            'delete-teachers',
            'assign-teachers',
            'manage-teacher-schedules',

            // Class Management Permissions
            'create-classes',
            'view-classes', 
            'edit-classes',
            'delete-classes',
            'manage-class-assignments',
            'view-class-rosters',

            // Subject Management Permissions
            'create-subjects',
            'view-subjects',
            'edit-subjects',
            'delete-subjects',
            'assign-subjects',
            'manage-curriculum',

            // Academic Year Permissions
            'create-academic-years',
            'view-academic-years',
            'edit-academic-years',
            'delete-academic-years',
            'set-current-academic-year',

            // Dashboard Access Permissions
            'access-admin-dashboard',
            'access-teacher-dashboard', 
            'access-student-dashboard',

            // Reports and Analytics Permissions
            'view-reports',
            'generate-reports',
            'view-analytics',
            'export-data',

            // Financial Management Permissions
            'manage-fees',
            'view-fees',
            'process-payments',
            'view-financial-reports',

            // System Configuration Permissions
            'manage-system-settings',
            'manage-permissions',
            'view-system-logs',
            'backup-system',

            // Assignment Permissions
            'create-assignments',
            'view-assignments',
            'edit-assignments',
            'delete-assignments',
            'grade-assignments',

            // Attendance Permissions (for future modules)
            'manage-attendance',
            'view-attendance',
            'mark-attendance',

            // Timetable Permissions (for future modules)
            'manage-timetables',
            'view-timetables',
            'create-schedules',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        $this->command->info('Permissions created successfully.');
    }

    /**
     * Assign permissions to roles based on the hierarchical structure.
     */
    private function assignPermissionsToRoles(): void
    {
        // SuperAdmin gets NO direct permissions (uses Gate::before bypass)
        $superAdmin = Role::findByName('SuperAdmin');
        // SuperAdmin bypasses all permissions via Gate::before() in AuthServiceProvider

        // Admin Role Permissions
        $admin = Role::findByName('Admin');
        $adminPermissions = [
            // User Management (limited - cannot create other admins/superadmins)
            'view-users',
            'edit-users',
            
            // Student Management (full control)
            'create-students',
            'view-students', 
            'edit-students',
            'delete-students',
            'enroll-students',
            'transfer-students',
            'view-student-details',
            'manage-student-grades',
            
            // Teacher Management (full control)
            'create-teachers',
            'view-teachers',
            'edit-teachers',
            'delete-teachers',
            'assign-teachers',
            'manage-teacher-schedules',
            
            // Class Management (full control)
            'create-classes',
            'view-classes',
            'edit-classes', 
            'delete-classes',
            'manage-class-assignments',
            'view-class-rosters',
            
            // Subject Management (full control)
            'create-subjects',
            'view-subjects',
            'edit-subjects',
            'delete-subjects',
            'assign-subjects',
            'manage-curriculum',
            
            // Academic Year (view only)
            'view-academic-years',
            
            // Dashboard Access
            'access-admin-dashboard',
            
            // Reports and Analytics
            'view-reports',
            'generate-reports',
            'view-analytics',
            'export-data',
            
            // Financial Management
            'manage-fees',
            'view-fees',
            'process-payments',
            'view-financial-reports',
            
            // Assignments
            'create-assignments',
            'view-assignments',
            'edit-assignments',
            'delete-assignments',
            'grade-assignments',
            
            // Attendance
            'manage-attendance',
            'view-attendance',
            'mark-attendance',
            
            // Timetable
            'manage-timetables',
            'view-timetables',
            'create-schedules',
        ];
        
        $admin->syncPermissions($adminPermissions);

        // Teacher Role Permissions (limited access)
        $teacher = Role::findByName('Teacher');
        $teacherPermissions = [
            // Student Management (view only + limited editing)
            'view-students',
            'view-student-details',
            'manage-student-grades', // Only for assigned subjects
            
            // Class Management (view only)
            'view-classes',
            'view-class-rosters',
            
            // Subject Management (view assigned subjects)
            'view-subjects',
            
            // Academic Year (view only)
            'view-academic-years',
            
            // Dashboard Access
            'access-teacher-dashboard',
            
            // Assignments (for assigned subjects/classes)
            'create-assignments',
            'view-assignments',
            'edit-assignments',
            'grade-assignments',
            
            // Attendance (for assigned classes)
            'mark-attendance',
            'view-attendance',
            
            // Timetable (view only)
            'view-timetables',
        ];
        
        $teacher->syncPermissions($teacherPermissions);

        // Student Role Permissions (very limited, mostly read-only)
        $student = Role::findByName('Student');
        $studentPermissions = [
            // Subject Management (view enrolled subjects only)
            'view-subjects',
            
            // Class Management (view own class)
            'view-classes',
            
            // Academic Year (view only)
            'view-academic-years',
            
            // Dashboard Access
            'access-student-dashboard',
            
            // Assignments (view own assignments)
            'view-assignments',
            
            // Fees (view own fees)
            'view-fees',
            
            // Attendance (view own attendance)
            'view-attendance',
            
            // Timetable (view own schedule)
            'view-timetables',
        ];
        
        $student->syncPermissions($studentPermissions);

        $this->command->info('Permissions assigned to roles successfully.');
        $this->command->info('');
        $this->command->info('Role Summary:');
        $this->command->info('- SuperAdmin: Bypasses all permissions (Gate::before)');
        $this->command->info('- Admin: Full academic management permissions');
        $this->command->info('- Teacher: Limited permissions for assigned classes/subjects');
        $this->command->info('- Student: Read-only access to own academic data');
    }
}