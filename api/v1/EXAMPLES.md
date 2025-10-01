# ekiliSense API Usage Examples

## Testing the API with cURL

### 1. Authentication

#### Login as School Admin
```bash
curl -X POST http://localhost/ekiliSense/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@school.com",
    "password": "your_password"
  }'
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

#### Register New School
```bash
curl -X POST http://localhost/ekiliSense/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "school_name": "New Academy",
    "email": "admin@newacademy.com",
    "password": "SecurePass123",
    "phone": "+255712345678"
  }'
```

### 2. Student Management

#### List Students (with pagination)
```bash
TOKEN="your_access_token_here"

curl -X GET "http://localhost/ekiliSense/api/v1/students?page=1&per_page=20" \
  -H "Authorization: Bearer $TOKEN"
```

#### Create Student
```bash
curl -X POST http://localhost/ekiliSense/api/v1/students \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "+255712345678",
    "class_id": "class_123"
  }'
```

#### Update Student
```bash
curl -X PUT http://localhost/ekiliSense/api/v1/students/student_123 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Smith",
    "phone": "+255787654321"
  }'
```

### 3. Analytics (NEW)

#### Get Dashboard Analytics
```bash
curl -X GET http://localhost/ekiliSense/api/v1/analytics/dashboard \
  -H "Authorization: Bearer $TOKEN"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "students": {
      "total": 250,
      "new_today": 5,
      "new_this_month": 20
    },
    "teachers": {
      "total": 15,
      "new_this_month": 2
    },
    "assignments": {
      "total": 45,
      "active": 12,
      "upcoming": 8
    },
    "attendance": {
      "total_records": 3500,
      "present": 3150,
      "absent": 350,
      "attendance_rate": 90.00
    },
    "performance_trends": [
      {
        "month": "2024-01",
        "avg_marks": 75.5,
        "students": 240
      }
    ]
  }
}
```

#### Get Student Performance Report
```bash
curl -X GET "http://localhost/ekiliSense/api/v1/analytics/student-performance?class_id=class_123" \
  -H "Authorization: Bearer $TOKEN"
```

#### Get Class Comparison
```bash
curl -X GET http://localhost/ekiliSense/api/v1/analytics/class-comparison \
  -H "Authorization: Bearer $TOKEN"
```

### 4. Webhooks (NEW)

#### Register Webhook
```bash
curl -X POST http://localhost/ekiliSense/api/v1/webhooks \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://your-app.com/webhook",
    "events": ["student.created", "assignment.created", "grade.updated"]
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "webhook_id": "webhook_abc123",
    "url": "https://your-app.com/webhook",
    "secret": "a1b2c3d4e5f6...",
    "events": ["student.created", "assignment.created", "grade.updated"]
  }
}
```

#### List Webhooks
```bash
curl -X GET http://localhost/ekiliSense/api/v1/webhooks \
  -H "Authorization: Bearer $TOKEN"
```

#### Delete Webhook
```bash
curl -X DELETE http://localhost/ekiliSense/api/v1/webhooks/webhook_abc123 \
  -H "Authorization: Bearer $TOKEN"
```

### 5. Subscription Management (NEW)

#### Get Current Subscription
```bash
curl -X GET http://localhost/ekiliSense/api/v1/subscription/current \
  -H "Authorization: Bearer $TOKEN"
```

#### Get Available Plans
```bash
curl -X GET http://localhost/ekiliSense/api/v1/subscription/plans \
  -H "Authorization: Bearer $TOKEN"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "plan_id": "plan_free",
      "name": "Free",
      "description": "Perfect for small schools",
      "price": 0,
      "currency": "TZS",
      "billing_cycle": "monthly",
      "features": ["Basic student management", "Teacher accounts"],
      "max_students": 50,
      "max_teachers": 5,
      "max_storage_gb": 1
    },
    {
      "plan_id": "plan_premium",
      "name": "Premium",
      "description": "Advanced features for larger schools",
      "price": 50000,
      "currency": "TZS",
      "billing_cycle": "monthly",
      "features": ["All Basic features", "Advanced analytics", "API access"],
      "max_students": 1000,
      "max_teachers": 100,
      "max_storage_gb": 20
    }
  ]
}
```

#### Subscribe to Plan
```bash
curl -X POST http://localhost/ekiliSense/api/v1/subscription/subscribe \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "plan_id": "plan_premium"
  }'
```

#### Get Billing History
```bash
curl -X GET http://localhost/ekiliSense/api/v1/subscription/billing-history \
  -H "Authorization: Bearer $TOKEN"
```

### 6. Parent Portal (NEW)

#### Get Parent's Children
```bash
curl -X GET http://localhost/ekiliSense/api/v1/parent/children \
  -H "Authorization: Bearer $PARENT_TOKEN"
