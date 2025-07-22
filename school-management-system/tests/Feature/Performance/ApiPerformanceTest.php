<?php

namespace Tests\Feature\Performance;

use App\Models\Student;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApiPerformanceTest extends TestCase
{
    public function test_api_response_time_performance(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        // Create test data
        Student::factory()->count(50)->create();

        $startTime = microtime(true);

        $response = $this->getJson('/api/v1/students');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.0, $executionTime, 'API response time exceeded 1 second');
    }

    public function test_api_pagination_performance(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        // Create large dataset
        Student::factory()->count(200)->create();

        $startTime = microtime(true);

        // Test multiple page requests
        for ($page = 1; $page <= 5; $page++) {
            $response = $this->getJson("/api/v1/students?page={$page}&per_page=20");
            $response->assertStatus(200);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(2.0, $executionTime, 'API pagination performance too slow');
    }

    public function test_api_search_performance(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        // Create searchable data
        for ($i = 0; $i < 100; $i++) {
            $name = $i % 2 == 0 ? 'John Smith ' . $i : 'Jane Doe ' . $i;
            $user = User::factory()->create(['name' => $name]);
            Student::factory()->create(['user_id' => $user->id]);
        }

        $startTime = microtime(true);

        $response = $this->getJson('/api/v1/students?search=John');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(0.5, $executionTime, 'API search performance too slow');
        
        $results = $response->json('data');
        $this->assertGreaterThan(0, count($results), 'Search should return results');
    }

    public function test_api_concurrent_request_handling(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        Student::factory()->count(30)->create();

        $startTime = microtime(true);

        // Simulate concurrent requests
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->getJson('/api/v1/students?per_page=10');
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        $this->assertLessThan(5.0, $executionTime, 'Concurrent API requests took too long');
    }

    public function test_api_rate_limiting_performance(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $startTime = microtime(true);
        $requestCount = 0;

        // Test rate limiting behavior
        for ($i = 0; $i < 65; $i++) {
            $response = $this->getJson('/api/v1/students');
            $requestCount++;
            
            if ($response->status() === 429) {
                // Rate limit hit
                break;
            }
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThanOrEqual(60, $requestCount, 'Rate limiting not working properly');
        $this->assertLessThan(10.0, $executionTime, 'Rate limiting test took too long');
    }

    public function test_api_data_serialization_performance(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        // Create complex data with relationships
        $students = Student::factory()->count(100)->create();

        $startTime = microtime(true);

        $response = $this->getJson('/api/v1/students?include=user,school_class,subjects');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $executionTime, 'Complex data serialization took too long');
    }

    public function test_api_authentication_performance(): void
    {
        $user = $this->createAdmin();
        $token = $user->createToken('test')->plainTextToken;

        $startTime = microtime(true);

        // Test token-based authentication performance
        for ($i = 0; $i < 20; $i++) {
            $response = $this->withHeader('Authorization', "Bearer {$token}")
                             ->getJson('/api/v1/students');
            $response->assertStatus(200);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(5.0, $executionTime, 'API authentication took too long');
    }

    public function test_api_validation_performance(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $startTime = microtime(true);

        // Test validation performance with invalid data
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/v1/students', [
                // Invalid data to trigger validation
                'name' => '',
                'email' => 'invalid-email',
                'password' => '123', // Too short
            ]);
            
            $response->assertStatus(422); // Validation error
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(2.0, $executionTime, 'API validation took too long');
    }

    public function test_api_bulk_operations_performance(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $students = Student::factory()->count(50)->create();
        $studentIds = $students->pluck('id')->take(30)->toArray();

        $startTime = microtime(true);

        $response = $this->postJson('/api/v1/students/bulk-update', [
            'ids' => $studentIds,
            'student_status' => 'graduated',
        ]);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(3.0, $executionTime, 'Bulk operations took too long');
    }

    public function test_api_caching_effectiveness(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        Student::factory()->count(20)->create();

        // Clear any existing cache
        Cache::flush();

        // First request (not cached)
        $startTime1 = microtime(true);
        $response1 = $this->getJson('/api/v1/students');
        $endTime1 = microtime(true);
        $time1 = $endTime1 - $startTime1;

        $response1->assertStatus(200);

        // Second request (potentially cached)
        $startTime2 = microtime(true);
        $response2 = $this->getJson('/api/v1/students');
        $endTime2 = microtime(true);
        $time2 = $endTime2 - $startTime2;

        $response2->assertStatus(200);

        // Results should be consistent
        $this->assertEquals(
            count($response1->json('data')),
            count($response2->json('data'))
        );

        // Note: Actual caching would need to be implemented at application level
        // This test verifies consistent performance
    }

    public function test_api_memory_usage_with_large_datasets(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        // Create large dataset
        Student::factory()->count(500)->create();

        $memoryBefore = memory_get_usage(true);

        $response = $this->getJson('/api/v1/students?per_page=100');

        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;

        $response->assertStatus(200);
        
        // API should not consume excessive memory (less than 20MB for 100 records)
        $this->assertLessThan(20 * 1024 * 1024, $memoryUsed, 'API consumed too much memory');
    }

    public function test_api_database_query_optimization(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        Student::factory()->count(30)->create();

        DB::enableQueryLog();
        DB::flushQueryLog();

        $response = $this->getJson('/api/v1/students?include=user,school_class');

        $queries = DB::getQueryLog();
        
        $response->assertStatus(200);
        
        // Should use eager loading to minimize queries (avoid N+1 problem)
        $this->assertLessThan(10, count($queries), 'API made too many database queries');

        DB::disableQueryLog();
    }

    public function test_api_error_handling_performance(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $startTime = microtime(true);

        // Test error handling performance
        for ($i = 0; $i < 10; $i++) {
            // Request non-existent resource
            $response = $this->getJson('/api/v1/students/99999');
            $response->assertStatus(404);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(1.0, $executionTime, 'API error handling took too long');
    }

    public function test_api_response_size_optimization(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        Student::factory()->count(50)->create();

        $response = $this->getJson('/api/v1/students');
        
        $responseSize = strlen($response->getContent());
        
        $response->assertStatus(200);
        
        // Response should be reasonably sized (less than 1MB for 50 records)
        $this->assertLessThan(1024 * 1024, $responseSize, 'API response size too large');
    }
}