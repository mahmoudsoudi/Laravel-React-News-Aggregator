# Docker Cron Setup for News Aggregator

## Overview
This guide explains how to set up and run the News Aggregator system with automated cron jobs in Docker containers.

## Architecture

The system uses multiple Docker containers:
- **PostgreSQL**: Database
- **Redis**: Caching and session storage
- **Backend**: Laravel application with PHP-FPM
- **Nginx**: Web server and reverse proxy
- **Scheduler**: Dedicated container for cron jobs

## Quick Start

### 1. Start the System
```bash
# Start all services
docker-compose up -d

# Check status
docker-compose ps

# View logs
docker-compose logs -f
```

### 2. Initialize the Database
```bash
# Run migrations and seeders
docker-compose exec backend php artisan migrate --force
docker-compose exec backend php artisan db:seed --force
```

### 3. Test the API
```bash
# Health check
curl http://localhost:8000/api/test

# Test news aggregation
curl http://localhost:8000/api/aggregate-news
```

## Cron Jobs Configuration

### Automatic Cron Jobs
The system includes several automated cron jobs:

1. **News Aggregation** (Every hour + every 30 minutes during business hours)
   ```bash
   0 * * * * php artisan news:aggregate
   */30 6-22 * * * php artisan news:aggregate
   ```

2. **News Cleanup** (Daily at 2 AM)
   ```bash
   0 2 * * * php artisan news:cleanup
   ```

3. **News Digest** (Daily at 8 AM)
   ```bash
   0 8 * * * php artisan news:digest
   ```

4. **Health Check** (Every 5 minutes)
   ```bash
   */5 * * * * php artisan schedule:run
   ```

### Manual Cron Execution
```bash
# Run news aggregation manually
docker-compose exec backend php artisan news:aggregate

# Run cleanup manually
docker-compose exec backend php artisan news:cleanup --days=30

# Run digest manually
docker-compose exec backend php artisan news:digest --type=daily

# Check cron logs
docker-compose exec backend tail -f /var/log/news-aggregation.log
```

## Environment Configuration

### Required Environment Variables
Create a `.env` file in the backend directory:

```env
APP_NAME="News Aggregator"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=aggregator
DB_USERNAME=aggregator
DB_PASSWORD=aggregator_password

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# News API Keys (Add your actual API keys)
NEWS_API_KEY=your_news_api_key
GUARDIAN_API_KEY=your_guardian_api_key
NYT_API_KEY=your_nyt_api_key
```

## Monitoring and Logs

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f backend
docker-compose logs -f scheduler

# Cron logs
docker-compose exec backend tail -f /var/log/news-aggregation.log
docker-compose exec backend tail -f /var/log/news-cleanup.log
docker-compose exec backend tail -f /var/log/news-digest.log
```

### Health Monitoring
```bash
# Check container health
docker-compose ps

# Check cron status
docker-compose exec backend crontab -l

# Check Laravel scheduler
docker-compose exec backend php artisan schedule:list
```

## Production Deployment

### 1. Security Considerations
- Change default passwords
- Use strong API keys
- Enable SSL/TLS
- Configure firewall rules
- Regular security updates

### 2. Performance Optimization
- Enable Redis caching
- Configure database indexes
- Use CDN for static assets
- Monitor resource usage

### 3. Backup Strategy
```bash
# Database backup
docker-compose exec postgres pg_dump -U aggregator aggregator > backup.sql

# Restore database
docker-compose exec -T postgres psql -U aggregator aggregator < backup.sql
```

## Troubleshooting

### Common Issues

1. **Cron not running**
   ```bash
   # Check cron service
   docker-compose exec backend service cron status
   
   # Restart cron
   docker-compose exec backend service cron restart
   ```

2. **Database connection issues**
   ```bash
   # Check database connectivity
   docker-compose exec backend php artisan migrate:status
   ```

3. **Permission issues**
   ```bash
   # Fix permissions
   docker-compose exec backend chown -R www-data:www-data /var/www/html
   docker-compose exec backend chmod -R 755 /var/www/html/storage
   ```

### Debug Mode
```bash
# Enable debug mode
docker-compose exec backend php artisan config:clear
docker-compose exec backend php artisan config:cache

# Check configuration
docker-compose exec backend php artisan config:show
```

## Scaling

### Horizontal Scaling
- Use load balancer for multiple backend instances
- Separate database and Redis into dedicated servers
- Use container orchestration (Kubernetes, Docker Swarm)

### Vertical Scaling
- Increase container resources
- Optimize database queries
- Use Redis clustering

## Maintenance

### Regular Tasks
1. Monitor disk space usage
2. Check log file sizes
3. Update dependencies
4. Review security patches
5. Backup data regularly

### Updates
```bash
# Update application
git pull origin main
docker-compose build --no-cache
docker-compose up -d

# Update dependencies
docker-compose exec backend composer update
```

## API Endpoints

### News Management
- `GET /api/news` - List all news
- `GET /api/news/trending` - Trending news
- `GET /api/news/search?q=query` - Search news
- `GET /api/news/category/{slug}` - News by category
- `GET /api/news/source/{slug}` - News by source

### User Management
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `GET /api/user` - User profile
- `PUT /api/user` - Update profile

### Preferences
- `GET /api/preferences` - Get user preferences
- `PUT /api/preferences` - Update preferences
- `POST /api/preferences/sources` - Add preferred source
- `DELETE /api/preferences/sources` - Remove preferred source

## Support

For issues and questions:
1. Check the logs first
2. Review this documentation
3. Check the test suite: `./run_news_tests.sh`
4. Verify API endpoints with Postman collection
