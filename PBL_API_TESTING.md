# PBL API Testing Guide

## 📝 Postman Collection Example

Berikut adalah request examples yang bisa digunakan di Postman atau tool API testing lainnya.

---

## 🔑 Setup Authentication

Sebelum testing, pastikan Anda sudah mendapat authentication token dari endpoint login:

```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@skillbytes.com",
  "password": "password123"
}
```

Response akan berisi token yang digunakan untuk semua request:
```json
{
  "data": {
    "token": "1|abcdefghijklmnopqrstuvwxyz123456",
    "user": {
      "id": 1,
      "email": "admin@skillbytes.com",
      "role": "admin"
    }
  }
}
```

**Simpan token dan gunakan di header:**
```
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz123456
```

---

## 🧪 Test Cases

### 1️⃣ Create Level (Optional - untuk setup)

```http
POST /api/admin/levels
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "Beginner"
}
```

Repeat untuk "Intermediate" dan "Advanced"

---

### 2️⃣ Create PBL Case

**Request:**
```http
POST /api/admin/pbl-cases
Content-Type: application/json
Authorization: Bearer {token}

{
  "case_number": 1,
  "title": "System Login Authentication Failed",
  "level_id": 1,
  "description": "Sistem login di aplikasi e-learning kami mengalami masalah. Beberapa user tidak bisa login meskipun password mereka benar. Anda diminta untuk menganalisis dan menemukan root cause dari masalah ini, kemudian memberikan solusi.",
  "image_url": "https://via.placeholder.com/400x200?text=Login+Problem",
  "time_limit": 180,
  "start_date": "2024-01-20 09:00:00",
  "deadline": "2024-01-27 17:00:00"
}
```

**Response (201):**
```json
{
  "message": "PBL case created successfully",
  "data": {
    "id": 1,
    "slug": "system-login-authentication-failed-a1b2c3d4",
    "case_number": 1,
    "title": "System Login Authentication Failed",
    "description": "Sistem login di aplikasi...",
    "image_url": "https://via.placeholder.com/400x200?text=Login+Problem",
    "time_limit": 180,
    "start_date": "2024-01-20T09:00:00.000000Z",
    "deadline": "2024-01-27T17:00:00.000000Z",
    "level": {
      "id": 1,
      "name": "Beginner"
    },
    "sections": [],
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

**Save case ID:** `1` (gunakan untuk step berikutnya)

---

### 3️⃣ Create Sections

**Section 1 - Problem Description:**
```http
POST /api/admin/pbl-cases/1/sections
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "Problem Description",
  "order": 1
}
```

Response: `section_id = 1`

**Section 2 - Background Information:**
```http
POST /api/admin/pbl-cases/1/sections
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "Background Information",
  "order": 2
}
```

Response: `section_id = 2`

---

### 4️⃣ Create Section Items

**Item 1.1 - Heading**
```http
POST /api/admin/sections/1/items
Content-Type: application/json
Authorization: Bearer {token}

{
  "type": "heading",
  "content": "Gambaran Masalah",
  "order": 1
}
```

**Item 1.2 - Text**
```http
POST /api/admin/sections/1/items
Content-Type: application/json
Authorization: Bearer {token}

{
  "type": "text",
  "content": "Pada tanggal 15 Januari 2024, sistem kami mulai menerima report dari user yang tidak bisa login. Error yang muncul adalah 'Invalid credentials' meskipun mereka memasukkan password yang benar. Masalah ini terjadi pada 30% dari total user aktif kami.",
  "order": 2
}
```

**Item 1.3 - Image**
```http
POST /api/admin/sections/1/items
Content-Type: application/json
Authorization: Bearer {token}

{
  "type": "image",
  "image_url": "https://via.placeholder.com/600x300?text=Error+Log",
  "order": 3
}
```

**Item 2.1 - Background Heading**
```http
POST /api/admin/sections/2/items
Content-Type: application/json
Authorization: Bearer {token}

