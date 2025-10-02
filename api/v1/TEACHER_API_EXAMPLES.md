# Teacher API Examples

This document provides practical examples for integrating with the ekiliSense Teacher API endpoints.

## Table of Contents

1. [Authentication](#authentication)
2. [Get Teacher's Classes](#get-teachers-classes)
3. [Get Teacher's Students](#get-teachers-students)
4. [Get Teacher's Subjects](#get-teachers-subjects)
5. [List Assignments](#list-assignments)
6. [Create Assignment](#create-assignment)
7. [Get Performance Stats](#get-performance-stats)
8. [Error Handling](#error-handling)

---

## Authentication

All teacher API endpoints require authentication. Include the authorization header in all requests.

### Session-Based Authentication (Web)
```http
GET /api/v1/teachers/teacher_123/classes
Cookie: PHPSESSID=abc123xyz
```

### Token-Based Authentication (Mobile/Third-party)
```http
GET /api/v1/teachers/teacher_123/classes
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

---

## Get Teacher's Classes

Retrieve all classes taught by a specific teacher.

### Endpoint
```
GET /api/v1/teachers/{teacher_id}/classes
```

### Request Example (cURL)
```bash
curl -X GET "https://your-school.ekilie.com/api/v1/teachers/teacher_abc123/classes" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Request Example (JavaScript/Fetch)
```javascript
const teacherId = 'teacher_abc123';

fetch(`https://your-school.ekilie.com/api/v1/teachers/${teacherId}/classes`, {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer YOUR_ACCESS_TOKEN',
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  console.log('Teacher Classes:', data);
})
.catch(error => {
  console.error('Error:', error);
});
```

### Request Example (Python)
```python
import requests

teacher_id = 'teacher_abc123'
url = f'https://your-school.ekilie.com/api/v1/teachers/{teacher_id}/classes'
headers = {
    'Authorization': 'Bearer YOUR_ACCESS_TOKEN',
    'Content-Type': 'application/json'
}

response = requests.get(url, headers=headers)
data = response.json()

if data['success']:
    for class_info in data['data']:
        print(f"Class: {class_info['name']}, Students: {class_info['student_count']}")
else:
    print(f"Error: {data['error']}")
```

### Response Example
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
    },
    {
      "id": "class_456",
      "name": "Form 2B",
      "short_name": "F2B",
      "subject_count": 3,
      "student_count": 28
    }
  ]
}
```

---

## Get Teacher's Students

Retrieve all students taught by a specific teacher across all classes.

### Endpoint
```
GET /api/v1/teachers/{teacher_id}/students
```

### Request Example (cURL)
```bash
curl -X GET "https://your-school.ekilie.com/api/v1/teachers/teacher_abc123/students" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Request Example (JavaScript/Axios)
```javascript
import axios from 'axios';

const teacherId = 'teacher_abc123';

axios.get(`https://your-school.ekilie.com/api/v1/teachers/${teacherId}/students`, {
  headers: {
    'Authorization': 'Bearer YOUR_ACCESS_TOKEN'
  }
})
.then(response => {
  const students = response.data.data;
  console.log(`Total students: ${students.length}`);
  students.forEach(student => {
    console.log(`${student.first_name} ${student.last_name} - ${student.class}`);
  });
})
.catch(error => {
  console.error('Error fetching students:', error);
});
```

### Response Example
```json
{
  "success": true,
  "data": [
    {
      "id": "student_789",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@school.com",
      "class": "Form 1A"
    },
    {
      "id": "student_790",
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "jane.smith@school.com",
      "class": "Form 1A"
    }
  ]
}
```

---

## Get Teacher's Subjects

Retrieve all subjects assigned to a teacher.

### Endpoint
```
GET /api/v1/teachers/{teacher_id}/subjects
```

### Request Example (cURL)
```bash
curl -X GET "https://your-school.ekilie.com/api/v1/teachers/teacher_abc123/subjects" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Request Example (PHP)
```php
<?php
$teacherId = 'teacher_abc123';
$url = "https://your-school.ekilie.com/api/v1/teachers/{$teacherId}/subjects";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer YOUR_ACCESS_TOKEN',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if ($data['success']) {
    foreach ($data['data'] as $subject) {
        echo "{$subject['name']} - {$subject['class_name']} ({$subject['student_count']} students)\n";
    }
}
?>
```

### Response Example
```json
{
  "success": true,
  "data": [
    {
      "id": "subject_111",
      "name": "Mathematics",
      "class_id": "class_123",
      "class_name": "Form 1A",
      "student_count": 30
    },
    {
      "id": "subject_222",
      "name": "Physics",
      "class_id": "class_456",
      "class_name": "Form 2B",
      "student_count": 28
    }
  ]
}
```

---

## List Assignments

Get all assignments created by a teacher.

### Endpoint
```
GET /api/v1/teachers/{teacher_id}/assignments
```

### Request Example (cURL)
```bash
curl -X GET "https://your-school.ekilie.com/api/v1/teachers/teacher_abc123/assignments" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Request Example (JavaScript/React)
```javascript
import React, { useEffect, useState } from 'react';

function TeacherAssignments({ teacherId }) {
  const [assignments, setAssignments] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchAssignments = async () => {
      try {
        const response = await fetch(
          `https://your-school.ekilie.com/api/v1/teachers/${teacherId}/assignments`,
          {
            headers: {
              'Authorization': 'Bearer YOUR_ACCESS_TOKEN'
            }
          }
        );
        const data = await response.json();
        
        if (data.success) {
          setAssignments(data.data);
        }
      } catch (error) {
        console.error('Error:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchAssignments();
  }, [teacherId]);

  if (loading) return <div>Loading...</div>;

  return (
    <div>
      <h2>My Assignments</h2>
      {assignments.map(assignment => (
        <div key={assignment.id}>
          <h3>{assignment.title}</h3>
          <p>Subject: {assignment.subject}</p>
          <p>Class: {assignment.class}</p>
          <p>Deadline: {assignment.deadline}</p>
          <p>Submissions: {assignment.submission_count}</p>
        </div>
      ))}
    </div>
  );
}

export default TeacherAssignments;
```

### Response Example
```json
{
  "success": true,
  "data": [
    {
      "id": "assignment_aaa",
      "title": "Chapter 1 Homework",
      "description": "Complete exercises 1-10",
      "subject": "Mathematics",
      "class": "Form 1A",
      "deadline": "2024-01-15",
      "created_at": "2024-01-01 10:00:00",
      "submission_count": 15
    },
    {
      "id": "assignment_bbb",
      "title": "Lab Report - Newton's Laws",
      "description": "Write a comprehensive lab report",
      "subject": "Physics",
      "class": "Form 2B",
      "deadline": "2024-01-20",
      "created_at": "2024-01-05 14:30:00",
      "submission_count": 12
    }
  ]
}
```

---

## Create Assignment

Create a new assignment for a subject.

### Endpoint
```
POST /api/v1/teachers/{teacher_id}/assignments
```

### Request Body
```json
{
  "title": "Chapter 2 Homework",
  "subject_id": "subject_111",
  "description": "Complete all exercises from chapter 2",
  "deadline": "2024-01-25"
}
```

### Request Example (cURL)
```bash
curl -X POST "https://your-school.ekilie.com/api/v1/teachers/teacher_abc123/assignments" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Chapter 2 Homework",
    "subject_id": "subject_111",
    "description": "Complete all exercises from chapter 2",
    "deadline": "2024-01-25"
  }'
```

### Request Example (JavaScript/Fetch)
```javascript
const teacherId = 'teacher_abc123';
const assignmentData = {
  title: 'Chapter 2 Homework',
  subject_id: 'subject_111',
  description: 'Complete all exercises from chapter 2',
  deadline: '2024-01-25'
};

fetch(`https://your-school.ekilie.com/api/v1/teachers/${teacherId}/assignments`, {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_ACCESS_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(assignmentData)
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    console.log('Assignment created:', data.data.assignment_id);
  } else {
    console.error('Error:', data.error);
  }
})
.catch(error => {
  console.error('Request failed:', error);
});
```

### Request Example (Python/Requests)
```python
import requests
from datetime import datetime, timedelta

teacher_id = 'teacher_abc123'
url = f'https://your-school.ekilie.com/api/v1/teachers/{teacher_id}/assignments'

# Set deadline to 2 weeks from now
deadline = (datetime.now() + timedelta(weeks=2)).strftime('%Y-%m-%d')

assignment_data = {
    'title': 'Chapter 2 Homework',
    'subject_id': 'subject_111',
    'description': 'Complete all exercises from chapter 2',
    'deadline': deadline
}

headers = {
    'Authorization': 'Bearer YOUR_ACCESS_TOKEN',
    'Content-Type': 'application/json'
}

response = requests.post(url, json=assignment_data, headers=headers)
data = response.json()

if data['success']:
    print(f"Assignment created with ID: {data['data']['assignment_id']}")
else:
    print(f"Error: {data['error']}")
```

### Response Example (Success)
```json
{
  "success": true,
  "data": {
    "assignment_id": "assignment_ccc123"
  },
  "message": "Assignment created successfully"
}
```

### Response Example (Error)
```json
{
  "success": false,
  "error": "You do not have permission to create assignments for this subject"
}
```

---

## Get Performance Stats

Retrieve performance statistics for a teacher.

### Endpoint
```
GET /api/v1/teachers/{teacher_id}/performance
```

### Request Example (cURL)
```bash
curl -X GET "https://your-school.ekilie.com/api/v1/teachers/teacher_abc123/performance" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Request Example (JavaScript/Vue.js)
```javascript
export default {
  data() {
    return {
      teacherId: 'teacher_abc123',
      performance: null,
      loading: true
    }
  },
  async mounted() {
    await this.fetchPerformance();
  },
  methods: {
    async fetchPerformance() {
      try {
        const response = await fetch(
          `https://your-school.ekilie.com/api/v1/teachers/${this.teacherId}/performance`,
          {
            headers: {
              'Authorization': 'Bearer YOUR_ACCESS_TOKEN'
            }
          }
        );
        
        const data = await response.json();
        
        if (data.success) {
          this.performance = data.data;
        }
      } catch (error) {
        console.error('Error:', error);
      } finally {
        this.loading = false;
      }
    }
  }
}
```

### Response Example
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

---

## Error Handling

### Common Error Responses

#### 401 Unauthorized
```json
{
  "success": false,
  "error": "Unauthorized. Please provide valid authentication credentials."
}
```

#### 403 Forbidden
```json
{
  "success": false,
  "error": "You do not have permission to access this resource."
}
```

#### 404 Not Found
```json
{
  "success": false,
  "error": "Teacher not found"
}
```

#### 400 Bad Request
```json
{
  "success": false,
  "error": "Missing required field: title"
}
```

#### 500 Internal Server Error
```json
{
  "success": false,
  "error": "An internal server error occurred. Please try again later."
}
```

### Error Handling Best Practices

#### JavaScript Example
```javascript
async function fetchTeacherData(teacherId) {
  try {
    const response = await fetch(`/api/v1/teachers/${teacherId}/classes`, {
      headers: {
        'Authorization': 'Bearer YOUR_ACCESS_TOKEN'
      }
    });
    
    const data = await response.json();
    
    if (!data.success) {
      // Handle API error
      throw new Error(data.error);
    }
    
    return data.data;
    
  } catch (error) {
    // Handle network or parsing errors
    console.error('Request failed:', error);
    
    // Show user-friendly error message
    alert('Failed to load data. Please try again.');
    
    return null;
  }
}
```

#### Python Example
```python
import requests
from requests.exceptions import RequestException

