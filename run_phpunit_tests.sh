#!/bin/bash

# PHPUnit Test Runner for Laravel API
# This script runs comprehensive PHPUnit tests for the API

set -e

echo "🧪 Starting PHPUnit API Tests"
echo "=============================="

# Change to backend directory
cd backend

# Check if PHPUnit is installed
if ! command -v ./vendor/bin/phpunit &> /dev/null; then
    echo "❌ PHPUnit not found. Installing dependencies..."
    composer install --no-interaction --prefer-dist
fi

# Set environment for testing
export APP_ENV=testing
export DB_CONNECTION=sqlite
export DB_DATABASE=:memory:

echo "🔧 Setting up test environment..."

# Clear any existing caches (skip cache:clear for testing)
php artisan config:clear
php artisan route:clear

echo "📋 Running API Test Suite..."
echo ""

# Run all API tests
echo "1️⃣ Running Auth Tests..."
./vendor/bin/phpunit tests/Feature/Api/AuthTest.php

echo ""
echo "2️⃣ Running User Tests..."
./vendor/bin/phpunit tests/Feature/Api/UserTest.php

echo ""
echo "3️⃣ Running Simple API Tests..."
./vendor/bin/phpunit tests/Feature/Api/SimpleApiTest.php

echo ""
echo "4️⃣ Running All API Tests..."
./vendor/bin/phpunit tests/Feature/Api --testdox

echo ""
echo "5️⃣ Running All Tests..."
./vendor/bin/phpunit --testdox

echo ""
echo "✅ All PHPUnit tests completed!"
echo "=============================="

# Generate test coverage report (optional)
if [ "$1" = "--coverage" ]; then
    echo ""
    echo "📊 Generating test coverage report..."
    ./vendor/bin/phpunit --coverage-html coverage-report
    echo "📁 Coverage report generated in: coverage-report/index.html"
fi

echo ""
echo "🎉 PHPUnit testing completed successfully!"
