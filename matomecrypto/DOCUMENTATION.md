# MatomeCrypto Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [System Architecture](#system-architecture)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Core Components](#core-components)
6. [Features](#features)
7. [API Documentation](#api-documentation)
8. [Security](#security)
9. [Database](#database)
10. [Testing](#testing)
11. [Deployment](#deployment)
12. [Maintenance](#maintenance)

## Introduction

MatomeCrypto is a secure PHP cryptocurrency dashboard and trading web application that integrates with the Luno API. It provides a comprehensive platform for managing cryptocurrency assets, executing trades, and monitoring market activities.

### Key Benefits
- Secure user authentication and authorization
- Real-time cryptocurrency data
- Advanced trading capabilities
- Comprehensive security features
- Scalable architecture
- Developer-friendly codebase

## System Architecture

### Technology Stack
- **Backend**: PHP 8.1+
- **Database**: MySQL
- **Cache**: Redis
- **Authentication**: JWT
- **Testing**: PHPUnit
- **Logging**: Monolog
- **API Integration**: Luno API

### Directory Structure
```
├── app/
│   ├── commands/          # CLI commands
│   ├── controllers/       # Application controllers
│   ├── core/             # Core system components
│   ├── models/           # Database models
│   ├── services/         # Business logic
│   ├── views/            # View templates
│   └── helpers/          # Helper functions
├── config/               # Configuration files
├── database/            # Database migrations and seeds
├── public/              # Public assets
├── storage/             # Storage for logs and cache
└── tests/               # Test files
```

## Installation

### Prerequisites
- PHP 8.1 or higher
- MySQL 5.7 or higher
- Redis server
- Composer
- Web server (Apache/Nginx)

### Installation Steps
1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/matomecrypto.git
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Configure environment:
   ```bash
   cp .env.example .env
   # Edit .env with your settings
   ```

4. Create required directories:
   ```bash
   mkdir -p storage/logs
   mkdir -p database/migrations
   ```

5. Run database migrations:
   ```bash
   php migrate.php migrate
   ```

## Configuration

### Environment Variables
```env
# Application
APP_ENV=development
APP_DEBUG=true
APP_KEY=your-secret-key
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=matomecrypto
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Luno API
LUNO_KEY=your-luno-key
LUNO_SECRET=your-luno-secret
```

## Core Components

### 1. Authentication System
```php
// JWT-based authentication
$token = AuthMiddleware::generateToken($userId, $userRole);
$user = AuthMiddleware::validateRole('admin');
```

Features:
- JWT token generation and validation
- Role-based access control
- Token expiration
- Secure password handling
- Session management

### 2. Database System
```php
// Database operations
$db = Database::getInstance();
$db->beginTransaction();
try {
    $db->query("INSERT INTO users (username) VALUES (?)", [$username]);
    $db->commit();
} catch (\Exception $e) {
    $db->rollBack();
    throw $e;
}
```

Features:
- PDO-based connections
- Migration system
- Transaction support
- Prepared statements
- Query optimization

### 3. Caching System
```php
// Cache operations
$cache = Cache::getInstance();
$cache->set('key', $value, 3600);
$value = $cache->remember('key', 3600, function() {
    return expensiveOperation();
});
```

Features:
- Redis-based caching
- TTL support
- Atomic operations
- Cache remember pattern
- Cache invalidation

### 4. Logging System
```php
// Logging operations
$logger = Logger::getInstance();
$logger->info('User logged in', ['user_id' => $userId]);
```

Features:
- Multiple log levels
- Rotating file handler
- Custom formatters
- Context support
- Log retention

## Features

### 1. User Management
- User registration
- Authentication
- Role management
- Profile management
- Password reset
- Email verification

### 2. Cryptocurrency Features
- Wallet management
- Transaction history
- Balance tracking
- Market data
- Trading functionality
- Price alerts

### 3. Security Features
- CSRF protection
- XSS prevention
- SQL injection protection
- Input validation
- Rate limiting
- IP blocking

### 4. API Integration
- Luno API integration
- RESTful endpoints
- Rate limiting
- API versioning
- Request validation
- Response formatting

## API Documentation

### Authentication Endpoints

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

Response:
```json
{
    "token": "jwt_token",
    "user": {
        "id": 1,
        "email": "user@example.com",
        "role": "user"
    }
}
```

#### Register
```http
POST /api/auth/register
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password",
    "username": "username"
}
```

### Cryptocurrency Endpoints

#### Get Balance
```http
GET /api/wallet/balance
Authorization: Bearer jwt_token
```

#### Create Order
```http
POST /api/trading/order
Authorization: Bearer jwt_token
Content-Type: application/json

{
    "type": "buy",
    "amount": "0.1",
    "price": "50000"
}
```

## Security

### Authentication
- JWT-based authentication
- Token expiration
- Role-based access
- Password hashing
- Session management

### Data Protection
- Input validation
- Output sanitization
- SQL injection prevention
- XSS protection
- CSRF protection

### API Security
- Rate limiting
- IP blocking
- Request validation
- Secure headers
- Token validation

## Database

### Schema
```sql
-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('buy', 'sell') NOT NULL,
    amount DECIMAL(20,8) NOT NULL,
    price DECIMAL(20,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Migrations
```bash
# Run migrations
php migrate.php migrate

# Rollback migrations
php migrate.php rollback

# Refresh migrations
php migrate.php refresh
```

## Testing

### Running Tests
```bash
# Run all tests
composer test

# Run specific test
phpunit tests/AuthTest.php
```

### Test Structure
```php
class AuthTest extends TestCase
{
    public function testLogin()
    {
        // Test login functionality
    }

    public function testRegister()
    {
        // Test registration functionality
    }
}
```

## Deployment

### Production Setup
1. Configure web server
2. Set up SSL certificate
3. Configure environment variables
4. Run database migrations
5. Set up monitoring
6. Configure backups

### Monitoring
- Error tracking
- Performance monitoring
- Resource usage
- System health checks
- Log monitoring

## Maintenance

### Regular Tasks
- Log rotation
- Cache clearing
- Database optimization
- Backup management
- Security updates

### Backup Procedures
```bash
# Database backup
mysqldump -u username -p database > backup.sql

# File backup
tar -czf backup.tar.gz storage/
```

### Update Procedures
1. Pull latest changes
2. Run migrations
3. Clear cache
4. Update dependencies
5. Test functionality

## Support

For support:
1. Check the documentation
2. Review error logs
3. Contact development team
4. Submit issue on GitHub

## Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create pull request

## License

This project is licensed under the MIT License. 