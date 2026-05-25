# User Resume Implementation - Quick Reference

## Fitur yang Diimplementasikan
✅ Multiple resumes per siswa (tidak terbatas 1)
✅ Upload resume sebagai file (PDF, DOC, DOCX, dll)
✅ Simpan resume sebagai text content
✅ Set resume sebagai active/utama (deactivate others)
✅ Full CRUD operations
✅ File auto-cleanup saat delete/replace
✅ User authorization (hanya pemilik bisa akses)

## Files Created

### Model & Migration
- `app/Models/UserResume.php` - Model untuk user resumes
- `database/migrations/2026_05_25_create_user_resumes_table.php` - Migration untuk table

### Controllers
- `app/Http/Controllers/UserResumeController.php` - Semua logic CRUD

### Requests (Validation)
- `app/Http/Requests/StoreUserResumeRequest.php` - Validation untuk create
- `app/Http/Requests/UpdateUserResumeRequest.php` - Validation untuk update

### Resources (Response Formatting)
- `app/Http/Resources/UserResumeResource.php` - Format response JSON

### Routes
- Updated `routes/api/learning.php` dengan 6 endpoints baru

### Model Relationship
- Added `resumes()` relation di `app/Models/User.php`

## API Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/user/resumes` | Get all resumes (dengan filter is_active) |
| POST | `/api/user/resumes` | Create resume (text atau file) |
| GET | `/api/user/resumes/{id}` | Get single resume |
| PUT | `/api/user/resumes/{id}` | Update resume |
| DELETE | `/api/user/resumes/{id}` | Delete resume |
| POST | `/api/user/resumes/{id}/set-active` | Set as active resume |

## Database Table Structure
```
user_resumes
├── id (primary key)
├── user_id (FK → users)
├── title (required, string)
├── content (optional, longtext - untuk text-based)
├── file_url (optional, string - untuk file-based)
├── file_type (optional, string - pdf, doc, etc)
├── description (optional, longtext)
├── is_active (boolean, default true)
├── created_at
├── updated_at
└── indexes: user_id, is_active
```

## File Storage Location
- Base: `/storage/app/public/user-resumes/`
- URL: `http://localhost:8000/storage/user-resumes/[filename]`
- Max Size: 100MB
- Supported: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP

## Key Features

### 1. Multiple Resumes
Siswa bisa menyimpan unlimited resumes dengan judul berbeda:
- CV untuk job applications
- CV untuk scholarship
- Resume untuk freelance portfolio
- dll...

### 2. Flexible Input
Bisa upload:
- **File-based**: Upload actual file (PDF, Word, dll)
- **Text-based**: Tulis content langsung di text area
- **Mixed**: Ada file + ada description text

### 3. Active Resume System
- Only 1 resume bisa `is_active = true` per user
- Set as active otomatis deactivate resume lain
- Berguna untuk quick access resume utama

### 4. Security
- User hanya bisa akses resume miliknya (checked di controller)
- File auto-deleted saat resume dihapus
- Validation ketat untuk input

## Commands to Test

### Run Migration
```bash
php artisan migrate
```

### Create Resume via Artisan Tinker
```bash
php artisan tinker

$user = User::find(1);
$user->resumes()->create([
  'title' => 'CV - Test',
  'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
  'description' => 'Test resume',
  'is_active' => true
]);
```

### Get All Resumes
```bash
$user = User::find(1);
$user->resumes;
```

## Frontend Usage Tips

### 1. Upload Form
```html
<form enctype="multipart/form-data">
  <input type="text" name="title" required />
  <textarea name="content"></textarea>
  <input type="file" name="file" accept=".pdf,.doc,.docx" />
  <textarea name="description"></textarea>
  <input type="checkbox" name="is_active" />
  <button type="submit">Upload Resume</button>
</form>
```

### 2. Display Resumes
```html
<!-- Get all resumes -->
GET /api/user/resumes

<!-- Loop through and display -->
<div v-for="resume in resumes" :key="resume.id">
  <h3>{{ resume.title }}</h3>
  <p>{{ resume.description }}</p>
  <a v-if="resume.file_url" :href="resume.file_url">Download</a>
  <p v-if="resume.content">{{ resume.content }}</p>
</div>
```

### 3. Update Resume
```javascript
// Update title only
PUT /api/user/resumes/1
{ "title": "Updated CV" }

// Replace file
PUT /api/user/resumes/1
FormData: { file: newFile }

// Update text content
PUT /api/user/resumes/1
{ "content": "Updated content..." }
```

## Validation Rules

### Create/Update
- `title`: required (create), string, max 255 chars
- `content`: optional, min 50 chars if provided
- `file`: optional, max 100MB, allowed types: pdf,doc,docx,ppt,pptx,xls,xlsx,zip
- `description`: optional, max 1000 chars
- `is_active`: optional, boolean

### At least one required
- Either `content` (text-based) OR `file` (file-based) should be provided
- Currently validation allows both optional for flexibility

## Notes
- All endpoints require Bearer token authentication
- Files stored at: `/storage/app/public/user-resumes/`
- Database indexes on `user_id` and `is_active` for performance
- Soft deletes not implemented (use hard delete)
- Cascade delete setup: deleting user deletes all resumes

## Next Steps / Enhancements (Optional)
1. Add resume download/preview endpoint
2. Add resume search/filter by title
3. Add resume version history
4. Add resume sharing with mentors
5. Add resume templates/suggestions
6. Add file preview (for PDF, images)
7. Add resume analytics (download count, view count)
8. Add resume status (draft, published, archived)
