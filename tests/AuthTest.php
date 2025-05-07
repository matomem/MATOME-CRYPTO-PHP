<?php

namespace Tests;

use App\Core\AuthMiddleware;

class AuthTest extends TestCase
{
    private $secretKey = 'test_secret_key';

    protected function setUp(): void
    {
        parent::setUp();
        AuthMiddleware::initialize($this->secretKey);
    }

    public function testGenerateToken()
    {
        $userId = 1;
        $userRole = 'admin';
        
        $token = AuthMiddleware::generateToken($userId, $userRole);
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testValidateRole()
    {
        $userId = 1;
        $userRole = 'admin';
        
        $token = AuthMiddleware::generateToken($userId, $userRole);
        
        // Mock the Authorization header
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        
        $user = AuthMiddleware::validateRole('admin');
        
        $this->assertEquals($userId, $user->user_id);
        $this->assertEquals($userRole, $user->role);
    }

    public function testInvalidToken()
    {
        $this->expectException(\Exception::class);
        
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer invalid_token';
        
        AuthMiddleware::authenticate();
    }

    public function testMissingToken()
    {
        $this->expectException(\Exception::class);
        
        unset($_SERVER['HTTP_AUTHORIZATION']);
        
        AuthMiddleware::authenticate();
    }
} 