# MatomeCrypto

A secure PHP cryptocurrency dashboard and trading web app using the Luno API.

## Features
- User registration and login with enhanced security
- Dashboard with live balances and recent activity
- Wallet overview
- Buy/sell crypto (Luno API)
- Transaction history
- Comprehensive security features
- Audit logging
- Brute force protection

## Setup
1. Clone or copy this project to your server or local machine.
2. Create a MySQL database and import the following tables:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    login_attempts INT DEFAULT 0,
    last_attempt TIMESTAMP
);

CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

3. Set your Luno API credentials as environment variables or edit `config/config.php`:
   - `LUNO_KEY` and `LUNO_SECRET`
4. Configure security settings in `config/config.php`:
   ```php
   define('MAX_FILE_SIZE', 5242880); // 5MB
   define('HASH_COST', 12);
   define('MAX_LOGIN_ATTEMPTS', 5);
   define('LOCKOUT_TIME', 900); // 15 minutes
   ```
5. Make sure the following PHP extensions are enabled:
   - curl
   - PDO
   - OpenSSL
   - FileInfo
6. Set your web server's document root to the `public` folder.

## Security Features

### 1. Session Security
```php
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
```

### 2. Security Headers
```php
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; ...');
```

### 3. Core Security Components

#### Security Class (`app/core/Security.php`)
- CSRF Protection
- Input Sanitization
- SQL Injection Prevention
- Password Hashing
- File Upload Validation
- Brute Force Protection
- Security Event Logging

#### Middleware (`app/core/Middleware.php`)
- Security Headers Management
- Request Origin Validation
- Input Sanitization
- CSRF Validation
- SQL Injection Detection
- XSS Attack Prevention
- Request Logging

## Directory Structure
```
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   └── DashboardController.php
│   ├── core/
│   │   ├── Security.php
│   │   └── Middleware.php
│   └── views/
│       ├── auth/
│       │   └── login.php
│       └── errors/
│           └── 404.php
├── config/
│   └── config.php
└── public/
    └── index.php
```

## Best Practices

1. **Security**
   - Always use prepared statements for database queries
   - Validate and sanitize all user input
   - Implement proper session management
   - Use secure password hashing
   - Enable security headers
   - Keep API keys secure
   - Use HTTPS in production

2. **Error Handling**
   - Implement proper error logging
   - Don't expose sensitive information in error messages
   - Use custom error pages

3. **File Upload**
   - Validate file types and sizes
   - Use secure file names
   - Store files outside web root
   - Implement proper access controls

## Dependencies
- PHP 7.4 or higher
- PDO Extension
- OpenSSL Extension
- FileInfo Extension
- cURL Extension

## Contributing
When contributing to this project:
1. Follow PSR-4 autoloading standards
2. Implement proper error handling
3. Add appropriate security measures
4. Document all new features
5. Write unit tests for new functionality

## License
MIT

## Support
For support, please open an issue in the GitHub repository. 