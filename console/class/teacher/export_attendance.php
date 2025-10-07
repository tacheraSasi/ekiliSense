<?php
session_start();
include_once "../../../config.php";
include_once "../../../middlwares/teacher_auth.php";

// Get parameters
$start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($conn, $_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($conn, $_GET['end_date']) : date('Y-m-t');
$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

// Get attendance data
$attendance_query = mysqli_query($conn, "
    SELECT 
        s.student_id,
        s.student_first_name,
        s.student_last_name,
        COUNT(sa.attendance_date) as present_days,
        (SELECT COUNT(DISTINCT attendance_date) FROM student_attendance 
         WHERE school_uid = '$school_uid' AND class_id = '$class_id' 
         AND attendance_date BETWEEN '$start_date' AND '$end_date') as total_possible_days
    FROM students s
    LEFT JOIN student_attendance sa ON s.student_id = sa.student_id 
        AND sa.attendance_date BETWEEN '$start_date' AND '$end_date'
        AND sa.status = 1
    WHERE s.school_uid = '$school_uid' AND s.class_id = '$class_id'
    GROUP BY s.student_id
    ORDER BY s.student_first_name ASC
");

if ($format === 'csv') {
    // Export as CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, array('Student ID', 'First Name', 'Last Name', 'Present Days', 'Total Days', 'Attendance %'));
    
    // Add data rows
    while ($row = mysqli_fetch_array($attendance_query)) {
        $total_days = $row['total_possible_days'] > 0 ? $row['total_possible_days'] : 1;
        $percentage = round(($row['present_days'] / $total_days) * 100, 2);
        
        fputcsv($output, array(
            $row['student_id'],
            $row['student_first_name'],
            $row['student_last_name'],
            $row['present_days'],
            $total_days,
            $percentage . '%'
        ));
    }
    
    fclose($output);
    exit();
    
} elseif ($format === 'pdf') {
    // Check if TCPDF is available
    $tcpdf_path = '../../../vendor/tecnickcom/tcpdf/tcpdf.php';
    
    if (file_exists($tcpdf_path)) {
        require_once($tcpdf_path);
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('ekiliSense');
        $pdf->SetAuthor($school['School_name']);
        $pdf->SetTitle('Attendance Report');
        $pdf->SetSubject('Student Attendance Report');
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', 'B', 16);
        
        // Title
        $pdf->Cell(0, 10, $school['School_name'], 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Attendance Report - ' . $class_info['Class_name'], 0, 1, 'C');
        $pdf->Cell(0, 5, 'Period: ' . date('M d, Y', strtotime($start_date)) . ' to ' . date('M d, Y', strtotime($end_date)), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(52, 73, 94);
        $pdf->SetTextColor(255, 255, 255);
        
        $html = '<table border="1" cellpadding="4">
                    <thead>
                        <tr style="background-color: #34495e; color: #ffffff;">
                            <th width="15%"><b>Student ID</b></th>
                            <th width="25%"><b>First Name</b></th>
                            <th width="25%"><b>Last Name</b></th>
                            <th width="15%"><b>Present Days</b></th>
                            <th width="20%"><b>Attendance %</b></th>
                        </tr>
                    </thead>
                    <tbody>';
        
        // Reset query
        mysqli_data_seek($attendance_query, 0);
        
        // Table data
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        
        while ($row = mysqli_fetch_array($attendance_query)) {
            $total_days = $row['total_possible_days'] > 0 ? $row['total_possible_days'] : 1;
            $percentage = round(($row['present_days'] / $total_days) * 100, 2);
            
            $color = '';
            if ($percentage < 75) {
                $color = ' style="background-color: #ffcccc;"';
            } elseif ($percentage >= 90) {
                $color = ' style="background-color: #ccffcc;"';
            }
            
            $html .= '<tr' . $color . '>
                        <td>' . htmlspecialchars($row['student_id']) . '</td>
                        <td>' . htmlspecialchars($row['student_first_name']) . '</td>
                        <td>' . htmlspecialchars($row['student_last_name']) . '</td>
                        <td>' . $row['present_days'] . ' / ' . $total_days . '</td>
                        <td>' . $percentage . '%</td>
                    </tr>';
        }
        
        $html .= '</tbody></table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Output PDF
        $pdf->Output('attendance_report_' . date('Y-m-d') . '.pdf', 'D');
        exit();
        
    } else {
        // Fallback to HTML if TCPDF is not available
        $format = 'html';
    }
}

// Default: Export as HTML (printable)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report - <?= $class_info['Class_name'] ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #34495e;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .low-attendance {
            background-color: #ffcccc !important;
        }
        .high-attendance {
            background-color: #ccffcc !important;
        }
        .print-btn {
            text-align: center;
            margin: 20px 0;
        }
        .print-btn button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-btn button:hover {
            background-color: #2980b9;
        }
        @media print {
            .print-btn {
                display: none;
            }
            body {
                background-color: white;
            }
            .container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $school['School_name'] ?></h1>
        <h2>Attendance Report - <?= $class_info['Class_name'] ?></h2>
        <p style="text-align: center;">
            <strong>Period:</strong> <?= date('M d, Y', strtotime($start_date)) ?> to <?= date('M d, Y', strtotime($end_date)) ?>
        </p>
        
        <div class="print-btn">
            <button onclick="window.print()">Print Report</button>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Present Days</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($attendance_query, 0);
                while ($row = mysqli_fetch_array($attendance_query)) {
                    $total_days = $row['total_possible_days'] > 0 ? $row['total_possible_days'] : 1;
                    $percentage = round(($row['present_days'] / $total_days) * 100, 2);
                    
                    $row_class = '';
                    if ($percentage < 75) {
                        $row_class = 'low-attendance';
                    } elseif ($percentage >= 90) {
                        $row_class = 'high-attendance';
                    }
                ?>
                <tr class="<?= $row_class ?>">
                    <td><?= htmlspecialchars($row['student_id']) ?></td>
                    <td><?= htmlspecialchars($row['student_first_name']) ?></td>
                    <td><?= htmlspecialchars($row['student_last_name']) ?></td>
                    <td><?= $row['present_days'] ?> / <?= $total_days ?></td>
                    <td><?= $percentage ?>%</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        
        <div class="print-btn">
            <button onclick="window.print()">Print Report</button>
        </div>
    </div>
</body>
</html>
