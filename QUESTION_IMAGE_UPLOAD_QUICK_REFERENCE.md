# Question Image Upload - Quick Reference Card

## 🎯 At a Glance

**Feature:** Upload gambar untuk Questions  
**Status:** ✅ Complete & Ready for Testing  
**Date:** May 19, 2026  

---

## 📍 Key Endpoints

### POST Create Questions
```bash
POST /api/assessments/{id}/questions
Content-Type: multipart/form-data

Form Data:
- questions[0][text]
- questions[0][explanation]
- questions[0][image] (file)
```

### PUT Update Question
```bash
PUT /api/questions/{id}
Content-Type: multipart/form-data

Form Data:
- text (optional)
- explanation (optional)
- image (file, optional)
```

### DELETE Delete Question
```bash
DELETE /api/questions/{id}

Automatic image cleanup ✓
```

---

## 🔍 Validation Rules

| Field | Type | Size | Formats | Required |
|-------|------|------|---------|----------|
| text | String | - | - | ✓ |
| explanation | String | - | - | ✗ |
| image | File | ≤5MB | jpeg,png,jpg,gif | ✗ |

---

## 💾 Storage

- **Disk:** `storage/app/public/questions/`
- **URL:** `/storage/questions/{uuid}/{filename}`
- **Structure:** UUID subdirectories prevent conflicts
- **Auto-cleanup:** Old images deleted on update/delete

---

## 🔧 Database

### New Column
```sql
ALTER TABLE questions ADD COLUMN image_path VARCHAR(255) NULL;
```

### Query
```sql
SELECT id, text, explanation, image_path FROM questions;
```

---

## 📦 Files Modified

| File | Change | Impact |
|------|--------|--------|
| Question.php | Added fillable | ✓ |
| StoreQuestionRequest.php | Added validation | ✓ |
| UpdateQuestionRequest.php | Added validation | ✓ |
| QuestionService.php | Added upload logic | ✓ |
| QuestionController.php | Updated responses | ✓ |
| QuestionResource.php | Added image_path | ✓ |
| QuestionWithAnswerResource.php | Added image_path | ✓ |
| Migration | Added column | ✓ |

---

## ✅ Setup Commands

```bash
# Apply migration
php artisan migrate

# Create storage link
php artisan storage:link

# Fix permissions
chmod -R 775 storage/

# Clear cache
php artisan cache:clear
```

---

## 🧪 Quick Test

### Postman
1. **Method:** POST
2. **URL:** `http://localhost:8000/api/assessments/1/questions`
3. **Body:** form-data
4. **Fields:**
   - `questions[0][text]` = "Test question?"
   - `questions[0][image]` = (select file)
5. **Header:** `Authorization: Bearer TOKEN`
6. **Send & Check:** 201 response with image_path

---

## 📊 Response Example

```json
{
  "success": true,
  "message": "1 questions created successfully",
  "data": {
    "total_created": 1,
    "questions": [
      {
        "id": 1,
        "assessment_id": 1,
        "text": "Test question?",
        "explanation": null,
        "image_path": "questions/abc123/test.jpg"
      }
    ]
  }
}
```

---

## ⚠️ Common Issues

| Issue | Solution |
|-------|----------|
| 404 on storage link | Run `php artisan storage:link` |
| Permission denied | Run `chmod -R 775 storage/` |
| Image not showing | Check storage directory has files |
| Upload fails | Check file size < 5MB & format is image |
| Database error | Run `php artisan migrate` |

---

## 🚀 Deployment

```bash
# 1. Backup
php artisan backup:run

# 2. Migrate
php artisan migrate

# 3. Link storage
php artisan storage:link

# 4. Permissions
chmod -R 775 storage/

# 5. Cache clear
php artisan cache:clear

# 6. Test
# Use QUESTION_IMAGE_UPLOAD_TESTING_CHECKLIST.md
```

---

## 📚 Documentation

| Doc | Purpose |
|-----|---------|
| [README.md](#) | Overview & navigation |
| [GUIDE.md](#) | API testing |
| [IMPLEMENTATION.md](#) | What changed |
| [DEVELOPER.md](#) | How it works |
| [ARCHITECTURE.md](#) | System design |
| [TESTING.md](#) | Test cases |

---

## 🎓 Learning Path

1. **Developer?** → DEVELOPER_GUIDE.md
2. **Tester?** → TESTING_CHECKLIST.md
3. **User?** → GUIDE.md
4. **Architect?** → ARCHITECTURE.md
5. **Manager?** → IMPLEMENTATION.md

---

## 💡 Key Concepts

**Storage Path Format:**
```
questions/{uuid}/{filename}.{ext}
```

**Image Lifecycle:**
1. Upload → Validate → Store → Get path → Save to DB
2. Update → Delete old → Upload new → Get new path → Update DB
3. Delete → Delete image → Delete record

**Response Includes:**
- `image_path` - Relative path to access image
- `image_path: null` - No image for this question

---

## 🔗 Access Images

**In API Response:**
```json
"image_path": "questions/abc123/image.jpg"
```

**Build Full URL:**
```
/storage/questions/abc123/image.jpg
```

**Full URL:**
```
http://your-domain.com/storage/questions/abc123/image.jpg
```

---

## 🎯 Success Criteria

- ✅ All 8 files updated
- ✅ Migration applied
- ✅ Storage link created
- ✅ All 25 tests pass
- ✅ Images upload/retrieve correctly
- ✅ Auto-cleanup works
- ✅ No orphaned files

---

## 📞 Quick Contact

**Questions?** Check documentation files in order:
1. This file (overview)
2. GUIDE.md (API usage)
3. DEVELOPER_GUIDE.md (implementation)
4. ARCHITECTURE.md (system design)

---

## ⏱️ Timeline

- **May 19, 2026:** ✅ Implementation complete
- **May 19, 2026:** ✅ Migration applied
- **May 19, 2026:** ✅ Documentation complete
- **TBD:** Testing phase
- **TBD:** Sign-off
- **TBD:** Production deployment

---

**Status:** 🟢 READY FOR TESTING

**Next Step:** Read [QUESTION_IMAGE_UPLOAD_TESTING_CHECKLIST.md](QUESTION_IMAGE_UPLOAD_TESTING_CHECKLIST.md)

---

## Version: 1.0 | Date: May 19, 2026
