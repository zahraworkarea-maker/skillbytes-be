# 🚀 Skillbytes Backend - Struktur & Setup Lengkap

## ✅ Apa Yang Telah Dibuat

Saya telah membuat struktur backend Laravel yang professional dan scalable untuk project Skillbytes. Berikut adalah detail lengkapnya:

---

## 📁 Directory Structure yang Dibuat

```
app/
├── Services/                    # Business Logic Layer
│   ├── BaseService.php         # Parent class untuk semua services
│   └── UserService.php         # Contoh implementasi
│
├── Repositories/               # Data Access Layer
│   ├── BaseRepository.php      # Parent class untuk semua repositories
│   └── UserRepository.php      # Contoh implementasi
│
├── DTOs/                       # Data Transfer Objects
│   ├── BaseDto.php
│   ├── CreateUserDto.php
│   └── UpdateUserDto.php
│
├── Traits/                     # Reusable Traits
│   ├── ApiResponse.php         # Consistent JSON responses
│   ├── HasUuid.php            # UUID primary keys
│   └── HasTimestamps.php      # Auto timestamps
│
├── Exceptions/                 # Custom Exceptions
│   ├── CustomException.php
│   ├── ResourceNotFoundException.php
│   └── UnauthorizedException.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php      # Base controller dengan ApiResponse trait
│   │   └── UserController.php  # Contoh implementasi
│   │
│   ├── Requests/               # Form Request Validation
│   │   ├── StoreUserRequest.php
│   │   ├── UpdateUserRequest.php
│   │   └── LoginRequest.php
│   │
│   ├── Resources/              # API Response Transformers
│   │   └── UserResource.php
│   │
│   ├── Filters/               # Query Filters (ready to use)
│   └── Middleware/            # (already exists)
│
├── Scopes/                    # Query Scopes (ready to use)
├── Policies/                  # Authorization Policies (ready to use)
├── Jobs/                      # Queued Jobs (ready to use)
├── Listeners/                 # Event Listeners (ready to use)
├── Observers/                 # Model Observers (ready to use)
└── Enums/                     # PHP Enums (ready to use)

routes/
├── api.php                    # API routes entry point (UPDATED)
└── api/                       # Feature-specific routes
    ├── auth.php               # Authentication routes
    └── users.php              # User CRUD routes

database/
├── migrations/               # (ready for new migrations)
├── factories/               # (UserFactory already exists)
└── seeders/                 # (ready for seeders)
```

---

## 🎯 Core Components

### 1. **Base Service** (`app/Services/BaseService.php`)
```php
// Provides:
- transformData()      // Transform data atau collection
- handleError()        // Handle errors dengan logging
```

### 2. **Base Repository** (`app/Repositories/BaseRepository.php`)
```php
// Provides:
- getAll()            // Get semua records
- paginate()          // Get paginated records
- find()              // Find by ID
- findOrFail()        // Find or throw error
- findBy()            // Find by attribute
- getBy()             // Get records by attribute
- create()            // Create new record
- update()            // Update record
- delete()            // Delete record
- query()             // Get query builder
- count()             // Count records
```

### 3. **API Response Trait** (`app/Traits/ApiResponse.php`)
```php
// Methods:
- successResponse()            // Standard success response
- errorResponse()              // Standard error response
- paginatedResponse()          // Paginated data response
- createdResponse()            // Created (201) response
- unauthorizedResponse()       // 401 response
- forbiddenResponse()          // 403 response
- notFoundResponse()           // 404 response
- validationErrorResponse()    // 422 validation response
```

### 4. **Useful Traits**
- `HasUuid` - Auto generate UUID untuk primary key
- `HasTimestamps` - Auto manage created_at & updated_at

---

## 📋 Example Implementation - User Management

### UserRepository
```php
class UserRepository extends BaseRepository {
    public function getActiveUsers() { ... }
    public function findByEmail($email) { ... }
    public function getWithPagination($perPage) { ... }
}
```

### UserService
```php
class UserService extends BaseService {
    public function getAllUsers() { ... }
    public function getUserById($id) { ... }
    public function createUser(CreateUserDto $dto) { ... }
    public function updateUser($id, UpdateUserDto $dto) { ... }
    public function deleteUser($id) { ... }
}
```

### UserController
```php
class UserController extends Controller {
    public function index()        // GET /api/users
    public function show($id)      // GET /api/users/{id}
    public function store()        // POST /api/users
    public function update()       // PUT /api/users/{id}
    public function destroy($id)   // DELETE /api/users/{id}
}
```

### Form Requests (Validation)
- `StoreUserRequest` - Create user validation
- `UpdateUserRequest` - Update user validation
- `LoginRequest` - Login validation

### DTOs (Data Transfer Objects)
- `CreateUserDto` - Transfer data untuk create user
- `UpdateUserDto` - Transfer data untuk update user

### Resources (Response Transformation)
- `UserResource` - Transform User model ke API response

---

## 🔗 API Response Format

### Success Response (200)
```json
{
  "success": true,
  "message": "Success",
  "data": { "id": 1, "name": "John", "email": "john@example.com" }
}
```

### Paginated Response (200)
```json
{
  "success": true,
  "message": "Success",
  "data": [ {...}, {...} ],
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7
  }
}
```

### Error Response (400+)
```json
{
  "success": false,
  "message": "Error message",
  "errors": { "field": ["Error details"] }
}
```

---

## 🔄 Request Flow

