# PHPUnit Testing Guide for Laravel API

This guide explains how to run comprehensive PHPUnit tests for the Laravel API authentication system.

## 🧪 Test Structure

### Test Files
- `tests/Feature/Api/AuthTest.php` - Authentication tests (register, login, logout)
- `tests/Feature/Api/UserTest.php` - User profile management tests
- `tests/Feature/Api/ApiTestSuite.php` - Complete workflow and comprehensive tests

### Test Categories

#### 1. Authentication Tests (`AuthTest.php`)
- ✅ User registration with validation
- ✅ User login with credentials
- ✅ User logout functionality
- ✅ Invalid credentials handling
- ✅ Validation error responses

#### 2. User Management Tests (`UserTest.php`)
- ✅ Get user profile
- ✅ Update user profile
- ✅ Update password
- ✅ Delete user account
- ✅ Authentication required for protected routes
- ✅ Validation for profile updates

#### 3. Complete Workflow Tests (`ApiTestSuite.php`)
- ✅ End-to-end user workflow (register → login → update → logout → delete)
- ✅ All validation scenarios
- ✅ Authentication scenarios
- ✅ API response format consistency

## 🚀 Running Tests

### Quick Start
```bash
# Run all tests
./run_phpunit_tests.sh

# Run with coverage report
./run_phpunit_tests.sh --coverage
```

### Individual Test Suites
```bash
cd backend

# Run only authentication tests
./vendor/bin/phpunit tests/Feature/Api/AuthTest.php

# Run only user management tests
./vendor/bin/phpunit tests/Feature/Api/UserTest.php

# Run complete API test suite
./vendor/bin/phpunit tests/Feature/Api/ApiTestSuite.php

# Run all API tests
./vendor/bin/phpunit tests/Feature/Api --verbose

# Run all tests
./vendor/bin/phpunit --verbose
```

### Specific Test Methods
```bash
# Run specific test method
./vendor/bin/phpunit --filter test_user_registration_success

# Run tests matching pattern
./vendor/bin/phpunit --filter "test_user_login"
```

## 📊 Test Coverage

The tests cover:

### API Endpoints
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get user profile
- `PUT /api/user` - Update user profile
- `DELETE /api/user` - Delete user account

### Validation Scenarios
- Missing required fields
- Invalid email formats
- Password validation (length, confirmation)
- Duplicate email addresses
- Invalid credentials

### Authentication Scenarios
- Access with valid token
- Access without token
- Access with invalid token
- Token expiration after logout

### Response Format Testing
- Success response structure
- Error response structure
- Validation error structure
- Consistent JSON formatting

## 🔧 Test Configuration

### Environment Setup
Tests run in a clean testing environment:
- Database: SQLite in-memory
- Cache: Array driver
- Mail: Array driver
- Session: Array driver

### Test Database
- Uses `RefreshDatabase` trait
- Each test runs in isolation
- No data persistence between tests

### Authentication
- Uses Laravel Sanctum tokens
- Tests both authenticated and unauthenticated scenarios
- Validates token-based access control

## 📈 Test Results

### Expected Output
```
🧪 Starting PHPUnit API Tests
==============================

1️⃣ Running Auth Tests...
PHPUnit 10.5.0 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.2.0
Configuration: /path/to/backend/phpunit.xml

Testing tests/Feature/Api/AuthTest.php
.                                                                   1 / 1 (100%)

Time: 00:00.123, Memory: 12.00 MB

OK (1 test, 5 assertions)

2️⃣ Running User Tests...
...

✅ All PHPUnit tests completed!
==============================
```

### Test Assertions
Each test includes multiple assertions:
- HTTP status codes
- JSON response structure
- Database state verification
- Authentication token validation
- Error message validation

## 🛠️ Customization

### Adding New Tests
1. Create test method in appropriate test file
2. Use Laravel testing helpers (`$this->postJson()`, `$this->getJson()`)
3. Add assertions for response structure and data
4. Run tests to verify functionality

### Test Data
- Uses Laravel factories for user creation
- Faker for generating test data
- Consistent test data across all tests

### Mocking
- Can mock external services if needed
- Uses real database for integration testing
- Tests actual API endpoints

## 🐛 Debugging Tests

### Verbose Output
```bash
./vendor/bin/phpunit --verbose
```

### Stop on First Failure
```bash
./vendor/bin/phpunit --stop-on-failure
```

### Debug Specific Test
```bash
./vendor/bin/phpunit --filter test_name --debug
```

### Coverage Analysis
```bash
./vendor/bin/phpunit --coverage-html coverage-report
```

## 📝 Best Practices

### Test Organization
- Group related tests in same file
- Use descriptive test method names
- Follow AAA pattern (Arrange, Act, Assert)

### Test Data
- Use factories for consistent data
- Clean up after each test
- Use meaningful test data

### Assertions
- Test both success and failure cases
- Verify response structure and content
- Check database state changes
- Validate authentication requirements

## 🎯 Integration with CI/CD

### GitHub Actions Example
```yaml
name: PHPUnit Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: cd backend && composer install
      - name: Run tests
        run: cd backend && ./vendor/bin/phpunit
```

## 📚 Additional Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Sanctum Testing](https://laravel.com/docs/sanctum#testing)

---

**Note**: These tests provide comprehensive coverage of the API functionality and can be integrated into your development workflow for continuous testing and quality assurance.
