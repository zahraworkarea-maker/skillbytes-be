# Skillbytes Backend - Project Structure Guide

## 📁 Struktur Project

Dokumentasi lengkap mengenai struktur dan konvensi backend Laravel untuk project Skillbytes.

### Directory Structure Overview

```
app/
├── Console/              # CLI Commands
├── Exceptions/           # Custom Exceptions
├── Http/
│   ├── Controllers/      # API Controllers
│   ├── Middleware/       # HTTP Middleware
│   ├── Requests/         # Form Request Validation
│   ├── Resources/        # API Response Resources
│   └── Filters/          # Query Filters
├── Models/               # Eloquent Models
├── Services/             # Business Logic Layer
├── Repositories/         # Data Access Layer
├── DTOs/                 # Data Transfer Objects
├── Enums/                # PHP Enums
├── Filters/              # Query Filters
├── Jobs/                 # Queued Jobs
├── Listeners/            # Event Listeners
├── Observers/            # Model Observers
├── Policies/             # Authorization Policies
├── Scopes/               # Query Scopes
├── Traits/               # Reusable Traits
├── Providers/            # Service Providers

database/
├── migrations/           # Database Migrations
├── seeders/              # Database Seeders
└── factories/            # Model Factories

routes/
├── api/                  # API Route Files
│   ├── auth.php
│   └── users.php
├── api.php               # API Routes Entry Point
├── web.php               # Web Routes
├── channels.php          # Broadcasting Channels
└── console.php           # Console Routes

tests/
├── Feature/              # Feature Tests
└── Unit/                 # Unit Tests
```

---

## 📝 Layer Architecture

### 1. **Controllers Layer** (`app/Http/Controllers/`)
- Handle HTTP requests
- Validate input using Form Requests
- Call appropriate services
- Return API responses

**Contoh:**
```php
class UserController extends Controller
{
    use ApiResponse;

    public function __construct(private UserService $service) {}

    public function index(Request $request)
    {
        $users = $this->service->getAllUsers($request);
        return $this->paginatedResponse($users);
    }
}
```

### 2. **Services Layer** (`app/Services/`)
- Business logic implementation
- Data transformation
- External API calls
- Complex operations

**Konvensi Naming:** `{Entity}Service` (contoh: `UserService`, `AuthService`)

**Contoh:**
```php
class UserService extends BaseService
{
    public function __construct(
        private UserRepository $repository,
        private MailService $mailService
    ) {}

    public function createUser(CreateUserDto $dto)
    {
        $user = $this->repository->create($dto->toArray());
        $this->mailService->sendWelcomeEmail($user);
        return $user;
    }
}
```

### 3. **Repository Layer** (`app/Repositories/`)
- Database queries
- Model operations
- Relationship loading
- Query filtering

**Konvensi Naming:** `{Entity}Repository` (contoh: `UserRepository`, `PostRepository`)

**Contoh:**
```php
class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getActiveUsers()
    {
        return $this->query()
            ->where('is_active', true)
            ->with('roles')
            ->latest()
            ->get();
    }
}
```

### 4. **Models** (`app/Models/`)
- Database representation
- Relationships
- Eloquent features
- Casts & Mutators

**Contoh:**
```php
class User extends Model
{
    use HasUuid, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
```

---

## 🛠️ Komponen Penting

### Form Requests (`app/Http/Requests/`)
Validasi input dari client

**Contoh:**
```php
class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ];
    }
}
```

### Resources (`app/Http/Resources/`)
Transform model data untuk API response

**Contoh:**
```php
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
        ];
    }
}
```

### DTOs (`app/DTOs/`)
Data Transfer Objects untuk komunikasi antar layer

**Contoh:**
```php
class CreateUserDto extends BaseDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
```

### Traits (`app/Traits/`)
- `ApiResponse` - Consistent JSON responses
- `HasUuid` - UUID primary keys
- `HasTimestamps` - Auto timestamps

### Exceptions (`app/Exceptions/`)
- `CustomException` - Base exception
- `ResourceNotFoundException` - 404 errors
- `UnauthorizedException` - 401 errors

---

## 📋 API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Success",
  "data": [],
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {}
}
```

---

## 🔄 Request Flow

```
Client Request
    ↓
Routes (routes/api/*)
    ↓
Controllers (validate via Form Requests)
    ↓
Services (business logic)
    ↓
Repositories (database queries)
    ↓
Models (Eloquent ORM)
    ↓
Database
    ↓
Response (via Resources)
    ↓
Client Response
```

---

## 📝 Best Practices

### 1. **Dependency Injection**
```php
public function __construct(
    private UserService $userService,
    private AuthService $authService
) {}
```

### 2. **Single Responsibility Principle**
- Controllers: Handle HTTP logic only
- Services: Implement business logic
- Repositories: Handle database queries

### 3. **Validation**
```php
public function store(StoreUserRequest $request)
{
    // Data already validated by Form Request
    $user = $this->service->create($request->validated());
    return $this->createdResponse($user);
}
```

### 4. **Error Handling**
```php
try {
    $user = $this->service->findUser($id);
} catch (ResourceNotFoundException $e) {
    return $this->notFoundResponse($e->getMessage());
}
```

### 5. **Type Hinting**
```php
public function store(StoreUserRequest $request): JsonResponse
{
    $user = $this->service->createUser(
        new CreateUserDto(...$request->validated())
    );
    return $this->createdResponse($user);
}
```

---

## 🧪 Testing Structure

### Feature Tests
```php
class UserTest extends TestCase
{
    public function test_can_create_user()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);
    }
}
```

---

## 🚀 Konvensi Naming

| Layer | Konvensi | Contoh |
|-------|----------|--------|
| Controllers | `{Entity}Controller` | `UserController` |
| Services | `{Entity}Service` | `UserService` |
| Repositories | `{Entity}Repository` | `UserRepository` |
| Models | `{Entity}` | `User` |
| Migrations | `create_{table}_table` | `create_users_table` |
| Factories | `{Entity}Factory` | `UserFactory` |
| Requests | `{Action}{Entity}Request` | `StoreUserRequest` |
| Resources | `{Entity}Resource` | `UserResource` |
| DTOs | `{Action}{Entity}Dto` | `CreateUserDto` |
| Policies | `{Entity}Policy` | `UserPolicy` |

---

## 🔑 Key Files

- **routes/api.php** - API route entry point
- **routes/api/*.php** - Feature-specific routes
- **config/app.php** - Application config
- **config/auth.php** - Authentication config
- **phpunit.xml** - Testing configuration
- **.env** - Environment variables

---

## ✅ Next Steps

1. ✅ Create Models dengan migrations
2. ✅ Create Repositories untuk setiap Model
3. ✅ Create Services untuk business logic
4. ✅ Create Controllers dan Routes
5. ✅ Create Form Requests untuk validation
6. ✅ Create Resources untuk API responses
7. ✅ Create Tests untuk coverage

---

Dengan struktur ini, backend Skillbytes akan scalable, maintainable, dan professional! 🎉
