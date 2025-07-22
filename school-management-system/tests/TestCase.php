<?php

namespace Tests;

use App\Models\User;
use App\Models\AcademicYear;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions for testing
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        // Create a current academic year for tests
        AcademicYear::factory()->current()->create();
    }

    /**
     * Create a user with a specific role for testing.
     */
    protected function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        
        return $user;
    }

    /**
     * Create and authenticate a user with a specific role.
     */
    protected function actingAsRole(string $role): User
    {
        $user = $this->createUserWithRole($role);
        $this->actingAs($user);
        
        return $user;
    }

    /**
     * Create a SuperAdmin user for testing.
     */
    protected function createSuperAdmin(): User
    {
        return $this->createUserWithRole('SuperAdmin');
    }

    /**
     * Create an Admin user for testing.
     */
    protected function createAdmin(): User
    {
        return $this->createUserWithRole('Admin');
    }

    /**
     * Create a Teacher user for testing.
     */
    protected function createTeacher(): User
    {
        return $this->createUserWithRole('Teacher');
    }

    /**
     * Create a Student user for testing.
     */
    protected function createStudent(): User
    {
        return $this->createUserWithRole('Student');
    }

    /**
     * Assert that a user has a specific permission.
     */
    protected function assertUserHasPermission(User $user, string $permission): void
    {
        $this->assertTrue($user->can($permission), "User does not have permission: {$permission}");
    }

    /**
     * Assert that a user does not have a specific permission.
     */
    protected function assertUserLacksPermission(User $user, string $permission): void
    {
        $this->assertFalse($user->can($permission), "User should not have permission: {$permission}");
    }
}
