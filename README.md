# Aggregator - User Authentication System

A full-stack user registration and login system built with Laravel (backend) and React (frontend), fully dockerized for easy deployment.

## 🚀 Features

### Backend (Laravel)
- **User Registration** - Create new user accounts with validation
- **User Login** - Secure authentication with JWT tokens
- **User Profile Management** - View, update, and delete user profiles
- **API Authentication** - Laravel Sanctum for secure API access
- **Database Integration** - PostgreSQL with migrations
- **Caching** - Redis for improved performance

### Frontend (React)
- **Modern UI** - Beautiful, responsive design
- **Authentication Pages** - Login and registration forms
- **Protected Routes** - Secure dashboard access
- **Profile Management** - Edit user information
- **State Management** - React Context for authentication state
- **TypeScript** - Type-safe development

### DevOps
- **Dockerized** - Complete containerization
- **Docker Compose** - Multi-service orchestration
- **Nginx** - Reverse proxy and static file serving
- **Database** - PostgreSQL with persistent storage
- **Caching** - Redis for session and cache storage

## 🛠️ Tech Stack

### Backend
- **Laravel 12** - PHP framework
- **Laravel Sanctum** - API authentication
- **PostgreSQL** - Database
- **Redis** - Caching and sessions
- **PHP 8.2** - Runtime

### Frontend
- **React 18** - UI library
- **TypeScript** - Type safety
- **Axios** - HTTP client
- **React Router** - Client-side routing
- **CSS3** - Styling

### Infrastructure
- **Docker** - Containerization
- **Docker Compose** - Orchestration
- **Nginx** - Web server
- **PostgreSQL** - Database
- **Redis** - Cache

## 📋 Prerequisites

- Docker and Docker Compose
- Git

## 🚀 Quick Start

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd aggregator
   ```

2. **Run the setup script**
   ```bash
   ./setup.sh
   ```

3. **Access the application**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8000/api
   - Database: localhost:5433 (PostgreSQL)

## 🔧 Manual Setup

### Backend Setup

1. **Navigate to backend directory**
   ```bash
   cd backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run migrations**
   ```bash
   php artisan migrate
   ```

5. **Start the server**
   ```bash
   php artisan serve
   ```

### Frontend Setup

1. **Navigate to frontend directory**
   ```bash
   cd frontend
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Start the development server**
   ```bash
   npm start
   ```

## 🐳 Docker Commands

### Start all services
```bash
# For newer Docker versions
docker compose up -d

# For older Docker versions
docker-compose up -d
```

### Stop all services
```bash
# For newer Docker versions
docker compose down

# For older Docker versions
docker-compose down
```

### View logs
```bash
# For newer Docker versions
docker compose logs -f

# For older Docker versions
docker-compose logs -f
```

### Access container shells
```bash
# Backend
docker compose exec backend bash
# or
docker-compose exec backend bash

# Frontend
docker compose exec frontend sh
# or
docker-compose exec frontend sh

# Database
docker compose exec db psql -U aggregator_user -d aggregator
# or
docker-compose exec db psql -U aggregator_user -d aggregator
```

### Rebuild containers
```bash
# For newer Docker versions
docker compose build --no-cache
docker compose up -d

# For older Docker versions
docker-compose build --no-cache
docker-compose up -d
```

## 📚 API Documentation

### Authentication Endpoints

#### Register User
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Login User
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Logout User
```http
POST /api/logout
Authorization: Bearer {token}
```

### User Management Endpoints

#### Get User Profile
```http
GET /api/user
Authorization: Bearer {token}
```

#### Update User Profile
```http
PUT /api/user
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Smith",
  "email": "johnsmith@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

#### Delete User Account
```http
DELETE /api/user
Authorization: Bearer {token}
```

## 🎨 Frontend Pages

### Authentication
- **Login Page** (`/login`) - User sign-in
- **Register Page** (`/register`) - User registration

### Dashboard
- **Dashboard** (`/dashboard`) - User profile management
  - View profile information
  - Edit profile details
  - Change password
  - Delete account

## 🔒 Security Features

- **Password Hashing** - Bcrypt encryption
- **JWT Tokens** - Secure API authentication
- **CORS Protection** - Cross-origin request security
- **Input Validation** - Server-side validation
- **SQL Injection Protection** - Eloquent ORM
- **XSS Protection** - Content Security Policy headers

## 📁 Project Structure

```
aggregator/
├── backend/                 # Laravel backend
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   └── Models/
│   ├── database/migrations/
│   ├── routes/api.php
│   ├── Dockerfile
│   └── .env.docker
├── frontend/               # React frontend
│   ├── src/
│   │   ├── components/
│   │   ├── contexts/
│   │   ├── pages/
│   │   └── services/
│   ├── Dockerfile
│   └── nginx.conf
├── nginx/                  # Nginx configuration
│   └── nginx.conf
├── docker-compose.yml      # Docker orchestration
├── setup.sh               # Setup script
└── README.md              # This file
```

## 🚀 Deployment

### Production Deployment

1. **Update environment variables**
   ```bash
   # Update .env files with production values
   # Set APP_ENV=production
   # Configure production database
   # Set secure APP_KEY
   ```

2. **Build production images**
   ```bash
   docker-compose -f docker-compose.prod.yml build
   ```

3. **Deploy to production**
   ```bash
   docker-compose -f docker-compose.prod.yml up -d
   ```

## 🧪 Testing

### Backend Tests
```bash
cd backend
php artisan test
```

### Frontend Tests
```bash
cd frontend
npm test
```

## 📝 Environment Variables

### Backend (.env)
```env
APP_NAME=Aggregator
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=aggregator
DB_USERNAME=aggregator_user
DB_PASSWORD=aggregator_password

CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

### Frontend (.env)
```env
REACT_APP_API_URL=http://localhost:8000/api
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

If you encounter any issues or have questions:

1. Check the logs: `docker-compose logs -f`
2. Verify environment configuration
3. Ensure all services are running: `docker-compose ps`
4. Check database connectivity

## 🔄 Updates

To update the application:

1. Pull latest changes
2. Rebuild containers: `docker-compose build --no-cache`
3. Restart services: `docker-compose up -d`
4. Run migrations if needed: `docker-compose exec backend php artisan migrate`
