<?php
session_start();
include_once "../../config.php";
include_once "../functions/timeAgo.php";

if(!isset($_SESSION['teacher_email']) || !isset($_SESSION['School_uid'])){
  header("location:https://auth.ekilie.com/sense/teacher");
}
$teacher_email = $_SESSION['teacher_email'];
$school_uid = $_SESSION['School_uid'];

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);

#getting the teachers information
$get_teacher = mysqli_query($conn, "SELECT * FROM teachers WHERE school_unique_id = '$school_uid' AND teacher_email = '$teacher_email'");
$teacher = mysqli_fetch_array($get_teacher);
$teacher_id = $teacher['teacher_id'];

#getting subjects for filtering
$subjects_query = mysqli_query($conn, "SELECT s.*, c.Class_name 
                                       FROM subjects s
                                       JOIN classes c ON s.class_id = c.Class_id
                                       WHERE s.teacher_id = '$teacher_id'
                                       ORDER BY c.Class_name, s.subject_name");

#checking if teacher is a class teacher
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

  <title><?=$school['School_name']?> | Grades & Results</title>
  
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="icon">
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="apple-touch-icon">

  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="../assets/css/custom.css" rel="stylesheet">

</head>

<body>
  <!-- ======= Header ======= -->
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
            <li>
              <a class="dropdown-item d-flex align-items-center" href="profile.php">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="./logout.php?logout_id=<?php echo $teacher_email?>">
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
        <a class="nav-link collapsed" href="./">
          <i class="bi bi-speedometer2"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="subjects.php">
          <i class="bi bi-book"></i>
          <span>My Subjects</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="assignments.php">
          <i class="bi bi-journal-text"></i>
          <span>Assignments</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="grades.php">
          <i class="bi bi-clipboard-check"></i>
          <span>Grades & Results</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="attendance.php">
          <i class="bi bi-clock"></i>
          <span>Attendance</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="./plans.php">
          <i class="bi bi-pen"></i>
          <span>Teaching Plans</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="messages.php">
          <i class="bi bi-chat-dots"></i>
          <span>Messages</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="performance.php">
          <i class="bi bi-graph-up"></i>
          <span>Performance</span>
        </a>
      </li>
      <?php if($is_class_teacher): ?>
      <li class="nav-heading">Class Teacher</li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../class/teacher/">
          <i class="bi bi-people"></i>
          <span>My Class Dashboard</span>
        </a>
      </li>
      <?php endif; ?>
      <li class="nav-heading">Pages</li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="profile.php">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li>
    </ul>
  </aside>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1><i class="bi bi-clipboard-check"></i> Grades & Results</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="./">Home</a></li>
          <li class="breadcrumb-item active">Grades</li>
        </ol>
      </nav>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Student Grades Management</h5>

              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Select a subject and class to view and manage student grades.
              </div>

              <!-- Subject and Class Selection -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <label for="subjectSelect" class="form-label">Select Subject:</label>
                  <select class="form-select" id="subjectSelect" onchange="loadStudentGrades()">
                    <option value="">Choose a subject...</option>
                    <?php while($subject = mysqli_fetch_array($subjects_query)): ?>
                    <option value="<?= $subject['subject_id'] ?>" data-class="<?= $subject['class_id'] ?>">
                      <?= $subject['Class_name'] ?> - <?= $subject['subject_name'] ?>
                    </option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>

              <!-- Students Grades Table (will be loaded dynamically) -->
              <div id="gradesTableContainer">
                <p class="text-muted">Please select a subject to view student grades.</p>
              </div>

            </div>
          </div>

        </div>
      </div>
    </section>

  </main>

  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>ekiliSense</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      From <a href="https://ekilie.com">ekilie</a>
    </div>
  </footer>
  
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  
  <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../assets/vendor/echarts/echarts.min.js"></script>
  <script src="../assets/vendor/quill/quill.min.js"></script>
  <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../assets/vendor/php-email-form/validate.js"></script>
  <script src="../assets/js/main.js"></script>
  
  <script>
  function loadStudentGrades() {
    const subjectSelect = document.getElementById('subjectSelect');
    const subjectId = subjectSelect.value;
    const classId = subjectSelect.options[subjectSelect.selectedIndex].dataset.class;
    
    if(!subjectId) {
      document.getElementById('gradesTableContainer').innerHTML = '<p class="text-muted">Please select a subject to view student grades.</p>';
      return;
    }
    
    // In a real implementation, this would fetch data via AJAX
    document.getElementById('gradesTableContainer').innerHTML = `
      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> Grade entry functionality will be integrated with the exam results system. 
        Please use the class teacher dashboard or the results page for detailed grade management.
      </div>
      <p class="text-muted">This feature allows viewing and managing grades for the selected subject and class.</p>
    `;
  }
  </script>

</body>

</html>
