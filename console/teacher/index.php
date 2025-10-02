<?php
session_start();
include_once "../../config.php";
include_once "../functions/timeAgo.php";

if(!isset($_SESSION['teacher_email']) || !isset($_SESSION['School_uid'])){
  header("location:https://auth.ekilie.com/sense/teacher");
}
$teacher_email = "";//////####
$teacher_email = $_SESSION['teacher_email'];

$school_uid = $_SESSION['School_uid'];

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);

#getting the teachers information
$get_teacher = mysqli_query($conn, "SELECT * FROM teachers WHERE school_unique_id = '$school_uid' AND teacher_email = '$teacher_email'");
$teacher = mysqli_fetch_array($get_teacher);
$teacher_id = $teacher['teacher_id'];

#getting teacher statistics
$subjects_query = mysqli_query($conn, "SELECT * FROM subjects WHERE teacher_id = '$teacher_id'");
$subject_count = mysqli_num_rows($subjects_query);

#getting classes taught by this teacher
$classes_query = mysqli_query($conn, "SELECT DISTINCT c.* FROM classes c 
                                      JOIN subjects s ON c.Class_id = s.class_id 
                                      WHERE s.teacher_id = '$teacher_id'");
$class_count = mysqli_num_rows($classes_query);

#getting students taught by this teacher
$students_query = mysqli_query($conn, "SELECT DISTINCT st.* FROM students st 
                                       JOIN subjects s ON st.class_id = s.class_id 
                                       WHERE s.teacher_id = '$teacher_id'");
$student_count = mysqli_num_rows($students_query);

#getting homework/assignments count
$assignments_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM homework_assignments 
                                          WHERE teacher_id = '$teacher_id'");
$assignment_count = mysqli_fetch_array($assignments_query)['count'];

#getting pending assignments
$pending_assignments_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM homework_assignments 
                                                  WHERE teacher_id = '$teacher_id' 
                                                  AND deadline >= CURDATE()");
$pending_count = mysqli_fetch_array($pending_assignments_query)['count'];

#checking if teacher is a class teacher
$is_class_teacher = false;
$class_teacher_query = mysqli_query($conn, "SELECT * FROM class_teacher WHERE teacher_id = '$teacher_id'");
if(mysqli_num_rows($class_teacher_query) > 0){
  $is_class_teacher = true;
  $class_teacher_data = mysqli_fetch_array($class_teacher_query);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title> <?=$school['School_name']?> | teacher | <?=$teacher['teacher_fullname'];?></title>
  <meta content="" name="description">
  <meta content="" name="keywords">
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
      <a href="#" class="logo d-flex align-items-center">
        <span class="d-none d-lg-block">ekiliSense </span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->


    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="../../assets/img/user.png" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?=$school['School_name']?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?=$school['School_name']?></h6>
              <span><?=$teacher['teacher_fullname']?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
                <i class="bi bi-question-circle"></i>
                <span>Need Help?</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="./logout.php?logout_id=<?php echo $teacher_email?>">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="./">
          <i class="bi bi-speedometer2"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->
      
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
        <a class="nav-link collapsed" href="grades.php">
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

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>
        <i class="bi bi-speedometer2"></i>
        Dashboard
      </h1>
      <p>Welcome back, <?=$teacher['teacher_fullname']?>!</p>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">
        
        <!-- Left side columns -->
        <div class="col-lg-8">
          <div class="row">
            
            <!-- Classes Card -->
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Classes</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-buildings"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= $class_count ?></h6>
                      <span class="text-muted small pt-2 ps-1">Teaching</span>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- End Classes Card -->

            <!-- Students Card -->
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Students</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= $student_count ?></h6>
                      <span class="text-muted small pt-2 ps-1">Total</span>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- End Students Card -->

            <!-- Subjects Card -->
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Subjects</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-book"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= $subject_count ?></h6>
                      <span class="text-muted small pt-2 ps-1">Teaching</span>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- End Subjects Card -->

            <!-- Assignments Card -->
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Assignments</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-journal-text"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= $pending_count ?></h6>
                      <span class="text-muted small pt-2 ps-1">Pending</span>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- End Assignments Card -->

            <!-- Classes List -->
            <div class="col-12">
              <div class="card recent-sales overflow-auto">
                <div class="card-body">
                  <h5 class="card-title">My Classes</h5>
                  
                  <?php if($class_count > 0): ?>
                  <table class="table table-borderless">
                    <thead>
                      <tr>
                        <th scope="col">Class</th>
                        <th scope="col">Subjects</th>
                        <th scope="col">Students</th>
                        <th scope="col">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      mysqli_data_seek($classes_query, 0);
                      while($class = mysqli_fetch_array($classes_query)): 
                        $class_id = $class['Class_id'];
                        $class_subjects = mysqli_query($conn, "SELECT * FROM subjects WHERE class_id = '$class_id' AND teacher_id = '$teacher_id'");
                        $class_students = mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE class_id = '$class_id'");
                        $student_num = mysqli_fetch_array($class_students)['count'];
                      ?>
                      <tr>
                        <td><?= $class['Class_name'] ?></td>
                        <td><?= mysqli_num_rows($class_subjects) ?> subjects</td>
                        <td><?= $student_num ?> students</td>
                        <td>
                          <a href="../view/class.php?cid=<?= $class_id ?>&suid=<?= $school_uid ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i> View
                          </a>
                        </td>
                      </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                  <?php else: ?>
                  <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> You are not assigned to any classes yet.
                  </div>
                  <?php endif; ?>
                  
                </div>
              </div>
            </div><!-- End Classes List -->

          </div>
        </div><!-- End Left side columns -->

        <!-- Right side columns -->
        <div class="col-lg-4">
          
          <!-- Profile Card -->
          <div class="card">
            <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
              <img src="../../assets/img/user.png" alt="Profile" class="rounded-circle">
              <h2><?=$teacher['teacher_fullname']?></h2>
              <h3><?=$school['School_name']?></h3>
              <?php if($is_class_teacher): ?>
                <span class="badge bg-success mt-2">Class Teacher</span>
              <?php endif; ?>
              <div class="mt-3">
                <a href="profile.php" class="btn btn-primary btn-sm">
                  <i class="bi bi-person"></i> View Profile
                </a>
              </div>
            </div>
          </div><!-- End Profile Card -->

          <!-- Quick Actions -->
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Quick Actions</h5>
              <div class="d-grid gap-2">
                <a href="subjects.php" class="btn btn-outline-primary">
                  <i class="bi bi-book"></i> View My Subjects
                </a>
                <a href="assignments.php" class="btn btn-outline-success">
                  <i class="bi bi-journal-text"></i> Manage Assignments
                </a>
                <a href="attendance.php" class="btn btn-outline-warning">
                  <i class="bi bi-clock"></i> Mark Attendance
                </a>
                <a href="grades.php" class="btn btn-outline-info">
                  <i class="bi bi-clipboard-check"></i> Enter Grades
                </a>
                <?php if($is_class_teacher): ?>
                <a href="../class/teacher/" class="btn btn-outline-danger">
                  <i class="bi bi-people"></i> Class Dashboard
                </a>
                <?php endif; ?>
              </div>
            </div>
          </div><!-- End Quick Actions -->

          <!-- Recent Assignments -->
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Recent Assignments</h5>
              <?php 
              $recent_assignments = mysqli_query($conn, "SELECT ha.*, s.subject_name, c.Class_name 
                                                         FROM homework_assignments ha
                                                         JOIN subjects s ON ha.subject_id = s.subject_id
                                                         JOIN classes c ON s.class_id = c.Class_id
                                                         WHERE ha.teacher_id = '$teacher_id'
                                                         ORDER BY ha.created_at DESC
                                                         LIMIT 5");
              if(mysqli_num_rows($recent_assignments) > 0):
              ?>
              <ul class="list-group list-group-flush">
                <?php while($assignment = mysqli_fetch_array($recent_assignments)): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">
                  <div class="ms-2 me-auto">
                    <div class="fw-bold"><?= $assignment['assignment_title'] ?></div>
                    <small class="text-muted"><?= $assignment['Class_name'] ?> - <?= $assignment['subject_name'] ?></small>
                    <br>
                    <small class="text-muted">Due: <?= date('M d, Y', strtotime($assignment['deadline'])) ?></small>
                  </div>
                </li>
                <?php endwhile; ?>
              </ul>
              <?php else: ?>
              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No assignments yet.
              </div>
              <?php endif; ?>
            </div>
          </div><!-- End Recent Assignments -->

          <!-- Upcoming Tasks -->
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">ekiliSense Updates</h5>
              <div class="activity">
                <div class="activity-item d-flex">
                  <div class="activite-label">1 hr</div>
                  <i class='bi bi-circle-fill activity-badge text-success align-self-start'></i>
                  <div class="activity-content">
                    New <a href="#" class="fw-bold">Teacher Dashboard</a> with enhanced features
                  </div>
                </div>
                <div class="activity-item d-flex">
                  <div class="activite-label">3 hrs</div>
                  <i class='bi bi-circle-fill activity-badge text-primary align-self-start'></i>
                  <div class="activity-content">
                    Updated <a href="#" class="fw-bold">API</a> for mobile app integration
                  </div>
                </div>
                <div class="activity-item d-flex">
                  <div class="activite-label">1 day</div>
                  <i class='bi bi-circle-fill activity-badge text-info align-self-start'></i>
                  <div class="activity-content">
                    New <a href="#" class="fw-bold">Analytics</a> for performance tracking
                  </div>
                </div>
              </div>
            </div>
          </div><!-- End Upcoming Tasks -->

        </div><!-- End Right side columns -->

      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>ekiliSense<span></strong>. All Rights Reserved
    </div>
    <div class="credits">
    From <a href="https://ekilie.com">ekilie</a>
    </div>
  </footer><!-- End Footer -->
  
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  
  <!-- Vendor JS Files -->
  <script src="../assets/js/modal-form.js"></script>
  
  <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../assets/vendor/echarts/echarts.min.js"></script>
  <script src="../assets/vendor/quill/quill.min.js"></script>
  <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../assets/vendor/php-email-form/validate.js"></script>

  <script src="../assets/js/main.js"></script>

</body>

</html>