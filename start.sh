#!/bin/bash

# News Aggregator Startup Script
echo "🚀 Starting News Aggregator System..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Check if docker-compose is available
if ! command -v docker-compose &> /dev/null; then
    echo "❌ docker-compose is not installed. Please install it first."
    exit 1
fi

echo "📦 Building and starting containers..."
docker-compose up -d --build

echo "⏳ Waiting for services to be ready..."
sleep 10

echo "🗄️ Setting up database..."
docker-compose exec -T backend php artisan migrate --force
docker-compose exec -T backend php artisan db:seed --force

echo "🔧 Clearing caches..."
docker-compose exec -T backend php artisan config:clear
docker-compose exec -T backend php artisan route:clear
docker-compose exec -T backend php artisan view:clear

echo "📊 Checking system status..."
echo "Containers:"
docker-compose ps

echo ""
echo "🌐 API Health Check:"
curl -s http://localhost:8000/api/test || echo "❌ API not responding"

echo ""
echo "📰 Testing News Aggregation:"
docker-compose exec -T backend php artisan news:aggregate

echo ""
echo "✅ News Aggregator is ready!"
echo "🌐 API URL: http://localhost:8000"
echo "📚 API Documentation: See NEWS_AGGREGATOR_POSTMAN_COLLECTION.json"
echo "📋 Setup Guide: See DOCKER_CRON_SETUP.md"
echo ""
echo "🔍 To view logs: docker-compose logs -f"
echo "🛑 To stop: docker-compose down"
