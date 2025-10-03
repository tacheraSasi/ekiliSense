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

// Get all submissions for this assignment
$submissions_query = mysqli_query($conn, "SELECT hs.*, st.student_name, st.student_email 
    FROM homework_submissions hs 
    LEFT JOIN students st ON hs.student_id = st.student_id 
    WHERE hs.assignment_uid = '$assignment_uid' 
    AND hs.school_uid = '$school_uid' 
    ORDER BY hs.submission_date DESC");

// Get total students count
$student_count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM students 
    WHERE class_id = '{$assignment['class_id']}' AND school_uid = '$school_uid'");
$total_students = mysqli_fetch_array($student_count_query)['count'];
$total_submissions = mysqli_num_rows($submissions_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ekiliSense | <?= $school["School_name"] ?> | Submissions</title>

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
        <h1 style="display:inline-block">Submissions <i class="bi bi-arrow-right-short"> </i> <?= htmlspecialchars($assignment['title']) ?></h1>
      </div>
      <a href="homework.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Homework
      </a>
    </div>

    <section class="section">
      <div class="row">
        <!-- Assignment Details Card -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Assignment Details</h5>
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Subject:</strong> <?= htmlspecialchars($assignment['subject_name']) ?></p>
                  <p><strong>Class:</strong> <?= htmlspecialchars($assignment['Class_name']) ?></p>
                  <p><strong>Type:</strong> <?= ucfirst($assignment['assignment_type']) ?></p>
                </div>
                <div class="col-md-6">
                  <p><strong>Due Date:</strong> <?= date('M d, Y', strtotime($assignment['due_date'])) ?>
                    <?php if($assignment['due_time']): ?>
                      at <?= date('h:i A', strtotime($assignment['due_time'])) ?>
                    <?php endif; ?>
                  </p>
                  <p><strong>Max Points:</strong> <?= $assignment['max_points'] ?></p>
                  <p><strong>Status:</strong> <span class="badge <?= $assignment['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($assignment['status']) ?></span></p>
                </div>
              </div>
              <?php if($assignment['description']): ?>
                <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($assignment['description'])) ?></p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Submission Statistics -->
        <div class="col-lg-12">
          <div class="row">
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Total Submissions</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= $total_submissions ?> / <?= $total_students ?></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-3 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Graded</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $graded_count = 0;
                        mysqli_data_seek($submissions_query, 0);
                        while($sub = mysqli_fetch_array($submissions_query)) {
                          if($sub['status'] == 'graded') {
                            $graded_count++;
                          }
                        }
                        echo $graded_count;
                        ?>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-3 col-md-6">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Pending Review</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        mysqli_data_seek($submissions_query, 0);
                        $pending = 0;
                        while($sub = mysqli_fetch_array($submissions_query)) {
                          if($sub['status'] == 'submitted') {
                            $pending++;
                          }
                        }
                        echo $pending;
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
                  <h5 class="card-title">Late Submissions</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        mysqli_data_seek($submissions_query, 0);
                        $late = 0;
                        while($sub = mysqli_fetch_array($submissions_query)) {
                          if($sub['status'] == 'late') {
                            $late++;
                          }
                        }
                        echo $late;
                        ?>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Submissions Table -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Student Submissions</h5>

              <table class="table table-dark table-hover datatable" style="overflow:auto">
                <thead>
                  <tr>
                    <th>Student</th>
                    <th>Submitted On</th>
                    <th>Status</th>
                    <th>Grade</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  mysqli_data_seek($submissions_query, 0);
                  if (mysqli_num_rows($submissions_query) > 0) {
                    while ($submission = mysqli_fetch_array($submissions_query)) {
                      $status_class = 'badge-primary';
                      if($submission['status'] == 'graded') $status_class = 'badge-success';
                      if($submission['status'] == 'late') $status_class = 'badge-warning';
                      if($submission['status'] == 'submitted') $status_class = 'badge-info';
                  ?>
                    <tr>
                      <td>
                        <strong><?= htmlspecialchars($submission['student_name'] ?? 'Unknown Student') ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($submission['student_email'] ?? '') ?></small>
                      </td>
                      <td>
                        <?= date('M d, Y h:i A', strtotime($submission['submission_date'])) ?>
                        <?php
                        $is_late = strtotime($submission['submission_date']) > strtotime($assignment['due_date'] . ' ' . ($assignment['due_time'] ?? '23:59:59'));
                        if ($is_late): ?>
                          <br><small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Late</small>
                        <?php endif; ?>
                      </td>
                      <td><span class="badge <?= $status_class ?>"><?= ucfirst($submission['status']) ?></span></td>
                      <td>
                        <?php if($submission['grade'] !== null): ?>
                          <strong><?= $submission['grade'] ?> / <?= $assignment['max_points'] ?></strong>
                        <?php else: ?>
                          <span class="text-muted">Not graded</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewSubmission(<?= $submission['submission_id'] ?>)">
                          <i class="bi bi-eye"></i> View
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="gradeSubmission(<?= $submission['submission_id'] ?>, <?= $assignment['max_points'] ?>)">
                          <i class="bi bi-pencil-square"></i> Grade
                        </button>
                      </td>
                    </tr>
                  <?php
                    }
                  } else {
                  ?>
                    <tr>
                      <td colspan="5" class="text-center">
                        <p class="text-muted">No submissions yet</p>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>

            </div>
          </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- View Submission Modal -->
  <div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-labelledby="viewSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewSubmissionModalLabel">Submission Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="submissionContent">
          <div class="text-center">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Loading...
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Grade Submission Modal -->
  <div class="modal fade" id="gradeSubmissionModal" tabindex="-1" aria-labelledby="gradeSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="gradeSubmissionModalLabel">Grade Submission</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="gradeSubmissionForm" method="POST" action="server/homework.php">
          <div class="modal-body">
            <div class="error-text" style="
                  background-color: rgba(243, 89, 89, 0.562);
                  border:solid 1px rgba(243, 89, 89, 0.822);
                  color:#fff;
                  padding:6px;
                  border-radius:8px;
                  display:none;">
            </div>
            
            <input type="hidden" name="form-type" value="grade-submission">
            <input type="hidden" name="submission_id" id="gradeSubmissionId">
            
            <div class="mb-3">
              <label for="grade" class="form-label">Grade (out of <span id="maxPoints"></span>)</label>
              <input type="number" class="form-control" name="grade" id="grade" min="0" required>
            </div>
            
            <div class="mb-3">
              <label for="feedback" class="form-label">Feedback</label>
              <textarea class="form-control" name="feedback" id="feedback" rows="4"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="submitGrade">Submit Grade</button>
          </div>
        </form>
      </div>
    </div>
  </div>

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
  <script src="js/homework_submissions.js"></script>

</body>

</html>
