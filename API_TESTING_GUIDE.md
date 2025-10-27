# API Testing Guide

## 🧪 **Comprehensive Testing Suite for User Authentication APIs**

This guide covers all testing methods for the user authentication system APIs.

## 📋 **Available Test Scripts**

### 1. **Quick Test Script** (`quick_test.sh`)
- **Purpose**: Fast verification of basic API functionality
- **Duration**: ~30 seconds
- **Use Case**: Quick smoke test after changes

```bash
./quick_test.sh
```

**What it tests:**
- ✅ User registration
- ✅ Get user profile
- ✅ Update user profile
- ✅ Update password
- ✅ Login with new password
- ✅ Logout
- ✅ Access after logout

### 2. **Comprehensive Test Script** (`test_api_clean.sh`)
- **Purpose**: Complete test suite with all edge cases
- **Duration**: ~2 minutes
- **Use Case**: Full regression testing

```bash
./test_api_clean.sh
```

**What it tests:**
- ✅ User registration (15 tests total)
- ✅ User login
- ✅ Profile management
- ✅ Password updates
- ✅ Authentication flows
- ✅ Validation errors
- ✅ Security checks

### 3. **Postman Collection** (`API_TESTS.postman_collection.json`)
- **Purpose**: Interactive testing with detailed assertions
- **Duration**: ~5 minutes
- **Use Case**: Manual testing and debugging

**How to use:**
1. Import `API_TESTS.postman_collection.json` into Postman
2. Set environment variable `base_url` to `http://localhost:8000`
3. Run the collection

## 🔧 **Test Categories**

### **Authentication Tests**
| Test | Method | Endpoint | Description |
|------|--------|----------|-------------|
| Register Valid User | POST | `/api/register` | Successful registration |
| Register Duplicate Email | POST | `/api/register` | Reject duplicate email |
| Register Missing Fields | POST | `/api/register` | Validation errors |
| Register Password Mismatch | POST | `/api/register` | Password confirmation |
| Login Valid Credentials | POST | `/api/login` | Successful login |
| Login Invalid Credentials | POST | `/api/login` | Reject wrong password |
| Login Missing Fields | POST | `/api/login` | Validation errors |

### **Profile Management Tests**
| Test | Method | Endpoint | Description |
|------|--------|----------|-------------|
| Get Profile Valid Token | GET | `/api/user` | Successful profile retrieval |
| Get Profile Invalid Token | GET | `/api/user` | Reject invalid token |
| Get Profile No Token | GET | `/api/user` | Reject missing token |
| Update Profile Name | PUT | `/api/user` | Update name only |
| Update Profile Email | PUT | `/api/user` | Update email only |
| Update Profile Password | PUT | `/api/user` | Update password |
| Update Invalid Email | PUT | `/api/user` | Reject invalid email format |
| Update Password Mismatch | PUT | `/api/user` | Reject password mismatch |
| Update No Authorization | PUT | `/api/user` | Reject without token |

### **Session Management Tests**
| Test | Method | Endpoint | Description |
|------|--------|----------|-------------|
| Logout Valid Token | POST | `/api/logout` | Successful logout |
| Logout Invalid Token | POST | `/api/logout` | Reject invalid token |
| Access After Logout | GET | `/api/user` | Reject after logout |
| Delete Account | DELETE | `/api/user` | Account deletion |

## 🚀 **Running Tests**

### **Prerequisites**
```bash
# Start the application
docker compose up -d

# Wait for services to be ready
sleep 10

# Verify API is running
curl http://localhost:8000/test.php
```

### **Quick Test**
```bash
./quick_test.sh
```

### **Full Test Suite**
```bash
./test_api_clean.sh
```

### **Manual Testing with cURL**

#### **1. Register a User**
```bash
curl -X POST "http://localhost:8000/api/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### **2. Login**
```bash
curl -X POST "http://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

#### **3. Get Profile (replace TOKEN with actual token)**
```bash
curl -X GET "http://localhost:8000/api/user" \
  -H "Authorization: Bearer TOKEN"
```

#### **4. Update Profile**
```bash
curl -X PUT "http://localhost:8000/api/user" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "name": "Updated Name",
    "email": "updated@example.com"
  }'
```

