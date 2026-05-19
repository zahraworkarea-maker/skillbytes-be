# Question Image Upload - Developer Guide

## Overview
Fitur Question sekarang mendukung upload gambar. Gambar disimpan secara otomatis di storage backend dan path dikembalikan dalam response API.

## Architecture

```
Client (upload image)
    ↓
API Controller (QuestionController)
    ↓
Request Validation (StoreQuestionRequest/UpdateQuestionRequest)
    ↓
Service Layer (QuestionService::handleImageUpload)
    ↓
Storage (public disk: storage/app/public/questions/)
    ↓
Database (questions.image_path)
    ↓
Response (image_path returned to client)
```

## Implementation Details

### 1. File Upload Flow

```php
// Client sends multipart/form-data
questions[0][text] = "Question text"
questions[0][image] = <file>

↓ Validated by StoreQuestionRequest

↓ Processed by QuestionService::createBulkQuestions()
foreach ($questions as $questionData) {
    $questionData = handleImageUpload($questionData);
    Question::create($questionData);
}

↓ Inside handleImageUpload()
if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
    $imagePath = $data['image']->store('questions', 'public');
    $data['image_path'] = $imagePath;
}

↓ Stored in questions table
image_path = "questions/uuid/filename.jpg"
```

### 2. Image Lifecycle

**Creation:**
- Image validated in Request class
- Passed to Service layer
- Stored to public disk
- Path saved to database

**Update:**
- Old image deleted if exists
- New image validated
- New image stored
- New path saved to database

**Deletion:**
- Image file deleted from disk
- Database record deleted

### 3. Storage Configuration

**Disk Configuration (config/filesystems.php):**
```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
    'throw' => false,
]
```

**Storage Link:**
```bash
# Make sure this is created
php artisan storage:link
```

This creates a symbolic link from `public/storage` → `storage/app/public`

### 4. Validation Rules

**Stored in Request Classes:**

```php
// StoreQuestionRequest
'questions.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'

// UpdateQuestionRequest
'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
```

**Rules Breakdown:**
- `nullable` - Image is optional
- `image` - Must be valid image file
- `mimes:jpeg,png,jpg,gif` - Only these formats allowed
- `max:5120` - Maximum 5MB (in KB)

### 5. API Endpoints

#### Create Questions with Images
```
POST /api/assessments/{assessmentId}/questions
Content-Type: multipart/form-data

Body:
questions[0][text] = "Question 1"
questions[0][explanation] = "Explanation 1"
questions[0][image] = <binary file>
questions[1][text] = "Question 2"
questions[1][image] = <binary file>
```

Response:
```json
{
  "success": true,
  "message": "2 questions created successfully",
  "data": {
    "total_created": 2,
    "questions": [
      {
        "id": 1,
        "assessment_id": 5,
        "text": "Question 1",
        "explanation": "Explanation 1",
        "image_path": "questions/abc123def456/question1.jpg"
      }
    ]
  }
}
```

#### Update Question with Image
```
PUT /api/questions/{id}
Content-Type: multipart/form-data

Body:
text = "Updated question?"
explanation = "Updated explanation"
image = <binary file>
```

Response:
```json
{
  "success": true,
  "message": "Question updated successfully",
  "data": {
    "id": 1,
    "text": "Updated question?",
    "explanation": "Updated explanation",
    "image_path": "questions/xyz789abc/updated_image.png"
  }
}
```

### 6. Resource Classes

Images are included in API responses through Resource classes:

**For Students (QuestionResource):**
```php
public function toArray(Request $request): array
{
    return [
        'id' => (string) $this->id,
        'question' => $this->text,
        'image_path' => $this->image_path,
        'options' => OptionResource::collection($this->options),
    ];
}
```

**For Results (QuestionWithAnswerResource):**
```php
public function toArray(Request $request): array
{
    return [
        'id' => (string) $this->id,
        'question' => $this->text,
        'explanation' => $this->explanation,
        'image_path' => $this->image_path,
        'options' => OptionWithAnswerResource::collection($this->options),
    ];
}
```

### 7. Image Cleanup

**Automatic Cleanup Scenarios:**

1. **Question Deleted:**
   ```php
   public function deleteQuestion(Question $question): bool
   {
       if ($question->image_path) {
           Storage::disk('public')->delete($question->image_path);
       }
       return $question->delete();
   }
   ```

