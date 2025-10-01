<?php
session_start();
include_once "../../config.php";
include_once "../../middlwares/school_auth.php";

// Get current school settings
$school_settings = mysqli_query($conn, "
  SELECT * FROM schools 
  WHERE unique_id = '$school_uid'
");
$school_data = mysqli_fetch_assoc($school_settings);

// Get subscription info
$subscription_info = mysqli_query($conn, "
  SELECT ss.*, sp.plan_name, sp.price, sp.features
  FROM school_subscriptions ss
  JOIN subscription_plans sp ON ss.plan_id = sp.plan_id
  WHERE ss.school_uid = '$school_uid'
  ORDER BY ss.created_at DESC
  LIMIT 1
");
$subscription = mysqli_fetch_assoc($subscription_info);

// Get login logs count (last 30 days)
$login_logs_count = mysqli_query($conn, "
  SELECT COUNT(*) as count 
  FROM login_logs 
  WHERE school_uid = '$school_uid' 
  AND login_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$login_count = mysqli_fetch_assoc($login_logs_count);

// Get active sessions count
$active_sessions_count = mysqli_query($conn, "
  SELECT COUNT(*) as count 
  FROM active_sessions 
  WHERE school_uid = '$school_uid' 
  AND last_activity >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
");
$sessions_count = mysqli_fetch_assoc($active_sessions_count);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>System Settings | ekiliSense</title>
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="icon">
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
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
            <li class="dropdown-header"><h6><?=$school['School_name']?></h6><span>ekiliSense</span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item d-flex align-items-center" href="../profile.php"><i class="bi bi-person"></i><span>Profile</span></a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item d-flex align-items-center" href="../logout.php?ref=<?=$school_uid?>"><i class="bi bi-box-arrow-right"></i><span>Sign Out</span></a></li>
          </ul>
        </li>
      </ul>
    </nav>
  </header>
  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
      <li class="nav-item"><a class="nav-link collapsed" href="../"><i class="bi bi-grid"></i><span>Home</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="../teachers/"><i class="bi bi-people"></i><span>Teachers</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="../classes/"><i class="bi bi-buildings"></i><span>Classes</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="../reports/"><i class="bi bi-bar-chart"></i><span>Reports & Analytics</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="../attendance/"><i class="bi bi-calendar-check"></i><span>Attendance</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="../events/"><i class="bi bi-calendar-event"></i><span>Events</span></a></li>
      <li class="nav-heading">Management</li>
      <li class="nav-item"><a class="nav-link" href="./"><i class="bi bi-gear"></i><span>System Settings</span></a></li>
    </ul>
  </aside>
  <main id="main" class="main">
    <div class="pagetitle">
      <h1><i class="bi bi-gear"></i> System Settings</h1>
      <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="../">Home</a></li><li class="breadcrumb-item active">Settings</li></ol></nav>
    </div>
    <section class="section">
      <div class="row"><div class="col-lg-12"><div class="card"><div class="card-body">
        <h5 class="card-title">System Information</h5>
        <div class="row">
          <div class="col-md-6"><table class="table">
            <tr><td><strong>School Name:</strong></td><td><?=$school_data['School_name']?></td></tr>
            <tr><td><strong>School ID:</strong></td><td><code><?=$school_uid?></code></td></tr>
            <tr><td><strong>Admin Email:</strong></td><td><?=$school_data['Admin_email']?></td></tr>
            <tr><td><strong>Registration Date:</strong></td><td><?=date('M d, Y', strtotime($school_data['created_at']))?></td></tr>
          </table></div>
          <div class="col-md-6"><table class="table">
            <tr><td><strong>Subscription Plan:</strong></td><td>
              <?php if($subscription): ?><span class="badge bg-success"><?=$subscription['plan_name']?></span>
              <?php else: ?><span class="badge bg-secondary">Free Plan</span><?php endif; ?>
            </td></tr>
            <tr><td><strong>Logins (30 days):</strong></td><td><?=$login_count['count']?></td></tr>
            <tr><td><strong>Active Sessions:</strong></td><td><?=$sessions_count['count']?></td></tr>
            <tr><td><strong>Total Users:</strong></td><td><?=$students_count + $teachers_count?></td></tr>
          </table></div>
        </div>
      </div></div></div></div>
      <div class="row">
        <div class="col-lg-6"><div class="card"><div class="card-body">
          <h5 class="card-title">School Information</h5>
          <div class="alert alert-info"><i class="bi bi-info-circle"></i> Update your school information here</div>
          <div class="list-group">
            <div class="list-group-item"><strong>School Name:</strong> <?=$school_data['School_name']?></div>
            <div class="list-group-item"><strong>Admin Email:</strong> <?=$school_data['Admin_email']?></div>
            <div class="list-group-item"><strong>Total Students:</strong> <?=$students_count?></div>
            <div class="list-group-item"><strong>Total Teachers:</strong> <?=$teachers_count?></div>
            <div class="list-group-item"><strong>Total Classes:</strong> <?=$classes_count?></div>
          </div>
        </div></div></div>
        <div class="col-lg-6"><div class="card"><div class="card-body">
          <h5 class="card-title">Security & Access</h5>
          <div class="list-group">
            <a href="../profile.php" class="list-group-item list-group-item-action"><i class="bi bi-key"></i> Change Password<i class="bi bi-chevron-right float-end"></i></a>
            <a href="#" class="list-group-item list-group-item-action"><i class="bi bi-shield-check"></i> Two-Factor Auth<span class="badge bg-warning float-end">Coming Soon</span></a>
            <a href="#" class="list-group-item list-group-item-action"><i class="bi bi-clock-history"></i> Login History<i class="bi bi-chevron-right float-end"></i></a>
            <a href="#" class="list-group-item list-group-item-action"><i class="bi bi-phone"></i> Active Sessions<span class="badge bg-primary float-end"><?=$sessions_count['count']?></span></a>
          </div>
        </div></div></div>
      </div>
      <div class="row"><div class="col-lg-12"><div class="card"><div class="card-body">
        <h5 class="card-title">Data Management</h5>
        <div class="row">
          <div class="col-md-3 text-center">
            <i class="bi bi-download" style="font-size: 2rem; color: #4154f1;"></i>
            <h6 class="mt-2">Export Data</h6>
            <p class="text-muted small">Download school data</p>
            <button class="btn btn-sm btn-outline-primary" onclick="alert('Export feature coming soon')">Export</button>
          </div>
          <div class="col-md-3 text-center">
            <i class="bi bi-arrow-clockwise" style="font-size: 2rem; color: #2eca6a;"></i>
            <h6 class="mt-2">Backup Data</h6>
            <p class="text-muted small">Create data backup</p>
            <button class="btn btn-sm btn-outline-success" onclick="alert('Backup feature coming soon')">Backup</button>
          </div>
          <div class="col-md-3 text-center">
            <i class="bi bi-trash" style="font-size: 2rem; color: #ff771d;"></i>
            <h6 class="mt-2">Archive Old Data</h6>
            <p class="text-muted small">Archive past records</p>
            <button class="btn btn-sm btn-outline-warning" onclick="alert('Archive feature coming soon')">Archive</button>
          </div>
          <div class="col-md-3 text-center">
            <i class="bi bi-graph-up" style="font-size: 2rem; color: #012970;"></i>
            <h6 class="mt-2">Database Stats</h6>
            <p class="text-muted small">View usage statistics</p>
            <a href="../reports/" class="btn btn-sm btn-outline-info">View Stats</a>
          </div>
        </div>
      </div></div></div></div>
    </section>
  </main>
  <footer id="footer" class="footer">
    <div class="copyright">&copy; Copyright <strong><span>ekiliSense</span></strong>. All Rights Reserved</div>
    <div class="credits">From <a href="https://ekilie.com">ekilie</a></div>
  </footer>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>
