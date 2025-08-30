# ekiliSense Authentication & Application Flow Documentation

## 🔐 Complete Authentication Flow

### **1. Initial Access & Landing**

```
User visits: https://ekilie.com/
    ↓
Landing Page (index.php) displays:
    - Feature showcase
    - "Get Started for free" button
    - "Sign in" link for existing users
    ↓
User clicks "Get Started" → Redirects to /onboarding/
User clicks "Sign in" → Redirects to /auth/
```

### **2. New School Onboarding Flow**

#### **Step 1: School Registration (/onboarding/index.html)**
```html
Form Fields Collected:
├── School Information
│   ├── School Name (required)
│   ├── School Email (required, becomes login email)
│   ├── School Phone (required)
│   └── About School (optional)
├── Administrator Details
│   ├── Admin Full Name (required)
│   ├── Admin Phone (required)
│   └── Admin Email (required)
└── Security
    ├── Password (required, encrypted with MD5)
    └── Confirm Password (required)
```

#### **Step 2: Terms & Policies Acceptance**
```javascript
// User must agree to:
- Terms & Conditions (/terms-and-policies/)
- Privacy Policy (/terms-and-policies/)
- Data protection guarantee
```

#### **Step 3: Account Creation Processing**
```php
// Onboarding server processing:
POST /onboarding/server/signup.php
    ↓
1. Validate form data
2. Check for existing school email
3. Generate unique school ID (school_uid)
4. Hash password with MD5 (⚠️ SECURITY ISSUE)
5. Insert into `schools` table
6. Set session variables
7. Redirect to /console/ (main dashboard)
```

### **3. Existing User Authentication Flow**

#### **Step 1: Login Page (/auth/index.php)**
```php
// Session check first:
if(isset($_SESSION['School_uid'])){
    header("location:../console");  // Already logged in
}

// Login form fields:
- Email (school_email)
- Password (encrypted)
- "Remember me" option
```

#### **Step 2: Login Processing (/auth/server/login.php)**
```php
// Authentication logic:
1. Check login attempt counter (max 3 attempts)
2. Validate email and password fields
3. Query database for matching school email
4. Compare MD5 hash of input password with stored hash
5. If successful:
   - Reset login attempts counter
   - Set $_SESSION['School_uid'] 
   - Return "success" status
6. If failed:
   - Increment login attempts
   - Return error message with remaining attempts
7. If max attempts reached:
   - Block further attempts
   - Return "Too many attempts" message
```

#### **Step 3: Session Management**
```php
// Session variables set on successful login:
$_SESSION['School_uid'] = $row['unique_id'];  // Primary identifier
$_SESSION['teacher_email'] = $email;          // If teacher login
$_SESSION['login_attempts'] = 0;              // Reset counter
```

### **4. Post-Authentication Routing**

#### **Main Console Access (/console/index.php)**
```php
// Middleware: school_auth.php
1. Check if $_SESSION['School_uid'] exists
2. If not set → Redirect to /auth/
3. If teacher session exists:
   a. Check if user is class teacher
   b. Route to appropriate interface:
      - Class Teacher → /console/class/teacher/
      - Regular Teacher → /console/teacher/
4. Load school data and statistics
5. Display main dashboard
```

### **5. Role-Based Access Control**

#### **School Administrator Flow**
```php
// Full access to all features:
├── Dashboard (statistics, overview)
├── Teachers Management
│   ├── Add new teachers
│   ├── Import teachers (CSV/Excel)
│   ├── View teacher profiles
│   └── Assign roles
├── Students Management
│   ├── Student enrollment
│   ├── Class assignments
│   └── Academic records
├── Classes Management
│   ├── Create/edit classes
│   ├── Assign teachers
│   └── Subject management
├── Settings & Account
│   ├── School profile
│   ├── Change password
│   └── Subscription management
└── Reports & Analytics
```

#### **Teacher Authentication (/middlwares/teacher_auth.php)**
```php
// Teacher-specific access control:
1. Verify both School_uid AND teacher_email sessions
2. If not set → Redirect to external teacher auth
3. Check Google account integration
4. Load teacher profile and permissions
5. Get assigned classes and subjects
6. Route to appropriate teacher interface
```

#### **Class Teacher Flow**
```php
// Enhanced teacher permissions:
├── Class Management Dashboard
├── Student Attendance Tracking
├── Grade Management
├── Homework Assignment Creation
├── Parent Communication
├── Class Performance Analytics
└── Resource Sharing
```

#### **Regular Teacher Flow**
```php
// Standard teaching tools:
├── Teaching Dashboard
├── Subject Management
├── Assignment Creation
├── Grade Entry
├── Student Progress View
└── Resource Access
```

