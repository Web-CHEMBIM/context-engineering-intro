<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    public function test_superadmin_has_all_permissions(): void
    {
        $superAdmin = $this->createSuperAdmin();

        // Test a few key permissions
        $this->assertUserHasPermission($superAdmin, 'users.create');
        $this->assertUserHasPermission($superAdmin, 'users.delete');
        $this->assertUserHasPermission($superAdmin, 'students.create');
        $this->assertUserHasPermission($superAdmin, 'teachers.create');
        $this->assertUserHasPermission($superAdmin, 'reports.financial');
    }

    public function test_admin_has_management_permissions(): void
    {
        $admin = $this->createAdmin();

        // Should have most management permissions
        $this->assertUserHasPermission($admin, 'students.create');
        $this->assertUserHasPermission($admin, 'students.update');
        $this->assertUserHasPermission($admin, 'teachers.create');
        $this->assertUserHasPermission($admin, 'classes.create');
        $this->assertUserHasPermission($admin, 'subjects.create');

        // But not user management
        $this->assertUserLacksPermission($admin, 'users.create');
        $this->assertUserLacksPermission($admin, 'users.delete');
    }

    public function test_teacher_has_limited_permissions(): void
    {
        $teacher = $this->createTeacher();

        // Should have read permissions
        $this->assertUserHasPermission($teacher, 'students.view');
        $this->assertUserHasPermission($teacher, 'classes.view');
        $this->assertUserHasPermission($teacher, 'subjects.view');

        // Should not have create/delete permissions
        $this->assertUserLacksPermission($teacher, 'students.create');
        $this->assertUserLacksPermission($teacher, 'students.delete');
        $this->assertUserLacksPermission($teacher, 'users.create');
        $this->assertUserLacksPermission($teacher, 'users.delete');
    }

    public function test_student_has_minimal_permissions(): void
    {
        $student = $this->createStudent();

        // Should only have view permissions for own data
        $this->assertUserHasPermission($student, 'profile.view');
        $this->assertUserHasPermission($student, 'profile.update');

        // Should not have any management permissions
        $this->assertUserLacksPermission($student, 'students.view');
        $this->assertUserLacksPermission($student, 'teachers.view');
        $this->assertUserLacksPermission($student, 'users.create');
        $this->assertUserLacksPermission($student, 'classes.create');
    }

    public function test_superadmin_can_access_user_management(): void
    {
        $superAdmin = $this->actingAsRole('SuperAdmin');

        $response = $this->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_user_management(): void
    {
        $admin = $this->actingAsRole('Admin');

        $response = $this->get('/admin/users');

        $response->assertStatus(403); // Forbidden
    }

    public function test_teacher_cannot_access_admin_areas(): void
    {
        $teacher = $this->actingAsRole('Teacher');

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_student_cannot_access_admin_areas(): void
    {
        $student = $this->actingAsRole('Student');

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_student_cannot_access_teacher_areas(): void
    {
        $student = $this->actingAsRole('Student');

        $response = $this->get('/teacher/dashboard');

        $response->assertStatus(403);
    }

    public function test_role_middleware_blocks_unauthorized_access(): void
    {
        $teacher = $this->actingAsRole('Teacher');

        // Try to access admin-only route
        $response = $this->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_permission_middleware_blocks_unauthorized_actions(): void
    {
        $teacher = $this->actingAsRole('Teacher');

        // Try to create a user (requires users.create permission)
        $response = $this->post('/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
    }

    public function test_users_can_access_their_own_profile(): void
    {
        $user = $this->actingAsRole('Student');

        $response = $this->get('/profile');

        $response->assertStatus(200);
    }

    public function test_users_can_update_their_own_profile(): void
    {
        $user = $this->actingAsRole('Student');

        $response = $this->put('/profile', [
            'name' => 'Updated Name',
            'phone' => '+1234567890',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Updated Name', $user->fresh()->name);
    }

    public function test_superadmin_can_create_students(): void
    {
        $superAdmin = $this->actingAsRole('SuperAdmin');

        $response = $this->post('/admin/students', [
            'name' => 'New Student',
            'email' => 'student@test.com',
            'password' => 'password',
            'date_of_birth' => '2010-01-01',
            'student_id' => 'STU001',
            'admission_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'New Student',
            'email' => 'student@test.com',
        ]);
    }

    public function test_admin_can_create_students(): void
    {
        $admin = $this->actingAsRole('Admin');

        $response = $this->post('/admin/students', [
            'name' => 'New Student',
            'email' => 'student@test.com',
            'password' => 'password',
            'date_of_birth' => '2010-01-01',
            'student_id' => 'STU002',
            'admission_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'New Student',
            'email' => 'student@test.com',
        ]);
    }

    public function test_teacher_cannot_create_students(): void
    {
        $teacher = $this->actingAsRole('Teacher');

        $response = $this->post('/admin/students', [
            'name' => 'New Student',
            'email' => 'student@test.com',
            'password' => 'password',
            'date_of_birth' => '2010-01-01',
            'student_id' => 'STU003',
            'admission_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', [
            'email' => 'student@test.com',
        ]);
    }

    public function test_role_hierarchy_permissions(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $admin = $this->createAdmin();
        $teacher = $this->createTeacher();
        $student = $this->createStudent();

        // Test hierarchical permission structure
        $permissions = [
            'students.view' => [$superAdmin, $admin, $teacher], // Not student
            'students.create' => [$superAdmin, $admin], // Not teacher or student
            'users.create' => [$superAdmin], // Only superadmin
        ];

        foreach ($permissions as $permission => $allowedRoles) {
            $allRoles = [$superAdmin, $admin, $teacher, $student];
            $deniedRoles = array_diff($allRoles, $allowedRoles);

            foreach ($allowedRoles as $user) {
                $this->assertUserHasPermission($user, $permission);
            }

            foreach ($deniedRoles as $user) {
                $this->assertUserLacksPermission($user, $permission);
            }
        }
    }

    public function test_permission_caching(): void
    {
        $user = $this->createTeacher();
        
        // Check permission twice to test caching
        $firstCheck = $user->can('students.view');
        $secondCheck = $user->can('students.view');
        
        $this->assertEquals($firstCheck, $secondCheck);
        $this->assertTrue($firstCheck);
    }

    public function test_role_assignment_is_persistent(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Teacher');
        
        // Reload user from database
        $reloadedUser = User::find($user->id);
        
        $this->assertTrue($reloadedUser->hasRole('Teacher'));
        $this->assertUserHasPermission($reloadedUser, 'students.view');
    }

    public function test_multiple_roles_not_allowed(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Teacher');
        
        // Attempt to assign another role
        $user->assignRole('Student');
        
        // User should only have the latest role assigned
        $this->assertTrue($user->hasRole('Student'));
        $this->assertFalse($user->hasRole('Teacher'));
    }

    public function test_inactive_users_denied_access(): void
    {
        $user = $this->createTeacher();
        $user->update(['is_active' => false]);
        
        $response = $this->actingAs($user)->get('/teacher/dashboard');
        
        // Should be redirected or denied access
        $response->assertRedirect('/login');
    }

    public function test_gate_before_superadmin_bypass(): void
    {
        $superAdmin = $this->createSuperAdmin();
        
        // SuperAdmin should be able to do anything via Gate::before()
        $this->assertTrue($superAdmin->can('non.existent.permission'));
        $this->assertTrue($superAdmin->can('any.random.permission'));
    }
}