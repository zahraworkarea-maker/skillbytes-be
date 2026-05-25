# User Resume Management - API Documentation

## Overview
Fitur User Resume memungkinkan setiap siswa untuk:
- Menyimpan **multiple resumes** (tidak terbatas hanya 1 resume)
- Mengunggah resume dalam format **file** (PDF, DOC, DOCX, dll)
- Menyimpan resume sebagai **text content** (tulis langsung)
- Menentukan resume mana yang **aktif/utama**
- Mengelola resume dengan operasi CRUD lengkap

## Database Structure

### Table: `user_resumes`
```
id                INTEGER (Primary Key)
user_id           INTEGER (Foreign Key → users.id) - Siswa yang memiliki resume
title             STRING(255) - Judul/nama resume (required)
content           LONGTEXT - Isi resume (optional, jika text-based)
file_url          STRING - Path URL file resume (optional, jika file-based)
file_type         STRING - Tipe file (pdf, doc, docx, etc)
description       LONGTEXT - Deskripsi/catatan tentang resume
is_active         BOOLEAN (default: true) - Menandai resume utama
created_at        TIMESTAMP
updated_at        TIMESTAMP

Indexes: user_id, is_active
```

## API Endpoints

### 1. GET /api/user/resumes
**Deskripsi:** Ambil semua resume milik user yang sedang login

**Authentication:** Required (Bearer Token)

**Query Parameters:**
- `is_active` (boolean, optional) - Filter by active status

**Response:**
```json
{
  "success": true,
  "message": "Resumes retrieved successfully",
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "title": "CV - Software Engineer",
      "content": "Lorem ipsum dolor sit amet...",
      "file_url": null,
      "file_type": null,
      "description": "Resume for job applications",
      "is_active": true,
      "created_at": "2026-05-25T10:30:00Z",
      "updated_at": "2026-05-25T10:30:00Z"
    },
    {
      "id": 2,
      "user_id": 5,
      "title": "CV - Backend Developer",
      "content": null,
      "file_url": "/storage/user-resumes/1716624600_abc12345.pdf",
      "file_type": "pdf",
      "description": "Resume for backend positions",
      "is_active": false,
      "created_at": "2026-05-24T14:20:00Z",
      "updated_at": "2026-05-25T09:15:00Z"
    }
  ]
}
```

### 2. POST /api/user/resumes
**Deskripsi:** Buat resume baru (text atau file)

**Authentication:** Required (Bearer Token)

**Content-Type:** 
- `application/json` (untuk text-based)
- `multipart/form-data` (untuk file upload)

**Request Body (Text-based):**
```json
{
  "title": "CV - Software Engineer",
  "content": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua...",
  "description": "Resume for job applications",
  "is_active": true
}
```

**Request Body (File-based):**
```
title: CV - Backend Developer
file: [binary file content]
description: Resume for backend positions
is_active: false
```

**Validation Rules:**
- `title` - Required, max 255 characters
- `content` - Optional, minimum 50 characters (jika diisi)
- `file` - Optional, max 100MB, format: pdf|doc|docx|ppt|pptx|xls|xlsx|zip
- `description` - Optional, max 1000 characters
- `is_active` - Optional, boolean

**Response:**
```json
{
  "success": true,
  "message": "Resume created successfully",
  "data": {
    "id": 3,
    "user_id": 5,
    "title": "CV - Software Engineer",
    "content": "Lorem ipsum...",
    "file_url": null,
    "file_type": null,
    "description": "Resume for job applications",
    "is_active": true,
    "created_at": "2026-05-25T10:35:00Z",
    "updated_at": "2026-05-25T10:35:00Z"
  }
}
```

**Note:** Jika `is_active` = true, resume lain yang aktif akan otomatis di-deactivate.

### 3. GET /api/user/resumes/{id}
**Deskripsi:** Ambil detail resume spesifik

**Authentication:** Required (Bearer Token)

**Parameters:**
- `id` - Resume ID (required, path parameter)

**Response:** 200 OK
```json
{
  "success": true,
  "message": "Resume retrieved successfully",
  "data": {
    "id": 1,
    "user_id": 5,
    "title": "CV - Software Engineer",
    "content": "...",
    "file_url": null,
    "file_type": null,
    "description": "Resume for job applications",
    "is_active": true,
    "created_at": "2026-05-25T10:30:00Z",
    "updated_at": "2026-05-25T10:30:00Z"
  }
}
```

**Error Response:** 403 Forbidden (jika resume milik user lain) / 404 Not Found

### 4. PUT /api/user/resumes/{id}
**Deskripsi:** Update resume existing

**Authentication:** Required (Bearer Token)

**Content-Type:** 
- `application/json` (untuk text-based)
- `multipart/form-data` (untuk file upload)

**Request Body (same as POST, semua field optional):**
```json
{
  "title": "Updated CV - Full Stack Engineer",
  "content": "Updated content...",
  "description": "Updated description",
  "is_active": false
}
```

**Response:** 200 OK (sama format GET /{id})

**Note:** 
- File lama akan otomatis dihapus jika upload file baru
- Jika hanya update text content, file lama tetap disimpan

### 5. DELETE /api/user/resumes/{id}
**Deskripsi:** Hapus resume

