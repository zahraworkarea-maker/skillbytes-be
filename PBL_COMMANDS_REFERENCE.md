# 🎓 PBL API - Quick Commands Reference

## 🚀 Project Setup

### Initial Setup
```bash
# Install dependencies
composer install

# Generate app key
php artisan key:generate

# Run all migrations
php artisan migrate

# Create storage symlink
php artisan storage:link

# Generate Swagger documentation
php artisan l5-swagger:generate

# Start dev server
php artisan serve
```

### Database Setup (Tinker)
```bash
php artisan tinker

# Create levels
Level::create(['name' => 'Beginner']);
Level::create(['name' => 'Intermediate']);
Level::create(['name' => 'Advanced']);

# List all levels
Level::all();

# Exit
exit
```

---

## 📚 API Base URL
```
http://localhost:8000/api
```

## 📖 Documentation
```
http://localhost:8000/api/documentation
```

---

## 🔑 Authentication

### Get Token (Login)
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@skillbytes.com",
    "password": "password123"
  }'
```

### Use Token in Header
```bash
# Replace {TOKEN} dengan token dari login response
Authorization: Bearer {TOKEN}

# Example in curl:
curl -H "Authorization: Bearer 1|abcdefghijk..." \
  http://localhost:8000/api/pbl-cases
```

---

## 📋 Common API Calls

### Get All Cases
```bash
curl -X GET http://localhost:8000/api/pbl-cases \
  -H "Authorization: Bearer {TOKEN}"
```

### Get Case by Slug
```bash
curl -X GET http://localhost:8000/api/pbl-cases/system-login-bermasalah-a1b2c3d4 \
  -H "Authorization: Bearer {TOKEN}"
```

### Create Case
```bash
curl -X POST http://localhost:8000/api/admin/pbl-cases \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "case_number": 1,
    "title": "Case Title",
    "level_id": 1,
    "description": "Description",
    "start_date": "2024-01-20 09:00:00",
    "deadline": "2024-01-27 17:00:00"
  }'
```

### Submit Answer
```bash
curl -X POST http://localhost:8000/api/pbl-submissions \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "case_id": 1,
    "answer": "My answer is..."
  }'
```

### Upload Image
```bash
curl -X POST http://localhost:8000/api/admin/upload-image \
  -H "Authorization: Bearer {TOKEN}" \
  -F "image=@/path/to/image.jpg"
```

---

## 🛠️ Development Commands

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Generate New Swagger Docs
```bash
php artisan l5-swagger:generate
```

### Database Refresh (WARNING - Destructive)
```bash
php artisan migrate:refresh
```

### Create New Controller
```bash
php artisan make:controller Api/Admin/YourController --api
```

### Create New Model with Migration
```bash
php artisan make:model YourModel -m
```

### Create New Request Validation
```bash
php artisan make:request StoreYourRequest
```

### Create New Resource
```bash
php artisan make:resource YourResource
```

---

## 📊 Database Queries

### Check Migrations Status
```bash
php artisan migrate:status
```

### Rollback Last Migration
```bash
php artisan migrate:rollback
```

### Tinker - Interactive Shell
```bash
php artisan tinker

# Examples inside tinker:
PblCase::all();
User::find(1)->submissions()->get();
Level::where('name', 'Beginner')->first();
exit
```

### Query Builder Examples
```bash
php artisan tinker

# Get case with sections
$case = PblCase::with('sections.items')->find(1);

# Get user submissions
$user = User::with('submissions')->find(1);

# Check status
$status = PblCaseStatusService::getStatusString($case, $user);

exit
```

---

## 🧪 Testing

### Run Unit Tests
```bash
php artisan test
```

### Run Specific Test
```bash
php artisan test --filter=TestName
```

### Generate Test Coverage
```bash
php artisan test --coverage
```

---

## 📁 File Operations

### List Route Files
```bash
ls routes/api/
```

### View Migrations
```bash
ls database/migrations/ | grep pbl
```

### View Models
```bash
ls app/Models/
```

### View Controllers
```bash
ls app/Http/Controllers/Api/
```

---

## 🔍 Debugging

### Enable Query Log
```php
// Add to bootstrap or middleware
\DB::enableQueryLog();

