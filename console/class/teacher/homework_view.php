<?php
session_start();
include_once "../../../config.php";
include_once "../../../middlwares/teacher_auth.php";

// Get assignment ID from URL
if (!isset($_GET['id'])) {
    header("Location: homework.php");
    exit();
}

$assignment_uid = mysqli_real_escape_string($conn, $_GET['id']);

// Get assignment details
$assignment_query = mysqli_query($conn, "SELECT ha.*, s.subject_name, c.Class_name 
    FROM homework_assignments ha 
    LEFT JOIN subjects s ON ha.subject_id = s.subject_id 
    LEFT JOIN classes c ON ha.class_id = c.Class_id 
    WHERE ha.assignment_uid = '$assignment_uid' 
    AND ha.school_uid = '$school_uid' 
    AND ha.teacher_id = '$teacher_id'");

if (mysqli_num_rows($assignment_query) == 0) {
    header("Location: homework.php");
    exit();
}

$assignment = mysqli_fetch_array($assignment_query);

// Get submission count
$submission_count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM homework_submissions 
    WHERE assignment_uid = '$assignment_uid'");
$submission_count = mysqli_fetch_array($submission_count_query)['count'];

// Get graded count
$graded_count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM homework_submissions 
    WHERE assignment_uid = '$assignment_uid' AND status = 'graded'");
$graded_count = mysqli_fetch_array($graded_count_query)['count'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ekiliSense | <?= $school["School_name"] ?> | Homework Details</title>

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
  $page = "homework";
  include_once "./includes/sidebar.php";
  ?>

  <main id="main" class="main">
    <div class="d-flex justify-content-between flex-wrap" style="margin:1rem auto;">
      <div class="pagetitle" style="display:inline-block">
        <h1 style="display:inline-block">Homework Details</h1>
      </div>
      <div>
        <a href="homework.php" class="btn btn-secondary me-2">
          <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="homework_submissions.php?id=<?= $assignment_uid ?>" class="btn btn-success">
          <i class="bi bi-clipboard-check"></i> View Submissions
        </a>
      </div>
    </div>

    <section class="section">
      <div class="row">
        <!-- Assignment Details Card -->
        <div class="col-lg-8">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($assignment['title']) ?></h5>
              
              <div class="row mb-3">
                <div class="col-md-6">
                  <p><strong>Subject:</strong> <?= htmlspecialchars($assignment['subject_name']) ?></p>
                  <p><strong>Class:</strong> <?= htmlspecialchars($assignment['Class_name']) ?></p>
                  <p><strong>Type:</strong> <span class="badge badge-outline-primary"><?= ucfirst($assignment['assignment_type']) ?></span></p>
                </div>
                <div class="col-md-6">
                  <p><strong>Due Date:</strong> <?= date('M d, Y', strtotime($assignment['due_date'])) ?>
                    <?php if($assignment['due_time']): ?>
                      at <?= date('h:i A', strtotime($assignment['due_time'])) ?>
                    <?php endif; ?>
                  </p>
                  <p><strong>Max Points:</strong> <?= $assignment['max_points'] ?></p>
                  <p><strong>Status:</strong> 
                    <?php
                    $status_class = 'bg-primary';
                    $status_text = ucfirst($assignment['status']);
                    if($assignment['due_date'] < date('Y-m-d') && $assignment['status'] == 'active') {
                      $status_class = 'bg-danger';
                      $status_text = 'Overdue';
                    } else {
                      if($assignment['status'] == 'active') $status_class = 'bg-success';
                      if($assignment['status'] == 'closed') $status_class = 'bg-secondary';
                    }
                    ?>
                    <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                  </p>
                </div>
              </div>

              <?php if($assignment['description']): ?>
                <div class="mb-3">
                  <h6>Description:</h6>
                  <div class="p-3 bg-light rounded">
                    <?= nl2br(htmlspecialchars($assignment['description'])) ?>
                  </div>
                </div>
              <?php endif; ?>

              <hr>

              <div class="d-flex justify-content-between">
                <button class="btn btn-outline-primary" onclick="editHomework('<?= $assignment_uid ?>')">
                  <i class="bi bi-pencil"></i> Edit Assignment
                </button>
                <button class="btn btn-outline-danger" onclick="deleteHomework('<?= $assignment_uid ?>')">
                  <i class="bi bi-trash"></i> Delete Assignment
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Submission Statistics</h5>
              
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-2">
                  <span>Total Submissions</span>
                  <strong><?= $submission_count ?></strong>
                </div>
                <div class="progress">
                  <div class="progress-bar" role="progressbar" style="width: <?= $student_num > 0 ? ($submission_count / $student_num * 100) : 0 ?>%"></div>
                </div>
                <small class="text-muted"><?= $submission_count ?> of <?= $student_num ?> students</small>
              </div>

              <div class="mb-3">
                <div class="d-flex justify-content-between mb-2">
                  <span>Graded</span>
                  <strong><?= $graded_count ?></strong>
                </div>
                <div class="progress">
                  <div class="progress-bar bg-success" role="progressbar" style="width: <?= $submission_count > 0 ? ($graded_count / $submission_count * 100) : 0 ?>%"></div>
                </div>
                <small class="text-muted"><?= $graded_count ?> of <?= $submission_count ?> submissions</small>
              </div>

              <hr>

              <div class="mb-2">
                <i class="bi bi-calendar-check text-primary"></i>
                <small>Created: <?= date('M d, Y', strtotime($assignment['created_at'])) ?></small>
              </div>

              <?php if($assignment['updated_at'] != $assignment['created_at']): ?>
              <div class="mb-2">
                <i class="bi bi-calendar-edit text-info"></i>
                <small>Updated: <?= date('M d, Y', strtotime($assignment['updated_at'])) ?></small>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Quick Actions</h5>
              
              <div class="d-grid gap-2">
                <a href="homework_submissions.php?id=<?= $assignment_uid ?>" class="btn btn-outline-success">
                  <i class="bi bi-clipboard-check"></i> View Submissions
                </a>
                <?php if ($assignment['assignment_type'] == 'quiz'): ?>
                <a href="homework_quiz_questions.php?id=<?= $assignment_uid ?>" class="btn btn-outline-warning">
                  <i class="bi bi-question-circle"></i> Manage Quiz Questions
                </a>
                <?php endif; ?>
                <button class="btn btn-outline-primary" onclick="toggleStatus('<?= $assignment_uid ?>', '<?= $assignment['status'] ?>')">
                  <i class="bi bi-toggle-on"></i> 
                  <?= $assignment['status'] == 'active' ? 'Close Assignment' : 'Reopen Assignment' ?>
                </button>
                <button class="btn btn-outline-info" onclick="sendReminder('<?= $assignment_uid ?>')">
                  <i class="bi bi-bell"></i> Send Reminder
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <?php include_once "./includes/footer.php"; ?>

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
  <script src="js/homework.js"></script>

  <script>
    function editHomework(assignmentUid) {
      // This would open an edit modal or redirect to an edit page
      alert('Edit functionality would go here. Assignment UID: ' + assignmentUid);
    }

    function toggleStatus(assignmentUid, currentStatus) {
      const newStatus = currentStatus === 'active' ? 'closed' : 'active';
      const confirmMsg = currentStatus === 'active' 
        ? 'Are you sure you want to close this assignment? Students will no longer be able to submit.'
        : 'Are you sure you want to reopen this assignment?';
      
      if (confirm(confirmMsg)) {
        // Implementation would use the toggleHomeworkStatus function from homework.js
        alert('Toggle status functionality. Assignment UID: ' + assignmentUid + ', New Status: ' + newStatus);
      }
    }

    function sendReminder(assignmentUid) {
      if (confirm('Send a reminder to all students who haven\'t submitted?')) {
        alert('Send reminder functionality would go here. Assignment UID: ' + assignmentUid);
      }
    }
  </script>

</body>

</html>
