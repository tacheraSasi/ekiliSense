<?php
session_start();
include_once "../../config.php";
include_once "../functions/timeAgo.php";
include_once "../../middlwares/school_auth.php";

// Get statistics for different time periods
$current_month = date('Y-m');
$last_month = date('Y-m', strtotime('-1 month'));
$current_year = date('Y');

// Monthly registration trends
$monthly_students = mysqli_query($conn, "
  SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
  FROM students 
  WHERE school_uid = '$school_uid' 
  GROUP BY month 
  ORDER BY month DESC 
  LIMIT 6
");

$monthly_teachers = mysqli_query($conn, "
  SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
  FROM teachers 
  WHERE School_unique_id = '$school_uid' 
  GROUP BY month 
  ORDER BY month DESC 
  LIMIT 6
");

// Get class sizes
$class_sizes = mysqli_query($conn, "
  SELECT c.Class_name, COUNT(s.student_id) as student_count
  FROM classes c
  LEFT JOIN students s ON c.class_id = s.class_id
  WHERE c.school_unique_id = '$school_uid'
  GROUP BY c.class_id, c.Class_name
  ORDER BY student_count DESC
");

// Get teacher workload
$teacher_workload = mysqli_query($conn, "
  SELECT t.teacher_fullname, COUNT(DISTINCT s.subject_id) as subject_count,
         COUNT(DISTINCT c.class_id) as class_count
  FROM teachers t
  LEFT JOIN subjects s ON t.teacher_id = s.teacher_id
  LEFT JOIN classes c ON s.class_id = c.class_id
  WHERE t.School_unique_id = '$school_uid'
  GROUP BY t.teacher_id, t.teacher_fullname
  ORDER BY subject_count DESC
  LIMIT 10
");

// Get recent homework statistics
$homework_stats = mysqli_query($conn, "
  SELECT COUNT(*) as total_assignments,
         SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_assignments,
         SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_assignments
  FROM homework_assignments
  WHERE school_uid = '$school_uid'
");
$hw_stats = mysqli_fetch_assoc($homework_stats);

// Get exam statistics
$exam_stats = mysqli_query($conn, "
  SELECT COUNT(*) as total_exams,
         SUM(CASE WHEN exam_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_exams,
         SUM(CASE WHEN exam_date < CURDATE() THEN 1 ELSE 0 END) as past_exams
  FROM exam_schedules
  WHERE school_uid = '$school_uid'
");
$ex_stats = mysqli_fetch_assoc($exam_stats);

// Get attendance overview (last 30 days)
$attendance_overview = mysqli_query($conn, "
  SELECT DATE(date) as attendance_date, 
         COUNT(*) as total_records,
         SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
         SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count
  FROM student_attendance
  WHERE school_uid = '$school_uid' 
  AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
  GROUP BY DATE(date)
  ORDER BY date DESC
  LIMIT 30
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Reports & Analytics | ekiliSense</title>
  <meta name="description" content="Reports and analytics for school management">
  
  <!-- Favicons -->
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="icon">
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="../assets/css/custom.css" rel="stylesheet">
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
      <a href="/console/" class="logo d-flex align-items-center">
        <span class="d-none d-lg-block">ekiliSense</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="../assets/img/school-1.png" alt="Profile" class="">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?=$school['School_name']?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?=$school['School_name']?></h6>
              <span>ekiliSense</span>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="../profile.php">
                <i class="bi bi-person"></i>
                <span>Profile</span>
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="../logout.php?ref=<?=$school_uid?>">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
  </header>

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
      <li class="nav-item">
        <a class="nav-link collapsed" href="../">
          <i class="bi bi-grid"></i>
          <span>Home</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../teachers/">
          <i class="bi bi-people"></i>
          <span>Teachers</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../classes/">
          <i class="bi bi-buildings"></i>
          <span>Classes</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../announcements">
          <i class="bi bi-bell"></i>
          <span>Announcements</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../performance">
          <i class="bi bi-rocket-takeoff"></i>
          <span>Performance</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./">
          <i class="bi bi-bar-chart"></i>
          <span>Reports & Analytics</span>
        </a>
      </li>
      <li class="nav-heading">Pages</li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../profile.php">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li>
    </ul>
  </aside>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1><i class="bi bi-bar-chart"></i> Reports & Analytics</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="../">Home</a></li>
          <li class="breadcrumb-item active">Reports</li>
        </ol>
      </nav>
    </div>

    <section class="section dashboard">
      <div class="row">
        
        <!-- Overview Cards -->
        <div class="col-lg-12">
          <div class="row">
            
            <!-- Students Card -->
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Total Students</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?=$students_count?></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Teachers Card -->
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Total Teachers</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-person"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?=$teachers_count?></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Classes Card -->
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Total Classes</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-buildings-fill"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?=$classes_count?></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Homework Card -->
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card">
                <div class="card-body">
                  <h5 class="card-title">Active Homework</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-file-text"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?=$hw_stats['active_assignments'] ?? 0?></h6>
                      <span class="text-muted small">of <?=$hw_stats['total_assignments'] ?? 0?> total</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Class Distribution -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Class Sizes Distribution</h5>
              <canvas id="classSizesChart" style="max-height: 300px;"></canvas>
            </div>
          </div>
        </div>

        <!-- Teacher Workload -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Teacher Workload (Top 10)</h5>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Teacher</th>
                    <th>Subjects</th>
                    <th>Classes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($teacher = mysqli_fetch_assoc($teacher_workload)): ?>
                  <tr>
                    <td><?=$teacher['teacher_fullname']?></td>
                    <td><span class="badge bg-primary"><?=$teacher['subject_count']?></span></td>
                    <td><span class="badge bg-info"><?=$teacher['class_count']?></span></td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Attendance Overview -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Attendance Overview (Last 30 Days)</h5>
              <canvas id="attendanceChart" style="max-height: 300px;"></canvas>
            </div>
          </div>
        </div>

        <!-- Academic Activity Summary -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Homework Statistics</h5>
              <div class="row">
                <div class="col-6 text-center">
                  <h3 class="text-primary"><?=$hw_stats['total_assignments'] ?? 0?></h3>
                  <p class="text-muted">Total Assignments</p>
                </div>
                <div class="col-6 text-center">
                  <h3 class="text-success"><?=$hw_stats['active_assignments'] ?? 0?></h3>
                  <p class="text-muted">Active</p>
                </div>
                <div class="col-6 text-center">
                  <h3 class="text-secondary"><?=$hw_stats['closed_assignments'] ?? 0?></h3>
                  <p class="text-muted">Closed</p>
                </div>
                <div class="col-6 text-center">
                  <h3 class="text-info">
                    <?php 
                    $completion_rate = $hw_stats['total_assignments'] > 0 
                      ? round(($hw_stats['closed_assignments'] / $hw_stats['total_assignments']) * 100) 
                      : 0;
                    echo $completion_rate;
                    ?>%
                  </h3>
                  <p class="text-muted">Completion Rate</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Exam Statistics -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Exam Statistics</h5>
              <div class="row">
                <div class="col-6 text-center">
                  <h3 class="text-primary"><?=$ex_stats['total_exams'] ?? 0?></h3>
                  <p class="text-muted">Total Exams</p>
                </div>
                <div class="col-6 text-center">
                  <h3 class="text-warning"><?=$ex_stats['upcoming_exams'] ?? 0?></h3>
                  <p class="text-muted">Upcoming</p>
                </div>
                <div class="col-6 text-center">
                  <h3 class="text-success"><?=$ex_stats['past_exams'] ?? 0?></h3>
                  <p class="text-muted">Completed</p>
                </div>
                <div class="col-6 text-center">
                  <h3 class="text-info">
                    <?php 
                    $exam_completion = $ex_stats['total_exams'] > 0 
                      ? round(($ex_stats['past_exams'] / $ex_stats['total_exams']) * 100) 
                      : 0;
                    echo $exam_completion;
                    ?>%
                  </h3>
                  <p class="text-muted">Completion Rate</p>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

  </main>

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>ekiliSense</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      From <a href="https://ekilie.com">ekilie</a>
    </div>
  </footer>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../assets/js/main.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      
      // Class Sizes Chart
      <?php
      $class_names = [];
      $class_counts = [];
      mysqli_data_seek($class_sizes, 0);
      while($row = mysqli_fetch_assoc($class_sizes)) {
        $class_names[] = $row['Class_name'];
        $class_counts[] = $row['student_count'];
      }
      ?>
      new Chart(document.querySelector('#classSizesChart'), {
        type: 'bar',
        data: {
          labels: <?=json_encode($class_names)?>,
          datasets: [{
            label: 'Number of Students',
            data: <?=json_encode($class_counts)?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgb(54, 162, 235)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });

      // Attendance Chart
      <?php
      $attendance_dates = [];
      $present_counts = [];
      $absent_counts = [];
      while($row = mysqli_fetch_assoc($attendance_overview)) {
        $attendance_dates[] = date('M d', strtotime($row['attendance_date']));
        $present_counts[] = $row['present_count'];
        $absent_counts[] = $row['absent_count'];
      }
      $attendance_dates = array_reverse($attendance_dates);
      $present_counts = array_reverse($present_counts);
      $absent_counts = array_reverse($absent_counts);
      ?>
      new Chart(document.querySelector('#attendanceChart'), {
        type: 'line',
        data: {
          labels: <?=json_encode($attendance_dates)?>,
          datasets: [{
            label: 'Present',
            data: <?=json_encode($present_counts)?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
          }, {
            label: 'Absent',
            data: <?=json_encode($absent_counts)?>,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });

    });
  </script>

</body>
</html>