// Later:
dd(\DB::getQueryLog());
```

### Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

### Check Supervisor Logs (if using queue)
```bash
tail -f storage/logs/horizon.log
```

---

## 📦 Composer Commands

### Update Dependencies
```bash
composer update
```

### Install New Package
```bash
composer require package/name
```

### Update Swagger
```bash
composer update darkaonline/l5-swagger
```

### Check for Security Issues
```bash
composer audit
```

---

## 🚀 Deployment

### Generate Optimized Autoloader
```bash
composer install --optimize-autoloader --no-dev
```

### Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Clear All Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Migrate on Production
```bash
php artisan migrate --force
```

---

## 🐛 Troubleshooting Commands

### Check PHP Version
```bash
php -v
```

### Check Installed Extensions
```bash
php -m
```

### Check Laravel Version
```bash
php artisan --version
```

### Verify Routes
```bash
php artisan route:list
```

### Check Model Relations
```bash
php artisan tinker
User::with('caseProgress', 'submissions')->find(1);
exit
```

### Check Storage Permissions
```bash
ls -la storage/app/public/
chmod -R 775 storage/app/public/pbl
```

---

## 📝 Log Files

### Application Log
```bash
tail -f storage/logs/laravel.log
```

### Check Recent Errors
```bash
tail -50 storage/logs/laravel.log
```

### Clear Logs
```bash
php artisan cache:clear
rm storage/logs/laravel.log*
```

---

## 🔐 Security Commands

### Generate New Application Key
```bash
php artisan key:generate
```

### Hash Password
```bash
php artisan tinker
Hash::make('password123')
exit
```

### Check CORS Configuration
```bash
cat config/cors.php
```

---

## 📊 Performance

### Cache Configuration
```bash
php artisan config:cache
```

### Optimize Autoloader
```bash
composer dump-autoload --optimize
```

### Cache Routes
```bash
php artisan route:cache
```

### Monitor Queries
```php
DB::listen(function ($query) {
    echo $query->sql;
});
```

---

## 🎯 Common Workflows

### Add New Endpoint (Complete)
```bash
# 1. Create migration (if needed)
php artisan make:migration create_table_name

# 2. Create model (if needed)
php artisan make:model ModelName -m

# 3. Create request
php artisan make:request StoreModelRequest

# 4. Create resource
php artisan make:resource ModelResource

# 5. Create controller
php artisan make:controller Api/ModelController --api

# 6. Add route in routes/api/pbl.php

# 7. Add Swagger annotations in controller

# 8. Generate docs
php artisan l5-swagger:generate

# 9. Test di /api/documentation
```

### Debug Failed Request
```bash
# 1. Check logs
tail -f storage/logs/laravel.log

# 2. Enable query log
php artisan tinker
DB::enableQueryLog()
# ... run your request ...
dd(DB::getQueryLog())
exit

# 3. Check validation errors
curl -X POST ... -v

# 4. Check status code
curl -X POST ... -w "\n%{http_code}\n"
```

### Setup Fresh Development Environment
```bash
# 1. Copy example env
cp .env.example .env

# 2. Generate key
php artisan key:generate

# 3. Setup database (update .env DB credentials)
php artisan migrate

# 4. Create storage link
php artisan storage:link

# 5. Generate swagger
php artisan l5-swagger:generate

# 6. Create levels
php artisan tinker
Level::create(['name' => 'Beginner']);
exit

# 7. Start server
php artisan serve
```

---

## 💡 Pro Tips

1. **Use Artisan Tinker untuk quick testing**
   ```bash
   php artisan tinker
   # Bisa langsung interact dengan models
   ```

2. **Gunakan Laravel Debugbar (dev only)**
   ```bash
   composer require barryvdh/laravel-debugbar --dev
   ```

3. **Enable Query Log untuk debugging**
   ```php
   DB::enableQueryLog();
   // ... your code
   dd(DB::getQueryLog());
   ```

4. **Check migration status sebelum deploy**
   ```bash
   php artisan migrate:status
   ```

5. **Always clear cache after deploy**
   ```bash
   php artisan cache:clear && php artisan config:clear
   ```

---

## 📞 Help Commands

```bash
# List all artisan commands
php artisan list

# Help for specific command
php artisan help migrate

# Check Laravel version & environment
php artisan --version
php artisan env

# Check application config
php artisan config:show
```

---

**Last Updated:** January 2024
