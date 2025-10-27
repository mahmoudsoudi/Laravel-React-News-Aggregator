#!/bin/bash

# Clean API Testing Script for User Authentication System
# This script tests all user-related APIs with clean data

BASE_URL="http://localhost:8000/api"
AUTH_TOKEN=""
USER_ID=""
TEST_EMAIL=""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    local status=$1
    local message=$2
    if [ "$status" = "SUCCESS" ]; then
        echo -e "${GREEN}✅ $message${NC}"
    elif [ "$status" = "ERROR" ]; then
        echo -e "${RED}❌ $message${NC}"
    elif [ "$status" = "INFO" ]; then
        echo -e "${BLUE}ℹ️  $message${NC}"
    elif [ "$status" = "WARNING" ]; then
        echo -e "${YELLOW}⚠️  $message${NC}"
    fi
}

# Function to make HTTP requests
make_request() {
    local method=$1
    local endpoint=$2
    local data=$3
    local headers=$4
    
    if [ -n "$data" ]; then
        if [ -n "$headers" ]; then
            curl -s -X $method "$BASE_URL$endpoint" \
                -H "Content-Type: application/json" \
                -H "$headers" \
                -d "$data"
        else
            curl -s -X $method "$BASE_URL$endpoint" \
                -H "Content-Type: application/json" \
                -d "$data"
        fi
    else
        if [ -n "$headers" ]; then
            curl -s -X $method "$BASE_URL$endpoint" \
                -H "$headers"
        else
            curl -s -X $method "$BASE_URL$endpoint"
        fi
    fi
}

# Function to check if API is running
check_api_status() {
    print_status "INFO" "Checking if API is running..."
    
    response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/../test.php")
    
    if [ "$response" = "200" ]; then
        print_status "SUCCESS" "API is running"
        return 0
    else
        print_status "ERROR" "API is not running. Please start the containers with: docker compose up -d"
        return 1
    fi
}

# Test 1: User Registration with unique email
test_register() {
    print_status "INFO" "Testing user registration..."
    
    # Use timestamp to ensure unique email
    local timestamp=$(date +%s)
    local test_data="{
        \"name\": \"Test User $timestamp\",
        \"email\": \"test$timestamp@example.com\",
        \"password\": \"password123\",
        \"password_confirmation\": \"password123\"
    }"
    
    response=$(make_request "POST" "/register" "$test_data")
    
    if echo "$response" | grep -q '"success":true'; then
        print_status "SUCCESS" "User registration successful"
        AUTH_TOKEN=$(echo "$response" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
        USER_ID=$(echo "$response" | grep -o '"id":[0-9]*' | cut -d':' -f2)
        TEST_EMAIL="test$timestamp@example.com"
        print_status "INFO" "Auth token: ${AUTH_TOKEN:0:20}..."
        print_status "INFO" "User ID: $USER_ID"
        print_status "INFO" "Test email: $TEST_EMAIL"
        return 0
    else
        print_status "ERROR" "User registration failed"
        echo "Response: $response"
        return 1
    fi
}

# Test 2: User Login
test_login() {
    print_status "INFO" "Testing user login..."
    
    local timestamp=$(date +%s)
    local test_data="{
        \"email\": \"test$timestamp@example.com\",
        \"password\": \"password123\"
    }"
    
    response=$(make_request "POST" "/login" "$test_data")
    
    if echo "$response" | grep -q '"success":true'; then
        print_status "SUCCESS" "User login successful"
        AUTH_TOKEN=$(echo "$response" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
        print_status "INFO" "Auth token: ${AUTH_TOKEN:0:20}..."
        return 0
    else
        print_status "ERROR" "User login failed"
        echo "Response: $response"
        return 1
    fi
}

# Test 3: Get User Profile
test_get_profile() {
    print_status "INFO" "Testing get user profile..."
    
    if [ -z "$AUTH_TOKEN" ]; then
        print_status "ERROR" "No auth token available"
        return 1
    fi
    
    response=$(make_request "GET" "/user" "" "Authorization: Bearer $AUTH_TOKEN")
    
    if echo "$response" | grep -q '"success":true'; then
        print_status "SUCCESS" "Get user profile successful"
        return 0
    else
        print_status "ERROR" "Get user profile failed"
        echo "Response: $response"
        return 1
    fi
}

