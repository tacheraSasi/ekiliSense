# Attendance Tracking & Reporting System

## Overview
The ekiliSense attendance system provides comprehensive tracking and reporting capabilities for both student and staff attendance. This document outlines the features, usage, and technical implementation.

## Features

### 1. Student Attendance Tracking
- **Single Student Marking**: Mark individual student attendance
- **Bulk Marking**: Mark all students in a class as present at once
- **Date-based Tracking**: Automatic tracking of attendance by date
- **Duplicate Prevention**: System prevents duplicate attendance records for the same day

### 2. Staff Attendance Tracking (Geolocation-based)
- **Geolocation Verification**: Teachers must be within a specified radius of the school to mark attendance
- **Configurable Location**: School location and allowed radius can be customized
- **Distance Calculation**: Real-time distance calculation from school location
- **User Feedback**: Clear error messages when outside allowed area

### 3. Attendance Reports
- **Multiple Export Formats**:
  - CSV: For spreadsheet analysis
  - PDF: For professional reports (requires TCPDF library)
  - HTML: For printable reports
- **Date Range Filtering**: Generate reports for specific date ranges
- **Color-coded Indicators**:
  - Red: Attendance below 75% (warning)
  - Green: Attendance 90% or above (excellent)
- **Statistics**: Present days, total days, and attendance percentage

### 4. Real-time Notifications
- **Low Attendance Alerts**: Automatic alerts when student attendance falls below 75%
- **Email Notifications**: HTML-formatted emails sent to parents via PHP mail()
- **In-app Messaging**: Messages stored in the system for parent access
- **Urgent Flagging**: Low attendance alerts marked as urgent

## Usage

### Student Attendance

#### Mark Single Student
```php
POST /console/class/teacher/server/attendance.php
{
    "form-type": "single",
    "student": "student_id",
    "class_id": "class_id"
}
```

#### Mark All Students
```php
POST /console/class/teacher/server/attendance.php
{
    "form-type": "all",
    "class": "class_id"
}
```

### Staff Attendance

#### Mark Teacher Attendance (with Geolocation)
```javascript
// Call from JavaScript with configurable parameters
stuffAttendance(
    submitUrl,           // Server endpoint
    schoolLatitude,      // School latitude (default: -6.832128)
    schoolLongitude,     // School longitude (default: 39.23968)
    allowedRadius        // Radius in meters (default: 100)
);
```

```php
POST /console/server/add.php
{
    "form-type": "staff-attendance",
    "latitude": "user_latitude",
    "longitude": "user_longitude",
    "owner": "teacher_email"
}
```

### Generate Attendance Reports

#### Export as CSV
```
GET /console/class/teacher/export_attendance.php?format=csv&start_date=2024-01-01&end_date=2024-01-31
```

#### Export as PDF
```
GET /console/class/teacher/export_attendance.php?format=pdf&start_date=2024-01-01&end_date=2024-01-31
```

#### View as HTML (Printable)
```
GET /console/class/teacher/export_attendance.php?format=html&start_date=2024-01-01&end_date=2024-01-31
```

### Send Attendance Alerts

```php
POST /console/class/teacher/server/send_attendance_alert.php
{
    "student_id": "student_id",
    "start_date": "2024-01-01",
    "end_date": "2024-01-31"
}
```

## Technical Implementation

### Database Schema

#### student_attendance Table
```sql
CREATE TABLE student_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(255),
    school_uid VARCHAR(255),
    class_id VARCHAR(255),
    attendance_date DATE,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### staff_attendance Table (New)
```sql
CREATE TABLE staff_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_uid VARCHAR(255) NOT NULL,
    teacher_id VARCHAR(255) NOT NULL,
    attendance_date DATE NOT NULL,
    status TINYINT(1) DEFAULT 1,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_attendance (teacher_id, attendance_date)
);
```

### Security Improvements

1. **Prepared Statements**: All database queries use prepared statements to prevent SQL injection
2. **Input Validation**: All user inputs are validated and sanitized
3. **Session Management**: Proper session checks for authentication
4. **Access Control**: Teachers can only mark attendance for their assigned classes

### Configuration

#### Geolocation Settings
The geolocation settings can be configured by modifying the function call:

```javascript
// Default values (Dar es Salaam, Tanzania)
const schoolLatitude = -6.832128;
const schoolLongitude = 39.23968;
const allowedRadius = 100; // meters

// Call with custom values
stuffAttendance(submitUrl, schoolLatitude, schoolLongitude, allowedRadius);
```

#### Email Settings
Email notifications use PHP's built-in mail() function. For production use, configure your server's mail settings or integrate with a third-party email service.

## Error Handling

### Common Error Messages

1. **"Access denied"**: User not authenticated or lacks permissions
2. **"Student not found"**: Invalid student ID or student not in the school
3. **"Attendance already marked for today"**: Duplicate attendance attempt
4. **"You are X km from the office"**: Teacher outside allowed geolocation radius
5. **"Failed to send attendance alert"**: Email delivery failure

## Best Practices

1. **Regular Backups**: Backup attendance data regularly
2. **Monitor Email Delivery**: Check mail server logs for delivery issues
3. **Update Geolocation**: Update school coordinates if location changes
4. **Review Reports Monthly**: Generate and review attendance reports monthly
5. **Parent Communication**: Follow up on low attendance alerts promptly

## Migration Guide

### Setting Up Staff Attendance Table

Run the migration script:
```bash
php database/migrate.php
```

Or manually execute:
```sql
source database/migrations/create_staff_attendance_table.sql;
```

## Troubleshooting

### Geolocation Not Working
- Ensure HTTPS is enabled (geolocation requires secure context)
- Check browser permissions for location access
- Verify GPS/location services are enabled on device

### Email Notifications Not Sending
- Check PHP mail configuration in php.ini
- Verify SMTP settings on server
- Check spam/junk folders
- Review mail server logs

### Reports Not Generating
- Verify date format (YYYY-MM-DD)
- Check user permissions
- Ensure database connection is stable
- For PDF, verify TCPDF library is installed

## Future Enhancements

1. **Biometric Integration**: Support for fingerprint/face recognition
2. **Mobile App**: Dedicated mobile app for attendance marking
3. **Analytics Dashboard**: Visual analytics and trends
4. **Automated Reports**: Schedule automatic report generation
5. **SMS Notifications**: Add SMS alerts in addition to email
6. **QR Code Attendance**: Quick attendance via QR code scanning
7. **Leave Management**: Integrate leave/absence management system

## Support

For issues or questions:
- Email: support@ekilie.com
- Documentation: https://ekilie.com/docs
- Repository: https://github.com/tacheraSasi/ekiliSense

---

**Version**: 2.0
**Last Updated**: 2024
**Maintained By**: ekiliSense Team