def fetch_teacher_data(teacher_id):
    url = f'https://your-school.ekilie.com/api/v1/teachers/{teacher_id}/classes'
    headers = {'Authorization': 'Bearer YOUR_ACCESS_TOKEN'}
    
    try:
        response = requests.get(url, headers=headers, timeout=10)
        response.raise_for_status()  # Raise exception for 4xx/5xx responses
        
        data = response.json()
        
        if not data.get('success'):
            print(f"API Error: {data.get('error')}")
            return None
            
        return data.get('data')
        
    except RequestException as e:
        print(f"Request failed: {e}")
        return None
```

---

## Rate Limiting

Some endpoints have rate limiting enabled:
- Default: 60 requests per minute per user
- Limit headers included in response:
  ```
  X-RateLimit-Limit: 60
  X-RateLimit-Remaining: 45
  X-RateLimit-Reset: 1640000000
  ```

### Handling Rate Limits

```javascript
async function fetchWithRateLimit(url, options) {
  const response = await fetch(url, options);
  
  // Check rate limit headers
  const remaining = response.headers.get('X-RateLimit-Remaining');
  const reset = response.headers.get('X-RateLimit-Reset');
  
  if (remaining === '0') {
    const resetTime = new Date(parseInt(reset) * 1000);
    console.warn(`Rate limit reached. Resets at ${resetTime}`);
  }
  
  return response.json();
}
```

---

## Testing

### Using Postman

1. Import the ekiliSense API collection
2. Set environment variables:
   - `base_url`: https://your-school.ekilie.com
   - `access_token`: Your authentication token
   - `teacher_id`: Your teacher ID

3. Run the collection to test all endpoints

### Sample Test Script (JavaScript/Jest)
```javascript
const { fetchTeacherClasses } = require('./api');

describe('Teacher API', () => {
  const teacherId = 'teacher_abc123';
  
  test('should fetch teacher classes', async () => {
    const classes = await fetchTeacherClasses(teacherId);
    
    expect(classes).toBeDefined();
    expect(Array.isArray(classes)).toBe(true);
    expect(classes.length).toBeGreaterThan(0);
  });
  
  test('should handle invalid teacher ID', async () => {
    const classes = await fetchTeacherClasses('invalid_id');
    
    expect(classes).toBeNull();
  });
});
```

---

## Additional Resources

- [Main API Documentation](./README.md)
- [Features List](./FEATURES.md)
- [Teacher Dashboard Guide](../docs/TEACHER_DASHBOARD_GUIDE.md)
- [Authentication Flow](../docs/AUTHENTICATION_FLOW.md)

---

*Last Updated: January 2024*
*API Version: v1*
