# Teacher Import Feature Documentation

## Overview
The Teacher Import feature allows school administrators to bulk import teacher data using CSV or Excel files, significantly reducing the time needed to add multiple teachers to the system.

## How to Use

### 1. Access the Import Feature
- Navigate to the ekiliSense Console dashboard
- Click the "Import Teachers" button (blue button with spreadsheet icon)

### 2. Prepare Your Data File

#### Supported File Formats
- **CSV files** (.csv) - Fully supported
- **Excel files** (.xlsx) - Supported with basic parsing
- **Legacy Excel** (.xls) - Not supported, please convert to .xlsx or CSV

#### Required Columns
Your file must contain the following columns (column names are flexible):

| Required | Column Names (any of these) | Example Data |
|----------|----------------------------|--------------|
| ✅ Yes | Name, Full Name, Teacher Name, Full_Name | John Doe |
| ✅ Yes | Email, Email Address, E-mail, Mail | john.doe@school.com |
| ⚪ Optional | Phone, Mobile, Telephone, Contact | +1234567890 |

#### Sample CSV Format
```csv
Name,Email,Phone
John Doe,john.doe@example.com,+1234567890
Jane Smith,jane.smith@example.com,+0987654321
Alice Johnson,alice.johnson@example.com,+1122334455
```

### 3. Import Process

1. **Upload File**: Click "Choose File" and select your CSV or Excel file
2. **Configure Options**:
   - ✅ **Validate email addresses**: Checks email format before import
   - ✅ **Skip duplicate email addresses**: Prevents importing teachers that already exist
   - ✅ **Send invitation emails**: Automatically sends verification emails to new teachers
3. **Import**: Click the "Import Teachers" button

### 4. Results
After import, you'll receive detailed feedback including:
- Number of teachers successfully imported
- Number of duplicates skipped
- Any errors encountered with specific row details

## Features

### ✅ Smart Column Mapping
The system automatically recognizes various column naming conventions:
- "Name" or "Full Name" or "Teacher Name" → Teacher Name
- "Email" or "Email Address" or "E-mail" → Teacher Email
- "Phone" or "Mobile" or "Contact" → Teacher Phone

### ✅ Data Validation
- Email format validation
- Required field checking
- Duplicate detection based on email addresses

### ✅ Error Handling
- Detailed error messages for each row
- Continues processing even if some rows fail
- Clear reporting of what succeeded and what failed

### ✅ Integration with Existing System
- Uses the same teacher creation logic as manual entry
- Generates unique teacher IDs automatically
- Integrates with email verification system
- Maintains data consistency

## CSV Template

Download the CSV template from the import modal to get started quickly. The template includes:
- Proper column headers
- Sample data
- Correct formatting

## Excel Support Details

### XLSX Files (.xlsx)
- Reads first worksheet automatically
- Extracts data from columns A, B, C (Name, Email, Phone)
- Supports shared strings and direct cell values
- Handles basic formatting

### XLS Files (.xls)
- Legacy format not supported
- Please save as .xlsx or convert to CSV

## Error Messages and Solutions

| Error | Solution |
|-------|----------|
| "Invalid file type" | Use only .csv, .xls, or .xlsx files |
| "Name is required" | Ensure all rows have a name in the Name column |
| "Email is required" | Ensure all rows have an email address |
| "Invalid email format" | Check email addresses for proper format (user@domain.com) |
| "Teacher already exists" | Either uncheck "Skip duplicates" or remove duplicate emails |

## Best Practices

1. **Start Small**: Test with a few teachers first
2. **Clean Data**: Review your data for accuracy before import
3. **Use Templates**: Download and use the provided CSV template
4. **Check Results**: Review the import results carefully
5. **Backup**: Keep a copy of your original data file

## Technical Implementation

The import feature:
- Processes files on the server for security
- Uses transaction-safe database operations
- Validates data before insertion
- Provides atomic operations (all or nothing for each teacher)
- Integrates with existing teacher management workflow

## Support

If you encounter issues:
1. Check that your file format matches the requirements
2. Verify column names are recognized
3. Review error messages for specific issues
4. Contact support with specific error details

For technical support, contact the ekiliSense support team with:
- The file you're trying to import (if possible)
- Screenshot of any error messages
- Number of rows in your file