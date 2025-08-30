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
    // Try to read Excel as XML-based format (modern .xlsx files)
    if (isXlsxFile($filePath)) {
        return parseXlsxFile($filePath);
    } else {
        // For older .xls files, suggest CSV conversion
        throw new Exception('Legacy Excel files (.xls) are not supported. Please save your file as .xlsx or convert to CSV format and try again.');
    }
}

/**
 * Check if file is a modern XLSX file
 */
function isXlsxFile($filePath) {
    $fileSignature = file_get_contents($filePath, false, null, 0, 4);
    return substr($fileSignature, 0, 2) === 'PK'; // ZIP file signature (XLSX is a ZIP archive)
}

/**
 * Parse XLSX file by extracting the shared strings and worksheet data
 */
function parseXlsxFile($filePath) {
    $teachers = [];
    
    try {
        // Create temporary directory for extraction
        $tempDir = sys_get_temp_dir() . '/xlsx_' . uniqid();
        mkdir($tempDir);
        
        // Extract the XLSX file (which is actually a ZIP archive)
        $zip = new ZipArchive();
        if ($zip->open($filePath) === TRUE) {
            $zip->extractTo($tempDir);
            $zip->close();
            
            // Read shared strings
            $sharedStrings = [];
            $sharedStringsPath = $tempDir . '/xl/sharedStrings.xml';
            if (file_exists($sharedStringsPath)) {
                $sharedStrings = parseSharedStrings($sharedStringsPath);
            }
            
            // Read worksheet data
            $worksheetPath = $tempDir . '/xl/worksheets/sheet1.xml';
            if (file_exists($worksheetPath)) {
                $teachers = parseWorksheet($worksheetPath, $sharedStrings);
            } else {
                throw new Exception('Could not find worksheet data in Excel file.');
            }
            
            // Clean up temporary directory
            deleteDirectory($tempDir);
        } else {
            throw new Exception('Could not open Excel file. Please ensure it is a valid .xlsx file.');
        }
    } catch (Exception $e) {
        throw new Exception('Error reading Excel file: ' . $e->getMessage() . '. Please convert to CSV format and try again.');
    }
    
    return $teachers;
}

/**
 * Parse shared strings XML file
 */
function parseSharedStrings($filePath) {
    $strings = [];
    
    if (file_exists($filePath)) {
        $xml = simplexml_load_file($filePath);
        if ($xml !== false) {
            foreach ($xml->si as $si) {
                $strings[] = (string)$si->t;
            }
        }
    }
    
    return $strings;
}

/**
 * Parse worksheet XML file
 */
function parseWorksheet($filePath, $sharedStrings) {
    $teachers = [];
    $rows = [];
    
    if (!file_exists($filePath)) {
        return $teachers;
    }
    
    $xml = simplexml_load_file($filePath);
    if ($xml === false) {
        return $teachers;
    }
    
    // Extract rows and cells
    foreach ($xml->sheetData->row as $row) {
        $rowData = [];
        $rowNum = (int)$row['r'];
        
        foreach ($row->c as $cell) {
            $cellRef = (string)$cell['r'];
            $cellValue = '';
            
            // Get cell column (A, B, C, etc.)
            preg_match('/([A-Z]+)/', $cellRef, $matches);
            $column = $matches[1];
            $columnIndex = columnLetterToNumber($column) - 1;
            
            // Get cell value
            if (isset($cell->v)) {
                if (isset($cell['t']) && $cell['t'] == 's') {
                    // Shared string
                    $sharedStringIndex = (int)$cell->v;
                    if (isset($sharedStrings[$sharedStringIndex])) {
                        $cellValue = $sharedStrings[$sharedStringIndex];
                    }
                } else {
                    // Direct value
                    $cellValue = (string)$cell->v;
                }
            }
            
            $rowData[$columnIndex] = trim($cellValue);
        }
        
        if (!empty($rowData)) {
            $rows[$rowNum] = $rowData;
        }
    }
    
    // Convert to teacher format
    if (!empty($rows)) {
        ksort($rows); // Sort by row number
        $firstRow = reset($rows);
        $header = array_map('trim', array_map('strtolower', $firstRow));
        
        foreach ($rows as $rowNum => $rowData) {
            if ($rowNum == key($rows)) continue; // Skip header row
            
            $teacher = [];
            foreach ($header as $colIndex => $column) {
                if (isset($rowData[$colIndex])) {
                    $teacher[$column] = $rowData[$colIndex];
                }
            }
            
            // Map common column names
            $teacher = mapTeacherColumns($teacher);
            
            if (!empty($teacher['name']) && !empty($teacher['email'])) {
                $teachers[] = $teacher;
            }
        }
    }
    
    return $teachers;
}

/**
 * Convert column letter to number (A=1, B=2, etc.)
 */
function columnLetterToNumber($column) {
    $number = 0;
    $length = strlen($column);
    
    for ($i = 0; $i < $length; $i++) {
        $number = $number * 26 + (ord($column[$i]) - ord('A') + 1);
    }
    
    return $number;
}

/**
 * Delete directory recursively
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
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