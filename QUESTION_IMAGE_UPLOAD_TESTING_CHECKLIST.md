# Question Image Upload - Testing Checklist

**Date:** May 19, 2026  
**Feature:** Question Image Upload  
**Status:** Ready for Testing

---

## Pre-Testing Verification

### Environment Setup
- [ ] Migration applied successfully: `php artisan migrate`
- [ ] Database has `image_path` column in questions table
- [ ] Storage link created: `php artisan storage:link`
- [ ] Storage directory exists: `storage/app/public/questions/`
- [ ] Storage directory is writable: `chmod -R 775 storage/`
- [ ] Laravel is running on `http://localhost:8000`
- [ ] Authentication token available for testing

### Code Verification
- [ ] QuestionService has `handleImageUpload()` method
- [ ] StoreQuestionRequest has image validation rule
- [ ] UpdateQuestionRequest has image validation rule
- [ ] Question model has `image_path` in fillable
- [ ] QuestionController returns `image_path` in responses
- [ ] Resource classes include `image_path`

---

## Functional Testing

### Test Case 1: Create Single Question with Image
**Endpoint:** `POST /api/assessments/{assessmentId}/questions`

**Steps:**
1. [ ] Open Postman
2. [ ] Create new POST request
3. [ ] Set URL: `http://localhost:8000/api/assessments/1/questions`
4. [ ] Set Body to form-data
5. [ ] Add fields:
   - [ ] `questions[0][text]` = "What is 2+2?"
   - [ ] `questions[0][image]` = Select a JPG file (< 5MB)
6. [ ] Add Authorization header with Bearer token
7. [ ] Send request

**Expected Result:**
- [ ] Status 201 Created
- [ ] Response includes `image_path` field
- [ ] Image file exists in `storage/app/public/questions/`
- [ ] Image accessible at `/storage/questions/{uuid}/{filename}.jpg`

**Response Example:**
```json
{
  "success": true,
  "message": "1 questions created successfully",
  "data": {
    "total_created": 1,
    "questions": [
      {
        "id": XX,
        "assessment_id": 1,
        "text": "What is 2+2?",
        "explanation": null,
        "image_path": "questions/abc123/image.jpg"
      }
    ]
  }
}
```

### Test Case 2: Create Multiple Questions, Some with Images
**Endpoint:** `POST /api/assessments/{assessmentId}/questions`

**Steps:**
1. [ ] Create new POST request
2. [ ] Set Body to form-data
3. [ ] Add fields:
   - [ ] `questions[0][text]` = "Question 1"
   - [ ] `questions[0][image]` = Select PNG file
   - [ ] `questions[1][text]` = "Question 2"
   - [ ] `questions[1][explanation]` = "This one has no image"
   - [ ] `questions[2][text]` = "Question 3"
   - [ ] `questions[2][image]` = Select GIF file
4. [ ] Send request

**Expected Result:**
- [ ] Status 201 Created
- [ ] Q1 has image_path
- [ ] Q2 has image_path = null
- [ ] Q3 has image_path
- [ ] All images stored correctly

### Test Case 3: Create Question Without Image
**Endpoint:** `POST /api/assessments/{assessmentId}/questions`

**Steps:**
1. [ ] Create new POST request
2. [ ] Set Body to form-data
3. [ ] Add fields:
   - [ ] `questions[0][text]` = "Question without image"
   - [ ] `questions[0][explanation]` = "No image needed"
4. [ ] Send request

**Expected Result:**
- [ ] Status 201 Created
- [ ] image_path = null
- [ ] Question created successfully

### Test Case 4: Update Question Add Image
**Endpoint:** `PUT /api/questions/{questionId}`

**Steps:**
1. [ ] Create PUT request
2. [ ] Set URL: `http://localhost:8000/api/questions/1`
3. [ ] Set Body to form-data
4. [ ] Add fields:
   - [ ] `text` = "Updated question?"
   - [ ] `image` = Select image file
5. [ ] Send request

**Expected Result:**
- [ ] Status 200 OK
- [ ] Text updated
- [ ] image_path has new value
- [ ] New image exists in storage
- [ ] Old image deleted from storage

### Test Case 5: Update Question Replace Image
**Endpoint:** `PUT /api/questions/{questionId}`

**Steps:**
1. [ ] Use question that already has image
2. [ ] Create PUT request to that question
3. [ ] Add new image in form-data
4. [ ] Send request

