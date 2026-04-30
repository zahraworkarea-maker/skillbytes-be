#!/bin/bash

# Test Update User dengan curl
# File: test_update_user_curl.sh

TOKEN="your_token_here"
USER_ID="2"
BASE_URL="http://localhost:8000"

echo "=== Testing Update User Endpoint ==="
echo ""

# Test 1: Update tanpa file (hanya text fields)
echo "Test 1: Update user tanpa file upload"
curl -X PUT \
  "${BASE_URL}/api/auth/user/${USER_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" \
  -F "name=Administrator Updated" \
  -F "email=admin_updated@test.com" \
  -F "username=admin_updated" \
  -v

echo -e "\n\n"

# Test 2: Update dengan file upload
echo "Test 2: Update user dengan file upload"
curl -X PUT \
  "${BASE_URL}/api/auth/user/${USER_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" \
  -F "name=Administrator With Photo" \
  -F "email=admin_photo@test.com" \
  -F "username=admin_photo" \
  -F "profile_photo=@/path/to/image.jpg" \
  -v

echo -e "\n\n"

# Test 3: Update hanya dengan file (test upload saja)
echo "Test 3: Update hanya profile photo"
curl -X PUT \
  "${BASE_URL}/api/auth/user/${USER_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" \
  -F "profile_photo=@/path/to/image.jpg" \
  -v

echo -e "\n\nTests complete!"
