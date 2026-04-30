# UPDATE USER ENDPOINT - TROUBLESHOOTING GUIDE

## Issue
Update user endpoint mengembalikan response 200 OK dengan success: true, tapi data di response terlihat belum ter-update dengan nilai yang dikirimkan.

## Root Cause Analysis

### MASALAH YANG DITEMUKAN:
**PHP/Laravel tidak parse PUT request dengan multipart/form-data** ⚠️

Ketika menggunakan `curl -X PUT ... -F (form-data)`, Laravel tidak bisa membaca form data dari request body untuk PUT method. Ini adalah limitation di PHP dan web server (Apache/Nginx).

**Status**:
- ✅ **POST method** - Data ter-parse dengan BENAR dan ter-update
- ❌ **PUT method** - Form data tidak ter-parse (Laravel menerima array kosong)
- ❌ **PATCH method** - Form data tidak ter-parse (sama seperti PUT)

### Logs yang menunjukkan issue:
```
[UserController] Update request - DETAILED: {"user_id":"2","validated_data":[],"all_input":[],...}
[UserService] updateUser START: {"user_id":"2","dto_array":[],...}
[BaseRepository] update() called: {"model":"User","id":"2","data_to_update":[],...}
```

## Solution Applied

### ✅ TESTED & CONFIRMED WORKING:

**Use POST method for updates dengan file/form-data:**

```bash
curl -X POST \
  http://localhost:8000/api/auth/user/2 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=New Name" \
  -F "email=new@email.com"
```

**Response** (dengan POST):
```json
{
  "success": true,
  "message": "User updated successfully",
  "data": {
    "id": 2,
    "name": "New Name",          ✅ Updated!
    "email": "new@email.com",     ✅ Updated!
    "updated_at": "2026-04-28T10:14:43.000000Z"  ✅ Timestamp changed!
  }
}
```

## Cara Test yang Benar

### ⭐ RECOMMENDED: Using POST Method (WORKS!)

**cURL Command** (TESTED & WORKING):
```bash
curl -X POST \
  http://localhost:8000/api/auth/user/2 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "name=Administrator Updated" \
  -F "email=admin_updated@test.com" \
  -F "username=admin_updated"
```

**PowerShell** (TESTED & WORKING):
```powershell
$TOKEN = "YOUR_TOKEN_HERE"
$USER_ID = "2"
$BASE_URL = "http://localhost:8000"

$headers = @{
    "Authorization" = "Bearer $TOKEN"
    "Accept" = "application/json"
}

$form = @{
    "name" = "Administrator Updated"
    "email" = "admin_updated@test.com"
    "username" = "admin_updated"
}

$response = Invoke-WebRequest -Uri "$BASE_URL/api/auth/user/$USER_ID" `
    -Method POST `
    -Headers $headers `
    -Form $form

$response.Content | ConvertFrom-Json | ConvertTo-Json
```

### ❌ NOT RECOMMENDED: Using PUT/PATCH (Form Data Not Parsed)

**Why PUT/PATCH doesn't work with multipart:**
- PHP limitation: `$_POST` is empty for PUT/PATCH requests with multipart/form-data
- Even though we added support for PUT/PATCH in routes, the form data isn't accessible
- Solution: Always use POST for multipart/form-data updates

---

## Old Testing Methods (Not Recommended - Use POST Instead)

### Option 1: Using cURL with PUT (❌ NOT WORKING)

```bash
# Test 1: Update tanpa file
curl -X PUT \
  http://localhost:8000/api/auth/user/2 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "name=Administrator Updated" \
  -F "email=admin_updated@test.com" \
  -F "username=admin_updated" \
  -v

# Test 2: Update dengan file
curl -X PUT \
  http://localhost:8000/api/auth/user/2 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "name=Administrator With Photo" \
  -F "email=admin_photo@test.com" \
  -F "username=admin_photo" \
  -F "profile_photo=@/path/to/image.jpg" \
  -v
```

### Option 2: Using PowerShell

```powershell
# Run test_update_user_curl.ps1
.\test_update_user_curl.ps1
```

### Option 3: Using Postman/Insomnia (USE POST METHOD)

1. **Method**: POST ⭐ (NOT PUT)
2. **URL**: `http://localhost:8000/api/auth/user/{user_id}`
3. **Headers**:
   - `Authorization: Bearer YOUR_TOKEN`
   - `Accept: application/json`
4. **Body**: Select `form-data` (NOT raw JSON)
5. **Form fields**:
   - name: `text` type
   - email: `text` type
   - username: `text` type
   - password: `text` type (optional)
   - role: `text` type (optional)
   - profile_photo: `file` type (optional)

### Option 4: Using Swagger UI

⚠️ **NOTE**: Swagger UI might be showing PUT by default. The endpoint now accepts **POST, PUT, and PATCH**, but **only POST works correctly with multipart/form-data**.

**Recommendation**:
1. For text-only updates: PUT/PATCH works if you use JSON body (not form-data)
2. For updates with file upload: Use POST method
3. In Swagger: The first "Try it out" will use the first method (PUT). If it fails, this is expected. Use Postman/cURL with POST instead.

## Debugging

### Check Logs for Issues
```bash
# View recent logs
tail -f storage/logs/laravel.log

# Filter for update-related logs
grep -i "UpdateUserRequest\|UserController.*DETAILED\|No profile photo provided" storage/logs/laravel.log
```

### What to Look For in Logs:

**Good logs** (data being processed):
```
[UpdateUserRequest] Raw input received {"all":{"name":"Updated","email":"test@example.com",...},...}
[UserController] Update request - DETAILED {"user_id":2,"validated_data":{"name":"Updated",...},...}
[UserService] Update user error...  (if succeeds, won't show error)
```

**Bad logs** (empty fields):
```
[UserController] Update request - DETAILED {"user_id":"2","validated_data":[],"fields":[],...}
[UserService] No profile photo provided
```

## Files Modified

1. `app/Http/Requests/UpdateUserRequest.php`
   - Added `prepareForValidation()` to merge file input
   - Enhanced logging

2. `app/Http/Controllers/UserController.php`
   - Added fallback logic using `$request->all()`
   - Enhanced logging for debugging
   - Reload user from database after update

3. `routes/api/auth.php`
   - Route now accepts PUT, PATCH, and POST methods

4. `storage/api-docs/openapi.yaml`
   - Updated documentation to reflect PUT/PATCH/POST methods

## Verification Steps

1. **Make an update request using POST** with test data (see examples above)
2. **Check response** - should show `success: true` with updated data values
3. **Verify data changed** in response - compare sent values vs response values
4. **Check database** - query user to confirm:
   ```php
   php artisan tinker
   > $user = User::find(2)
   > $user->name  // should show your updated value
   > $user->email // should show your updated value  
   > $user->updated_at // should show recent timestamp
   ```

## Recommended Approach Going Forward

### For API Clients Using This Endpoint:

1. **Text-only updates** (no file upload):
   - Use: `PUT` with `Content-Type: application/json` (send JSON body)
   - Or: Use `POST` with form-data

2. **Updates with file/photo upload**:
   - **MUST use: `POST`** with `multipart/form-data`
   - PUT/PATCH with multipart doesn't work due to PHP limitation

3. **Best Practice**:
   - Send all form data (including files) as `POST`
   - This is compatible with all frameworks and works everywhere

### Example: Correct POST Request Format

```bash
curl -X POST http://localhost:8000/api/auth/user/2 \
  -H "Authorization: Bearer TOKEN" \
  -F "name=New Name" \
  -F "email=new@email.com" \
  -F "profile_photo=@/path/to/file.jpg"
```

Response shows updated values immediately ✅
