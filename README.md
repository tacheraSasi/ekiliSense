# ekiliSense - Modern School Management SaaS Platform

[![License](https://img.shields.io/badge/license-Proprietary-blue.svg)](https://ekilie.com)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-blue.svg)](https://mysql.com)
[![API Version](https://img.shields.io/badge/API-v1-green.svg)](api/v1/README.md)

A comprehensive, production-ready school management system designed for multi-tenant SaaS deployment. Built for schools across Tanzania and East Africa with localized payment integration and modern API-first architecture.

🌐 **Live Demo:** [sense.ekilie.com](https://sense.ekilie.com)

## 🎯 Overview

ekiliSense transforms traditional school administration into a modern, cloud-based experience. With robust multi-tenant architecture, RESTful API, and comprehensive features, it serves schools of all sizes - from small academies to large educational institutions.

## ✨ Key Features

### 🏫 Core Management
- **Multi-tenant Architecture**: Isolated data per school with shared infrastructure
- **User Management**: Administrators, Teachers, Students, Parents
- **Class Management**: Flexible class structure with subject assignments
- **Academic Tracking**: Homework, exams, grades, progress reports
- **Attendance System**: Daily attendance tracking with reporting

### 📱 Modern SaaS Features (NEW)
- **RESTful API v1**: Complete API access for mobile apps and integrations
- **JWT Authentication**: Secure, stateless authentication with token refresh
- **Parent Portal**: Parents can track student progress, grades, and attendance
- **Analytics Dashboard**: Real-time insights into school performance
- **Webhook System**: Event-driven integrations with third-party tools
- **Subscription Management**: Flexible pricing tiers (Free, Basic, Premium, Enterprise)
- **Rate Limiting**: Built-in protection against API abuse

### 🔐 Security & Performance
- **Bcrypt Password Hashing**: Modern password security (auto-upgrades from MD5)
- **Role-Based Access Control**: Granular permissions system
- **API Rate Limiting**: 100 requests/minute per user
- **Input Sanitization**: SQL injection and XSS prevention
- **CORS Support**: Secure cross-origin requests

### 💳 Payment Integration
- **Pesapal Integration**: East African payment gateway
- **Multiple Plans**: Free to Enterprise pricing
- **Auto-renewal**: Seamless subscription management
- **Billing History**: Complete transaction tracking

### 📊 Analytics & Reporting
- **Dashboard Analytics**: Real-time school statistics
- **Student Performance**: Individual and class-level reports
- **Teacher Effectiveness**: Performance tracking and metrics
- **Attendance Reports**: Daily, weekly, monthly summaries
- **Trend Analysis**: 6-month performance trends

## 🚀 Quick Start

### Installation

```bash
# Clone repository
git clone https://github.com/tacheraSasi/ekiliSense.git
cd ekiliSense

# Install dependencies
composer install

# Configure environment
cp .env.example .env
# Edit .env with your database credentials

# Setup database
mysql -u username -p < database_schema.sql
cat api/v1/migrations/*.sql | mysql -u username -p database_name

# Configure web server (Apache/Nginx)
# See INSTALLATION.md for detailed instructions
```

### API Quick Test

```bash
# Register a school
curl -X POST http://localhost/ekiliSense/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "school_name": "Test School",
    "email": "admin@testschool.com",
    "password": "SecurePass123",
    "phone": "+255712345678"
  }'

# Login
curl -X POST http://localhost/ekiliSense/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@testschool.com",
    "password": "SecurePass123"
  }'
```

## 📚 Documentation

- **[API Documentation](api/v1/README.md)**: Complete API reference
- **[Installation Guide](api/v1/INSTALLATION.md)**: Detailed setup instructions
- **[API Examples](api/v1/EXAMPLES.md)**: Code samples in multiple languages
- **[System Analysis](docs/SYSTEM_ANALYSIS_AND_ROADMAP.md)**: Architecture and roadmap
- **[Authentication Flow](docs/AUTHENTICATION_FLOW.md)**: Security implementation

## 🔌 API Integration

### JavaScript/React

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'https://your-domain.com/api/v1',
  headers: { 'Content-Type': 'application/json' }
});

// Login
const response = await api.post('/auth/login', {
  email: 'admin@school.com',
  password: 'password'
});

// Use token
api.defaults.headers.common['Authorization'] = `Bearer ${response.data.data.access_token}`;

// Get students
const students = await api.get('/students');
```

### Python

```python
import requests

class EkiliSenseAPI:
    def __init__(self, base_url):
        self.base_url = base_url
        self.session = requests.Session()
    
    def login(self, email, password):
        response = self.session.post(
            f'{self.base_url}/auth/login',
            json={'email': email, 'password': password}
        )
        token = response.json()['data']['access_token']
        self.session.headers.update({'Authorization': f'Bearer {token}'})
        return response.json()
    
    def get_students(self, page=1):
        return self.session.get(
            f'{self.base_url}/students',
            params={'page': page}
        ).json()
```

### Mobile (React Native)

```javascript
const API_BASE = 'https://your-domain.com/api/v1';

async function login(email, password) {
  const response = await fetch(`${API_BASE}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await response.json();
  await AsyncStorage.setItem('access_token', data.data.access_token);
  return data;
}
```

## 📊 API Endpoints

### Authentication
- `POST /auth/login` - User authentication
- `POST /auth/register` - School registration
- `POST /auth/refresh` - Refresh access token
- `POST /auth/logout` - User logout

### School Management
- `GET /schools/profile` - Get school profile
- `PUT /schools/profile` - Update profile
- `GET /schools/stats` - Get statistics

### Student Management
- `GET /students` - List students (paginated)
- `POST /students` - Create student
- `GET /students/{id}` - Get student details
- `PUT /students/{id}` - Update student
- `DELETE /students/{id}` - Delete student

### Analytics (NEW)
- `GET /analytics/dashboard` - Dashboard metrics
- `GET /analytics/student-performance` - Student reports
- `GET /analytics/teacher-performance` - Teacher reports
- `GET /analytics/class-comparison` - Class comparison

### Webhooks (NEW)
- `GET /webhooks` - List webhooks
- `POST /webhooks` - Register webhook
- `DELETE /webhooks/{id}` - Delete webhook

### Subscription (NEW)
- `GET /subscription/current` - Current subscription
- `GET /subscription/plans` - Available plans
- `POST /subscription/subscribe` - Subscribe to plan
- `GET /subscription/billing-history` - Billing history

### Parent Portal (NEW)
- `GET /parent/children` - Get children
- `GET /parent/children/{id}/grades` - Child's grades
- `GET /parent/children/{id}/attendance` - Child's attendance
- `GET /parent/notifications` - Get notifications

## 💰 Pricing Tiers

### Free Plan
- Up to 50 students
- 5 teachers
- Basic features
- 1GB storage

### Basic Plan (10,000 TZS/month)
- Up to 200 students
- 20 teachers
- Parent portal
- Assignment management
- 5GB storage

### Premium Plan (50,000 TZS/month)
- Up to 1000 students
- 100 teachers
- Advanced analytics
- API access
- Webhooks
- 20GB storage

### Enterprise Plan (150,000 TZS/month)
- Unlimited students
- Unlimited teachers
- Custom integrations
- White-labeling
- Dedicated support
- 100GB storage

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Authentication**: JWT (JSON Web Tokens)
- **API**: RESTful architecture
- **Payments**: Pesapal integration
- **Security**: Bcrypt, CORS, Rate Limiting
- **Dependencies**: Composer

## 🤝 Contributing

This is a proprietary project. For feature requests or bug reports, please contact support@ekilie.com.

## 📄 License

Proprietary software owned by ekilie. All rights reserved.

## 🔗 Links

- **Live System**: [sense.ekilie.com](https://sense.ekilie.com)
- **Company**: [ekilie.com](https://ekilie.com)
- **Support**: support@ekilie.com
- **Documentation**: [API Docs](api/v1/README.md)

## 📞 Support

- **Email**: support@ekilie.com
- **GitHub Issues**: [Report Issue](https://github.com/tacheraSasi/ekiliSense/issues)
- **Documentation**: [Full Docs](docs/)

## 🙏 Acknowledgments

Built with ❤️ in Tanzania for schools across East Africa.

---

**Note**: This README covers the SaaS transformation with RESTful API v1. For the original PHP application documentation, see [docs/SYSTEM_ANALYSIS_AND_ROADMAP.md](docs/SYSTEM_ANALYSIS_AND_ROADMAP.md).
