<?php

/**
 * School Management System Test Runner
 * 
 * Comprehensive test execution with performance monitoring
 */

echo "🧪 School Management System - Test Suite Runner\n";
echo "===============================================\n\n";

// Test configurations
$testSuites = [
    'Unit Tests' => [
        'command' => 'php artisan test tests/Unit --coverage --min=80',
        'description' => 'Testing individual models and relationships',
    ],
    'Feature Tests - Authentication' => [
        'command' => 'php artisan test tests/Feature/Auth',
        'description' => 'Testing authentication and authorization flows',
    ],
    'Feature Tests - API' => [
        'command' => 'php artisan test tests/Feature/Api',
        'description' => 'Testing API endpoints and responses',
    ],
    'Integration Tests' => [
        'command' => 'php artisan test tests/Feature/Integration',
        'description' => 'Testing enrollment and business processes',
    ],
    'Browser Tests' => [
        'command' => 'php artisan test tests/Feature/Browser',
        'description' => 'Testing user workflows and interactions',
    ],
    'Performance Tests' => [
        'command' => 'php artisan test tests/Feature/Performance',
        'description' => 'Testing database and API performance',
    ],
];

$totalTests = 0;
$passedTests = 0;
$failedSuites = [];

foreach ($testSuites as $suiteName => $config) {
    echo "🔄 Running: {$suiteName}\n";
    echo "   {$config['description']}\n";
    echo "   Command: {$config['command']}\n\n";
    
    $startTime = microtime(true);
    
    // Execute test command
    $output = [];
    $returnCode = 0;
    exec($config['command'], $output, $returnCode);
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    if ($returnCode === 0) {
        echo "✅ {$suiteName} - PASSED (${duration}s)\n";
        $passedTests++;
    } else {
        echo "❌ {$suiteName} - FAILED (${duration}s)\n";
        $failedSuites[] = $suiteName;
        
        // Show last few lines of output for debugging
        echo "   Last output:\n";
        foreach (array_slice($output, -5) as $line) {
            echo "   > {$line}\n";
        }
    }
    
    echo "\n";
    $totalTests++;
}

// Summary
echo "📊 Test Summary\n";
echo "===============\n";
echo "Total Suites: {$totalTests}\n";
echo "Passed: {$passedTests}\n";
echo "Failed: " . count($failedSuites) . "\n";

if (!empty($failedSuites)) {
    echo "\n❌ Failed Suites:\n";
    foreach ($failedSuites as $suite) {
        echo "  - {$suite}\n";
    }
    echo "\n💡 Run individual suites for detailed error information.\n";
    exit(1);
} else {
    echo "\n🎉 All test suites passed!\n";
    echo "\n📈 Next Steps:\n";
    echo "  - Review test coverage reports\n";
    echo "  - Run performance benchmarks\n";
    echo "  - Deploy to staging environment\n";
    exit(0);
}
?>