<?php
session_start();
include_once "../../config.php";
include_once "../functions/timeAgo.php";
include_once "../../middlwares/school_auth.php";

// Get filter parameters
$filter_class = isset($_GET['class']) ? $_GET['class'] : 'all';
$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get attendance summary by class for selected date
$attendance_by_class = mysqli_query($conn, "
  SELECT c.Class_name, c.class_id,
         COUNT(DISTINCT sa.student_id) as total_students,
         SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present,
         SUM(CASE WHEN sa.status = 'absent' THEN 1 ELSE 0 END) as absent,
         SUM(CASE WHEN sa.status = 'late' THEN 1 ELSE 0 END) as late
  FROM classes c
  LEFT JOIN student_attendance sa ON c.class_id = sa.class_id 
    AND DATE(sa.date) = '$filter_date'
  WHERE c.school_unique_id = '$school_uid'
  GROUP BY c.class_id, c.Class_name
  ORDER BY c.Class_name
");

// Get detailed attendance for specific class if filtered
$student_attendance = null;
if($filter_class != 'all') {
  $student_attendance = mysqli_query($conn, "
    SELECT s.student_name, s.student_id,
           COALESCE(sa.status, 'not_marked') as status,
           sa.marked_by, sa.notes,
           TIME(sa.date) as marked_time
    FROM students s
    LEFT JOIN student_attendance sa ON s.student_id = sa.student_id 
      AND DATE(sa.date) = '$filter_date'
    WHERE s.school_uid = '$school_uid' 
      AND s.class_id = '$filter_class'
    ORDER BY s.student_name
  ");
}

// Get attendance trends (last 7 days)
$attendance_trends = mysqli_query($conn, "
  SELECT DATE(date) as attendance_date,
         COUNT(*) as total_records,
         SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
         SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
         SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
  FROM student_attendance
  WHERE school_uid = '$school_uid'
    AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
  GROUP BY DATE(date)
  ORDER BY date ASC
");

// Calculate overall attendance rate
$overall_stats = mysqli_query($conn, "
  SELECT 
    COUNT(*) as total_records,
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
  FROM student_attendance
  WHERE school_uid = '$school_uid'
    AND DATE(date) = '$filter_date'
");
$stats = mysqli_fetch_assoc($overall_stats);
$attendance_rate = $stats['total_records'] > 0 
  ? round(($stats['present'] / $stats['total_records']) * 100, 1) 
  : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Attendance Overview | ekiliSense</title>
  
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
        <a class="nav-link collapsed" href="../reports/">
          <i class="bi bi-bar-chart"></i>
          <span>Reports & Analytics</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./">
          <i class="bi bi-calendar-check"></i>
          <span>Attendance</span>
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
      <h1><i class="bi bi-calendar-check"></i> Attendance Overview</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="../">Home</a></li>
          <li class="breadcrumb-item active">Attendance</li>
        </ol>
      </nav>
    </div>

    <section class="section">
      <!-- Filters -->
      <div class="row mb-3">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Filters</h5>
              <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Date</label>
                  <input type="date" class="form-control" name="date" value="<?=$filter_date?>" max="<?=date('Y-m-d')?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Class</label>
                  <select class="form-select" name="class">
                    <option value="all" <?=$filter_class == 'all' ? 'selected' : ''?>>All Classes</option>
                    <?php
                    mysqli_data_seek($get_classes, 0);
                    while($class = mysqli_fetch_assoc($get_classes)):
                    ?>
                    <option value="<?=$class['class_id']?>" <?=$filter_class == $class['class_id'] ? 'selected' : ''?>>
                      <?=$class['Class_name']?>
                    </option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-funnel"></i> Apply Filters
                  </button>
                  <a href="./" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="row">
        <div class="col-xxl-3 col-md-6">
          <div class="card info-card sales-card">
            <div class="card-body">
              <h5 class="card-title">Attendance Rate</h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                  <i class="bi bi-percent"></i>
                </div>
                <div class="ps-3">
                  <h6><?=$attendance_rate?>%</h6>
                  <span class="text-muted small"><?=date('M d, Y', strtotime($filter_date))?></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xxl-3 col-md-6">
          <div class="card info-card revenue-card">
            <div class="card-body">
              <h5 class="card-title">Present</h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #e8f5e9; color: #4caf50;">
                  <i class="bi bi-check-circle"></i>
                </div>
                <div class="ps-3">
                  <h6><?=$stats['present'] ?? 0?></h6>
                  <span class="text-success small">Students</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xxl-3 col-md-6">
          <div class="card info-card customers-card">
            <div class="card-body">
              <h5 class="card-title">Absent</h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #ffebee; color: #f44336;">
                  <i class="bi bi-x-circle"></i>
                </div>
                <div class="ps-3">
                  <h6><?=$stats['absent'] ?? 0?></h6>
                  <span class="text-danger small">Students</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xxl-3 col-md-6">
          <div class="card info-card">
            <div class="card-body">
              <h5 class="card-title">Late</h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background: #fff3e0; color: #ff9800;">
                  <i class="bi bi-clock-history"></i>
                </div>
                <div class="ps-3">
                  <h6><?=$stats['late'] ?? 0?></h6>
                  <span class="text-warning small">Students</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance by Class -->
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Attendance by Class - <?=date('M d, Y', strtotime($filter_date))?></h5>
              <table class="table datatable">
                <thead>
                  <tr>
                    <th>Class Name</th>
                    <th>Total Students</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Rate</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($class = mysqli_fetch_assoc($attendance_by_class)): 
                    $class_rate = $class['total_students'] > 0 
                      ? round(($class['present'] / $class['total_students']) * 100, 1) 
                      : 0;
                    $rate_color = $class_rate >= 90 ? 'success' : ($class_rate >= 75 ? 'warning' : 'danger');
                  ?>
                  <tr>
                    <td><strong><?=$class['Class_name']?></strong></td>
                    <td><?=$class['total_students']?></td>
                    <td><span class="badge bg-success"><?=$class['present']?></span></td>
                    <td><span class="badge bg-danger"><?=$class['absent']?></span></td>
                    <td><span class="badge bg-warning"><?=$class['late']?></span></td>
                    <td><span class="badge bg-<?=$rate_color?>"><?=$class_rate?>%</span></td>
                    <td>
                      <a href="?date=<?=$filter_date?>&class=<?=$class['class_id']?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i> View Details
                      </a>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Student Attendance (if class is selected) -->
      <?php if($student_attendance): ?>
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Student Attendance Details</h5>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Student Name</th>
                    <th>Status</th>
                    <th>Marked At</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($student = mysqli_fetch_assoc($student_attendance)): 
                    $status_class = '';
                    $status_icon = '';
                    switch($student['status']) {
                      case 'present':
                        $status_class = 'success';
                        $status_icon = 'check-circle';
                        break;
                      case 'absent':
                        $status_class = 'danger';
                        $status_icon = 'x-circle';
                        break;
                      case 'late':
                        $status_class = 'warning';
                        $status_icon = 'clock-history';
                        break;
                      default:
                        $status_class = 'secondary';
                        $status_icon = 'question-circle';
                    }
                  ?>
                  <tr>
                    <td><?=$student['student_name']?></td>
                    <td>
                      <span class="badge bg-<?=$status_class?>">
                        <i class="bi bi-<?=$status_icon?>"></i> <?=ucfirst($student['status'])?>
                      </span>
                    </td>
                    <td><?=$student['marked_time'] ?? '-'?></td>
                    <td><?=$student['notes'] ?? '-'?></td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Attendance Trends -->
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Attendance Trends (Last 7 Days)</h5>
              <canvas id="trendsChart" style="max-height: 300px;"></canvas>
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
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../assets/js/main.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // Attendance Trends Chart
      <?php
      $trend_dates = [];
      $trend_present = [];
      $trend_absent = [];
      $trend_late = [];
      while($row = mysqli_fetch_assoc($attendance_trends)) {
        $trend_dates[] = date('M d', strtotime($row['attendance_date']));
        $trend_present[] = $row['present'];
        $trend_absent[] = $row['absent'];
        $trend_late[] = $row['late'];
      }
      ?>
      new Chart(document.querySelector('#trendsChart'), {
        type: 'line',
        data: {
          labels: <?=json_encode($trend_dates)?>,
          datasets: [{
            label: 'Present',
            data: <?=json_encode($trend_present)?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
          }, {
            label: 'Absent',
            data: <?=json_encode($trend_absent)?>,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
          }, {
            label: 'Late',
            data: <?=json_encode($trend_late)?>,
            borderColor: 'rgb(255, 205, 86)',
            backgroundColor: 'rgba(255, 205, 86, 0.2)',
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
