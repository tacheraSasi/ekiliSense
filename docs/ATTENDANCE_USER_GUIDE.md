# Attendance System - User Guide for Teachers

## Table of Contents
1. [Student Attendance](#student-attendance)
2. [Staff Attendance](#staff-attendance)
3. [Viewing Reports](#viewing-reports)
4. [Sending Alerts](#sending-alerts)
5. [Exporting Data](#exporting-data)
6. [Troubleshooting](#troubleshooting)

---

## Student Attendance

### Marking Individual Student Attendance

1. Navigate to your class dashboard
2. Go to the **Attendance** section
3. Find the student in the list
4. Click the **Mark Present** button next to their name
5. The system will confirm the attendance has been recorded

**Note**: Students can only be marked present once per day. If you try to mark them again, the system will accept it but won't create a duplicate record.

### Marking All Students Present

For days when the entire class is present:

1. Navigate to your class dashboard
2. Go to the **Attendance** section
3. Click the **Mark All** button at the top of the page
4. Confirm the action when prompted
5. All students in the class will be marked present

**Important**: This action cannot be undone. Use it only when you're certain all students are present.

---

## Staff Attendance

### Marking Your Own Attendance

Teachers can mark their own attendance using the geolocation feature:

1. Navigate to your **Profile** page
2. Click on the **Work Attendance** tab
3. Click the **Sign** button
4. **Allow location access** when prompted by your browser
5. The system will verify you are within 100 meters of the school
6. If verified, your attendance will be marked automatically

### Location Requirements

- You must be within **100 meters** of the school location
- Your device must have **GPS/location services enabled**
- Your browser must allow **location permissions**
- The page must be accessed via **HTTPS** (secure connection)

### What if I'm not at the school location?

If you're outside the allowed radius, you'll see an error message showing:
- Your distance from the school
- A message that you need to be within 100m

You cannot mark attendance remotely. This is a security feature to ensure accurate attendance tracking.

---

## Viewing Reports

### Accessing Attendance Reports

1. Navigate to your class dashboard
2. Click on **Attendance Reports** in the sidebar
3. Select the date range you want to review
4. Click **Apply Filters**

### Understanding the Report

The report shows:
- **Student Name**: Full name of each student
- **Present Days**: Number of days the student was present
- **Total Days**: Total number of school days in the period
- **Attendance Percentage**: Calculated percentage

#### Color Coding
- 🟢 **Green** (90%+): Excellent attendance
- ⚪ **White** (75-89%): Good attendance
- 🔴 **Red** (<75%): Low attendance - requires attention

### Filtering Reports

You can filter reports by:
- **Start Date**: Beginning of the period
- **End Date**: End of the period
- **Default**: Current month is selected automatically

---

## Sending Alerts

### When to Send an Alert

Send attendance alerts when:
- A student's attendance drops below 75%
- There's a pattern of absences
- Parents need to be informed about attendance issues

### How to Send an Alert

1. Open the **Attendance Reports** page
2. Find students with low attendance (marked in red)
3. Click the **Alert Parents** button next to the student's name
4. Confirm the action when prompted
5. The system will:
   - Create a message in the parent's inbox
   - Send an email to the parent (if email is on file)
   - Mark the alert as urgent

### What's Included in the Alert

The alert contains:
- Student's name
- Date range of the report
- Number of present days
- Total school days
- Attendance percentage
- Warning message (if below 75%)
- Contact information for follow-up

---

## Exporting Data

### Export Formats

You can export attendance data in three formats:

#### 1. CSV (Spreadsheet)
Best for: Data analysis, importing to Excel

**How to Export**:
1. Open Attendance Reports
2. Set your date range
3. Click **Export Report**
4. The CSV file will download automatically

**Use Cases**:
- Importing into Excel or Google Sheets
- Further data analysis
- Creating custom reports

#### 2. PDF (Professional Report)
Best for: Official records, printing

**How to Export**:
1. Open Attendance Reports
2. Set your date range
3. Add `&format=pdf` to the URL
4. The PDF will download automatically

**Features**:
- Professional formatting
- School header
- Color-coded attendance levels
- Ready to print

#### 3. HTML (Printable)
Best for: Quick printing, viewing

**How to Export**:
1. Open Attendance Reports
2. Set your date range
3. Add `&format=html` to the URL
4. Click the **Print** button in the page

**Features**:
- Clean, printable layout
- Print-optimized design
- No buttons or navigation in print

---

## Troubleshooting

### Student Attendance Issues

**Problem**: "Attendance already marked for today"
- **Solution**: This is normal. Students can only be marked once per day.

**Problem**: Student not appearing in attendance list
- **Solution**: 
  1. Verify the student is enrolled in your class
  2. Check with the administrator
  3. Refresh the page

**Problem**: "Student not found" error
- **Solution**: Contact the school administrator to verify the student's enrollment

### Staff Attendance Issues

**Problem**: "Geolocation is not supported"
- **Solution**: Use a modern browser (Chrome, Firefox, Safari, Edge)

**Problem**: "You are X km from the office"
- **Solution**: 
  1. Make sure you're physically at the school
  2. Move closer to the main building
  3. Check if your GPS is working properly

**Problem**: Location permission denied
- **Solution**:
  1. Check browser settings
  2. Allow location access for the site
  3. Reload the page and try again

**Problem**: Location stuck on "Loading"
- **Solution**:
  1. Check if GPS is enabled on your device
  2. Move to a location with better GPS signal
  3. Try refreshing the page

### Report Issues

**Problem**: Report shows wrong dates
- **Solution**: 
  1. Check the date range filters
  2. Use YYYY-MM-DD format
  3. Make sure start date is before end date

**Problem**: PDF not generating
- **Solution**: Use HTML format and print to PDF using your browser

**Problem**: Email alerts not being received
- **Solution**:
  1. Verify parent email is correct in the system
  2. Check spam/junk folders
  3. Contact school IT support

### Export Issues

**Problem**: CSV file opens with garbled text
- **Solution**: 
  1. Open Excel first
  2. Use Data > Import > From Text/CSV
  3. Select UTF-8 encoding

**Problem**: Nothing happens when clicking export
- **Solution**:
  1. Check browser's download settings
  2. Disable popup blockers
  3. Try a different browser

---

## Best Practices

### Daily Routine
1. Mark attendance first thing in the morning
2. Review any absences
3. Note patterns or concerns

### Weekly Routine
1. Review attendance reports
2. Identify students with low attendance
3. Send alerts to parents as needed

### Monthly Routine
1. Generate full month report
2. Export data for records
3. Share report with administration
4. Follow up on previous alerts

### Tips for Accuracy
- ✅ Mark attendance at the same time each day
- ✅ Double-check before marking all students present
- ✅ Keep notes about extended absences
- ✅ Follow up on patterns of absences
- ✅ Communicate with parents proactively

---

## Need Help?

If you encounter any issues not covered in this guide:

1. **Check with your school administrator**
2. **Contact technical support**: support@ekilie.com
3. **Report a bug**: Use the feedback form in the system

---

**Remember**: Accurate attendance tracking is crucial for student success. Take a few minutes each day to ensure records are up to date!
