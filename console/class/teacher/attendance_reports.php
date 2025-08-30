<?php
session_start();
include_once "../../../config.php";
include_once "../../../middlwares/teacher_auth.php";

// Get date range for reports (default to current month)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get attendance data for the date range
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

// Calculate class attendance statistics
$class_stats_query = mysqli_query($conn, "
    SELECT 
        COUNT(DISTINCT sa.student_id) as students_present,
        COUNT(DISTINCT s.student_id) as total_students,
        sa.attendance_date
    FROM student_attendance sa
    RIGHT JOIN students s ON sa.student_id = s.student_id AND sa.status = 1
    WHERE s.school_uid = '$school_uid' AND s.class_id = '$class_id'
    AND sa.attendance_date BETWEEN '$start_date' AND '$end_date'
    GROUP BY sa.attendance_date
    ORDER BY sa.attendance_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ekiliSense | <?= $school["School_name"] ?> | Attendance Reports</title>

  <!-- Favicons -->
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="icon">
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../../assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../../assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../../assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <link href="../../assets/css/style.css" rel="stylesheet">
  <link href="../../assets/css/custom.css" rel="stylesheet">

</head>

<body>

  <?php include_once "./includes/topbar.php"; ?>
  <?php
  $page = "attendance_reports";
  include_once "./includes/sidebar.php";
  ?>

  <main id="main" class="main">
    <div class="d-flex justify-content-between flex-wrap" style="margin:1rem auto;">
      <div class="pagetitle" style="display:inline-block">
        <h1 style="display:inline-block">Attendance Reports <i class="bi bi-arrow-right-short"> </i> <?= $class_info["Class_name"] ?></h1>
      </div>
      <button class="btn btn-success" onclick="exportAttendanceReport()">
        <i class="bi bi-download"></i> Export Report
      </button>
    </div>

    <!-- Date Range Filter -->
    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Report Filters</h5>
              <form method="GET" class="row g-3">
                <div class="col-md-4">
                  <label for="start_date" class="form-label">Start Date</label>
                  <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
                </div>
                <div class="col-md-4">
                  <label for="end_date" class="form-label">End Date</label>
                  <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">&nbsp;</label>
                  <button type="submit" class="btn btn-primary d-block">Generate Report</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Statistics -->
      <div class="row">
        <div class="col-lg-12">
          <div class="row">
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Total Students</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= $student_num ?></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-3 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Reporting Period</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-calendar-range"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= date('M d', strtotime($start_date)) ?> - <?= date('M d', strtotime($end_date)) ?></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-3 col-md-6">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Average Attendance</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-percent"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $total_percentage = 0;
                        $student_count = 0;
                        mysqli_data_seek($attendance_query, 0);
                        while($student = mysqli_fetch_array($attendance_query)) {
                          if($student['total_possible_days'] > 0) {
                            $percentage = ($student['present_days'] / $student['total_possible_days']) * 100;
                            $total_percentage += $percentage;
                            $student_count++;
                          }
                        }
                        $average_attendance = $student_count > 0 ? round($total_percentage / $student_count, 1) : 0;
                        echo $average_attendance . '%';
                        ?>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-3 col-md-6">
              <div class="card info-card">
                <div class="card-body">
                  <h5 class="card-title">Low Attendance</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $low_attendance_count = 0;
                        mysqli_data_seek($attendance_query, 0);
                        while($student = mysqli_fetch_array($attendance_query)) {
                          if($student['total_possible_days'] > 0) {
                            $percentage = ($student['present_days'] / $student['total_possible_days']) * 100;
                            if($percentage < 75) {
                              $low_attendance_count++;
                            }
                          }
                        }
                        echo $low_attendance_count;
                        ?>
                      </h6>
                      <small class="text-muted">(&lt;75%)</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Student Attendance Report -->
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Student Attendance Summary</h5>

              <table class="table table-dark table-hover datatable" style="overflow:auto">
                <thead>
                  <tr>
                    <th>Student Name</th>
                    <th>Present Days</th>
                    <th>Total Days</th>
                    <th>Attendance %</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  mysqli_data_seek($attendance_query, 0);
                  while ($student = mysqli_fetch_array($attendance_query)) {
                    $total_days = $student['total_possible_days'] > 0 ? $student['total_possible_days'] : 1;
                    $percentage = ($student['present_days'] / $total_days) * 100;
                    
                    // Determine status and badge color
                    $status_class = 'badge-success';
                    $status_text = 'Good';
                    if($percentage < 75) {
                      $status_class = 'badge-danger';
                      $status_text = 'Poor';
                    } elseif($percentage < 85) {
                      $status_class = 'badge-warning';
                      $status_text = 'Fair';
                    }
                  ?>
                    <tr>
                      <td>
                        <strong><?= htmlspecialchars($student['student_first_name'] . ' ' . $student['student_last_name']) ?></strong>
                      </td>
                      <td><?= $student['present_days'] ?></td>
                      <td><?= $student['total_possible_days'] ?></td>
                      <td>
                        <div class="progress" style="width: 100px; height: 20px;">
                          <div class="progress-bar <?= $percentage >= 75 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                               role="progressbar" 
                               style="width: <?= $percentage ?>%" 
                               aria-valuenow="<?= $percentage ?>" 
                               aria-valuemin="0" 
                               aria-valuemax="100">
                            <?= round($percentage, 1) ?>%
                          </div>
                        </div>
                      </td>
                      <td><span class="badge <?= $status_class ?>"><?= $status_text ?></span></td>
                      <td>
                        <a href="student_attendance_detail.php?id=<?= $student['student_id'] ?>&start=<?= $start_date ?>&end=<?= $end_date ?>" 
                           class="btn btn-sm btn-outline-primary">
                          <i class="bi bi-eye"></i> Details
                        </a>
                        <?php if($percentage < 75): ?>
                          <button class="btn btn-sm btn-outline-warning" 
                                  onclick="sendAttendanceAlert('<?= $student['student_id'] ?>')">
                            <i class="bi bi-exclamation-triangle"></i> Alert Parents
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>

            </div>
          </div>
        </div>
      </div>

      <!-- Daily Attendance Chart -->
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Daily Attendance Trend</h5>
              
              <canvas id="attendanceChart" width="400" height="100"></canvas>
              
            </div>
          </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../../assets/vendor/echarts/echarts.min.js"></script>
  <script src="../../assets/vendor/quill/quill.min.js"></script>
  <script src="../../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../../assets/vendor/php-email-form/validate.js"></script>

  <script src="../../assets/js/main.js"></script>
  
  <script>
    // Attendance Chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                <?php
                $dates = [];
                $percentages = [];
                mysqli_data_seek($class_stats_query, 0);
                while($day = mysqli_fetch_array($class_stats_query)) {
                  $dates[] = "'" . date('M d', strtotime($day['attendance_date'])) . "'";
                  $percentage = $day['total_students'] > 0 ? round(($day['students_present'] / $day['total_students']) * 100, 1) : 0;
                  $percentages[] = $percentage;
                }
                echo implode(',', array_reverse($dates));
                ?>
            ],
            datasets: [{
                label: 'Class Attendance %',
                data: [<?= implode(',', array_reverse($percentages)) ?>],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Daily Class Attendance Percentage'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
    
    // Functions for actions
    function exportAttendanceReport() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        window.location.href = `export_attendance.php?start_date=${startDate}&end_date=${endDate}&format=csv`;
    }
    
    function sendAttendanceAlert(studentId) {
        if(confirm('Send attendance alert to parents?')) {
            // Implementation for sending attendance alerts
            fetch('server/send_attendance_alert.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `student_id=${studentId}&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>`
            })
            .then(response => response.text())
            .then(data => {
                if(data === 'success') {
                    alert('Alert sent successfully!');
                } else {
                    alert('Error: ' + data);
                }
            });
        }
    }
  </script>

</body>

</html>