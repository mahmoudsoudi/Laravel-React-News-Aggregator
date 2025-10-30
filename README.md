# 📰 News Aggregator System

A comprehensive news aggregation platform built with Laravel backend and Docker containerization, featuring automated news collection, user personalization, and real-time API endpoints.

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- Git

### One-Command Setup
```bash
git clone <repository-url>
cd aggregator
./start.sh
```

The system will be available at `http://localhost:8000`

## 🏗️ Architecture

### Backend Services
- **Laravel 11** - PHP framework with API endpoints
- **PostgreSQL** - Primary database
- **Redis** - Caching and session storage
- **Nginx** - Web server and reverse proxy

### Automated Features
- **News Aggregation** - Automated collection from multiple sources
- **Cron Jobs** - Scheduled tasks for maintenance
- **User Preferences** - Personalized news experience
- **API Testing** - Comprehensive test suite

## 📋 Features

### News Management
- ✅ Multi-source news aggregation (NewsAPI, Guardian, NYT, BBC, etc.)
- ✅ Real-time news collection via cron jobs
- ✅ News categorization and filtering
- ✅ Search functionality
- ✅ Trending news detection
- ✅ Pagination and performance optimization

### User Experience
- ✅ User registration and authentication
- ✅ Personalized news preferences
- ✅ Source and category preferences
- ✅ User profile management
- ✅ Notification settings

### API Features
- ✅ RESTful API design
- ✅ JWT token authentication
- ✅ Rate limiting and security
- ✅ Comprehensive error handling
- ✅ API documentation (Postman collection)

## 🛠️ Development

### Running Tests
```bash
# Run all tests
./run_news_tests.sh

# Run specific test suite
cd backend && ./vendor/bin/phpunit tests/Feature/Api/NewsControllerTest.php
```

### API Testing
Import `NEWS_AGGREGATOR_POSTMAN_COLLECTION.json` into Postman for comprehensive API testing.

### Database Management
```bash
# Run migrations
docker-compose exec backend php artisan migrate

# Seed data
docker-compose exec backend php artisan db:seed

# Reset database
docker-compose exec backend php artisan migrate:fresh --seed
```

## 🔧 Configuration

### Environment Variables
Create a `.env` file in the project root with your API keys:
```env
# News API Keys
NEWSAPI_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_api_key_here
NYTIMES_API_KEY=your_nytimes_api_key_here
```

**Get your free API keys:**
- **NewsAPI.org**: https://newsapi.org/register (1,000 requests/day)
- **The Guardian**: https://open-platform.theguardian.com/access/ (5,000 requests/day)
- **New York Times**: https://developer.nytimes.com/ (4,000 requests/day)

📖 **Detailed setup instructions**: See [ENVIRONMENT_SETUP.md](ENVIRONMENT_SETUP.md)

### Cron Jobs
The system includes automated cron jobs:
- **News Aggregation**: Every hour + every 30 minutes during business hours
- **News Cleanup**: Daily at 2 AM (removes articles older than 30 days)
- **News Digest**: Daily at 8 AM (generates user digests)
- **Health Check**: Every 5 minutes

## 📊 API Endpoints

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout

### News
- `GET /api/news` - List all news with filters
- `GET /api/news/trending` - Trending news
- `GET /api/news/search?q=query` - Search news
- `GET /api/news/category/{slug}` - News by category
- `GET /api/news/source/{slug}` - News by source
- `GET /api/news/{id}` - Single news article

### Sources & Categories
- `GET /api/sources` - List news sources
- `GET /api/sources/{slug}` - Single news source
- `GET /api/categories` - List categories
- `GET /api/categories/{slug}` - Single category

### User Preferences
- `GET /api/preferences` - Get user preferences
- `PUT /api/preferences` - Update preferences
- `POST /api/preferences/sources` - Add preferred source
- `DELETE /api/preferences/sources` - Remove preferred source
- `POST /api/preferences/categories` - Add preferred category
- `DELETE /api/preferences/categories` - Remove preferred category

## 🐳 Docker Setup

### Services
- **postgres**: PostgreSQL database
- **redis**: Redis cache
- **backend**: Laravel application
- **nginx**: Web server
- **scheduler**: Cron job runner

### Commands
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Rebuild containers
docker-compose up -d --build
```

## 📈 Monitoring

### Health Checks
- API health: `GET /api/test`
- Container status: `docker-compose ps`
- Cron logs: `docker-compose exec backend tail -f /var/log/news-aggregation.log`

### Performance
- Redis caching for improved performance
- Database indexing for fast queries
- Pagination for large datasets
- Rate limiting for API protection

## 🔒 Security

### Implemented Security Features
- JWT token authentication
- Rate limiting on API endpoints
- Input validation and sanitization
- SQL injection prevention
- XSS protection headers
- CORS configuration

### Best Practices
- Environment variable management
- Secure password hashing
- API key protection
- Regular security updates

## 📚 Documentation

- **API Documentation**: `NEWS_AGGREGATOR_POSTMAN_COLLECTION.json`
- **Docker Setup**: `DOCKER_CRON_SETUP.md`
- **Testing Guide**: `PHPUNIT_TESTING_GUIDE.md`
- **News Aggregator Guide**: `NEWS_AGGREGATOR_GUIDE.md`

## 🧪 Testing

### Test Coverage
- **57 tests** with **338 assertions**
- **100% API endpoint coverage**
- **Authentication and authorization tests**
- **Data validation tests**
- **Error handling tests**

### Running Tests
```bash
# Full test suite
./run_news_tests.sh

# Individual test files
cd backend && ./vendor/bin/phpunit tests/Feature/Api/NewsControllerTest.php
```

## 🚀 Deployment

### Production Checklist
- [ ] Set strong API keys
- [ ] Configure SSL/TLS
- [ ] Set up monitoring
- [ ] Configure backups
- [ ] Review security settings
- [ ] Test all endpoints
- [ ] Monitor performance

### Scaling
- Horizontal: Multiple backend instances with load balancer
- Vertical: Increase container resources
- Database: Separate PostgreSQL instance
- Cache: Redis clustering

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `./run_news_tests.sh`
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For issues and questions:
1. Check the logs: `docker-compose logs -f`
2. Review documentation
3. Run tests to verify functionality
4. Check API endpoints with Postman

## 🎯 Roadmap

- [ ] Frontend React application
- [ ] Real-time notifications
- [ ] Advanced analytics
- [ ] Machine learning recommendations
- [ ] Mobile app support
- [ ] Multi-language support

---

**Built with ❤️ using Laravel, Docker, and modern web technologies**