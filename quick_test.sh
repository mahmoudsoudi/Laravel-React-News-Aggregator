#!/bin/bash

# Quick API Test Script
# Simple curl-based tests for immediate verification

BASE_URL="http://localhost:8000/api"

echo "🚀 Quick API Test - User Authentication System"
echo "=============================================="

# Test 1: Register a user
echo "1. Testing user registration..."
REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Quick Test User",
    "email": "quicktest@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }')

echo "Register Response: $REGISTER_RESPONSE"
echo ""

# Extract token for further tests
TOKEN=$(echo "$REGISTER_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

if [ -n "$TOKEN" ]; then
    echo "✅ Registration successful! Token: ${TOKEN:0:20}..."
    echo ""
    
    # Test 2: Get user profile
    echo "2. Testing get user profile..."
    PROFILE_RESPONSE=$(curl -s -X GET "$BASE_URL/user" \
      -H "Authorization: Bearer $TOKEN")
    
    echo "Profile Response: $PROFILE_RESPONSE"
    echo ""
    
    # Test 3: Update user profile
    echo "3. Testing update user profile..."
    UPDATE_RESPONSE=$(curl -s -X PUT "$BASE_URL/user" \
      -H "Content-Type: application/json" \
      -H "Authorization: Bearer $TOKEN" \
      -d '{
        "name": "Updated Quick Test User",
        "email": "updatedquick@example.com"
      }')
    
    echo "Update Response: $UPDATE_RESPONSE"
    echo ""
    
    # Test 4: Update password
    echo "4. Testing update password..."
    PASSWORD_RESPONSE=$(curl -s -X PUT "$BASE_URL/user" \
      -H "Content-Type: application/json" \
      -H "Authorization: Bearer $TOKEN" \
      -d '{
        "password": "newpassword123",
        "password_confirmation": "newpassword123"
      }')
    
    echo "Password Update Response: $PASSWORD_RESPONSE"
    echo ""
    
    # Test 5: Login with new password
    echo "5. Testing login with new password..."
    LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
      -H "Content-Type: application/json" \
      -d '{
        "email": "updatedquick@example.com",
        "password": "newpassword123"
      }')
    
    echo "Login Response: $LOGIN_RESPONSE"
    echo ""
    
    # Extract new token
    NEW_TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    
    if [ -n "$NEW_TOKEN" ]; then
        echo "✅ Login with new password successful! New Token: ${NEW_TOKEN:0:20}..."
        echo ""
        
        # Test 6: Logout
        echo "6. Testing logout..."
        LOGOUT_RESPONSE=$(curl -s -X POST "$BASE_URL/logout" \
          -H "Authorization: Bearer $NEW_TOKEN")
        
        echo "Logout Response: $LOGOUT_RESPONSE"
        echo ""
        
        # Test 7: Try to access profile after logout
        echo "7. Testing access after logout (should fail)..."
        ACCESS_AFTER_LOGOUT=$(curl -s -X GET "$BASE_URL/user" \
          -H "Authorization: Bearer $NEW_TOKEN")
        
        echo "Access After Logout Response: $ACCESS_AFTER_LOGOUT"
        echo ""
        
        echo "🎉 All quick tests completed!"
    else
        echo "❌ Login with new password failed"
    fi
else
    echo "❌ Registration failed"
fi

echo ""
echo "=============================================="
echo "Quick test completed!"
