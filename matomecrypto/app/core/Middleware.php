<?php

class Middleware {
    private $security;

    public function __construct() {
        $this->security = Security::getInstance();
    }

    public function handle() {
        // Set security headers
        $this->security->setSecurityHeaders();

        // Validate request origin
        if (!$this->security->validateRequestOrigin()) {
            header('HTTP/1.1 403 Forbidden');
            exit('Access denied');
        }

        // Sanitize all input
        $_GET = $this->security->sanitizeInput($_GET);
        $_POST = $this->security->sanitizeInput($_POST);
        $_REQUEST = $this->security->sanitizeInput($_REQUEST);

        // Validate CSRF for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->security->validateCSRF();
            } catch (Exception $e) {
                header('HTTP/1.1 403 Forbidden');
                exit('CSRF validation failed');
            }
        }

        // Check for SQL injection attempts
        $this->checkForSQLInjection();

        // Check for XSS attempts
        $this->checkForXSS();

        // Log the request
        $this->logRequest();
    }

    private function checkForSQLInjection() {
        $patterns = [
            '/\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|ALTER|CREATE)\b/i',
            '/\b(OR|AND)\s+[\d\'\"]+\s*=\s*[\d\'\"]/i',
            '/\b(OR|AND)\s+[\d\'\"]+\s*=\s*[\d\'\"]/i',
            '/\b(OR|AND)\s+[\d\'\"]+\s*=\s*[\d\'\"]/i'
        ];

        $input = array_merge($_GET, $_POST, $_REQUEST);
        foreach ($input as $value) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    $this->security->logSecurityEvent(null, 'SQL_INJECTION_ATTEMPT', [
                        'input' => $value,
                        'ip' => $_SERVER['REMOTE_ADDR']
                    ]);
                    header('HTTP/1.1 403 Forbidden');
                    exit('Invalid input detected');
                }
            }
        }
    }

    private function checkForXSS() {
        $patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/data:/i'
        ];

        $input = array_merge($_GET, $_POST, $_REQUEST);
        foreach ($input as $value) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    $this->security->logSecurityEvent(null, 'XSS_ATTEMPT', [
                        'input' => $value,
                        'ip' => $_SERVER['REMOTE_ADDR']
                    ]);
                    header('HTTP/1.1 403 Forbidden');
                    exit('Invalid input detected');
                }
            }
        }
    }

    private function logRequest() {
        $this->security->logSecurityEvent(
            $_SESSION['user']['id'] ?? null,
            'REQUEST',
            [
                'method' => $_SERVER['REQUEST_METHOD'],
                'uri' => $_SERVER['REQUEST_URI'],
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ]
        );
    }
} 