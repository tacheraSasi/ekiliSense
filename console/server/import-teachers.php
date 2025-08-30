<?php
session_start();
include_once "../../config.php";
require_once "../../app/app.php";

// Set content type to JSON
header('Content-Type: application/json');

$school_uid = $_SESSION['School_uid'];
if (!isset($school_uid)) {
    echo json_encode(['success' => false, 'message' => 'ACCESS DENIED']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['teacher_file']) || $_FILES['teacher_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error occurred.']);
    exit;
}

$file = $_FILES['teacher_file'];
$filePath = $file['tmp_name'];
$fileName = $file['name'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validate file type
$allowedExtensions = ['csv', 'xls', 'xlsx'];
if (!in_array($fileExt, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload CSV, XLS, or XLSX files only.']);
    exit;
}

// Get options
$validateEmails = isset($_POST['validate_emails']) && $_POST['validate_emails'] == '1';
$skipDuplicates = isset($_POST['skip_duplicates']) && $_POST['skip_duplicates'] == '1';
$sendInvites = isset($_POST['send_invites']) && $_POST['send_invites'] == '1';

// Get school details
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);
$school_name = $school['School_name'];

try {
    $teachers = parseTeacherFile($filePath, $fileExt);
    $result = importTeachers($conn, $teachers, $school_uid, $school_name, $validateEmails, $skipDuplicates, $sendInvites);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error processing file: ' . $e->getMessage()]);
}

/**
 * Parse teacher data from uploaded file
 */
function parseTeacherFile($filePath, $fileExt) {
    $teachers = [];
    
    if ($fileExt === 'csv') {
        $teachers = parseCSV($filePath);
    } else {
        // For Excel files, try to convert to CSV first or use simple parsing
        $teachers = parseExcel($filePath);
    }
    
    return $teachers;
}

/**
 * Parse CSV file
 */
function parseCSV($filePath) {
    $teachers = [];
    $header = null;
    
    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        $rowIndex = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if ($rowIndex === 0) {
                // First row is header
                $header = array_map('trim', array_map('strtolower', $data));
                $rowIndex++;
                continue;
            }
            
            if (count($data) >= 2) { // At least name and email
                $teacher = [];
                foreach ($header as $index => $column) {
                    if (isset($data[$index])) {
                        $teacher[$column] = trim($data[$index]);
                    }
                }
                
                // Map common column names
                $teacher = mapTeacherColumns($teacher);
                
                if (!empty($teacher['name']) && !empty($teacher['email'])) {
                    $teachers[] = $teacher;
                }
            }
            $rowIndex++;
        }
        fclose($handle);
    }
    
    return $teachers;
}

/**
 * Parse Excel file (basic implementation)
 */
function parseExcel($filePath) {
    // For now, return an error asking users to convert to CSV
    // This can be enhanced later with proper Excel parsing library
    throw new Exception('Excel files are not yet supported. Please convert your file to CSV format and try again.');
}

/**
 * Map common column names to standardized format
 */
function mapTeacherColumns($teacher) {
    $mapped = ['name' => '', 'email' => '', 'phone' => ''];
    
    foreach ($teacher as $key => $value) {
        $key = strtolower(trim($key));
        
        // Map name columns
        if (in_array($key, ['name', 'full name', 'fullname', 'teacher name', 'full_name'])) {
            $mapped['name'] = $value;
        }
        // Map email columns
        elseif (in_array($key, ['email', 'email address', 'email_address', 'e-mail', 'mail'])) {
            $mapped['email'] = $value;
        }
        // Map phone columns
        elseif (in_array($key, ['phone', 'mobile', 'telephone', 'tel', 'phone number', 'mobile number', 'contact'])) {
            $mapped['phone'] = $value;
        }
    }
    
    return $mapped;
}

/**
 * Import teachers into database
 */
function importTeachers($conn, $teachers, $school_uid, $school_name, $validateEmails, $skipDuplicates, $sendInvites) {
    $imported = 0;
    $skipped = 0;
    $errors = [];
    
    foreach ($teachers as $index => $teacher) {
        $rowNum = $index + 2; // +2 because index starts at 0 and we skip header
        
        // Sanitize data
        $name = mysqli_real_escape_string($conn, trim($teacher['name']));
        $email = mysqli_real_escape_string($conn, trim(strtolower($teacher['email'])));
        $phone = mysqli_real_escape_string($conn, trim($teacher['phone']));
        
        // Validate required fields
        if (empty($name)) {
            $errors[] = "Row $rowNum: Name is required";
            continue;
        }
        
        if (empty($email)) {
            $errors[] = "Row $rowNum: Email is required";
            continue;
        }
        
        // Validate email format
        if ($validateEmails && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Row $rowNum: Invalid email format ($email)";
            continue;
        }
        
        // Check for duplicates
        if ($skipDuplicates) {
            $check_query = mysqli_query($conn, "SELECT * FROM teachers 
                WHERE teacher_email = '$email' AND School_unique_id = '$school_uid'");
            
            if (mysqli_num_rows($check_query) > 0) {
                $skipped++;
                continue;
            }
        }
        
        // Generate unique teacher ID
        $teacher_uid = UID();
        
        // Insert teacher
        $insert_teacher_query = mysqli_query($conn, "INSERT INTO teachers 
            (school_unique_id, teacher_id, teacher_fullname, teacher_email, teacher_active_phone) 
            VALUES ('$school_uid', '$teacher_uid', '$name', '$email', '$phone')");
        
        if ($insert_teacher_query) {
            $imported++;
            
            // Send invitation email if requested
            if ($sendInvites) {
                try {
                    $otp = randString(10);
                    $insert_otp_query = mysqli_query($conn, "INSERT INTO otps 
                        (teacher_email, school_id, value) VALUES ('$email', '$school_uid', '$otp')");
                    
                    if ($insert_otp_query) {
                        sendMail($email, $name, $otp, $school_name);
                    }
                } catch (Exception $e) {
                    // Don't fail the import if email sending fails
                    error_log("Failed to send invitation email to $email: " . $e->getMessage());
                }
            }
        } else {
            $errors[] = "Row $rowNum: Failed to import teacher $name ($email)";
        }
    }
    
    // Prepare result message
    $message = "Import completed: $imported teachers imported";
    if ($skipped > 0) {
        $message .= ", $skipped duplicates skipped";
    }
    if (!empty($errors)) {
        $message .= ". Errors: " . implode('; ', $errors);
    }
    
    return [
        'success' => true,
        'message' => $message,
        'imported' => $imported,
        'skipped' => $skipped,
        'errors' => $errors
    ];
}

/**
 * Generate random string for OTP
 */
function randString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, $max)];
    }
    
    return $string;
}
?>