# Test 4: Update User Profile
test_update_profile() {
    print_status "INFO" "Testing update user profile..."
    
    if [ -z "$AUTH_TOKEN" ]; then
        print_status "ERROR" "No auth token available"
        return 1
    fi
    
    local timestamp=$(date +%s)
    local test_data="{
        \"name\": \"Updated Test User $timestamp\",
        \"email\": \"updated$timestamp@example.com\"
    }"
    
    response=$(make_request "PUT" "/user" "$test_data" "Authorization: Bearer $AUTH_TOKEN")
    
    if echo "$response" | grep -q '"success":true'; then
        print_status "SUCCESS" "Update user profile successful"
        return 0
    else
        print_status "ERROR" "Update user profile failed"
        echo "Response: $response"
        return 1
    fi
}

# Test 5: Update Password
test_update_password() {
    print_status "INFO" "Testing update password..."
    
    if [ -z "$AUTH_TOKEN" ]; then
        print_status "ERROR" "No auth token available"
        return 1
    fi
    
    local test_data='{
        "password": "newpassword123",
        "password_confirmation": "newpassword123"
    }'
    
    response=$(make_request "PUT" "/user" "$test_data" "Authorization: Bearer $AUTH_TOKEN")
    
    if echo "$response" | grep -q '"success":true'; then
        print_status "SUCCESS" "Update password successful"
        return 0
    else
        print_status "ERROR" "Update password failed"
        echo "Response: $response"
        return 1
    fi
}

# Test 6: Test Login with New Password
test_login_new_password() {
    print_status "INFO" "Testing login with new password..."
    
    local timestamp=$(date +%s)
    local test_data="{
        \"email\": \"updated$timestamp@example.com\",
        \"password\": \"newpassword123\"
    }"
    
    response=$(make_request "POST" "/login" "$test_data")
    
    if echo "$response" | grep -q '"success":true'; then
        print_status "SUCCESS" "Login with new password successful"
        AUTH_TOKEN=$(echo "$response" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
        return 0
    else
        print_status "ERROR" "Login with new password failed"
        echo "Response: $response"
        return 1
    fi
}

# Test 7: User Logout
test_logout() {
    print_status "INFO" "Testing user logout..."
    
    if [ -z "$AUTH_TOKEN" ]; then
        print_status "ERROR" "No auth token available"
        return 1
    fi
    
    response=$(make_request "POST" "/logout" "" "Authorization: Bearer $AUTH_TOKEN")
    
    if echo "$response" | grep -q '"success":true'; then
        print_status "SUCCESS" "User logout successful"
        AUTH_TOKEN=""  # Clear token after logout
        return 0
    else
        print_status "ERROR" "User logout failed"
        echo "Response: $response"
        return 1
    fi
}

# Test 8: Test Access After Logout
test_access_after_logout() {
    print_status "INFO" "Testing access after logout..."
    
    response=$(make_request "GET" "/user" "" "Authorization: Bearer $AUTH_TOKEN")
    
    if echo "$response" | grep -q '"success":false'; then
        print_status "SUCCESS" "Access properly denied after logout"
        return 0
    else
        print_status "ERROR" "Access should be denied after logout"
        echo "Response: $response"
        return 1
    fi
}

# Test 9: Registration with Duplicate Email
test_duplicate_registration() {
    print_status "INFO" "Testing registration with duplicate email..."
    
    # Use a static email that we know exists
    local test_data='{
        "name": "Another User",
        "email": "duplicate@example.com",
        "password": "password123",
        "password_confirmation": "password123"
    }'
    
    response=$(make_request "POST" "/register" "$test_data")
    
    if echo "$response" | grep -q '"success":false'; then
        print_status "SUCCESS" "Duplicate email registration properly rejected"
        return 0
    else
        print_status "ERROR" "Duplicate email registration should be rejected"
        echo "Response: $response"
        return 1
    fi
}

# Test 10: Invalid Login Credentials
test_invalid_login() {
    print_status "INFO" "Testing invalid login credentials..."
    
    local timestamp=$(date +%s)
    local test_data="{
        \"email\": \"test$timestamp@example.com\",
        \"password\": \"wrongpassword\"
    }"
    
    response=$(make_request "POST" "/login" "$test_data")
    
    if echo "$response" | grep -q '"success":false'; then
        print_status "SUCCESS" "Invalid login properly rejected"
        return 0
    else
        print_status "ERROR" "Invalid login should be rejected"
        echo "Response: $response"
        return 1
    fi
}

# Test 11: Registration with Missing Fields
test_registration_missing_fields() {
    print_status "INFO" "Testing registration with missing fields..."
    
    local test_data='{
        "name": "",
        "email": "",
        "password": "",
        "password_confirmation": ""
    }'
    
    response=$(make_request "POST" "/register" "$test_data")
    
    if echo "$response" | grep -q '"success":false'; then
        print_status "SUCCESS" "Registration with missing fields properly rejected"
        return 0
    else
        print_status "ERROR" "Registration with missing fields should be rejected"
        echo "Response: $response"
        return 1
    fi
}

