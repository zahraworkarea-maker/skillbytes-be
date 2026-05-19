# Question Image Upload Feature - Implementation Summary

## Date: May 19, 2026

### Feature Description
Implementasi fitur untuk menambahkan gambar pada Question. Gambar akan disimpan di backend dan dikembalikan sebagai path dalam response API.

---

## Files Modified/Created

### 1. ✅ Database Migration (CREATED)
**File:** `database/migrations/2026_05_19_000001_add_image_path_to_questions_table.php`

**Changes:**
- Tambah kolom `image_path` (nullable string) ke tabel `questions`
- Kolom diposisikan setelah `explanation`
- Migration sudah dijalankan ✓

### 2. ✅ Model Update
**File:** `app/Models/Question.php`

**Changes:**
```php
protected $fillable = [
    'assessment_id',
    'text',
    'explanation',
    'image_path',  // NEW
];
```

### 3. ✅ Request Validation - Store
**File:** `app/Http/Requests/StoreQuestionRequest.php`

**Changes:**
- Tambah validasi `questions.*.image` untuk handle file upload
- Validasi: `nullable|image|mimes:jpeg,png,jpg,gif|max:5120`
- Update error messages

### 4. ✅ Request Validation - Update
**File:** `app/Http/Requests/UpdateQuestionRequest.php`

**Changes:**
- Tambah validasi `image` field
- Validasi: `nullable|image|mimes:jpeg,png,jpg,gif|max:5120`

### 5. ✅ Service Layer
**File:** `app/Services/QuestionService.php`

**Changes:**
- Tambah `handleImageUpload()` private method untuk:
  - Process uploaded file
  - Store ke disk 'public' di path 'questions'
  - Return file path
- Update `createQuestion()` untuk call `handleImageUpload()`
- Update `createBulkQuestions()` untuk handle image per question
- Update `updateQuestion()` untuk:
  - Delete old image jika ada
  - Upload dan store image baru
- Update `deleteQuestion()` untuk cleanup image file

**New Imports:**
```php
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
```

### 6. ✅ Controller Update
**File:** `app/Http/Controllers/Api/Admin/QuestionController.php`

**Changes:**
- Update `store()` method response untuk include `image_path`
- Update `update()` method response untuk include `image_path`
- Update Swagger documentation untuk kedua endpoint

### 7. ✅ Resource Classes - Student View
**File:** `app/Http/Resources/QuestionResource.php`

**Changes:**
```php
return [
    'id' => (string) $this->id,
    'question' => $this->text,
    'image_path' => $this->image_path,  // NEW
    'options' => OptionResource::collection($this->options),
];
```

### 8. ✅ Resource Classes - Results View
**File:** `app/Http/Resources/QuestionWithAnswerResource.php`

**Changes:**
```php
return [
    'id' => (string) $this->id,
    'question' => $this->text,
    'explanation' => $this->explanation,
    'image_path' => $this->image_path,  // NEW
    'options' => OptionWithAnswerResource::collection($this->options),
];
```

### 9. ✅ Documentation
**File:** `QUESTION_IMAGE_UPLOAD_GUIDE.md` (CREATED)
- Comprehensive API testing guide
- cURL examples
- Postman setup instructions
- Error handling guide

---

## Technical Specifications

### Image Storage
- **Disk:** public (Laravel storage)
- **Path:** `storage/app/public/questions/`
- **URL Pattern:** `/storage/questions/{uuid}/{filename}.{ext}`
- **Auto-cleanup:** Images deleted when question/assessment deleted

### Validation Rules
| Field | Type | Required | Constraints |
|-------|------|----------|-------------|
| questions | array | ✓ | min: 1, max: 1000 |
| questions.*.text | string | ✓ | - |
| questions.*.explanation | string | ✗ | - |
| questions.*.image | file | ✗ | jpeg,png,jpg,gif, max 5MB |
| image (update) | file | ✗ | jpeg,png,jpg,gif, max 5MB |

### API Response
```json
{
  "success": true,
  "message": "... questions created successfully",
  "data": {
    "total_created": 2,
    "questions": [
      {
        "id": 1,
        "assessment_id": 5,
        "text": "Question text",
        "explanation": "Explanation",
        "image_path": "questions/uuid/filename.jpg"
      }
    ]
  }
}
```

---

## Testing Checklist

- [x] Migration applied successfully
- [x] Model updated with fillable
- [x] Request validation rules set
- [x] Service handles file upload
- [x] Controller responses updated
- [x] Resource classes include image_path
- [x] Swagger documentation updated
- [ ] Manual API testing with Postman
- [ ] Test with bulk questions + images
- [ ] Test image update/delete
- [ ] Verify storage path accessibility

---

## Database Schema

### questions table (after migration)
```
id               - integer
assessment_id    - integer (FK)
text             - text
explanation      - text (nullable)
image_path       - string (nullable) ← NEW
created_at       - timestamp
updated_at       - timestamp
```

---

## Next Steps

1. **Testing:**
   - Run manual tests with Postman/Insomnia
   - Test all CRUD operations with images
   - Verify image cleanup on delete

2. **Optimization (Optional):**
   - Add image resizing/compression
   - Add image CDN integration
   - Add image validation for dimensions

3. **Documentation:**
   - Update main API documentation
   - Add to Swagger UI if not auto-generated
   - Update client documentation

---

## Rollback Instructions

If needed to rollback:
```bash
php artisan migrate:rollback --step=1
```

This will remove the `image_path` column from the `questions` table.

---

## Status: ✅ COMPLETE

All implementation tasks completed successfully. The feature is ready for testing and deployment.