{
  "type": "heading",
  "content": "Informasi Teknis",
  "order": 1
}
```

**Item 2.2 - List**
```http
POST /api/admin/sections/2/items
Content-Type: application/json
Authorization: Bearer {token}

{
  "type": "list",
  "content": "- Database: MySQL 8.0\n- Authentication: Laravel Sanctum\n- Framework: Laravel 10\n- Server: Ubuntu 20.04\n- Last update: 10 Januari 2024",
  "order": 2
}
```

---

### 5️⃣ Upload Image (Optional)

Jika ingin upload image baru:

```http
POST /api/admin/upload-image
Content-Type: multipart/form-data
Authorization: Bearer {token}

Form Data:
- image: [SELECT FILE]
```

Response:
```json
{
  "message": "Image uploaded successfully",
  "url": "/storage/pbl/1704884400_a1b2c3d4.jpg",
  "filename": "1704884400_a1b2c3d4.jpg"
}
```

Gunakan URL ini untuk section items

---

### 6️⃣ Get All Cases (as User)

**Request:**
```http
GET /api/pbl-cases?page=1
Authorization: Bearer {user_token}
```

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "slug": "system-login-authentication-failed-a1b2c3d4",
      "case_number": 1,
      "title": "System Login Authentication Failed",
      "description": "Sistem login...",
      "image_url": "https://via.placeholder.com/400x200?text=Login+Problem",
      "time_limit": 180,
      "start_date": "2024-01-20T09:00:00.000000Z",
      "deadline": "2024-01-27T17:00:00.000000Z",
      "status": "not-started",
      "level": {
        "id": 1,
        "name": "Beginner"
      }
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/pbl-cases?page=1",
    "last": "http://localhost:8000/api/pbl-cases?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

---

### 7️⃣ Get Case Detail (as User)

**Request:**
```http
GET /api/pbl-cases/system-login-authentication-failed-a1b2c3d4
Authorization: Bearer {user_token}
```

**Response (200):**
```json
{
  "id": 1,
  "slug": "system-login-authentication-failed-a1b2c3d4",
  "case_number": 1,
  "title": "System Login Authentication Failed",
  "description": "Sistem login...",
  "image_url": "https://via.placeholder.com/400x200?text=Login+Problem",
  "time_limit": 180,
  "start_date": "2024-01-20T09:00:00.000000Z",
  "deadline": "2024-01-27T17:00:00.000000Z",
  "status": "not-started",
  "level": {
    "id": 1,
    "name": "Beginner"
  },
  "sections": [
    {
      "id": 1,
      "title": "Problem Description",
      "order": 1,
      "items": [
        {
          "id": 1,
          "type": "heading",
          "content": "Gambaran Masalah",
          "image_url": null,
          "order": 1
        },
        {
          "id": 2,
          "type": "text",
          "content": "Pada tanggal 15 Januari 2024...",
          "image_url": null,
          "order": 2
        },
        {
          "id": 3,
          "type": "image",
          "content": null,
          "image_url": "https://via.placeholder.com/600x300?text=Error+Log",
          "order": 3
        }
      ]
    },
    {
      "id": 2,
      "title": "Background Information",
      "order": 2,
      "items": [
        {
          "id": 4,
          "type": "heading",
          "content": "Informasi Teknis",
          "image_url": null,
          "order": 1
        },
        {
          "id": 5,
          "type": "list",
          "content": "- Database: MySQL 8.0\n- ...",
          "image_url": null,
          "order": 2
        }
      ]
    }
  ],
  "created_at": "2024-01-15T10:30:00.000000Z",
  "updated_at": "2024-01-15T10:30:00.000000Z"
}
```

---

### 8️⃣ Submit Case Answer

**Request:**
```http
POST /api/pbl-submissions
Content-Type: application/json
Authorization: Bearer {user_token}