```

#### Get Child's Grades
```bash
curl -X GET http://localhost/ekiliSense/api/v1/parent/children/student_123/grades \
  -H "Authorization: Bearer $PARENT_TOKEN"
```

#### Get Child's Attendance
```bash
curl -X GET http://localhost/ekiliSense/api/v1/parent/children/student_123/attendance \
  -H "Authorization: Bearer $PARENT_TOKEN"
```

## JavaScript/React Examples

### Using Axios

```javascript
import axios from 'axios';

const API_BASE = 'http://localhost/ekiliSense/api/v1';

// Create axios instance with interceptor
const api = axios.create({
  baseURL: API_BASE,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Add token to requests
api.interceptors.request.use(config => {
  const token = localStorage.getItem('access_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Login
async function login(email, password) {
  const response = await api.post('/auth/login', { email, password });
  localStorage.setItem('access_token', response.data.data.access_token);
  return response.data;
}

// Get students
async function getStudents(page = 1, perPage = 20) {
  const response = await api.get(`/students?page=${page}&per_page=${perPage}`);
  return response.data;
}

// Get analytics dashboard
async function getAnalytics() {
  const response = await api.get('/analytics/dashboard');
  return response.data;
}

// Register webhook
async function registerWebhook(url, events) {
  const response = await api.post('/webhooks', { url, events });
  return response.data;
}

// Get subscription plans
async function getPlans() {
  const response = await api.get('/subscription/plans');
  return response.data;
}
```

## Python Examples

### Using Requests

```python
import requests

API_BASE = 'http://localhost/ekiliSense/api/v1'

class EkiliSenseAPI:
    def __init__(self):
        self.session = requests.Session()
        self.token = None
    
    def login(self, email, password):
        response = self.session.post(
            f'{API_BASE}/auth/login',
            json={'email': email, 'password': password}
        )
        data = response.json()
        self.token = data['data']['access_token']
        self.session.headers.update({'Authorization': f'Bearer {self.token}'})
        return data
    
    def get_students(self, page=1, per_page=20):
        response = self.session.get(
            f'{API_BASE}/students',
            params={'page': page, 'per_page': per_page}
        )
        return response.json()
    
    def get_analytics(self):
        response = self.session.get(f'{API_BASE}/analytics/dashboard')
        return response.json()
    
    def register_webhook(self, url, events):
        response = self.session.post(
            f'{API_BASE}/webhooks',
            json={'url': url, 'events': events}
        )
        return response.json()
    
    def get_subscription_plans(self):
        response = self.session.get(f'{API_BASE}/subscription/plans')
        return response.json()

# Usage
api = EkiliSenseAPI()
api.login('admin@school.com', 'password')
students = api.get_students()
analytics = api.get_analytics()
```

## Webhook Verification

When receiving webhooks, verify the signature:

```javascript
const crypto = require('crypto');

function verifyWebhook(payload, signature, secret) {
  const expectedSignature = crypto
    .createHmac('sha256', secret)
    .update(JSON.stringify(payload))
    .digest('hex');
  
  return signature === expectedSignature;
}

// Express.js example
app.post('/webhook', (req, res) => {
  const signature = req.headers['x-webhook-signature'];
  const secret = 'your_webhook_secret';
  
  if (!verifyWebhook(req.body, signature, secret)) {
    return res.status(401).send('Invalid signature');
  }
  
  // Process webhook
  const { event, data } = req.body;
  console.log(`Received event: ${event}`, data);
  
  res.status(200).send('OK');
});
```

## Error Handling

```javascript
async function safeApiCall() {
  try {
    const response = await api.get('/students');
    return response.data;
  } catch (error) {
    if (error.response) {
      // Server responded with error
      console.error('Error:', error.response.data.message);
      
      if (error.response.status === 401) {
        // Token expired - refresh it
        await refreshToken();
      } else if (error.response.status === 429) {
        // Rate limit exceeded
        const retryAfter = error.response.data.errors.retry_after;
        console.log(`Rate limited. Retry after ${retryAfter} seconds`);
      }
    } else {
      // Network error
      console.error('Network error:', error.message);
    }
  }
}
```

## Rate Limit Handling

```javascript
// Exponential backoff for rate limits
async function apiCallWithRetry(fn, maxRetries = 3) {
  for (let i = 0; i < maxRetries; i++) {
    try {
      return await fn();
    } catch (error) {
      if (error.response?.status === 429) {
        const delay = Math.pow(2, i) * 1000; // 1s, 2s, 4s
        console.log(`Rate limited. Waiting ${delay}ms...`);
        await new Promise(resolve => setTimeout(resolve, delay));
      } else {
        throw error;
      }
    }
  }
  throw new Error('Max retries exceeded');
}
```