**Authentication:** Required (Bearer Token)

**Parameters:**
- `id` - Resume ID (required, path parameter)

**Response:** 200 OK
```json
{
  "success": true,
  "message": "Resume deleted successfully",
  "data": null
}
```

**Note:** File di storage juga akan dihapus otomatis

### 6. POST /api/user/resumes/{id}/set-active
**Deskripsi:** Set resume sebagai aktif/utama (deactivate resume lain)

**Authentication:** Required (Bearer Token)

**Parameters:**
- `id` - Resume ID (required, path parameter)

**Response:** 200 OK (same format GET /{id})

## Implementation Details

### File Storage
- **Directory:** `/storage/app/public/user-resumes/`
- **URL Access:** `http://localhost:8000/storage/user-resumes/[filename]`
- **Max Size:** 100MB
- **Supported Formats:** PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP

### Security Features
1. **User Authorization:** Hanya user yang memiliki resume bisa akses/edit/delete
2. **File Cleanup:** File lama otomatis dihapus saat delete atau replace
3. **Validation:** Input validation ketat untuk mencegah abuse
4. **Role-based:** Semua endpoint require `auth:sanctum` middleware

### Active Resume Logic
- Setiap user bisa punya multiple resumes
- Hanya **1 resume yang bisa active** per user
- Setting `is_active: true` otomatis menonaktifkan resume lain
- Saat create: jika tidak specify `is_active`, default adalah `true`

## Usage Examples

### Example 1: Upload Resume sebagai Text
```bash
curl -X POST http://localhost:8000/api/user/resumes \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "CV - Tahun 2026",
    "content": "Nama: John Doe\nEmail: john@example.com\n\nPengalaman:\n- Software Engineer di PT ABC (2024-2026)\n- Junior Developer di PT XYZ (2022-2024)",
    "description": "Resume terbaru untuk aplikasi kerja",
    "is_active": true
  }'
```

### Example 2: Upload Resume sebagai File
```bash
curl -X POST http://localhost:8000/api/user/resumes \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "title=CV - PDF Format" \
  -F "file=@/path/to/resume.pdf" \
  -F "description=CV dalam format PDF" \
  -F "is_active=true"
```

### Example 3: Get All Active Resumes
```bash
curl -X GET "http://localhost:8000/api/user/resumes?is_active=true" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Example 4: Update Resume
```bash
curl -X PUT http://localhost:8000/api/user/resumes/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "CV - Updated",
    "description": "Resume yang sudah diupdate"
  }'
```

### Example 5: Set Resume sebagai Active
```bash
curl -X POST http://localhost:8000/api/user/resumes/2/set-active \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Frontend Integration Guide

### React Example (Upload Resume)
```javascript
const uploadResume = async (title, content, description, isActive, file = null) => {
  const token = localStorage.getItem('auth_token');
  
  if (file) {
    // Upload dengan file
    const formData = new FormData();
    formData.append('title', title);
    formData.append('file', file);
    formData.append('description', description);
    formData.append('is_active', isActive);
    
    const response = await fetch('/api/user/resumes', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`
      },
      body: formData
    });
    
    return await response.json();
  } else {
    // Upload dengan text content
    const response = await fetch('/api/user/resumes', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        title,
        content,
        description,
        is_active: isActive
      })
    });
    
    return await response.json();
  }
};

// Usage
uploadResume(
  'CV - Software Engineer',
  'Pengalaman kerja...',
  'Resume terbaru',
  true
);
```

### React Example (Get All Resumes)
```javascript
const getAllResumes = async () => {
  const token = localStorage.getItem('auth_token');
  
  const response = await fetch('/api/user/resumes', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  return data.data; // Array of resumes
};
```

## Error Handling

### Common Error Responses

**400 Bad Request - Validation Error**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["Resume title is required"],
    "file": ["File size must not exceed 100MB"]
  }
}
```

**401 Unauthorized**
```json
{
  "success": false,
  "message": "Unauthorized - Bearer token is missing or invalid"
}
```

**403 Forbidden**
```json
{
  "success": false,
  "message": "Unauthorized access to this resume"
}
```

**404 Not Found**
```json
{
  "success": false,
  "message": "Resume not found"
}
```

**500 Internal Server Error**
```json
{
  "success": false,
  "message": "Failed to create resume: [error details]"
}
```

## Database Queries Examples

### Get All Resumes for a User
```php
$user = auth()->user();
$resumes = $user->resumes()->orderBy('created_at', 'desc')->get();
```

### Get Active Resume
```php
$user = auth()->user();
$activeResume = $user->resumes()->where('is_active', true)->first();
```

### Get Resumes Count
```php
$user = auth()->user();
$count = $user->resumes()->count();
```

## Migration Info

Migration file: `database/migrations/2026_05_25_create_user_resumes_table.php`

Untuk rollback:
```bash
php artisan migrate:rollback
```

## Notes
- Fitur ini fully integrated dengan authentication system (Sanctum)
- Semua file disimpan di `/storage/app/public/user-resumes/`
- Pastikan storage sudah dilink: `php artisan storage:link`
- Setiap resume hanya bisa diakses oleh pemiliknya
- Soft deletes tidak diimplementasikan (permanent delete)