**Expected Result:**
- [ ] Status 200 OK
- [ ] image_path updated with new filename
- [ ] New image in storage
- [ ] Old image deleted from storage

### Test Case 6: Update Question Remove Image
**Endpoint:** `PUT /api/questions/{questionId}`

**Steps:**
1. [ ] Use question with image
2. [ ] Create PUT request
3. [ ] Set Body to form-data
4. [ ] Add only `text` field (no image field)
5. [ ] Send request

**Expected Result:**
- [ ] Status 200 OK
- [ ] Text updated
- [ ] image_path unchanged (old image kept)

### Test Case 7: Delete Question with Image
**Endpoint:** `DELETE /api/questions/{questionId}`

**Steps:**
1. [ ] Get questionId with image
2. [ ] Create DELETE request
3. [ ] Send request

**Expected Result:**
- [ ] Status 200 OK
- [ ] Question deleted from database
- [ ] Image deleted from storage
- [ ] Verify file doesn't exist in `storage/app/public/questions/`

### Test Case 8: Delete Question without Image
**Endpoint:** `DELETE /api/questions/{questionId}`

**Steps:**
1. [ ] Get questionId without image (image_path = null)
2. [ ] Create DELETE request
3. [ ] Send request

**Expected Result:**
- [ ] Status 200 OK
- [ ] Question deleted successfully
- [ ] No errors

---

## Validation Testing

### Test Case 9: Invalid Image Format
**Endpoint:** `POST /api/assessments/{assessmentId}/questions`

**Steps:**
1. [ ] Create POST request
2. [ ] Add form-data:
   - [ ] `questions[0][text]` = "Test"
   - [ ] `questions[0][image]` = Select a PDF or TXT file
3. [ ] Send request

**Expected Result:**
- [ ] Status 422 Unprocessable Entity
- [ ] Error message: "Image must be in jpeg, png, jpg, or gif format"

### Test Case 10: Image Too Large
**Endpoint:** `POST /api/assessments/{assessmentId}/questions`

**Steps:**
1. [ ] Create POST request
2. [ ] Add form-data:
   - [ ] `questions[0][text]` = "Test"
   - [ ] `questions[0][image]` = Select image > 5MB
3. [ ] Send request

**Expected Result:**
- [ ] Status 422 Unprocessable Entity
- [ ] Error message: "Image size must not exceed 5MB"

### Test Case 11: Invalid Image File
**Endpoint:** `POST /api/assessments/{assessmentId}/questions`

**Steps:**
1. [ ] Create POST request
2. [ ] Add form-data:
   - [ ] `questions[0][text]` = "Test"
   - [ ] `questions[0][image]` = Rename TXT file to .jpg
3. [ ] Send request

**Expected Result:**
- [ ] Status 422 Unprocessable Entity
- [ ] Error message: "Image must be a valid image file"

### Test Case 12: Missing Required Fields
**Endpoint:** `POST /api/assessments/{assessmentId}/questions`

**Steps:**
1. [ ] Create POST request
2. [ ] Add form-data:
   - [ ] `questions[0][image]` = Select image (without text)
3. [ ] Send request

**Expected Result:**
- [ ] Status 422 Unprocessable Entity
- [ ] Error message: "Question text is required"

---

## Integration Testing

### Test Case 13: Get Assessment Questions (with images)
**Endpoint:** `GET /api/assessments/{assessmentId}/questions`

**Steps:**
1. [ ] Create GET request
2. [ ] Send request

**Expected Result:**
- [ ] Status 200 OK
- [ ] Questions list includes image_path field
- [ ] Can verify image paths are correct

### Test Case 14: Get Question Details
**Endpoint:** `GET /api/questions/{questionId}`

**Steps:**
1. [ ] Create GET request for question with image
2. [ ] Send request

**Expected Result:**
- [ ] Status 200 OK
- [ ] Response includes image_path
- [ ] image_path is correct and accessible

### Test Case 15: Student View (QuestionResource)
**Where:** During assessment attempt

**Steps:**
1. [ ] Take a test with questions that have images
2. [ ] Verify images display correctly
3. [ ] Check that `image_path` is included in response

**Expected Result:**
- [ ] Images load correctly
- [ ] image_path returned in API response
- [ ] Image accessible at returned path