{
  "case_id": 1,
  "answer": "Setelah menganalisis masalah login, saya menemukan beberapa kemungkinan penyebab:\n\n1. **Password Hashing Issue**\nDiagnosis: Algoritmen hashing password mungkin berubah antara versi aplikasi.\nSolusi: Verify algoritma bcrypt yang digunakan, pastikan work factor konsisten.\n\n2. **Database Connection**\nDiagnosis: Mungkin terjadi race condition pada koneksi database.\nSolusi: Implementasi connection pooling dan retry mechanism.\n\n3. **Character Encoding**\nDiagnosis: Perbedaan charset pada database atau aplikasi.\nSolusi: Pastikan charset UTF-8 konsisten di semua layer.\n\nRekomendasi: Cek log database dan aplikasi untuk menemukan error spesifik, kemudian lakukan rollback ke versi sebelumnya sambil menginvestigasi root cause."
}
```

**Response (201):**
```json
{
  "message": "Answer submitted successfully",
  "data": {
    "id": 1,
    "case_id": 1,
    "answer": "Setelah menganalisis masalah login...",
    "submitted_at": "2024-01-22T10:15:00.000000Z",
    "score": null,
    "feedback": null,
    "created_at": "2024-01-22T10:15:00.000000Z",
    "updated_at": "2024-01-22T10:15:00.000000Z"
  }
}
```

---

### 9️⃣ Get User Submissions

**Request:**
```http
GET /api/pbl-submissions
Authorization: Bearer {user_token}
```

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "case_id": 1,
      "answer": "Setelah menganalisis...",
      "submitted_at": "2024-01-22T10:15:00.000000Z",
      "score": null,
      "feedback": null,
      "created_at": "2024-01-22T10:15:00.000000Z",
      "updated_at": "2024-01-22T10:15:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

### 🔟 Filter Cases by Level

**Request:**
```http
GET /api/pbl-cases?page=1&level_id=1
Authorization: Bearer {user_token}
```

Hanya akan menampilkan cases dengan level_id = 1 (Beginner)

---

## 🔄 Workflow Lengkap

1. ✅ Admin membuat level
2. ✅ Admin membuat PBL case
3. ✅ Admin membuat sections (bisa multiple)
4. ✅ Admin menambahkan items ke setiap section
5. ✅ Admin upload images jika diperlukan
6. ✅ User login dan melihat list cases
7. ✅ User buka detail case untuk membaca lengkap
8. ✅ User submit jawaban
9. ✅ User bisa lihat history submissions
10. ✅ Admin bisa grade dan memberi feedback (future feature)

---

## 🐛 Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "message": "Forbidden: insufficient role"
}
```

### 404 Not Found
```json
{
  "message": "Not Found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "case_id": [
      "The case_id field is required."
    ],
    "answer": [
      "The answer must be at least 10 characters."
    ]
  }
}
```

### 400 Bad Request
```json
{
  "message": "You have already submitted an answer for this case"
}
```

---

## 💡 Tips

1. **Test di Swagger UI:** Buka `http://localhost:8000/api/documentation` untuk testing langsung
2. **Use Postman Environment:** Simpan token dalam environment variable untuk reusability
3. **Check Status:** Perhatikan status case yang berubah sesuai waktu (not-started → in-progress → late/completed)
4. **Pagination:** Gunakan `?page=2` untuk melihat halaman berikutnya
5. **Timestamps:** Semua timestamp dalam format UTC ISO 8601

---

## 📊 Database Check

Untuk verify data di database:

```sql
-- Check levels
SELECT * FROM levels;

-- Check PBL cases
SELECT * FROM pbl_cases;

-- Check sections
SELECT * FROM case_sections WHERE case_id = 1;

-- Check items
SELECT * FROM case_section_items WHERE section_id = 1;

-- Check submissions
SELECT * FROM case_submissions WHERE user_id = 1;

-- Check progress
SELECT * FROM user_case_progress WHERE user_id = 1;
```

