# ekiliSense API v1 Documentation

## Overview

The ekiliSense API is a RESTful API that provides programmatic access to the school management system. It uses JWT (JSON Web Tokens) for authentication and follows standard HTTP methods and status codes.

**Base URL:** `https://yourdomain.com/api/v1`

## Authentication

The API uses JWT Bearer tokens for authentication. After logging in, include the access token in the Authorization header of subsequent requests.

### Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@school.com",
  "password": "your_password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIs...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": {
      "id": "school_123",
      "name": "Example School",
      "email": "admin@school.com",
      "role": "admin"
    }
  }
}
```

### Using the Token

Include the access token in all protected API requests:

```http
GET /api/v1/students
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

### Refresh Token

When the access token expires, use the refresh token to get a new one:

```http
POST /api/v1/auth/refresh
Content-Type: application/json

{
  "refresh_token": "eyJhbGciOiJIUzI1NiIs..."
}
```

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/auth/login` | Login with email and password | No |
| POST | `/auth/register` | Register new school | No |
| POST | `/auth/refresh` | Refresh access token | Yes |
| POST | `/auth/logout` | Logout (client-side token removal) | Yes |

### School Management

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/schools/profile` | Get school profile | Yes |
| PUT | `/schools/profile` | Update school profile | Yes |
| GET | `/schools/stats` | Get school statistics | Yes |

### Student Management

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/students` | List all students (paginated) | Yes |
| POST | `/students` | Create new student | Yes |
| GET | `/students/{id}` | Get student details | Yes |
| PUT | `/students/{id}` | Update student | Yes |
| DELETE | `/students/{id}` | Delete student | Yes |

### Teacher Management

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/teachers` | List all teachers | Yes |
| POST | `/teachers` | Create new teacher | Yes |
| GET | `/teachers/{id}` | Get teacher details | Yes |
| PUT | `/teachers/{id}` | Update teacher | Yes |

### Class Management

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/classes` | List all classes | Yes |
| POST | `/classes` | Create new class | Yes |
| GET | `/classes/{id}` | Get class with students | Yes |

### Assignment Management

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/assignments` | List assignments | Yes |
| POST | `/assignments` | Create assignment | Yes |
| GET | `/assignments/{id}` | Get assignment details | Yes |

### Parent Portal (NEW)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/parent/children` | Get parent's children | Yes |
| GET | `/parent/children/{id}/grades` | Get child's grades | Yes |
| GET | `/parent/children/{id}/attendance` | Get child's attendance | Yes |
| GET | `/parent/notifications` | Get notifications | Yes |

### Notifications

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/notifications` | List notifications | Yes |
| POST | `/notifications/mark-read` | Mark notification as read | Yes |

## Response Format

All API responses follow this structure:

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": { /* response data */ },
  "timestamp": "2024-01-15 10:30:00"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { /* validation errors */ },
  "timestamp": "2024-01-15 10:30:00"
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Success",
  "data": [ /* array of items */ ],
  "pagination": {
    "total": 100,
    "page": 1,
    "per_page": 20,
    "total_pages": 5
  },
  "timestamp": "2024-01-15 10:30:00"
}
```

## HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request data |
| 401 | Unauthorized - Invalid or missing token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 409 | Conflict - Resource already exists |
| 422 | Unprocessable Entity - Validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Server error |

## Rate Limiting

The API implements rate limiting to prevent abuse:

- **Limit:** 100 requests per minute per user/IP
- **Headers:** 
  - `X-RateLimit-Limit`: Maximum requests allowed
  - `X-RateLimit-Remaining`: Requests remaining
  - `X-RateLimit-Reset`: Time when limit resets

When rate limit is exceeded, you'll receive a 429 status code with retry information.

## Examples

### Create a Student

```http
POST /api/v1/students
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "phone": "+255712345678",
  "class_id": "class_123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Student created successfully",
  "data": {
    "student_id": "student_abc123",
    "name": "John Doe",
    "email": "john.doe@example.com"
  }
}
```

### Get Student List with Pagination

```http
GET /api/v1/students?page=1&per_page=20
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

### Get School Statistics

```http
GET /api/v1/schools/stats
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "students": 250,
    "teachers": 15,
    "classes": 10
  }
}
```

## Security Best Practices

1. **Always use HTTPS** in production
2. **Never share your access tokens** or commit them to version control
3. **Store refresh tokens securely** (httpOnly cookies recommended)
4. **Implement token rotation** regularly
5. **Validate all input data** on the client side
6. **Handle errors gracefully** and never expose sensitive information

## Integration Examples

### JavaScript/React

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('https://api.yourdomain.com/api/v1/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await response.json();
  localStorage.setItem('access_token', data.data.access_token);
  return data;
};

// Get Students
const getStudents = async (page = 1) => {
  const token = localStorage.getItem('access_token');
  const response = await fetch(`https://api.yourdomain.com/api/v1/students?page=${page}`, {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  return await response.json();
};
```

### Python

```python
import requests

# Login
def login(email, password):
    response = requests.post(
        'https://api.yourdomain.com/api/v1/auth/login',
        json={'email': email, 'password': password}
    )
    data = response.json()
    return data['data']['access_token']

# Get Students
def get_students(token, page=1):
    headers = {'Authorization': f'Bearer {token}'}
    response = requests.get(
        f'https://api.yourdomain.com/api/v1/students?page={page}',
        headers=headers
    )
    return response.json()
```

### Mobile (React Native)

```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE = 'https://api.yourdomain.com/api/v1';

const login = async (email, password) => {
  const response = await fetch(`${API_BASE}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await response.json();
  await AsyncStorage.setItem('access_token', data.data.access_token);
  return data;
};
```

## Support

For API support, please contact:
- Email: support@ekilie.com
- Documentation: https://docs.ekilie.com
- GitHub: https://github.com/tacheraSasi/ekiliSense

## Changelog

### v1.0.0 (2024-01-15)
- Initial API release
- Authentication with JWT
- Student, Teacher, Class management
- Parent Portal features
- Rate limiting
- Comprehensive error handling
