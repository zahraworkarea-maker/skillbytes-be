# 📚 Swagger/OpenAPI Documentation Setup - Skillbytes Backend

Dokumentasi lengkap tentang cara kerja sistem dokumentasi API menggunakan L5-Swagger di backend Skillbytes.

---

## 📖 Daftar Isi

1. [Overview](#overview)
2. [Arsitektur Sistem](#arsitektur-sistem)
3. [File-File Konfigurasi](#file-file-konfigurasi)
4. [Struktur Folder](#struktur-folder)
5. [Cara Kerja Swagger](#cara-kerja-swagger)
6. [Proses Generate Dokumentasi](#proses-generate-dokumentasi)
7. [Anotasi PHP untuk Endpoint](#anotasi-php-untuk-endpoint)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)
10. [Testing & Akses](#testing--akses)

---

## Overview

### Apa itu L5-Swagger?

**L5-Swagger** adalah package Laravel yang mengotomatisasi pembuatan dokumentasi OpenAPI/Swagger dari anotasi PHP dalam code.

**Keunggulan:**
- ✅ Dokumentasi auto-generated dari code
- ✅ UI interaktif untuk testing endpoint
- ✅ Format standar industri (OpenAPI 3.0.3)
- ✅ Sync otomatis dengan kode

### Package yang Digunakan

```json
{
  "darkaonline/l5-swagger": "^8.6"
}
```

---

## Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────┐
│                    SWAGGER WORKFLOW                         │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  1. Controllers dengan Anotasi @OA\*                        │
│     ↓                                                        │
│  2. SwaggerController.php (Metadata Global)                 │
│     ↓                                                        │
│  3. L5-Swagger Config (config/l5-swagger.php)              │
│     ↓                                                        │
│  4. Generate Dokumentasi: php artisan l5-swagger:generate  │
│     ↓                                                        │
│  5. Output (storage/api-docs/)                             │
│     ├── api-docs.json (format JSON)                        │
│     ├── api-docs.yaml (format YAML)                        │
│     └── swagger-ui.html (interface)                        │
│     ↓                                                        │
│  6. Akses via Swagger UI (Browser)                         │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## File-File Konfigurasi

### 1. **config/l5-swagger.php** (Konfigurasi Utama)

**Lokasi:** `config/l5-swagger.php`

**Bagian-bagian penting:**

#### a) Judul & Rute Akses
```php
'api' => [
    'title' => 'Skillbytes API Documentation',
],

'routes' => [
    'api' => 'api/documentation',  // URL: http://localhost:8000/api/documentation
],
```

#### b) Path Output
```php
'paths' => [
    'docs' => storage_path('api-docs'),          // Folder output
    'docs_json' => 'api-docs.json',              // File JSON
    'docs_yaml' => 'api-docs.yaml',              // File YAML
    'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
],
```

#### c) Anotasi Source
```php
'annotations' => [
    base_path('app'),  // Scan semua file di folder app/ untuk anotasi
],
```

#### d) Servers (Environment)
```php
'servers' => [
    [
        'url' => env('L5_SWAGGER_LOCAL_HOST', 'http://localhost:8000'),
        'description' => 'Local Development',
    ],
    [
        'url' => env('L5_SWAGGER_PRODUCTION_HOST', 'http://145.79.13.180'),
        'description' => 'Production Server',
    ],
],
```

---

### 2. **.env** (Environment Variables)

**File:** `.env`

```env
# Swagger Configuration
L5_SWAGGER_LOCAL_HOST=http://localhost:8000
L5_SWAGGER_PRODUCTION_HOST=http://145.79.13.180
L5_FORMAT_TO_USE_FOR_DOCS=json
```

---

### 3. **app/Http/Controllers/SwaggerController.php** (Global Metadata)

**Fungsi:** Mendefinisikan metadata global untuk dokumentasi

```php
<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Skillbytes API - PBL Module",
 *     description="REST API for Problem-Based Learning (PBL) Case Management System",
 *     @OA\Contact(
 *         email="support@skillbytes.com"
 *     ),
 *     @OA\License(
 *         name="MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development"
 * )
 *
 * @OA\Server(
 *     url="http://145.79.13.180",
 *     description="Production Server"
 * )
 *
 * @OA\Components(
 *     @OA\SecurityScheme(
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT",
 *         securityScheme="bearer_token",
 *         description="Bearer token for API authentication"
 *     )
 * )
 */

class SwaggerController
{
    // File ini hanya untuk dokumentasi Swagger
}
```

**Komponen:**
- `@OA\Info` - Informasi API (title, version, description)
- `@OA\Server` - Server yang tersedia
- `@OA\Components` - Komponen global (SecurityScheme, Schemas)

---

### 4. **storage/api-docs/** (Output Documentation)

**Generated Files:**

```
storage/api-docs/
├── api-docs.json       ← File JSON lengkap
├── api-docs.yaml       ← File YAML lengkap
└── swagger-ui/         ← UI assets
```

---

## Struktur Folder

### Routes API

```
routes/api/
├── api.php             ← Main router (includes semua)
├── auth.php            ← Auth endpoints
├── learning.php        ← Levels & Lessons
├── users.php           ← User CRUD
├── pbl.php             ← PBL Cases & Sections
├── file.php            ← File upload
└── sanctum_auth.php    ← Sanctum config
```

### Controllers dengan Anotasi

```
app/Http/Controllers/
├── SwaggerController.php          ← Global metadata
└── Api/
    ├── Admin/
    │   ├── PblLevelController.php       (CRUD PBL Levels)
    │   ├── PblCaseController.php        (CRUD PBL Cases)
    │   ├── CaseSectionController.php    (CRUD Sections)
    │   ├── CaseSectionItemController.php (CRUD Section Items)
    │   └── ImageUploadController.php    (Upload Image)
    └── User/
        ├── PblCaseUserController.php    (View Cases)
        └── CaseSubmissionController.php (Submit Answer)
```

---

## Cara Kerja Swagger

### 1. **Scan Phase** (Scanning)

Saat menjalankan `php artisan l5-swagger:generate`, L5-Swagger:

```
1. Membaca konfigurasi dari config/l5-swagger.php
2. Menemukan folder scan: base_path('app')
3. Mencari semua anotasi @OA\* di file PHP
4. Mengumpulkan metadata dari:
   - SwaggerController.php (global)
   - Controllers individual (endpoint-specific)
```

### 2. **Parse Phase** (Parsing)

```
1. Parse anotasi PHP menjadi struktur data
2. Validasi terhadap OpenAPI 3.0.3 schema
3. Resolve references dan dependencies
```

### 3. **Generate Phase** (Generating)

```
1. Compile ke format JSON
2. Compile ke format YAML (jika dikonfigurasi)
3. Simpan ke storage/api-docs/
```

### 4. **Serve Phase** (Serving)

```
1. Route /api/documentation menampilkan Swagger UI
2. Swagger UI membaca api-docs.json/yaml
3. Render dokumentasi interaktif di browser
```

---

## Proses Generate Dokumentasi

### Command Generate

```bash
# Generate dokumentasi dari anotasi
php artisan l5-swagger:generate

# Lihat progress
php artisan l5-swagger:generate --verbose

# Force regenerate (clear cache)
php artisan cache:clear && php artisan l5-swagger:generate
```

### Hasil Generate

**storage/api-docs/api-docs.json** (snippet):

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "Skillbytes API - PBL Module",
    "version": "1.0.0",
    "description": "REST API for PBL..."
  },
  "servers": [
    {
      "url": "http://localhost:8000",
      "description": "Local Development"
    }
  ],
  "paths": {
    "/api/pbl-cases": {
      "get": { ... },
      "post": { ... }
    },
    "/api/pbl-cases/{pblCase}": {
      "get": { ... },
      "put": { ... },
      "delete": { ... }
    }
  }
}
```

---

## Anotasi PHP untuk Endpoint

### Struktur Dasar Anotasi

```php
/**
 * @OA\Get(
 *     path="/pbl-cases",
 *     operationId="listPblCases",
 *     tags={"PBL Cases"},
 *     summary="List all PBL cases",
 *     description="Retrieve all available PBL cases",
 *     @OA\Parameter(...),
 *     @OA\Response(...),
 *     security={{"bearer_token": {}}}
 * )
 */
```

### Contoh 1: GET Endpoint (List)

**File:** `app/Http/Controllers/Api/User/PblCaseUserController.php`

```php
/**
 * @OA\Get(
 *     path="/pbl-cases",
 *     operationId="listPblCases",
 *     tags={"PBL Cases"},
 *     summary="List all PBL cases",
 *     description="Retrieve all available PBL cases with current user's status",
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Page number",
 *         schema={"type": "integer", "default": 1}
 *     ),
 *     @OA\Parameter(
 *         name="pbl_level_id",
 *         in="query",
 *         description="Filter by PBL level ID",
 *         schema={"type": "integer"}
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of PBL cases",
 *         @OA\JsonContent(
 *             properties={
 *                 @OA\Property(property="data", type="array", items={"type":"object"}),
 *                 @OA\Property(property="pagination", type="object")
 *             }
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     security={{"bearer_token": {}}}
 * )
 */
public function index()
{
    // Implementation
}
```

### Contoh 2: POST Endpoint (Create)

```php
/**
 * @OA\Post(
 *     path="/pbl-cases",
 *     operationId="createPblCase",
 *     tags={"PBL Cases"},
 *     summary="Create a new PBL case (Admin/Guru only)",
 *     @OA\RequestBody(
 *         required=true,
 *         description="PBL case data",
 *         @OA\JsonContent(
 *             required={"case_number", "title", "pbl_level_id", "description", "start_date", "deadline"},
 *             @OA\Property(property="case_number", type="integer", example=1),
 *             @OA\Property(property="title", type="string", example="System Login Bermasalah"),
 *             @OA\Property(property="pbl_level_id", type="integer", example=1),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="start_date", type="string", format="date-time"),
 *             @OA\Property(property="deadline", type="string", format="date-time")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="PBL case created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="slug", type="string"),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     @OA\Response(response=422, description="Validation error"),
 *     @OA\Response(response=403, description="Forbidden - Only Admin and Guru can create cases"),
 *     security={{"bearer_token": {}}}
 * )
 */
public function store(StorePblCaseRequest $request)
{
    // Implementation
}
```

### Contoh 3: PUT Endpoint (Update)

```php
/**
 * @OA\Put(
 *     path="/pbl-cases/{pblCase}",
 *     operationId="updatePblCase",
 *     tags={"PBL Cases"},
 *     summary="Update a PBL case (Admin/Guru only)",
 *     @OA\Parameter(
 *         name="pblCase",
 *         in="path",
 *         description="PBL case ID",
 *         required=true,
 *         schema={"type": "integer"}
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="pbl_level_id", type="integer"),
 *             @OA\Property(property="deadline", type="string", format="date-time")
 *         )
 *     ),
 *     @OA\Response(response=200, description="PBL case updated successfully"),
 *     @OA\Response(response=404, description="PBL case not found"),
 *     @OA\Response(response=403, description="Forbidden - Only Admin and Guru can update cases"),
 *     security={{"bearer_token": {}}}
 * )
 */
public function update(UpdatePblCaseRequest $request, PblCase $pblCase)
{
    // Implementation
}
```

### Contoh 4: DELETE Endpoint

```php
/**
 * @OA\Delete(
 *     path="/pbl-cases/{pblCase}",
 *     operationId="deletePblCase",
 *     tags={"PBL Cases"},
 *     summary="Delete a PBL case (Admin/Guru only)",
 *     @OA\Parameter(
 *         name="pblCase",
 *         in="path",
 *         description="PBL case ID",
 *         required=true,
 *         schema={"type": "integer"}
 *     ),
 *     @OA\Response(response=200, description="PBL case deleted successfully"),
 *     @OA\Response(response=404, description="PBL case not found"),
 *     @OA\Response(response=403, description="Forbidden - Only Admin and Guru can delete cases"),
 *     security={{"bearer_token": {}}}
 * )
 */
public function destroy(PblCase $pblCase)
{
    // Implementation
}
```

### Contoh 5: Multipart File Upload

```php
/**
 * @OA\Post(
 *     path="/upload-image",
 *     operationId="uploadImage",
 *     tags={"Media"},
 *     summary="Upload an image (Admin/Guru only)",
 *     @OA\RequestBody(
 *         required=true,
 *         description="Image file",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"image"},
 *                 @OA\Property(
 *                     property="image",
 *                     type="string",
 *                     format="binary",
 *                     description="Image file to upload"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Image uploaded successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="url", type="string", example="/storage/pbl/filename.jpg"),
 *             @OA\Property(property="filename", type="string")
 *         )
 *     ),
 *     security={{"bearer_token": {}}}
 * )
 */
public function upload(ImageUploadRequest $request)
{
    // Implementation
}
```

---

## Endpoint Summary

Berikut adalah daftar lengkap endpoint yang ter-generate di Swagger:

### 🟢 PBL Levels
| Method | Path | Deskripsi | Auth |
|--------|------|-----------|------|
| GET | `/api/pbl-levels` | List semua PBL level | ✅ Semua |
| GET | `/api/pbl-levels/{pblLevel}` | Detail PBL level | ✅ Semua |
| POST | `/api/pbl-levels` | Buat PBL level | ✅ Admin |
| PUT | `/api/pbl-levels/{pblLevel}` | Update PBL level | ✅ Admin |
| DELETE | `/api/pbl-levels/{pblLevel}` | Hapus PBL level | ✅ Admin |

### 🟠 PBL Cases
| Method | Path | Deskripsi | Auth |
|--------|------|-----------|------|
| GET | `/api/pbl-cases` | List semua cases | ✅ Semua |
| GET | `/api/pbl-cases/{pblCase}` | Detail case | ✅ Semua |
| POST | `/api/pbl-cases` | Buat case | ✅ Admin/Guru |
| PUT | `/api/pbl-cases/{pblCase}` | Update case | ✅ Admin/Guru |
| DELETE | `/api/pbl-cases/{pblCase}` | Hapus case | ✅ Admin/Guru |

### 🔵 Case Sections
| Method | Path | Deskripsi | Auth |
|--------|------|-----------|------|
| GET | `/api/pbl-cases/{pblCase}/sections` | List sections | ✅ Admin/Guru |
| POST | `/api/pbl-cases/{pblCase}/sections` | Buat section | ✅ Admin/Guru |
| PUT | `/api/pbl-sections/{caseSection}` | Update section | ✅ Admin/Guru |
| DELETE | `/api/pbl-sections/{caseSection}` | Hapus section | ✅ Admin/Guru |

### 🟣 Section Items
| Method | Path | Deskripsi | Auth |
|--------|------|-----------|------|
| POST | `/api/pbl-sections/{caseSection}/items` | Buat item | ✅ Admin/Guru |
| PUT | `/api/pbl-items/{caseSectionItem}` | Update item | ✅ Admin/Guru |
| DELETE | `/api/pbl-items/{caseSectionItem}` | Hapus item | ✅ Admin/Guru |

### 📤 Submissions
| Method | Path | Deskripsi | Auth |
|--------|------|-----------|------|
| GET | `/api/pbl-submissions` | List submissions user | ✅ Semua |
| POST | `/api/pbl-submissions` | Submit answer | ✅ Semua |
| GET | `/api/pbl-submissions/{id}` | Detail submission | ✅ Semua |

### 🖼️ Media
| Method | Path | Deskripsi | Auth |
|--------|------|-----------|------|
| POST | `/api/upload-image` | Upload image | ✅ Admin/Guru |

---

## Best Practices

### 1. **Selalu Describe Endpoint**

```php
/**
 * @OA\Get(
 *     path="/resource",
 *     operationId="getResource",  // ← Harus unik
 *     tags={"Resource Category"},
 *     summary="Short description",
 *     description="Longer detailed description",
 *     ...
 * )
 */
```

### 2. **Konsisten Naming Convention**

```
✅ BAIK:
- operationId: listPblCases, getPblCase, createPblCase
- tag: "PBL Cases", "PBL Levels"

❌ BURUK:
- operationId: get_pbl_cases, getPBLCases
- tag: "pbl-cases", "cases"
```

### 3. **Dokumentasikan Error Response**

```php
@OA\Response(response=400, description="Bad Request"),
@OA\Response(response=401, description="Unauthorized"),
@OA\Response(response=403, description="Forbidden"),
@OA\Response(response=404, description="Not Found"),
@OA\Response(response=422, description="Validation Error"),
```

### 4. **Gunakan Security Scheme**

```php
security={{"bearer_token": {}}}

// Atau tidak perlu auth:
// security={}
```

### 5. **Contoh Value di Schema**

```php
@OA\Property(property="title", type="string", example="System Login Bermasalah")
@OA\Property(property="deadline", type="string", format="date-time", example="2026-05-31T23:59:59Z")
```

### 6. **Regenerate Setiap Ada Perubahan**

```bash
# Setelah menambah/mengubah endpoint:
php artisan l5-swagger:generate

# Kemudian commit ke git:
git add storage/api-docs/
git commit -m "Update API documentation"
```

---

## Troubleshooting

### 1. **Endpoint tidak muncul di Swagger UI**

**Penyebab:**
- Anotasi belum di-generate
- File controller tidak di-scan

**Solusi:**

```bash
# Clear cache dan regenerate
php artisan cache:clear
php artisan l5-swagger:generate --verbose

# Pastikan controller di folder app/
# Periksa config untuk 'annotations' path
```

### 2. **Format tidak sesuai OpenAPI**

**Penyebab:**
- Anotasi syntax salah

**Solusi:**

```bash
# Cek verbose output
php artisan l5-swagger:generate --verbose

# Lihat error message
# Perbaiki syntax anotasi
```

### 3. **Security scheme tidak bekerja**

**Penyebab:**
- Security scheme tidak didefinisikan di SwaggerController
- Endpoint tidak punya security property

**Solusi:**

```php
// Di SwaggerController.php
@OA\Components(
    @OA\SecurityScheme(
        type="http",
        scheme="bearer",
        bearerFormat="JWT",
        securityScheme="bearer_token"
    )
)

// Di endpoint:
security={{"bearer_token": {}}}
```

### 4. **UI tidak load CSS/JS**

**Penyebab:**
- Asset path salah
- L5_SWAGGER_USE_ABSOLUTE_PATH configuration

**Solusi:**

```php
// config/l5-swagger.php
'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),
```

---

## Testing & Akses

### 1. **Akses Swagger UI**

```
Local:  http://localhost:8000/api/documentation
Prod:   http://145.79.13.180/api/documentation
```

### 2. **Struktur Swagger UI**

```
┌─────────────────────────────────────────────┐
│  Skillbytes API Documentation v1.0.0         │
├─────────────────────────────────────────────┤
│ ☀️  Servers: [Local, Production]            │
│ 🔐 Authorize (Bearer Token)                 │
├─────────────────────────────────────────────┤
│ ▼ PBL Cases                                 │
│   ├─ GET   /api/pbl-cases                  │
│   ├─ POST  /api/pbl-cases                  │
│   └─ GET   /api/pbl-cases/{id}            │
│                                            │
│ ▼ PBL Levels                               │
│   ├─ GET   /api/pbl-levels                │
│   └─ POST  /api/pbl-levels                │
│                                            │
│ ... (endpoint lainnya)                    │
└─────────────────────────────────────────────┘
```

### 3. **Testing dengan Swagger UI**

**Step 1: Setup Authorization**
- Click "Authorize" button
- Enter Bearer token: `<your-sanctum-token>`
- Click "Authorize"

**Step 2: Test Endpoint**
- Expand endpoint category
- Click endpoint
- Click "Try it out"
- Isi parameters (jika ada)
- Click "Execute"
- Lihat response

### 4. **Raw API Endpoints**

```
# Get JSON spec
curl http://localhost:8000/api/documentation

# Get JSON docs file
curl http://localhost:8000/storage/api-docs/api-docs.json

# Get YAML docs file (jika L5_FORMAT_TO_USE_FOR_DOCS=yaml)
curl http://localhost:8000/storage/api-docs/api-docs.yaml
```

---

## Workflow Integration

### Development Workflow

```
1. Create endpoint di Controller
   ↓
2. Write anotasi @OA\* di method
   ↓
3. Write route di routes/api/
   ↓
4. Test endpoint locally
   ↓
5. Run php artisan l5-swagger:generate
   ↓
6. Verify di http://localhost:8000/api/documentation
   ↓
7. Commit & push
   ↓
8. Deploy & regenerate di production
```

### CI/CD Integration

```bash
# Dalam deployment script:
php artisan l5-swagger:generate
git add storage/api-docs/
git commit -m "Auto-generate Swagger docs"
git push
```

---

## File Reference

### Lokasi File Penting

```
📁 Project Root
├── 📄 config/l5-swagger.php              ← Konfigurasi utama
├── 📄 .env                               ← Environment variables
├── 📁 app/Http/Controllers/
│   ├── 📄 SwaggerController.php          ← Global metadata
│   └── 📁 Api/
│       ├── 📁 Admin/                     ← Admin controllers
│       └── 📁 User/                      ← User controllers
├── 📁 routes/api/
│   ├── 📄 api.php                        ← Main router
│   ├── 📄 auth.php
│   ├── 📄 pbl.php                        ← PBL routes
│   ├── 📄 learning.php
│   ├── 📄 users.php
│   └── 📄 file.php
└── 📁 storage/api-docs/                  ← Generated docs
    ├── 📄 api-docs.json
    ├── 📄 api-docs.yaml
    └── 📁 swagger-ui/
```

---

## Summary

| Komponen | Fungsi | File |
|----------|--------|------|
| **L5-Swagger Package** | Auto-generate docs | composer.json |
| **Configuration** | Setup docs behavior | config/l5-swagger.php |
| **Global Metadata** | API title, servers, auth | SwaggerController.php |
| **Endpoint Anotasi** | Dokumentasi endpoint | Controllers |
| **Routes** | Definisi route | routes/api/* |
| **Generated Output** | JSON/YAML spec | storage/api-docs/ |
| **Swagger UI** | Interactive interface | /api/documentation |

---

## Resources

- 📚 [L5-Swagger GitHub](https://github.com/DarkaOnline/L5-Swagger)
- 📖 [OpenAPI 3.0 Spec](https://spec.openapis.org/oas/v3.0.3)
- 🔗 [Swagger UI Demo](https://petstore.swagger.io/)
- 📝 [Laravel Documentation](https://laravel.com/docs)

---

## Quick Commands

```bash
# Generate documentation
php artisan l5-swagger:generate

# Generate with verbose output
php artisan l5-swagger:generate --verbose

# Clear cache before regenerate
php artisan cache:clear && php artisan l5-swagger:generate

# View Swagger UI
# Open: http://localhost:8000/api/documentation

# Export spec
curl http://localhost:8000/storage/api-docs/api-docs.json > api-spec.json
```

---

**Last Updated:** May 14, 2026  
**Version:** 1.0.0  
**Status:** Production Ready ✅
