# Role-Based Authorization Implementation Summary

## Overview
Added comprehensive role-based access control (RBAC) to all API endpoints with proper bearer token verification and role-specific authorization checks.

## Roles Definition
- **admin**: Full access to all resources and operations
- **guru** (Teacher): Can view users, manage own profile and password
- **siswa** (Student): Can view own profile, manage own password

## Endpoint Authorization Matrix

### Authentication Endpoints
| Endpoint | Method | Auth Required | Allowed Roles | Description |
|----------|--------|---------------|---------------|-------------|
| `/auth/user/login` | POST | No | Public | Login to get bearer token |

### User Management Endpoints
| Endpoint | Method | Auth Required | Allowed Roles | Description |
|----------|--------|---------------|---------------|-------------|
| `/auth/user` | GET | Yes | admin, guru, siswa | List users with pagination |
| `/auth/user` | POST | Yes | admin | Create new user |
| `/auth/user` | PUT | Yes | admin, guru, siswa | Update own profile or admin updates any |
| `/auth/user/all` | GET | Yes | admin, guru | Get all active users (no pagination) |
| `/auth/user/{id}` | GET | Yes | admin, guru, siswa | Get user by ID or own profile |
| `/auth/user/{id}` | DELETE | Yes | admin | Delete user |
| `/auth/user/update-password/{id}` | PUT | Yes | admin, guru, siswa | Update own password or admin changes any |

## Implementation Changes

### 1. OpenAPI Specification Updated (`storage/api-docs/api-docs.json`)
- Added detailed description of authentication and RBAC in API info section
- Each endpoint now includes:
  - **Authentication** requirement (Bearer Token)
  - **Allowed Roles** for that endpoint
- Added new `ForbiddenResponse` schema for 403 errors
- Updated all 403 error responses to reference `ForbiddenResponse`
- Added role descriptions in API documentation

### 2. Controller Authorization Checks (`app/Http/Controllers/UserController.php`)

**Created User (store)**
```php
if ($request->user()->role !== 'admin') {
    return $this->errorResponse('Forbidden: Only admin can create users', 403);
}
```

**Delete User (destroy)**
```php
if (auth()->user()->role !== 'admin') {
    return $this->errorResponse('Forbidden: Only admin can delete users', 403);
}
```

**Update User (update)**
```php
if (auth()->user()->role !== 'admin' && auth()->user()->id != $id) {
    return $this->errorResponse('Forbidden: You can only update your own profile', 403);
}
```

**Update Password (updatePassword)**
```php
if (auth()->user()->role !== 'admin' && auth()->user()->id != $id) {
    return $this->errorResponse('Forbidden: You can only change your own password', 403);
}
```

**Get All Users (getAllUsers)**
```php
if (!in_array(auth()->user()->role, ['admin', 'guru'])) {
    return $this->errorResponse('Forbidden: Only admin and guru can view all users', 403);
}
```

## Bearer Token Usage

### Authentication Header Format
```
Authorization: Bearer {token}
```

### Example Request with Bearer Token
```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer 3|DWm9ATl5JEGsDT4kqFHUMGvT2sYSx3QDXqfG7JgX733b0e7c"
```

### Response When Authorization Fails
```json
{
  "success": false,
  "message": "Forbidden: Only admin can create users"
}
```

## HTTP Status Codes

- **200**: Request successful
- **201**: Resource created
- **401**: Missing or invalid bearer token
- **403**: Authenticated but lacking required role for the endpoint
- **404**: Resource not found
- **422**: Validation error

## Testing Authorization

To test the authorization:

1. **Login** to get a bearer token
2. **Make API requests** with the token in Authorization header
3. **Check responses**:
   - 200/201 if authorized and operation successful
   - 403 if role doesn't have permission
   - 401 if token is missing or invalid

## Security Notes

1. All role checks verify the authenticated user's role from the database
2. Users can only access/modify their own profile unless they're admin
3. Sensitive operations (create, delete) are admin-only
4. Non-authenticated requests to protected endpoints return 401
5. Authenticated requests to endpoints requiring specific roles return 403

## API Documentation

Visit `http://localhost:8000/docs` to view the complete OpenAPI specification with:
- All endpoints listed with their role requirements
- Bearer token authentication example
- Request/response schemas
- Error response examples for authorization failures
