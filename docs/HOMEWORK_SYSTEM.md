# Homework Assignment & Submission System

## Overview

The ekiliSense Homework System provides a complete solution for teachers to assign homework and students to submit their work online. It includes support for file uploads, auto-grading for quiz-type assignments, and comprehensive tracking of submissions.

## Features

### For Teachers

1. **Create Homework Assignments**
   - Multiple assignment types: homework, project, essay, quiz
   - Set due dates and times
   - Configure maximum points
   - Add detailed descriptions

2. **Manage Quiz Questions** (for quiz-type assignments)
   - Add multiple choice, true/false, and short answer questions
   - Set point values per question
   - Auto-grading enabled when questions are defined

3. **View Submissions**
   - See all student submissions for an assignment
   - Track submission status (submitted, graded, late, missing)
   - View statistics: total submissions, graded count, pending review

4. **Grade Submissions**
   - Manual grading with feedback
   - Auto-grading for quiz assignments
   - Download submitted files

### For Students

1. **View Assignments**
   - See all active homework assignments
   - Track submission status and deadlines
   - View grades and feedback

2. **Submit Homework**
   - Text-based responses
   - File uploads (PDF, DOC, DOCX, TXT, JPG, PNG - max 5MB)
   - Quiz interface for quiz-type assignments

3. **Track Progress**
   - Dashboard with statistics
   - View past submissions
   - Check grades and teacher feedback

## Database Schema

### Tables Created

1. **homework_assignments**
   - Stores assignment details
   - Fields: title, description, due_date, max_points, assignment_type, status

2. **homework_submissions**
   - Stores student submissions
   - Fields: submission_text, file_name, file_path, grade, teacher_feedback, status

3. **quiz_questions**
   - Stores quiz questions for auto-grading
   - Fields: question_text, question_type, correct_answer, options, points

4. **quiz_answers**
   - Stores individual student answers
   - Fields: student_answer, is_correct, points_earned

## Usage Guide

### Teacher Workflow

1. **Create an Assignment**
   - Navigate to Homework Management page
   - Click "Assign Homework"
   - Fill in assignment details
   - Select assignment type
   - Set due date and max points
   - Click "Assign Homework"

2. **Add Quiz Questions** (for quiz assignments)
   - Go to homework details page
   - Click "Manage Quiz Questions"
   - Click "Add Question"
   - Enter question text and type
   - Add answer options (for multiple choice)
   - Enter correct answer
   - Set point value
   - Click "Add Question"

3. **View Submissions**
   - Go to homework list
   - Click "Submissions" on any assignment
   - View submission statistics
   - Click "View" to see submission details
   - Click "Grade" to add grade and feedback

4. **Grade Manually**
   - Open submission details
   - Click "Grade"
   - Enter grade (0 to max_points)
   - Add feedback (optional)
   - Submit

### Student Workflow

1. **View Assignments**
   - Navigate to My Homework page
   - See all active assignments
   - Check due dates and status

2. **Submit Regular Homework**
   - Click "Submit" on an assignment
   - Enter your response
   - Optionally attach a file
   - Click "Submit Homework"

3. **Submit Quiz**
   - Click "Submit" on a quiz assignment
   - Answer all questions
   - Questions are auto-graded upon submission
   - View your grade immediately

4. **View Submission**
   - Click "View" on submitted assignments
   - See your submission
   - Check grade and feedback
   - Download submitted files

## Auto-Grading

### How It Works

1. **Quiz Questions Setup**
   - Teachers add questions with correct answers
   - Each question has a point value

2. **Student Submission**
   - Students answer questions through the interface
   - Answers are submitted as JSON: `{"question_id": "answer"}`

3. **Grading Process**
   - System compares student answers with correct answers
   - Multiple choice/true-false: exact match required
   - Short answer: partial/fuzzy match supported
   - Points are calculated per question
   - Total grade is computed: (earned_points / total_points) * max_points

4. **Results**
   - Grade is automatically assigned
   - Individual answers are stored in quiz_answers table
   - Students see grade immediately
   - Teachers can review individual answers

### Answer Format

**For Multiple Choice:**
- Correct answer: A, B, C, or D

**For True/False:**
- Correct answer: True or False

**For Short Answer:**
- Correct answer: The expected text/keyword

## File Upload

### Supported Formats
- PDF (.pdf)
- Word Documents (.doc, .docx)
- Text files (.txt)
- Images (.jpg, .jpeg, .png)

### Size Limit
- Maximum file size: 5MB

### Storage
- Files are stored in: `uploads/homework_submissions/`
- Naming format: `{student_id}_{assignment_uid}_{timestamp}.{extension}`

## Security Features

1. **Authentication**
   - Student authentication via student_auth.php middleware
   - Teacher authentication via teacher_auth.php middleware

2. **Authorization**
   - Students can only view their own submissions
   - Teachers can only grade submissions for their assignments
   - File access is controlled per user

3. **Input Validation**
   - SQL injection protection with mysqli_real_escape_string
   - File type validation
   - File size validation
   - XSS protection with htmlspecialchars

## API Endpoints

### Teacher Endpoints

- `POST server/homework.php` - Create/update/delete assignments, grade submissions
- `GET server/get_submission.php` - Fetch submission details
- `POST server/quiz_questions.php` - Add/delete quiz questions

### Student Endpoints

- `POST server/submit_homework.php` - Submit homework with optional file upload
- `GET server/get_my_submission.php` - Fetch own submission
- `GET server/get_quiz_questions.php` - Load quiz questions

## Pages

### Teacher Pages

- `homework.php` - Main homework management dashboard
- `homework_view.php` - Detailed view of a specific assignment
- `homework_submissions.php` - View all submissions for an assignment
- `homework_quiz_questions.php` - Manage quiz questions

### Student Pages

- `student/homework.php` - Student homework dashboard

## Troubleshooting

### Common Issues

1. **File Upload Failed**
   - Check file size (max 5MB)
   - Verify file type is supported
   - Ensure uploads directory has write permissions

2. **Auto-Grading Not Working**
   - Verify quiz questions are added
   - Check that correct answers are properly formatted
   - Ensure student answers are submitted in correct format

3. **Cannot View Submissions**
   - Verify teacher owns the assignment
   - Check school_uid and teacher_id match
   - Ensure assignment exists and is active

## Future Enhancements

Potential improvements for the homework system:

1. **Notifications**
   - Email notifications for new assignments
   - Reminders for upcoming due dates
   - Notifications when work is graded

2. **Advanced Auto-Grading**
   - Support for essay grading with AI
   - Plagiarism detection
   - Code submission and auto-testing

3. **Analytics**
   - Student performance trends
   - Class-wide statistics
   - Submission patterns

4. **Collaboration**
   - Group assignments
   - Peer review system
   - Discussion threads

5. **Integration**
   - Google Classroom integration
   - Microsoft Teams integration
   - Calendar sync

## Support

For issues or questions about the homework system, contact the school administrator or submit a support ticket through the ekiliSense platform.