# Test 12: Registration with Password Mismatch
test_registration_password_mismatch() {
    print_status "INFO" "Testing registration with password mismatch..."
    
    local timestamp=$(date +%s)
    local test_data="{
        \"name\": \"Test User\",
        \"email\": \"mismatch$timestamp@example.com\",
        \"password\": \"password123\",
        \"password_confirmation\": \"differentpassword\"
    }"
    
    response=$(make_request "POST" "/register" "$test_data")
    
    if echo "$response" | grep -q '"success":false'; then
        print_status "SUCCESS" "Registration with password mismatch properly rejected"
        return 0
    else
        print_status "ERROR" "Registration with password mismatch should be rejected"
        echo "Response: $response"
        return 1
    fi
}

# Test 13: Update Profile with Invalid Email
test_update_invalid_email() {
    print_status "INFO" "Testing update profile with invalid email..."
    
    # First register a user
    local timestamp=$(date +%s)
    local register_data="{
        \"name\": \"Test User\",
        \"email\": \"valid$timestamp@example.com\",
        \"password\": \"password123\",
        \"password_confirmation\": \"password123\"
    }"
    
    register_response=$(make_request "POST" "/register" "$register_data")
    if echo "$register_response" | grep -q '"success":true'; then
        AUTH_TOKEN=$(echo "$register_response" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
        
        # Now test invalid email update
        local test_data='{
            "email": "invalid-email-format"
        }'
        
        response=$(make_request "PUT" "/user" "$test_data" "Authorization: Bearer $AUTH_TOKEN")
        
        if echo "$response" | grep -q '"success":false'; then
            print_status "SUCCESS" "Update with invalid email properly rejected"
            return 0
        else
            print_status "ERROR" "Update with invalid email should be rejected"
            echo "Response: $response"
            return 1
        fi
    else
        print_status "ERROR" "Failed to register user for invalid email test"
        return 1
    fi
}

# Test 14: Update Profile with Password Mismatch
test_update_password_mismatch() {
    print_status "INFO" "Testing update profile with password mismatch..."
    
    if [ -z "$AUTH_TOKEN" ]; then
        print_status "ERROR" "No auth token available"
        return 1
    fi
    
    local test_data='{
        "password": "newpassword123",
        "password_confirmation": "differentpassword"
    }'
    
    response=$(make_request "PUT" "/user" "$test_data" "Authorization: Bearer $AUTH_TOKEN")
    
    if echo "$response" | grep -q '"success":false'; then
        print_status "SUCCESS" "Update with password mismatch properly rejected"
        return 0
    else
        print_status "ERROR" "Update with password mismatch should be rejected"
        echo "Response: $response"
        return 1
    fi
}

# Test 15: Access Profile Without Token
test_access_without_token() {
    print_status "INFO" "Testing access profile without token..."
    
    response=$(make_request "GET" "/user" "")
    
    if echo "$response" | grep -q '"success":false'; then
        print_status "SUCCESS" "Access without token properly rejected"
        return 0
    else
        print_status "ERROR" "Access without token should be rejected"
        echo "Response: $response"
        return 1
    fi
}

# Main test execution
main() {
    echo -e "${BLUE}🚀 Starting Clean API Tests for User Authentication System${NC}"
    echo "============================================================="
    
    local total_tests=0
    local passed_tests=0
    
    # Check if API is running
    if ! check_api_status; then
        exit 1
    fi
    
    echo ""
    
    # Run tests
    tests=(
        "test_register"
        "test_login"
        "test_get_profile"
        "test_update_profile"
        "test_update_password"
        "test_login_new_password"
        "test_logout"
        "test_access_after_logout"
        "test_duplicate_registration"
        "test_invalid_login"
        "test_registration_missing_fields"
        "test_registration_password_mismatch"
        "test_update_invalid_email"
        "test_update_password_mismatch"
        "test_access_without_token"
    )
    
    for test in "${tests[@]}"; do
        total_tests=$((total_tests + 1))
        if $test; then
            passed_tests=$((passed_tests + 1))
        fi
        echo ""
    done
    
    # Summary
    echo "============================================================="
    echo -e "${BLUE}📊 Test Summary${NC}"
    echo "Total tests: $total_tests"
    echo -e "Passed: ${GREEN}$passed_tests${NC}"
    echo -e "Failed: ${RED}$((total_tests - passed_tests))${NC}"
    
    if [ $passed_tests -eq $total_tests ]; then
        print_status "SUCCESS" "All tests passed! 🎉"
        exit 0
    else
        print_status "ERROR" "Some tests failed. Please check the output above."
        exit 1
    fi
}

# Run main function
main "$@"
