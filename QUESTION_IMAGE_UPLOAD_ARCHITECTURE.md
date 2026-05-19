# Question Image Upload - Architecture Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENT APPLICATION                       │
│                   (Web/Mobile/Desktop)                           │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                    multipart/form-data
                (questions[0][text], image file)
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                    LARAVEL API SERVER                            │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │         QuestionController (API Endpoint)                │  │
│  │  POST   /assessments/{id}/questions                      │  │
│  │  PUT    /questions/{id}                                  │  │
│  │  DELETE /questions/{id}                                  │  │
│  └───────────────────┬──────────────────────────────────────┘  │
│                      │                                           │
│  ┌──────────────────▼──────────────────┐                       │
│  │  Request Validation Layer           │                       │
│  │  - StoreQuestionRequest             │                       │
│  │  - UpdateQuestionRequest            │                       │
│  │                                     │                       │
│  │  Validates:                         │                       │
│  │  ✓ questions array                  │                       │
│  │  ✓ text (required)                  │                       │
│  │  ✓ explanation (optional)           │                       │
│  │  ✓ image file (optional)            │                       │
│  │    - Type: image/*                  │                       │
│  │    - Formats: jpeg, png, jpg, gif   │                       │
│  │    - Max: 5MB                       │                       │
│  └───────────────────┬──────────────────┘                       │
│                      │                                           │
│  ┌──────────────────▼──────────────────┐                       │
│  │  Service Layer                      │                       │
│  │  (QuestionService)                  │                       │
│  │                                     │                       │
│  │  Methods:                           │                       │
│  │  • createBulkQuestions()            │                       │
│  │  • updateQuestion()                 │                       │
│  │  • deleteQuestion()                 │                       │
│  │  • handleImageUpload()              │                       │
│  └────────┬──────────────┬─────────────┘                       │
│           │              │                                      │
│  ┌────────▼──┐  ┌──────▼──────┐                               │
│  │  Database │  │Storage Layer │                               │
│  │  (MySQL)  │  │(Local Disk)  │                               │
│  └───────────┘  └──────────────┘                               │
└─────────────────────────────────────────────────────────────────┘
         │                           │
         ▼                           ▼
    ┌─────────────┐        ┌──────────────────┐
    │  questions  │        │  storage/app/    │
    │   table     │        │  public/          │
    │             │        │  questions/       │
    │ ┌─────────┐ │        │                  │
    │ │id       │ │        │ ┌──────────────┐ │
    │ │text     │ │        │ │uuid/img.jpg  │ │
    │ │expl.    │ │        │ ├──────────────┤ │
    │ │image_   │◄────────►│ │uuid/img.png  │ │
    │ │path     │ │        │ ├──────────────┤ │
    │ │created  │ │        │ │uuid/img.gif  │ │
    │ │updated  │ │        │ └──────────────┘ │
    │ └─────────┘ │        └──────────────────┘
    └─────────────┘
```

## Data Flow

### 1. Create Question with Image Flow

```
Client sends multipart request
        ↓
StoreQuestionRequest validates
        ↓
QuestionController::store() receives validated data
        ↓
QuestionService::createBulkQuestions() processes
        ↓
For each question:
  ├─ handleImageUpload() checks if image exists
  ├─ If image exists:
  │   ├─ Store to public disk
  │   ├─ Get storage path
  │   └─ Add path to data array
  ├─ Question::create() saves to database
  └─ Retrieve created record
        ↓
Format response with image_path
        ↓
Return to client
```

### 2. Update Question with Image Flow

```
Client sends multipart request
        ↓
UpdateQuestionRequest validates
        ↓
QuestionController::update() receives data
        ↓
Find question by ID
        ↓
QuestionService::updateQuestion() processes
        ↓
If new image provided:
  ├─ Delete old image from storage
  ├─ handleImageUpload() processes new image
  └─ Store new image
        ↓
$question->update() saves to database
        ↓
Return updated record with new image_path
```

### 3. Delete Question Flow

```
Client sends DELETE request
        ↓
QuestionController::destroy() receives
        ↓
Find question by ID
        ↓
QuestionService::deleteQuestion() processes
        ↓
If image_path exists:
  └─ Delete image from storage
        ↓
$question->delete() removes from database
        ↓
Return success response
```

## File Storage Structure

```
storage/
├── app/
│   └── public/
│       └── questions/
│           ├── a1b2c3d4e5f6/
│           │   ├── image1.jpg
│           │   └── image2.png
│           ├── x9y8z7w6v5u4/
│           │   └── diagram.gif
│           └── p3o2n1m0l9k8/
│               └── photo.jpg
└── [other directories]

Accessible via:
/storage/questions/a1b2c3d4e5f6/image1.jpg
/storage/questions/x9y8z7w6v5u4/diagram.gif
```

## Database Schema

### questions table (after migration)

```
┌──────────────────────────────┐
│         questions            │
├──────────────────────────────┤
│ id              INT (PK)     │ ◄─── Auto-increment
│ assessment_id   INT (FK)     │ ◄─── Foreign key to assessments
│ text            TEXT         │ ◄─── Question content
│ explanation     TEXT NULL    │ ◄─── Optional explanation
│ image_path      VARCHAR NULL │ ◄─── NEW: Path to image
│ created_at      TIMESTAMP    │ ◄─── Creation timestamp
│ updated_at      TIMESTAMP    │ ◄─── Update timestamp
└──────────────────────────────┘
```

## API Response Structure

### Create Questions Response

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
        "text": "Question text",
        "explanation": "Explanation",
        "image_path": "questions/uuid1/image.jpg"
      },
      {
        "id": 2,
        "assessment_id": 5,
        "text": "Another question",
        "explanation": null,
        "image_path": "questions/uuid2/photo.png"
      }
    ]
  }
}
```

### Question Resource Structure

```
QuestionResource (for students)
├── id
├── question (text)
├── image_path ◄─── NEW
└── options[]
    └── option details

QuestionWithAnswerResource (for results)
├── id
├── question (text)
├── explanation
├── image_path ◄─── NEW
└── options[]
    └── option details with is_correct
```

## Error Handling Flow

```
Request received
        ↓
Validate in Request class
        ↓
If validation fails:
  ├─ Image not a valid file
  ├─ Image format not allowed
  ├─ Image size exceeds 5MB
  └─ Return 422 with errors
        ↓
If validation passes:
  ├─ Process in controller
  ├─ Handle in service layer
  ├─ Store in database
  └─ Return 201 with data

If exception during processing:
  ├─ Catch in controller try-catch
  ├─ Return 400 with error message
  └─ Log error
```

## Directory Structure (Project Files)

```
skillbytes-be/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/Admin/
│   │   │       └── QuestionController.php ◄─── UPDATED
│   │   ├── Requests/
│   │   │   ├── StoreQuestionRequest.php ◄─── UPDATED
│   │   │   └── UpdateQuestionRequest.php ◄─── UPDATED
│   │   └── Resources/
│   │       ├── QuestionResource.php ◄─── UPDATED
│   │       └── QuestionWithAnswerResource.php ◄─── UPDATED
│   ├── Models/
│   │   └── Question.php ◄─── UPDATED
│   └── Services/
│       └── QuestionService.php ◄─── UPDATED
├── database/
│   └── migrations/
│       └── 2026_05_19_000001_add_image_path_to_questions_table.php ◄─── NEW
├── storage/
│   └── app/public/
│       └── questions/ ◄─── Image storage directory
├── config/
│   └── filesystems.php ◄─── Configuration reference
└── Documentation files
    ├── QUESTION_IMAGE_UPLOAD_GUIDE.md ◄─── NEW
    ├── QUESTION_IMAGE_UPLOAD_IMPLEMENTATION.md ◄─── NEW
    └── QUESTION_IMAGE_UPLOAD_DEVELOPER_GUIDE.md ◄─── NEW
```

## Integration Points

```
┌─────────────────────┐
│  Assessment Module  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Question Module    │ ◄─── Handles images
├─────────────────────┤
│ • Create questions  │
│ • Update questions  │ ◄─── NEW: Upload images
│ • Delete questions  │ ◄─── NEW: Clean up images
│ • List questions    │ ◄─── NEW: Return image paths
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Option Module      │
└─────────────────────┘
```

## Scalability Considerations

```
Current Architecture (Local Storage)
├─ Suitable for: Development, small-scale deployment
├─ Max: ~1000s of images
└─ Limitation: Single server only

Future Improvements
├─ Cloud Storage (S3/GCS)
│  └─ Unlimited scalability
├─ CDN Integration
│  └─ Faster image delivery
├─ Image Resizing Service
│  └─ Reduced storage size
└─ Image Compression
   └─ Optimized file sizes
```