2. **Image Updated:**
   ```php
   public function updateQuestion(Question $question, array $data): Question
   {
       if (isset($data['image']) && $data['image']) {
           if ($question->image_path) {
               Storage::disk('public')->delete($question->image_path);
           }
           $data = $this->handleImageUpload($data);
       }
       $question->update($data);
       return $question;
   }
   ```

### 8. Error Handling

**Validation Errors:**
```json
{
  "message": "Validation failed",
  "errors": {
    "questions.0.image": ["Image must be a valid image file"]
  }
}
```

**File Too Large:**
```json
{
  "message": "Validation failed",
  "errors": {
    "questions.0.image": ["Image size must not exceed 5MB"]
  }
}
```

**Invalid Format:**
```json
{
  "message": "Validation failed",
  "errors": {
    "questions.0.image": ["Image must be in jpeg, png, jpg, or gif format"]
  }
}
```

## Usage Examples

### Example 1: Create Single Question with Image
```bash
curl -X POST http://localhost:8000/api/assessments/1/questions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "questions[0][text]=What is photosynthesis?" \
  -F "questions[0][explanation]=Process by which plants make food" \
  -F "questions[0][image]=@./plant.jpg"
```

### Example 2: Create Multiple Questions with Images
```bash
curl -X POST http://localhost:8000/api/assessments/1/questions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "questions[0][text]=Question 1?" \
  -F "questions[0][image]=@./image1.jpg" \
  -F "questions[1][text]=Question 2?" \
  -F "questions[1][explanation]=Explanation for Q2" \
  -F "questions[1][image]=@./image2.png" \
  -F "questions[2][text]=Question 3 without image?" \
  -F "questions[2][image]="
```

### Example 3: Update Question with New Image
```bash
curl -X PUT http://localhost:8000/api/questions/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "text=Updated question text?" \
  -F "image=@./new_image.jpg"
```

### Example 4: Remove Image (Set to null)
```bash
curl -X PUT http://localhost:8000/api/questions/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "text=Question without image"
```

## Testing Checklist

- [ ] Create single question with image
- [ ] Create multiple questions with images
- [ ] Create questions without images (should work)
- [ ] Update question with new image
- [ ] Update question text only (image unchanged)
- [ ] Delete question (verify image deleted from storage)
- [ ] Test image size limit (>5MB should fail)
- [ ] Test invalid image format (should fail)
- [ ] Verify storage path accessibility
- [ ] Test with Postman/Insomnia
- [ ] Test with different image formats (jpg, png, gif)

## Performance Considerations

1. **File Storage:**
   - Images stored locally (consider cloud storage for scale)
   - Path format: `questions/{uuid}/{filename}.{ext}`
   - UUID prevents naming conflicts

2. **Database:**
   - Only path stored (not file content)
   - Minimal database impact
   - Index on image_path recommended for large datasets

3. **Optimization Options:**
   - Image resizing on upload
   - Image compression
   - CDN integration for image delivery
   - S3/cloud storage for scalability

## Security Notes

1. **File Validation:**
   - MIME type validation
   - File size limit enforced
   - Extension whitelist

2. **Storage Path:**
   - Files stored outside public directory
   - Accessible through symbolic link
   - Can be configured for different visibility levels

3. **Recommendations:**
   - Validate file contents (not just extension)
   - Consider rate limiting uploads
   - Add virus scanning for production
   - Implement access controls if needed

## Troubleshooting

**Issue: Storage link not working**
```bash
# Solution: Create the symbolic link
php artisan storage:link
```

**Issue: Images not visible in response**
- Check resource class includes `image_path`
- Verify migration applied
- Check database has `image_path` column

**Issue: Upload fails with permission error**
```bash
# Solution: Fix permissions
chmod -R 775 storage/
```

**Issue: Images not found after upload**
- Verify storage disk configuration
- Check file actually exists in `storage/app/public/questions/`
- Ensure symbolic link exists

## Related Files

- [QUESTION_IMAGE_UPLOAD_GUIDE.md](QUESTION_IMAGE_UPLOAD_GUIDE.md) - API Testing Guide
- [QUESTION_IMAGE_UPLOAD_IMPLEMENTATION.md](QUESTION_IMAGE_UPLOAD_IMPLEMENTATION.md) - Implementation Summary
- `app/Services/QuestionService.php` - File handling logic
- `app/Http/Controllers/Api/Admin/QuestionController.php` - API endpoints
- `config/filesystems.php` - Storage configuration
