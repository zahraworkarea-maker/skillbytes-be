# Question Image Upload - API Testing Guide

## Feature Overview
Endpoint untuk Question sekarang mendukung upload gambar. Gambar akan disimpan secara otomatis dan path dikembalikan dalam response.

## Endpoints

### 1. Create Multiple Questions with Images
**POST** `/assessments/{assessmentId}/questions`

**Request Type:** multipart/form-data

**Parameters:**
```
questions[0][text] = "What is 2+2?"
questions[0][explanation] = "Simple arithmetic" (optional)
questions[0][image] = <image_file> (optional)

questions[1][text] = "What is the capital of France?"
questions[1][image] = <image_file> (optional)
...
```

**Validation Rules:**
- `questions` - required, array, min 1, max 1000 items
- `questions.*.text` - required, string
- `questions.*.explanation` - optional, string
- `questions.*.image` - optional, image file (jpeg, png, jpg, gif), max 5MB

**Example Response (201 Created):**
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
        "text": "What is 2+2?",
        "explanation": "Simple arithmetic",
        "image_path": "questions/abc123/image1.jpg"
      },
      {
        "id": 2,
        "assessment_id": 5,
        "text": "What is the capital of France?",
        "explanation": null,
        "image_path": "questions/def456/image2.png"
      }
    ]
  }
}
```

### 2. Update Question
**PUT** `/questions/{id}`

**Request Type:** multipart/form-data

**Parameters:**
```
text = "Updated question text?" (optional)
explanation = "Updated explanation" (optional)
image = <image_file> (optional)
```

**Validation Rules:**
- `text` - optional, string
- `explanation` - optional, string
- `image` - optional, image file (jpeg, png, jpg, gif), max 5MB

**Example Response (200 OK):**
```json
{
  "success": true,
  "message": "Question updated successfully",
  "data": {
    "id": 1,
    "text": "Updated question text?",
    "explanation": "Updated explanation",
    "image_path": "questions/xyz789/updated_image.jpg"
  }
}
```

## Testing with cURL

### Create Question with Image
```bash
curl -X POST http://localhost:8000/api/assessments/1/questions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "questions[0][text]=What is the capital of France?" \
  -F "questions[0][explanation]=Paris is the capital city" \
  -F "questions[0][image]=@/path/to/image.jpg"
```

### Update Question with New Image
```bash
curl -X PUT http://localhost:8000/api/questions/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "text=Updated question?" \
  -F "image=@/path/to/new_image.png"
```

## Testing with Postman

1. Create new request (POST)
2. Set URL: `http://localhost:8000/api/assessments/{assessmentId}/questions`
3. Go to "Body" tab
4. Select "form-data"
5. Add fields:
   - Key: `questions[0][text]`, Value: "Question text"
   - Key: `questions[0][image]`, Select file from computer
6. Add Authorization header with Bearer token
7. Click Send

## Image Storage

**Storage Location:**
- Path: `storage/app/public/questions/`
- Accessible URL: `/storage/questions/...`

**Image Path Format:**
- Stored as: `questions/{uuid}/{filename}.{extension}`
- Returned in API response as: `image_path`

## Important Notes

1. **Image is Optional**: Questions can be created without images
2. **File Size Limit**: Maximum 5MB per image
3. **Allowed Formats**: JPEG, PNG, JPG, GIF
4. **Automatic Cleanup**: Old images are deleted when question is updated or deleted
5. **Storage Path**: Images are publicly accessible via `/storage/questions/...`

## Error Examples

**Invalid Image File:**
```json
{
  "success": false,
  "message": "Image must be a valid image file"
}
```

**File Too Large:**
```json
{
  "success": false,
  "message": "Image size must not exceed 5MB"
}
```

**Invalid Assessment:**
```json
{
  "success": false,
  "message": "Assessment not found"
}
```
