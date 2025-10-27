#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# Navigate to the backend directory
cd backend

# Set up environment variables for testing with in-memory SQLite
export DB_CONNECTION=sqlite
export DB_DATABASE=:memory:

echo "🧪 Starting News Aggregator API Tests"
echo "======================================"
echo "🔧 Setting up test environment..."

# Clear any existing caches (skip cache:clear for testing)
php artisan config:clear
php artisan route:clear

echo "📋 Running News Aggregator Test Suite..."
echo ""

# Run all news-related tests
echo "1️⃣ Running News Controller Tests..."
./vendor/bin/phpunit tests/Feature/Api/NewsControllerTest.php

echo ""
echo "2️⃣ Running News Source Controller Tests..."
./vendor/bin/phpunit tests/Feature/Api/NewsSourceControllerTest.php

echo ""
echo "3️⃣ Running Category Controller Tests..."
./vendor/bin/phpunit tests/Feature/Api/CategoryControllerTest.php

echo ""
echo "4️⃣ Running User Preference Controller Tests..."
./vendor/bin/phpunit tests/Feature/Api/UserPreferenceControllerTest.php

echo ""
echo "5️⃣ Running All News API Tests..."
./vendor/bin/phpunit tests/Feature/Api --testdox

echo ""
echo "6️⃣ Running All Tests (including existing auth/user tests)..."
./vendor/bin/phpunit --testdox

echo ""
echo "✅ All News Aggregator tests completed!"
echo "======================================"

# Return to the root directory
cd ..
