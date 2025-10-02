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

#getting subjects for dropdown
$subjects_query = mysqli_query($conn, "SELECT s.*, c.Class_name 
                                       FROM subjects s
                                       JOIN classes c ON s.class_id = c.Class_id
                                       WHERE s.teacher_id = '$teacher_id'
                                       ORDER BY c.Class_name, s.subject_name");

#filter by subject if provided
$filter_subject = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
$subject_filter_sql = $filter_subject ? "AND ha.subject_id = '$filter_subject'" : '';

#getting assignments
$assignments_query = mysqli_query($conn, "SELECT ha.*, s.subject_name, c.Class_name, c.Class_id
                                          FROM homework_assignments ha
                                          JOIN subjects s ON ha.subject_id = s.subject_id
                                          JOIN classes c ON s.class_id = c.Class_id
                                          WHERE ha.teacher_id = '$teacher_id' $subject_filter_sql
                                          ORDER BY ha.deadline DESC");

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

  <title><?=$school['School_name']?> | Assignments</title>
  
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
        <a class="nav-link" href="assignments.php">
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
  </aside>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1><i class="bi bi-journal-text"></i> Assignments</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="./">Home</a></li>
          <li class="breadcrumb-item active">Assignments</li>
        </ol>
      </nav>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">My Assignments</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
                  <i class="bi bi-plus-circle"></i> Create Assignment
                </button>
              </div>

              <!-- Filter -->
              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="subjectFilter" class="form-label">Filter by Subject:</label>
                  <select class="form-select" id="subjectFilter" onchange="window.location.href='assignments.php?subject_id='+this.value">
                    <option value="">All Subjects</option>
                    <?php 
                    mysqli_data_seek($subjects_query, 0);
                    while($subj = mysqli_fetch_array($subjects_query)): 
                    ?>
                    <option value="<?= $subj['subject_id'] ?>" <?= $filter_subject == $subj['subject_id'] ? 'selected' : '' ?>>
                      <?= $subj['Class_name'] ?> - <?= $subj['subject_name'] ?>
                    </option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>

              <?php if(mysqli_num_rows($assignments_query) > 0): ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Title</th>
                      <th scope="col">Subject</th>
                      <th scope="col">Class</th>
                      <th scope="col">Deadline</th>
                      <th scope="col">Status</th>
                      <th scope="col">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $count = 1;
                    while($assignment = mysqli_fetch_array($assignments_query)): 
                      $deadline = strtotime($assignment['deadline']);
                      $today = strtotime(date('Y-m-d'));
                      $status = $deadline >= $today ? 'Active' : 'Expired';
                      $status_class = $deadline >= $today ? 'bg-success' : 'bg-danger';
                      
                      // Get submission count
                      $submission_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM homework_submissions 
                                                               WHERE assignment_uid = '{$assignment['assignment_uid']}'");
                      $submission_count = mysqli_fetch_array($submission_query)['count'];
                      
                      // Get total students in class
                      $class_id = $assignment['Class_id'];
                      $student_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE class_id = '$class_id'");
                      $total_students = mysqli_fetch_array($student_query)['count'];
                    ?>
                    <tr>
                      <th scope="row"><?= $count++ ?></th>
                      <td><?= $assignment['assignment_title'] ?></td>
                      <td><span class="badge bg-primary"><?= $assignment['subject_name'] ?></span></td>
                      <td><?= $assignment['Class_name'] ?></td>
                      <td><?= date('M d, Y', strtotime($assignment['deadline'])) ?></td>
                      <td><span class="badge <?= $status_class ?>"><?= $status ?></span></td>
                      <td>
                        <button class="btn btn-sm btn-info" onclick="viewAssignment('<?= $assignment['assignment_uid'] ?>')">
                          <i class="bi bi-eye"></i>
                        </button>
                        <span class="badge bg-secondary"><?= $submission_count ?>/<?= $total_students ?> submitted</span>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
              <?php else: ?>
              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No assignments found. Create your first assignment to get started!
              </div>
              <?php endif; ?>

            </div>
          </div>

        </div>
      </div>
    </section>

  </main>

  <!-- Create Assignment Modal -->
  <div class="modal fade" id="addAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create New Assignment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="server/manage-assignments.php" method="POST">
          <div class="modal-body">
            <div class="mb-3">
              <label for="assignmentTitle" class="form-label">Assignment Title *</label>
              <input type="text" class="form-control" id="assignmentTitle" name="title" required>
            </div>
            <div class="mb-3">
              <label for="assignmentSubject" class="form-label">Subject *</label>
              <select class="form-select" id="assignmentSubject" name="subject_id" required>
                <option value="">Select Subject</option>
                <?php 
                mysqli_data_seek($subjects_query, 0);
                while($subj = mysqli_fetch_array($subjects_query)): 
                ?>
                <option value="<?= $subj['subject_id'] ?>">
                  <?= $subj['Class_name'] ?> - <?= $subj['subject_name'] ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="assignmentDescription" class="form-label">Description</label>
              <textarea class="form-control" id="assignmentDescription" name="description" rows="4"></textarea>
            </div>
            <div class="mb-3">
              <label for="assignmentDeadline" class="form-label">Deadline *</label>
              <input type="date" class="form-control" id="assignmentDeadline" name="deadline" required min="<?= date('Y-m-d') ?>">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="create_assignment" class="btn btn-primary">Create Assignment</button>
          </div>
        </form>
      </div>
    </div>
  </div>

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
  function viewAssignment(uid) {
    window.location.href = 'view_assignment.php?uid=' + uid;
  }
  </script>

</body>

</html>
