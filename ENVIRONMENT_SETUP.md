# Environment Variables Setup

This document explains how to set up the required environment variables for the News Aggregator application.

## Required News API Keys

The application uses three main news APIs that require API keys:

### 1. NewsAPI.org
- **Purpose**: Primary news source for multiple providers (BBC, OpenNews, NewsCred)
- **Get API Key**: https://newsapi.org/register
- **Free Tier**: 1,000 requests per day
- **Environment Variable**: `NEWSAPI_KEY`

### 2. The Guardian API
- **Purpose**: Direct access to The Guardian's news content
- **Get API Key**: https://open-platform.theguardian.com/access/
- **Free Tier**: 5,000 requests per day
- **Environment Variable**: `GUARDIAN_API_KEY`

### 3. New York Times API
- **Purpose**: Direct access to NYT's news content
- **Get API Key**: https://developer.nytimes.com/
- **Free Tier**: 4,000 requests per day
- **Environment Variable**: `NYTIMES_API_KEY`

## Setup Instructions

### Option 1: Using Docker Compose (Recommended)

1. Create a `.env` file in the project root:
```bash
touch .env
```

2. Add the following content to your `.env` file:
```env
# News API Keys
NEWSAPI_KEY=your_actual_newsapi_key_here
GUARDIAN_API_KEY=your_actual_guardian_key_here
NYTIMES_API_KEY=your_actual_nytimes_key_here
```

3. Start the application:
```bash
docker-compose up -d
```

### Option 2: Local Development

1. Create a `.env` file in the `backend/` directory:
```bash
touch backend/.env
```

2. Copy the Laravel configuration and add news API keys:
```env
APP_NAME="News Aggregator"
APP_ENV=local
APP_KEY=base64:your_app_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=aggregator
DB_USERNAME=aggregator
DB_PASSWORD=aggregator_password

# Redis
REDIS_HOST=localhost
REDIS_PORT=6379

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# News API Keys
NEWSAPI_KEY=your_actual_newsapi_key_here
GUARDIAN_API_KEY=your_actual_guardian_key_here
NYTIMES_API_KEY=your_actual_nytimes_key_here
```

## API Key Registration

### NewsAPI.org
1. Visit https://newsapi.org/register
2. Fill in your details
3. Verify your email
4. Copy your API key from the dashboard

### The Guardian API
1. Visit https://open-platform.theguardian.com/access/
2. Click "Get API Key"
3. Fill in the form with your details
4. Copy your API key from the confirmation email

### New York Times API
1. Visit https://developer.nytimes.com/
2. Click "Get API Key"
3. Create an account and fill in the form
4. Copy your API key from the dashboard

## Testing API Keys

After setting up your API keys, you can test them using the provided test scripts:

```bash
# Test all APIs
./test_api.sh

# Test specific API
curl "https://newsapi.org/v2/everything?q=technology&apiKey=YOUR_NEWSAPI_KEY"
```

## Troubleshooting

### Common Issues

1. **API Key Not Working**: Ensure you've copied the key correctly without extra spaces
2. **Rate Limit Exceeded**: Check your API usage in the respective dashboards
3. **CORS Issues**: Make sure you're running the application through the proper Docker setup

### Verification

Check the logs to ensure API keys are working:
```bash
docker-compose logs backend
docker-compose logs scheduler
```

Look for successful news fetching messages in the logs.

## Security Notes

- Never commit your `.env` file to version control
- Use different API keys for development and production
- Monitor your API usage to avoid exceeding free tier limits
- Consider using environment-specific configuration for production deployments
