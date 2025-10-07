# Homework System Implementation - Setup Guide

## Quick Start

This implementation adds a complete homework assignment and submission system to ekiliSense with auto-grading capabilities for quiz-type assignments.

## Database Setup

### Required Tables

Run the following SQL files to create the necessary database tables:

1. **Main homework tables** (if not already created):
```bash
mysql -u your_user -p your_database < database_updates.sql
```

2. **Quiz questions tables** (for auto-grading):
```bash
mysql -u your_user -p your_database < database/migrations/003_quiz_questions.sql
```

### Table Structure

The system creates 4 main tables:

1. `homework_assignments` - Stores teacher-created assignments
2. `homework_submissions` - Stores student submissions and grades
3. `quiz_questions` - Stores questions for auto-graded quizzes
4. `quiz_answers` - Stores individual student answers for grading

## File System Setup

### Upload Directory

Ensure the uploads directory has proper permissions:

```bash
mkdir -p uploads/homework_submissions
chmod 755 uploads/homework_submissions
```

### Required PHP Extensions

- mysqli (for database)
- fileinfo (for file upload validation)
- json (for quiz answer parsing)

## Access URLs

### Teacher Portal

- Homework Management: `/console/class/teacher/homework.php`
- View Submissions: `/console/class/teacher/homework_submissions.php?id={assignment_uid}`
- Assignment Details: `/console/class/teacher/homework_view.php?id={assignment_uid}`
- Quiz Questions: `/console/class/teacher/homework_quiz_questions.php?id={assignment_uid}`

### Student Portal

- My Homework: `/console/class/student/homework.php`

## Testing Checklist

### Teacher Side

- [ ] Create a regular homework assignment
- [ ] Create a quiz-type assignment
- [ ] Add quiz questions (multiple choice, true/false, short answer)
- [ ] View assignment details
- [ ] Check submissions page (before any student submits)

### Student Side

- [ ] View homework list
- [ ] Submit regular homework with text
- [ ] Submit homework with file attachment
- [ ] Submit a quiz and verify auto-grading
- [ ] View submitted homework
- [ ] Check grade and feedback

### Grading

- [ ] Teacher manually grades a submission
- [ ] Teacher adds feedback
- [ ] Verify quiz auto-grading works correctly
- [ ] Check grade calculations match expected values

### File Upload

- [ ] Upload valid file types (PDF, DOC, DOCX, TXT, JPG, PNG)
- [ ] Verify file size limit (5MB)
- [ ] Try to upload invalid file type (should fail)
- [ ] Try to upload oversized file (should fail)
- [ ] Download submitted file

## Configuration

### File Upload Settings

Edit `console/class/student/server/submit_homework.php` to change:

```php
// Maximum file size (default: 5MB)
$max_size = 5 * 1024 * 1024;

// Allowed file types
$allowed_types = ['application/pdf', 'application/msword', ...];
```

### Student Authentication

Student authentication uses `middlwares/student_auth.php`. Ensure student login is configured and sessions are set correctly:

- `$_SESSION['School_uid']` - School unique identifier
- `$_SESSION['student_email']` - Student email address

## Troubleshooting

### Common Issues

**1. Database Connection Error**
- Verify `config.php` has correct database credentials
- Check if all tables are created

**2. File Upload Fails**
- Check directory permissions: `chmod 755 uploads/homework_submissions`
- Verify PHP upload settings in `php.ini`:
  - `upload_max_filesize = 10M`
  - `post_max_size = 10M`

**3. Auto-Grading Not Working**
- Ensure quiz questions are added before student submission
- Check that correct answers are properly formatted
- Verify student answers are in JSON format

**4. Student Cannot Access Portal**
- Check student authentication middleware
- Verify student session variables are set
- Ensure student record exists in database

**5. Cannot View Submissions**
- Verify teacher_id matches in homework_assignments
- Check school_uid matches across tables
- Ensure assignment exists and is not deleted

## Security Notes

### Input Validation

All user inputs are sanitized using:
- `mysqli_real_escape_string()` for SQL queries
- `htmlspecialchars()` for output
- File type and size validation for uploads

### Access Control

- Teachers can only view/grade submissions for their assignments
- Students can only view their own submissions
- File uploads are validated for type and size
- Authentication required for all pages

### Recommended Improvements

For production deployment, consider:

1. **Prepared Statements**: Replace `mysqli_real_escape_string()` with prepared statements
2. **CSRF Protection**: Add CSRF tokens to all forms
3. **Rate Limiting**: Implement rate limiting for submissions
4. **File Scanning**: Add antivirus scanning for uploaded files
5. **Backup Strategy**: Regular backups of uploads directory

## Feature Usage

### For Teachers

1. **Creating a Quiz**:
   - Assign homework with type "quiz"
   - Go to assignment details
   - Click "Manage Quiz Questions"
   - Add questions with correct answers
   - Students can now submit and get auto-graded

2. **Manual Grading**:
   - Go to homework submissions page
   - Click "Grade" on any submission
   - Enter grade and feedback
   - Submit

### For Students

1. **Submitting Regular Homework**:
   - Open homework page
   - Click "Submit" on assignment
   - Enter response text
   - Optionally attach file
   - Submit

2. **Taking a Quiz**:
   - Click "Submit" on quiz assignment
   - Answer all questions
   - Click submit
   - Grade is calculated automatically

## Performance Considerations

### Database Indexes

For better performance with large datasets, add indexes:

```sql
CREATE INDEX idx_submissions_assignment ON homework_submissions(assignment_uid);
CREATE INDEX idx_submissions_student ON homework_submissions(student_id);
CREATE INDEX idx_assignments_teacher ON homework_assignments(teacher_id, school_uid);
CREATE INDEX idx_questions_assignment ON quiz_questions(assignment_uid);
```

### File Storage

For large deployments, consider:
- Using cloud storage (AWS S3, Google Cloud Storage)
- Implementing CDN for file delivery
- Regular cleanup of old submissions

## API Integration

The system exposes several endpoints that can be integrated with other systems:

- `GET server/get_submission.php?id={submission_id}` - Get submission details
- `POST server/homework.php` - CRUD operations on assignments
- `GET server/get_quiz_questions.php?assignment_uid={uid}` - Get quiz questions
- `POST server/submit_homework.php` - Submit homework

## Future Enhancements

Planned features for future versions:

1. **Email Notifications**
   - New assignment alerts
   - Due date reminders
   - Grade notifications

2. **Advanced Analytics**
   - Student performance tracking
   - Class-wide statistics
   - Submission patterns

3. **Enhanced Auto-Grading**
   - Support for code submissions
   - AI-powered essay grading
   - Plagiarism detection

4. **Collaboration**
   - Group assignments
   - Peer review system
   - Discussion threads

## Support

For detailed documentation, see: `docs/HOMEWORK_SYSTEM.md`

For issues or questions:
1. Check troubleshooting section above
2. Review error logs in browser console and PHP error log
3. Verify database schema is correct
4. Contact system administrator

## Credits

Implemented by: GitHub Copilot
For: ekiliSense School Management System
Date: 2024
Version: 1.0