## 🔒 Security Implementation Analysis

### **Current Security Measures**
```php
// ✅ Good practices:
- Session-based authentication
- SQL injection prevention with mysqli_real_escape_string()
- Login attempt limiting (3 attempts max)
- Input validation and sanitization
- Session timeout handling

// ⚠️ Security concerns:
- MD5 password hashing (easily crackable)
- No CSRF protection
- No 2FA implementation
- Session fixation vulnerability
- No password complexity requirements
```

### **Authentication Middleware Stack**

#### **1. School Authentication (/middlwares/school_auth.php)**
```php
Purpose: Verify school admin access
Functions:
├── Session validation
├── School data loading
├── Statistics calculation (students, teachers, classes)
├── Teacher role detection and routing
└── Data preparation for dashboard
```

#### **2. Teacher Authentication (/middlwares/teacher_auth.php)**
```php
Purpose: Verify teacher access and permissions
Functions:
├── Dual session validation (school + teacher)
├── Google account integration check
├── Teacher profile loading
├── Class assignment verification
├── Subject permissions loading
└── Student data access control
```

## 📊 Database Schema Integration

### **Authentication Tables**
```sql
-- Primary authentication table
schools: 
├── unique_id (primary key, session identifier)
├── school_email (login username)
├── auth (MD5 password hash)
├── School_name
├── School_phone
└── about

-- Teacher authentication
teachers:
├── teacher_id
├── School_unique_id (foreign key)
├── teacher_email (login identifier)
├── teacher_fullname
└── permissions

-- Google OAuth integration
teachers_google:
├── email (teacher identifier)
├── google_id
├── access_token
└── refresh_token
```

### **Session Management Tables**
```sql
-- Payment subscriptions (linked to sessions)
subscriptions:
├── user_id (links to school)
├── amount
├── token (payment consent)
├── interval (billing cycle)
├── last_payment
└── next_payment
```

## 🚀 Flow Optimization Recommendations

### **1. Security Improvements**
```php
// Replace MD5 with secure hashing:
// OLD:
$user_pass = md5($password);

// NEW:
$user_pass = password_hash($password, PASSWORD_DEFAULT);
$is_valid = password_verify($password, $stored_hash);
```

### **2. Enhanced Session Management**
```php
// Add security headers:
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => 'ekilie.com',
    'secure' => true,      // HTTPS only
    'httponly' => true,    // No JavaScript access
    'samesite' => 'Strict' // CSRF protection
]);
```

### **3. Multi-Factor Authentication**
```php
// Proposed 2FA flow:
1. Primary authentication (email/password)
2. Generate TOTP code
3. Send via SMS or email
4. Verify code before session creation
5. Set MFA completion flag in session
```

### **4. API Authentication**
```php
// JWT token-based API auth:
1. Issue JWT on successful login
2. Include school_uid and role in payload
3. Verify JWT on API endpoints
4. Refresh token rotation
5. Blacklist on logout
```

## 🔄 Complete User Journey Map

### **New School Journey**
```
Marketing Site → Onboarding → Account Creation → Email Verification → 
Console Setup → Teacher Invitation → Student Enrollment → 
Payment Setup → Full Platform Access
```

### **Daily User Journey**
```
Login → Dashboard → Role Detection → Feature Access → 
Task Completion → Data Sync → Session Management → Logout
```

### **Teacher Journey**
```
External Auth → School Validation → Role Assignment → 
Interface Routing → Class Access → Student Interaction → 
Grade Management → Parent Communication
```

## 📱 Multi-Platform Access

### **Current Platform Support**
- ✅ Web (responsive design)
- ⚠️ Mobile (web-based, needs native app)
- ❌ Desktop app (not implemented)
- ❌ Tablet optimization (basic responsive)

### **Recommended Platform Expansion**
- [ ] Native mobile apps (iOS/Android)
- [ ] Progressive Web App (PWA)
- [ ] Desktop Electron app
- [ ] Tablet-optimized interface

## 🔗 Integration Points

### **External Authentication Systems**
```php
// Google OAuth (partially implemented):
- Teacher Google account integration
- Google Classroom sync potential
- Google Drive file management

// Planned integrations:
- Microsoft Azure AD
- LDAP/Active Directory
- Social media login options
```

### **Payment Flow Integration**
```php
// Pesapal integration workflow:
Login → Subscription Check → Payment Processing → 
Feature Unlock → Usage Tracking → Renewal Handling
```

---

This authentication flow documentation provides a complete technical overview of how users access and navigate the ekiliSense platform, with specific attention to security considerations and optimization opportunities.