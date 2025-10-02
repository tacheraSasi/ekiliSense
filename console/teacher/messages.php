<?php
session_start();
include_once "../../config.php";
include_once "../functions/timeAgo.php";

if(!isset($_SESSION['teacher_email']) || !isset($_SESSION['School_uid'])){
  header("location:https://auth.ekilie.com/sense/teacher");
}
$teacher_email = $_SESSION['teacher_email'];
$school_uid = $_SESSION['School_uid'];

$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);

$get_teacher = mysqli_query($conn, "SELECT * FROM teachers WHERE school_unique_id = '$school_uid' AND teacher_email = '$teacher_email'");
$teacher = mysqli_fetch_array($get_teacher);
$teacher_id = $teacher['teacher_id'];

$is_class_teacher = false;
$class_teacher_query = mysqli_query($conn, "SELECT * FROM class_teacher WHERE teacher_id = '$teacher_id'");
if(mysqli_num_rows($class_teacher_query) > 0){
  $is_class_teacher = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?=$school['School_name']?> | Messages</title>
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="icon">
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="../assets/css/custom.css" rel="stylesheet">
</head>
<body>
  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
      <a href="./" class="logo d-flex align-items-center">
        <span class="d-none d-lg-block">ekiliSense</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>
    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="../../assets/img/user.png" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?=$school['School_name']?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?=$school['School_name']?></h6>
              <span><?=$teacher['teacher_fullname']?></span>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item d-flex align-items-center" href="profile.php"><i class="bi bi-person"></i><span>My Profile</span></a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item d-flex align-items-center" href="./logout.php?logout_id=<?php echo $teacher_email?>"><i class="bi bi-box-arrow-right"></i><span>Sign Out</span></a></li>
          </ul>
        </li>
      </ul>
    </nav>
  </header>

  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
      <li class="nav-item"><a class="nav-link collapsed" href="./"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="subjects.php"><i class="bi bi-book"></i><span>My Subjects</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="assignments.php"><i class="bi bi-journal-text"></i><span>Assignments</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="grades.php"><i class="bi bi-clipboard-check"></i><span>Grades & Results</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="attendance.php"><i class="bi bi-clock"></i><span>Attendance</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="./plans.php"><i class="bi bi-pen"></i><span>Teaching Plans</span></a></li>
      <li class="nav-item"><a class="nav-link" href="messages.php"><i class="bi bi-chat-dots"></i><span>Messages</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="performance.php"><i class="bi bi-graph-up"></i><span>Performance</span></a></li>
      <?php if($is_class_teacher): ?>
      <li class="nav-heading">Class Teacher</li>
      <li class="nav-item"><a class="nav-link collapsed" href="../class/teacher/"><i class="bi bi-people"></i><span>My Class Dashboard</span></a></li>
      <?php endif; ?>
      <li class="nav-heading">Pages</li>
      <li class="nav-item"><a class="nav-link collapsed" href="profile.php"><i class="bi bi-person"></i><span>Profile</span></a></li>
    </ul>
  </aside>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1><i class="bi bi-chat-dots"></i> Messages & Announcements</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="./">Home</a></li>
          <li class="breadcrumb-item active">Messages</li>
        </ol>
      </nav>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Communication Center</h5>

              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> This page will integrate with the school messaging system. 
                <?php if($is_class_teacher): ?>
                As a class teacher, you have additional messaging capabilities in your 
                <a href="../class/teacher/messages.php" class="alert-link">Class Dashboard</a>.
                <?php endif; ?>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="card info-card">
                    <div class="card-body">
                      <h5 class="card-title">Inbox</h5>
                      <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                          <i class="bi bi-inbox"></i>
                        </div>
                        <div class="ps-3">
                          <h6>0</h6>
                          <span class="text-muted small">New messages</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="card info-card">
                    <div class="card-body">
                      <h5 class="card-title">Sent</h5>
                      <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                          <i class="bi bi-send"></i>
                        </div>
                        <div class="ps-3">
                          <h6>0</h6>
                          <span class="text-muted small">Messages sent</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="card info-card">
                    <div class="card-body">
                      <h5 class="card-title">Announcements</h5>
                      <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                          <i class="bi bi-megaphone"></i>
                        </div>
                        <div class="ps-3">
                          <h6>0</h6>
                          <span class="text-muted small">Active announcements</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="alert alert-secondary mt-3">
                <h6><i class="bi bi-lightbulb"></i> Coming Soon</h6>
                <p>Messaging features will include:</p>
                <ul>
                  <li>Send messages to students and parents</li>
                  <li>Class announcements</li>
                  <li>Assignment notifications</li>
                  <li>Grade reports</li>
                  <li>Real-time chat (via Convo integration)</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer id="footer" class="footer">
    <div class="copyright">&copy; Copyright <strong><span>ekiliSense</span></strong>. All Rights Reserved</div>
    <div class="credits">From <a href="https://ekilie.com">ekilie</a></div>
  </footer>
  
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>
