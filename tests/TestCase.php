<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Core\ErrorHandler;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize error handler
        ErrorHandler::initialize();
        
        // Set up test environment
        putenv('APP_ENV=testing');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up after tests
        putenv('APP_ENV');
    }

    protected function createMockDatabase()
    {
        // Create a mock database connection for testing
        return new \PDO('sqlite::memory:');
    }

    protected function createTestUser($role = 'user')
    {
        // Create a test user with the specified role
        return [
            'id' => 1,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role' => $role
        ];
    }
} 