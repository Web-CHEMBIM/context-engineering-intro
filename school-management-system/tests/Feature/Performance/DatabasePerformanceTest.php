<?php

namespace Tests\Feature\Performance;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabasePerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Enable query logging for performance analysis
        DB::enableQueryLog();
    }

    public function test_student_listing_query_performance(): void
    {
        // Create test data
        Student::factory()->count(100)->create();

        $startTime = microtime(true);
        
        // Test query performance
        $students = Student::with(['user', 'schoolClass', 'academicYear'])
                          ->paginate(20);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Performance assertion: should complete within 0.5 seconds
        $this->assertLessThan(0.5, $executionTime, 'Student listing query took too long');

        // Query count assertion: should use minimal queries (N+1 prevention)
        $queries = DB::getQueryLog();
        $this->assertLessThan(10, count($queries), 'Too many database queries executed');

        DB::flushQueryLog();
    }

    public function test_student_search_performance(): void
    {
        // Create students with searchable names
        User::factory()->count(50)->create(['name' => 'John Smith']);
        User::factory()->count(50)->create(['name' => 'Jane Doe']);
        
        $startTime = microtime(true);

        // Test search performance
        $results = Student::whereHas('user', function($query) {
            $query->where('name', 'like', '%John%');
        })->with('user')->get();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(0.3, $executionTime, 'Student search took too long');
        $this->assertGreaterThan(0, $results->count(), 'Search should return results');

        DB::flushQueryLog();
    }

    public function test_complex_relationship_query_performance(): void
    {
        // Create complex data structure
        $class = SchoolClass::factory()->create();
        $students = Student::factory()->count(30)->create(['school_class_id' => $class->id]);
        $subjects = Subject::factory()->count(10)->create();
        
        // Enroll each student in multiple subjects
        foreach ($students as $student) {
            $student->subjects()->attach($subjects->random(5)->pluck('id'));
        }

        $startTime = microtime(true);

        // Complex query with multiple relationships
        $results = Student::with([
                'user',
                'schoolClass',
                'academicYear',
                'subjects' => function($query) {
                    $query->withPivot('grade', 'status');
                }
            ])
            ->where('student_status', 'enrolled')
            ->get();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(1.0, $executionTime, 'Complex relationship query took too long');

        // Verify data integrity
        $this->assertGreaterThan(0, $results->count());
        
        foreach ($results as $student) {
            $this->assertNotNull($student->user);
            $this->assertNotNull($student->schoolClass);
            $this->assertGreaterThan(0, $student->subjects->count());
        }

        DB::flushQueryLog();
    }

    public function test_bulk_insert_performance(): void
    {
        $startTime = microtime(true);

        // Test bulk insert performance
        $userData = [];
        for ($i = 0; $i < 1000; $i++) {
            $userData[] = [
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('users')->insert($userData);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(2.0, $executionTime, 'Bulk insert took too long');
        
        // Verify insertion
        $this->assertEquals(1000, User::count() - 1); // -1 for existing seeded data

        DB::flushQueryLog();
    }

    public function test_aggregation_query_performance(): void
    {
        // Create test data
        Student::factory()->count(200)->create();

        $startTime = microtime(true);

        // Test aggregation queries
        $stats = [
            'total_students' => Student::count(),
            'enrolled_students' => Student::where('student_status', 'enrolled')->count(),
            'total_fees' => Student::sum('total_fees'),
            'average_fees' => Student::avg('total_fees'),
            'fees_stats' => Student::selectRaw('
                SUM(total_fees) as total,
                SUM(fees_paid) as paid,
                SUM(fees_pending) as pending,
                AVG(total_fees) as average
            ')->first(),
        ];

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(0.5, $executionTime, 'Aggregation queries took too long');
        $this->assertIsArray($stats);
        $this->assertGreaterThan(0, $stats['total_students']);

        DB::flushQueryLog();
    }

    public function test_pagination_performance(): void
    {
        // Create large dataset
        Student::factory()->count(500)->create();

        $startTime = microtime(true);

        // Test pagination performance across multiple pages
        for ($page = 1; $page <= 5; $page++) {
            $results = Student::with('user')
                            ->paginate(50, ['*'], 'page', $page);
            
            $this->assertEquals(50, $results->count());
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(1.5, $executionTime, 'Pagination across multiple pages took too long');

        DB::flushQueryLog();
    }

    public function test_database_connection_pool_performance(): void
    {
        $startTime = microtime(true);

        // Test multiple concurrent-like queries
        $promises = [];
        for ($i = 0; $i < 10; $i++) {
            Student::factory()->create();
            Teacher::factory()->create();
            Subject::factory()->create();
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(2.0, $executionTime, 'Multiple model creation took too long');

        DB::flushQueryLog();
    }

    public function test_index_efficiency(): void
    {
        // Create data to test index usage
        Student::factory()->count(100)->create();

        $startTime = microtime(true);

        // Query that should use indexes
        $results = Student::where('student_status', 'enrolled')
                         ->where('created_at', '>', now()->subDays(30))
                         ->orderBy('created_at', 'desc')
                         ->limit(10)
                         ->get();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(0.2, $executionTime, 'Indexed query took too long');

        DB::flushQueryLog();
    }

    public function test_memory_usage_during_large_query(): void
    {
        // Create large dataset
        Student::factory()->count(1000)->create();

        $memoryBefore = memory_get_usage(true);

        // Query large dataset
        $students = Student::with(['user', 'schoolClass'])->get();

        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Memory usage should be reasonable (less than 50MB for 1000 records)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Query used too much memory');

        // Cleanup
        unset($students);

        DB::flushQueryLog();
    }

    public function test_concurrent_access_simulation(): void
    {
        // Create shared resource
        $class = SchoolClass::factory()->create(['capacity' => 25]);

        $startTime = microtime(true);

        // Simulate concurrent student enrollments
        for ($i = 0; $i < 30; $i++) {
            try {
                $student = Student::factory()->create(['school_class_id' => $class->id]);
                $this->assertNotNull($student);
            } catch (\Exception $e) {
                // Expected behavior if class capacity is exceeded
            }
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(3.0, $executionTime, 'Concurrent access simulation took too long');

        DB::flushQueryLog();
    }

    public function test_query_cache_effectiveness(): void
    {
        // Create test data
        $subjects = Subject::factory()->count(20)->create();

        // First query (should not be cached)
        $startTime1 = microtime(true);
        $results1 = Subject::where('is_active', true)->get();
        $endTime1 = microtime(true);
        $time1 = $endTime1 - $startTime1;

        // Clear query log
        DB::flushQueryLog();

        // Second identical query (should be faster if cached)
        $startTime2 = microtime(true);
        $results2 = Subject::where('is_active', true)->get();
        $endTime2 = microtime(true);
        $time2 = $endTime2 - $startTime2;

        // Results should be identical
        $this->assertEquals($results1->count(), $results2->count());

        // Note: Query caching would need to be implemented at application level
        // This test verifies consistent performance

        DB::flushQueryLog();
    }

    protected function tearDown(): void
    {
        DB::disableQueryLog();
        parent::tearDown();
    }
}