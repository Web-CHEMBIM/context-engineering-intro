<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    public function test_api_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_superadmin_can_list_users(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                    'is_active',
                    'created_at',
                    'updated_at',
                ]
            ],
            'links',
            'meta',
        ]);
    }

    public function test_admin_cannot_list_users(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    public function test_superadmin_can_create_user(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        $userData = [
            'name' => 'New Test User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Teacher',
            'phone' => '+1234567890',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'roles',
                'is_active',
            ],
            'message',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New Test User',
            'email' => 'newuser@test.com',
        ]);
    }

    public function test_api_validation_errors(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        // Missing required fields
        $response = $this->postJson('/api/v1/users', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }

    public function test_api_rate_limiting(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        // Make multiple requests quickly
        for ($i = 0; $i < 61; $i++) {
            $response = $this->getJson('/api/v1/users');
            
            if ($i < 60) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
                break;
            }
        }
    }

    public function test_user_can_view_own_profile(): void
    {
        $user = $this->createStudent();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    public function test_user_cannot_view_others_profile(): void
    {
        $user1 = $this->createStudent();
        $user2 = $this->createStudent();
        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/v1/users/{$user2->id}");

        $response->assertStatus(403);
    }

    public function test_api_search_functionality(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        // Create users with searchable data
        User::factory()->create(['name' => 'John Smith']);
        User::factory()->create(['name' => 'Jane Doe']);

        $response = $this->getJson('/api/v1/users?search=John');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'John Smith');
    }

    public function test_api_filtering_by_role(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        // Create users with different roles
        $teacher = $this->createTeacher();
        $student = $this->createStudent();

        $response = $this->getJson('/api/v1/users?role=Teacher');

        $response->assertStatus(200);
        
        // Should contain the teacher but not the student
        $userIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($teacher->id, $userIds);
        $this->assertNotContains($student->id, $userIds);
    }

    public function test_api_pagination(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        // Create multiple users
        User::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/users?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonPath('meta.per_page', 10);
        $response->assertJsonPath('meta.total', function($total) {
            return $total >= 25; // At least 25 users (plus seeded ones)
        });
        $response->assertJsonCount(10, 'data');
    }

    public function test_api_sorting(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        // Create users
        User::factory()->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Bob']);
        User::factory()->create(['name' => 'Charlie']);

        $response = $this->getJson('/api/v1/users?sort=name&direction=asc');

        $response->assertStatus(200);
        
        $names = collect($response->json('data'))->pluck('name')->toArray();
        $sortedNames = collect($names)->sort()->values()->toArray();
        
        $this->assertEquals($sortedNames, $names);
    }

    public function test_api_user_update(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'phone' => '+9876543210',
        ];

        $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '+9876543210',
        ]);
    }

    public function test_api_user_deletion(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'User deleted successfully']);

        // Should be soft deleted
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_api_bulk_operations(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        $users = User::factory()->count(3)->create();
        $userIds = $users->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/users/bulk-delete', [
            'ids' => $userIds,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.deleted_count', 3);

        foreach ($userIds as $userId) {
            $this->assertSoftDeleted('users', ['id' => $userId]);
        }
    }

    public function test_api_error_handling(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        // Try to access non-existent user
        $response = $this->getJson('/api/v1/users/99999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'User not found']);
    }

    public function test_api_token_authentication(): void
    {
        $user = $this->createSuperAdmin();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson('/api/v1/users');

        $response->assertStatus(200);
    }

    public function test_api_invalid_token(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
                         ->getJson('/api/v1/users');

        $response->assertStatus(401);
    }

    public function test_api_content_type_validation(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        // Send non-JSON data to JSON endpoint
        $response = $this->post('/api/v1/users', [
            'name' => 'Test User',
        ], ['Content-Type' => 'application/x-www-form-urlencoded']);

        // Should still work as Laravel handles both
        $response->assertStatus(422); // Validation error (missing fields)
    }

    public function test_api_cors_headers(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200);
        // CORS headers would be tested if CORS middleware is configured
    }

    public function test_api_versioning(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        // Test v1 API
        $response = $this->getJson('/api/v1/users');
        $response->assertStatus(200);

        // Test invalid version
        $response = $this->getJson('/api/v2/users');
        $response->assertStatus(404);
    }

    public function test_api_response_format_consistency(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'per_page',
                'to',
                'total',
            ],
        ]);
    }
}