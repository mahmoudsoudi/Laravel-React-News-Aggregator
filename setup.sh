#!/bin/bash

echo "🚀 Setting up Aggregator Application..."

# Generate Laravel application key
echo "📝 Generating Laravel application key..."
cd backend
php artisan key:generate
cd ..

# Copy environment file for Docker
echo "📝 Setting up environment files..."
cp backend/.env.docker backend/.env

# Generate a new application key for Docker
cd backend
php artisan key:generate --force
cd ..

# Check if docker-compose or docker compose is available
if command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
elif docker compose version &> /dev/null; then
    DOCKER_COMPOSE="docker compose"
else
    echo "❌ Error: Neither 'docker-compose' nor 'docker compose' command found."
    echo "Please install Docker Compose and try again."
    exit 1
fi

# Build and start Docker containers
echo "🐳 Building and starting Docker containers..."
$DOCKER_COMPOSE down
$DOCKER_COMPOSE build
$DOCKER_COMPOSE up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# Run migrations
echo "🗄️ Running database migrations..."
$DOCKER_COMPOSE exec backend php artisan migrate --force

# Install frontend dependencies
echo "📦 Installing frontend dependencies..."
cd frontend
npm install
cd ..

echo "✅ Setup complete!"
echo ""
echo "🌐 Application URLs:"
echo "   Frontend: http://localhost:3000"
echo "   Backend API: http://localhost:8000/api"
echo "   Database: localhost:5432"
echo ""
echo "📋 Available commands:"
echo "   Start: docker-compose up -d"
echo "   Stop: docker-compose down"
echo "   Logs: docker-compose logs -f"
echo "   Backend shell: docker-compose exec backend bash"
echo "   Frontend shell: docker-compose exec frontend sh"