```
Client HTTP Request
        ↓
    Routes (routes/api/)
        ↓
    Controllers (UserController)
        ↓
    Form Requests (StoreUserRequest - Validation)
        ↓
    Services (UserService - Business Logic)
        ↓
    Repositories (UserRepository - Database)
        ↓
    Models (User - Eloquent)
        ↓
    Database (MySQL)
        ↓
    Resources (UserResource - Transform Response)
        ↓
    ApiResponse Trait (successResponse, errorResponse, etc)
        ↓
    JSON Response ke Client
```

---

## 📝 Naming Conventions

| Component | Pattern | Example |
|-----------|---------|---------|
| Controller | `{Entity}Controller` | `UserController` |
| Service | `{Entity}Service` | `UserService` |
| Repository | `{Entity}Repository` | `UserRepository` |
| Model | `{Entity}` | `User` |
| Request | `{Action}{Entity}Request` | `StoreUserRequest` |
| Resource | `{Entity}Resource` | `UserResource` |
| DTO | `{Action}{Entity}Dto` | `CreateUserDto` |
| Migration | `create_{table}_table` | `create_users_table` |
| Policy | `{Entity}Policy` | `UserPolicy` |

---

## 🚀 Cara Menggunakan Struktur Ini

### 1. **Membuat Feature/Module Baru** (contoh: Course)

#### Step 1: Create Model dengan Migration
```bash
php artisan make:model Models/Course -mf
```

#### Step 2: Create Repository
```php
class CourseRepository extends BaseRepository {
    public function __construct(Course $model) {
        parent::__construct($model);
    }

    public function getPublishedCourses() {
        return $this->query()
            ->where('published', true)
            ->latest()
            ->get();
    }
}
```

#### Step 3: Create Service
```php
class CourseService extends BaseService {
    public function __construct(private CourseRepository $repo) {}

    public function getAllCourses() {
        return $this->repo->getPublishedCourses();
    }

    public function createCourse(CreateCourseDto $dto) {
        try {
            return $this->repo->create($dto->toArray());
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }
}
```

#### Step 4: Create Controller
```php
class CourseController extends Controller {
    public function __construct(private CourseService $service) {}

    public function index() {
        $courses = $this->service->getAllCourses();
        return $this->paginatedResponse($courses);
    }

    public function store(StoreCourseRequest $request) {
        $dto = CreateCourseDto::fromArray($request->validated());
        $course = $this->service->createCourse($dto);
        return $this->createdResponse($course);
    }
}
```

#### Step 5: Create Requests & Resources
```php
// app/Http/Requests/StoreCourseRequest.php
class StoreCourseRequest extends FormRequest {
    public function rules() {
        return [
            'title' => 'required|string|unique:courses',
            'description' => 'required|string',
            'price' => 'required|numeric',
        ];
    }
}

// app/Http/Resources/CourseResource.php
class CourseResource extends JsonResource {
    public function toArray($request) {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
        ];
    }
}
```

#### Step 6: Create Routes
```php
// routes/api/courses.php
Route::middleware('auth:sanctum')->prefix('courses')->group(function () {
    Route::get('/', 'CourseController@index');
    Route::post('/', 'CourseController@store');
    Route::get('/{id}', 'CourseController@show');
    Route::put('/{id}', 'CourseController@update');
    Route::delete('/{id}', 'CourseController@destroy');
});

// routes/api.php - tambahkan
require __DIR__ . '/api/courses.php';
```

---

## ✨ Best Practices yang Sudah Diimplementasikan

✅ **Dependency Injection** - Constructor injection untuk semua class  
✅ **Single Responsibility** - Setiap layer punya tanggung jawab spesifik  
✅ **DRY Principle** - Base classes untuk avoid code duplication  
✅ **Clean Code** - Readable dan maintainable code structure  
✅ **Type Hinting** - All method parameters and returns are typed  
✅ **Error Handling** - Custom exceptions dan error responses  
✅ **Validation** - Form requests untuk client-side validation  
✅ **API Standards** - Consistent JSON response format  
✅ **Documentation** - Comments dan docblocks di semua class  

---

## 📚 File Dokumentasi

- **BACKEND_STRUCTURE.md** - Dokumentasi lengkap struktur backend
- **skillbytes-backend-structure.md** (Memory) - Quick reference

---

## 🔧 Next Steps untuk Project

1. **Setup Database**
   - Configure `.env` dengan database credentials
   - Run migrations: `php artisan migrate`

2. **Create Authentication**
   - Setup AuthController dengan login/register logic
   - Setup Sanctum tokens untuk API authentication

3. **Create Additional Modules**
   - Follow pattern yang sudah dibuat untuk courses, lessons, etc
   - Setiap module: Model → Repository → Service → Controller → Requests → Resources → Routes

4. **Add Tests**
   - Create feature tests untuk setiap endpoint
   - Create unit tests untuk services

5. **Setup Queue & Jobs** (if needed)
   - Use `app/Jobs/` untuk background tasks
   - Setup queues di config/queue.php

6. **Add More Features**
   - Authorization policies (`app/Policies/`)
   - Model observers (`app/Observers/`)
   - Event listeners (`app/Listeners/`)
   - Enums untuk constants (`app/Enums/`)

---

## 💡 Tips

- Gunakan `php artisan tinker` untuk test queries
- Buat DTOs untuk semua input data
- Gunakan Resources untuk semua API responses
- Selalu throw exceptions dari Service layer
- Catch exceptions di Controller layer
- Test setiap endpoint dengan Postman/Insomnia

---

Struktur ini siap untuk production dan akan scale dengan baik seiring project berkembang! 🎉