### Test Case 16: Results View (QuestionWithAnswerResource)
**Where:** After assessment submission

**Steps:**
1. [ ] View assessment results
2. [ ] Verify images are shown with explanations
3. [ ] Check that image_path is included

**Expected Result:**
- [ ] Images display with correct answers
- [ ] image_path returned in API response
- [ ] Image accessible at returned path

---

## Performance Testing

### Test Case 17: Bulk Upload Performance
**Endpoint:** `POST /api/assessments/{assessmentId}/questions`

**Steps:**
1. [ ] Create POST with 100 questions, 50 with images
2. [ ] Measure response time
3. [ ] Verify all uploaded correctly

**Expected Result:**
- [ ] Response time < 10 seconds
- [ ] All 100 questions created
- [ ] All 50 images stored
- [ ] Storage not corrupted

### Test Case 18: Large Image Handling
**Endpoint:** `POST /api/assessments/{assessmentId}/questions`

**Steps:**
1. [ ] Create POST with 5MB image (at limit)
2. [ ] Measure upload time
3. [ ] Verify image stored correctly

**Expected Result:**
- [ ] Accepts 5MB image
- [ ] Image stored completely
- [ ] No corruption

---

## Storage Testing

### Test Case 19: Image Storage Location
**Step:**
1. [ ] Navigate to `storage/app/public/questions/`
2. [ ] Verify directory structure
3. [ ] Check files exist

**Expected Result:**
- [ ] Directory exists: `storage/app/public/questions/`
- [ ] Files organized in UUID subdirectories
- [ ] File names preserved with extensions

### Test Case 20: Storage Accessibility
**Steps:**
1. [ ] Get image_path from API response
2. [ ] Build URL: `/storage/questions/{path}`
3. [ ] Open in browser
4. [ ] Verify image displays

**Expected Result:**
- [ ] Image accessible via HTTP
- [ ] Image displays correctly
- [ ] No 404 errors

### Test Case 21: Symbolic Link Verification
**Steps:**
1. [ ] Check if `public/storage` exists
2. [ ] Verify it's a symbolic link
3. [ ] Check target is `storage/app/public`

**Expected Result:**
- [ ] Symbolic link exists
- [ ] Points to correct directory
- [ ] Images accessible

---

## Error Handling Testing

### Test Case 22: Database Error
**Steps:**
1. [ ] Disconnect database
2. [ ] Try to create question with image
3. [ ] Observe error handling

**Expected Result:**
- [ ] Status 400 Bad Request
- [ ] Error message returned
- [ ] No partial files left

### Test Case 23: Storage Permission Error
**Steps:**
1. [ ] Remove write permission: `chmod 444 storage/app/public/questions/`
2. [ ] Try to upload image
3. [ ] Observe error handling
4. [ ] Restore permissions: `chmod 775 storage/app/public/questions/`

**Expected Result:**
- [ ] Status 400 Bad Request
- [ ] Error message about permissions
- [ ] Upload fails gracefully

### Test Case 24: Disk Space Error
**Steps:**
1. [ ] Fill up storage (simulate)
2. [ ] Try to upload image
3. [ ] Observe error handling

**Expected Result:**
- [ ] Status 400 Bad Request
- [ ] Error message about disk space
- [ ] Upload fails gracefully

---

## Cleanup Testing

### Test Case 25: Clean Storage
**Steps:**
1. [ ] Create questions with images
2. [ ] Delete all questions
3. [ ] Verify images deleted

**Expected Result:**
- [ ] All images removed from storage
- [ ] Directory empty (except for UUID subdirs)
- [ ] No orphaned files

---

## Summary

**Total Test Cases:** 25

**Test Categories:**
- Functional: 8
- Validation: 4
- Integration: 4
- Performance: 2
- Storage: 3
- Error Handling: 3
- Cleanup: 1

**Success Criteria:**
- [ ] All test cases pass
- [ ] No orphaned files in storage
- [ ] API responses match documentation
- [ ] Performance acceptable
- [ ] Error handling graceful

---

## Sign-Off

| Role | Name | Date | Sign |
|------|------|------|------|
| Developer | | | |
| QA/Tester | | | |
| Project Lead | | | |

---

## Notes

- Keep Postman collection for regression testing
- Document any issues found
- Update documentation if needed
- Prepare for deployment after sign-off