#### **5. Update Password**
```bash
curl -X PUT "http://localhost:8000/api/user" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

#### **6. Logout**
```bash
curl -X POST "http://localhost:8000/api/logout" \
  -H "Authorization: Bearer TOKEN"
```

## 📊 **Test Results Interpretation**

### **Success Indicators**
- ✅ **Status Code 200**: Successful operations
- ✅ **Status Code 422**: Proper validation errors
- ✅ **Status Code 401**: Proper authentication errors
- ✅ **Status Code 404**: Proper not found errors

### **Response Format**
```json
{
  "success": true/false,
  "message": "Description",
  "data": {
    "user": {...},
    "token": "...",
    "token_type": "Bearer"
  },
  "errors": {
    "field": ["error message"]
  }
}
```

## 🐛 **Debugging Failed Tests**

### **Common Issues**

#### **1. API Not Running**
```bash
# Check if containers are running
docker compose ps

# Check logs
docker compose logs backend
docker compose logs nginx
```

#### **2. Database Connection Issues**
```bash
# Check database logs
docker compose logs db

# Test database connection
docker exec aggregator_db psql -U aggregator_user -d aggregator -c "SELECT 1;"
```

#### **3. Port Conflicts**
```bash
# Check if ports are in use
lsof -i :8000
lsof -i :3000
lsof -i :5433
```

#### **4. Token Issues**
- Ensure token is properly extracted from responses
- Check token format (should be 64 characters)
- Verify Authorization header format: `Bearer <token>`

### **Debug Commands**

#### **Check API Health**
```bash
curl -I http://localhost:8000/test.php
```

#### **Check Database State**
```bash
docker exec aggregator_db psql -U aggregator_user -d aggregator -c "SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5;"
```

#### **Check Token Table**
```bash
docker exec aggregator_db psql -U aggregator_user -d aggregator -c "SELECT id, tokenable_id, name, created_at FROM personal_access_tokens ORDER BY created_at DESC LIMIT 5;"
```

## 📈 **Performance Testing**

### **Load Testing with Apache Bench**
```bash
# Test registration endpoint
ab -n 100 -c 10 -H "Content-Type: application/json" -p register_data.json http://localhost:8000/api/register

# Test login endpoint
ab -n 100 -c 10 -H "Content-Type: application/json" -p login_data.json http://localhost:8000/api/login
```

### **Concurrent User Testing**
```bash
# Run multiple test instances
for i in {1..5}; do
  ./test_api_clean.sh &
done
wait
```

## 🔒 **Security Testing**

### **SQL Injection Tests**
```bash
curl -X POST "http://localhost:8000/api/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test",
    "email": "test@example.com\"; DROP TABLE users; --",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### **XSS Testing**
```bash
curl -X POST "http://localhost:8000/api/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "<script>alert(\"XSS\")</script>",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

## 📝 **Test Data Management**

### **Clean Test Data**
```bash
# Clear all test data
docker exec aggregator_db psql -U aggregator_user -d aggregator -c "DELETE FROM personal_access_tokens; DELETE FROM users;"
```

### **Test Data Seeding**
```bash
# Create test users
curl -X POST "http://localhost:8000/api/register" \
  -H "Content-Type: application/json" \
  -d '{"name": "Admin User", "email": "admin@test.com", "password": "admin123", "password_confirmation": "admin123"}'
```

## 🎯 **Best Practices**

### **Test Organization**
1. **Unit Tests**: Test individual functions
2. **Integration Tests**: Test API endpoints
3. **End-to-End Tests**: Test complete user flows
4. **Security Tests**: Test authentication and authorization

### **Test Data**
- Use unique identifiers (timestamps, UUIDs)
- Clean up test data after tests
- Use realistic but fake data
- Test edge cases and boundary conditions

### **Assertions**
- Verify response status codes
- Check response structure
- Validate data integrity
- Test error messages
- Verify security constraints

## 🚀 **Continuous Integration**

### **GitHub Actions Example**
```yaml
name: API Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Start services
        run: docker compose up -d
      - name: Wait for services
        run: sleep 30
      - name: Run tests
        run: ./test_api_clean.sh
```

This comprehensive testing suite ensures your user authentication APIs are robust, secure, and reliable! 🎉
