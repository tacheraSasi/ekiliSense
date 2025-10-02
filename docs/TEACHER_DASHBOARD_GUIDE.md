# Teacher Dashboard Guide

## Overview

The ekiliSense Teacher Dashboard provides a comprehensive interface for teachers to manage their classes, students, assignments, and track performance. This guide covers all features available to teachers in the system.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Managing Subjects](#managing-subjects)
4. [Assignments & Homework](#assignments--homework)
5. [Grades & Results](#grades--results)
6. [Attendance Tracking](#attendance-tracking)
7. [Messages & Communication](#messages--communication)
8. [Performance Analytics](#performance-analytics)
9. [Profile Management](#profile-management)
10. [Class Teacher Features](#class-teacher-features)
11. [API Integration](#api-integration)

---

## Getting Started

### Accessing the Dashboard

1. Navigate to your school's ekiliSense portal
2. Login with your teacher credentials
3. You will be redirected to your personal teacher dashboard

### Authentication Flow

Teachers authenticate through:
- Email and password login
- OTP verification (when enabled)
- Session management via `$_SESSION['teacher_email']` and `$_SESSION['School_uid']`

---

## Dashboard Overview

### Main Dashboard Features

The dashboard provides at-a-glance statistics:

- **Classes**: Number of classes you're teaching
- **Students**: Total students across all your classes
- **Subjects**: Number of subjects you're assigned to
- **Assignments**: Pending and total assignments

### Quick Actions

From the dashboard, you can quickly:
- View your subjects
- Manage assignments
- Mark attendance
- Enter grades
- Access class teacher dashboard (if applicable)

### Navigation Sidebar

The sidebar provides easy access to all features:
- Dashboard (Home)
- My Subjects
- Assignments
- Grades & Results
- Attendance
- Teaching Plans
- Messages
- Performance Analytics
- Profile

---

## Managing Subjects

### Viewing Your Subjects

**Path**: `console/teacher/subjects.php`

Features:
- List all subjects you're teaching
- View subject details by class
- See student count per subject
- Quick access to assignments for each subject

### Subject Information Displayed

For each subject, you can see:
- Subject name
- Class name
- Number of students
- Quick action buttons (View, Assignments)

---

## Assignments & Homework

### Managing Assignments

**Path**: `console/teacher/assignments.php`

#### Creating an Assignment

1. Click "Create Assignment" button
2. Fill in the following details:
   - Assignment Title *
   - Subject (select from dropdown) *
   - Description
   - Deadline *
3. Click "Create Assignment"

#### Viewing Assignments

The assignments page displays:
- Title
- Subject and Class
- Deadline
- Status (Active/Expired)
- Submission count (e.g., "5/30 submitted")

#### Filtering Assignments

Use the subject filter dropdown to:
- View all assignments
- Filter by specific subject

#### Assignment Details

Click on an assignment to view:
- Full description
- Submission details
- Student submissions
- Grading options

### Server-side Management

Assignment operations are handled by:
```php
console/teacher/server/manage-assignments.php
```

Operations include:
- Create assignment
- Update assignment
- Delete assignment
- Permission verification

---

## Grades & Results

### Accessing Grades

**Path**: `console/teacher/grades.php`

Features:
- Select subject to view students
- Enter grades for assessments
- View grade history
- Export grade reports

### Grade Management

The grades page allows:
- Subject-based grade entry
- Student performance tracking
- Integration with exam results system

**Note**: Detailed grade management is available in the class teacher dashboard for class teachers.

---

## Attendance Tracking

### Recording Attendance

**Path**: `console/teacher/attendance.php`

Features:
- Select class and date
- Mark student attendance
- View attendance history
- Generate attendance reports

### Attendance Options

For each student, you can mark:
- Present
- Absent
- Late
- Excused

**Note**: Class teachers have enhanced attendance features in their class dashboard.

---

## Messages & Communication

### Communication Center

**Path**: `console/teacher/messages.php`

Features (Coming Soon):
- Send messages to students and parents
- Class announcements
- Assignment notifications
- Grade reports
- Real-time chat (via Convo integration)

### Current Features

- View inbox
- Track sent messages
- Manage announcements

---

## Performance Analytics

### Viewing Your Performance

**Path**: `console/teacher/performance.php`

#### Statistics Displayed

- Total classes teaching
- Total subjects
- Total students
- Total assignments created

#### Performance Charts

- Teaching activity over time
- Assignment creation trends
- Class engagement metrics

#### Quick Insights

- Assignment statistics
- Student reach
- Subject distribution
- Class teacher responsibilities (if applicable)

#### Recommendations

The system provides recommendations for:
- Student performance review
- Assignment management
- Attendance consistency
- Parent communication

---

## Profile Management

### Updating Your Profile

**Path**: `console/teacher/profile.php`

#### Profile Information

View and edit:
- Full name
- Email (read-only)
- Phone number
- Address
- Role (Teacher/Class Teacher)

#### Profile Sections

1. **Overview Tab**
   - View all profile information
   - See role and permissions

2. **Edit Profile Tab**
   - Update personal information
   - Save changes

---

## Class Teacher Features

### Additional Responsibilities

If you are a **Class Teacher**, you have access to:

- Enhanced class dashboard
- Student management
- Detailed attendance tracking
- Homework management
- Exam scheduling
- Results entry
- Parent communication
- Class performance analytics

### Accessing Class Dashboard

From any page, use the sidebar:
- Look for "Class Teacher" section
- Click "My Class Dashboard"
- Redirects to: `console/class/teacher/`

### Class Teacher Dashboard Features

Available at `console/class/teacher/`:
- Dashboard with class statistics
- Student management
- Subject management
- Attendance (mark and reports)
- Homework assignments
- Exam management
- Results entry
- Messages
- Events
- Teaching plans
- Profile

---

## API Integration

### Teacher API Endpoints

The following API endpoints are available for mobile apps and third-party integrations:

#### 1. List Teacher's Classes
```
GET /api/v1/teachers/{id}/classes
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": "class_123",
      "name": "Form 1A",
      "short_name": "F1A",
      "subject_count": 5,
      "student_count": 30
    }
  ]
}
```

#### 2. List Teacher's Students
```
GET /api/v1/teachers/{id}/students
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": "student_456",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@example.com",
      "class": "Form 1A"
    }
  ]
}
```

#### 3. List Teacher's Subjects
```
GET /api/v1/teachers/{id}/subjects
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": "subject_789",
      "name": "Mathematics",
      "class_id": "class_123",
      "class_name": "Form 1A",
      "student_count": 30
    }
  ]
}
```

#### 4. List Teacher's Assignments
```
GET /api/v1/teachers/{id}/assignments
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": "assignment_abc",
      "title": "Chapter 1 Homework",
      "description": "Complete exercises 1-10",
      "subject": "Mathematics",
      "class": "Form 1A",
      "deadline": "2024-01-15",
      "created_at": "2024-01-01 10:00:00",
      "submission_count": 15
    }
  ]
}
```

#### 5. Create Assignment
```
POST /api/v1/teachers/{id}/assignments
```

Request Body:
```json
{
  "title": "Chapter 2 Homework",
  "subject_id": "subject_789",
  "description": "Complete all exercises",
  "deadline": "2024-01-20"
}
```

Response:
```json
{
  "success": true,
  "data": {
    "assignment_id": "assignment_def"
  },
  "message": "Assignment created successfully"
}
```

#### 6. Get Performance Statistics
```
GET /api/v1/teachers/{id}/performance
```

Response:
```json
{
  "success": true,
  "data": {
    "total_assignments": 25,
    "pending_assignments": 10,
    "total_classes": 3,
    "total_students": 90,
    "total_subjects": 5
  }
}
```

### Authentication

All API endpoints require authentication:

```http
Authorization: Bearer {access_token}
```

Or using session-based authentication for web applications.

### Error Handling

API responses follow standard format:

Success:
```json
{
  "success": true,
  "data": {...}
}
```

Error:
```json
{
  "success": false,
  "error": "Error message here"
}
```

---

## Best Practices

### For Teachers

1. **Regular Updates**
   - Update your profile information
   - Keep assignments current
   - Review student performance weekly

2. **Communication**
   - Respond to messages promptly
   - Use announcements for class-wide updates
   - Keep parents informed

3. **Attendance**
   - Mark attendance daily
   - Review attendance reports regularly
   - Follow up on absent students

4. **Assignments**
   - Set clear deadlines
   - Provide detailed descriptions
   - Review submissions promptly

5. **Performance Tracking**
   - Monitor your teaching analytics
   - Adjust strategies based on data
   - Set goals for improvement

### For Class Teachers

Additional responsibilities:
1. Maintain accurate class records
2. Monitor overall class performance
3. Coordinate with other teachers
4. Communicate regularly with parents
5. Track individual student progress

---

## Troubleshooting

### Common Issues

1. **Cannot Access Dashboard**
   - Verify you're logged in
   - Check session hasn't expired
   - Ensure correct permissions

2. **Cannot Create Assignment**
   - Verify you're assigned to the subject
   - Check all required fields are filled
   - Ensure deadline is in the future

3. **Cannot View Students**
   - Verify you have classes assigned
   - Check if subjects are properly linked
   - Contact administrator if issues persist

### Support

For technical support:
- Contact your school administrator
- Email: support@ekilie.com
- Visit: https://ekilie.com/support

---

## Changelog

### Version 2.0 (Current)
- ✅ Complete teacher dashboard redesign
- ✅ Enhanced statistics and analytics
- ✅ New assignment management system
- ✅ Grades and results tracking
- ✅ Performance analytics page
- ✅ 6 new API endpoints
- ✅ Role-based navigation
- ✅ Class teacher integration

### Version 1.0
- Basic teacher profile
- Teaching plans
- Simple navigation

---

## Future Features

Planned enhancements:
- Real-time notifications
- Mobile app integration
- Advanced analytics
- AI-powered insights
- Video conferencing integration
- Digital gradebook
- Automated report generation
- Parent portal integration

---

## Contact & Support

- **Website**: https://ekilie.com
- **Documentation**: https://docs.ekilie.com
- **GitHub**: https://github.com/tacheraSasi/ekiliSense
- **Email**: support@ekilie.com

---

*Last Updated: January 2024*
*Version: 2.0*
