<?php
session_start();
include_once "../../../config.php";
include_once "../../../middlwares/student_auth.php";

// Get homework assignments for this student's class
$homework_query = mysqli_query($conn, "SELECT ha.*, s.subject_name, 
    (SELECT COUNT(*) FROM homework_submissions WHERE assignment_uid = ha.assignment_uid AND student_id = '$student_id') as submitted
    FROM homework_assignments ha 
    LEFT JOIN subjects s ON ha.subject_id = s.subject_id 
    WHERE ha.school_uid = '$school_uid' 
    AND ha.class_id = '$class_id' 
    AND ha.status = 'active'
    ORDER BY ha.due_date ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ekiliSense | <?= $school["School_name"] ?> | My Homework</title>

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

  <?php if(file_exists("./includes/topbar.php")) include_once "./includes/topbar.php"; ?>
  <?php
  $page = "homework";
  if(file_exists("./includes/sidebar.php")) include_once "./includes/sidebar.php";
  ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>My Homework <i class="bi bi-arrow-right-short"> </i> <?= $class_info["Class_name"] ?></h1>
    </div>

    <section class="section">
      <div class="row">
        <!-- Homework Statistics -->
        <div class="col-lg-12">
          <div class="row">
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Active Assignments</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-journal-text"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= mysqli_num_rows($homework_query) ?></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-3 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Submitted</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $submitted_count = 0;
                        mysqli_data_seek($homework_query, 0);
                        while($hw = mysqli_fetch_array($homework_query)) {
                          if($hw['submitted'] > 0) {
                            $submitted_count++;
                          }
                        }
                        echo $submitted_count;
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
                  <h5 class="card-title">Pending</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        mysqli_data_seek($homework_query, 0);
                        $pending = 0;
                        while($hw = mysqli_fetch_array($homework_query)) {
                          if($hw['submitted'] == 0) {
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
                  <h5 class="card-title">Overdue</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        mysqli_data_seek($homework_query, 0);
                        $overdue = 0;
                        $now = date('Y-m-d H:i:s');
                        while($hw = mysqli_fetch_array($homework_query)) {
                          $due_datetime = $hw['due_date'] . ' ' . ($hw['due_time'] ?? '23:59:59');
                          if($due_datetime < $now && $hw['submitted'] == 0) {
                            $overdue++;
                          }
                        }
                        echo $overdue;
                        ?>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Homework Assignments Table -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">My Assignments</h5>

              <!-- Table with stripped rows -->
              <table class="table table-dark table-hover datatable" style="overflow:auto">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Due Date</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Grade</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  mysqli_data_seek($homework_query, 0);
                  if (mysqli_num_rows($homework_query) > 0) {
                    while ($homework = mysqli_fetch_array($homework_query)) {
                      // Check if submitted
                      $submission_query = mysqli_query($conn, "SELECT * FROM homework_submissions 
                          WHERE assignment_uid = '{$homework['assignment_uid']}' AND student_id = '$student_id'");
                      $has_submission = mysqli_num_rows($submission_query) > 0;
                      $submission = $has_submission ? mysqli_fetch_array($submission_query) : null;
                      
                      // Check if overdue
                      $due_datetime = $homework['due_date'] . ' ' . ($homework['due_time'] ?? '23:59:59');
                      $is_overdue = $due_datetime < date('Y-m-d H:i:s');
                      
                      // Determine status
                      if ($has_submission) {
                        $status_class = 'badge-success';
                        $status_text = 'Submitted';
                        if ($submission['status'] == 'graded') {
                          $status_class = 'badge-primary';
                          $status_text = 'Graded';
                        }
                      } elseif ($is_overdue) {
                        $status_class = 'badge-danger';
                        $status_text = 'Overdue';
                      } else {
                        $status_class = 'badge-warning';
                        $status_text = 'Pending';
                      }
                  ?>
                    <tr>
                      <td>
                        <strong><?= htmlspecialchars($homework['title']) ?></strong>
                        <br><small class="text-muted"><?= substr(htmlspecialchars($homework['description']), 0, 50) ?>...</small>
                      </td>
                      <td><?= htmlspecialchars($homework['subject_name'] ?? 'N/A') ?></td>
                      <td>
                        <?= date('M d, Y', strtotime($homework['due_date'])) ?>
                        <?php if($homework['due_time']): ?>
                          <br><small><?= date('h:i A', strtotime($homework['due_time'])) ?></small>
                        <?php endif; ?>
                        <?php if($is_overdue && !$has_submission): ?>
                          <br><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Overdue</small>
                        <?php endif; ?>
                      </td>
                      <td><span class="badge badge-outline-primary"><?= ucfirst($homework['assignment_type']) ?></span></td>
                      <td><span class="badge <?= $status_class ?>"><?= $status_text ?></span></td>
                      <td>
                        <?php if($has_submission && $submission['grade'] !== null): ?>
                          <strong><?= $submission['grade'] ?> / <?= $homework['max_points'] ?></strong>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($has_submission): ?>
                          <button class="btn btn-sm btn-outline-primary" onclick="viewMySubmission('<?= $homework['assignment_uid'] ?>')">
                            <i class="bi bi-eye"></i> View
                          </button>
                        <?php else: ?>
                          <button class="btn btn-sm btn-outline-success" onclick="submitHomework('<?= $homework['assignment_uid'] ?>', '<?= htmlspecialchars($homework['title']) ?>', '<?= $homework['assignment_type'] ?>')">
                            <i class="bi bi-upload"></i> Submit
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php
                    }
                  } else {
                  ?>
                    <tr>
                      <td colspan="7" class="text-center">
                        <p class="text-muted">No active homework assignments</p>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
              <!-- End Table with stripped rows -->

            </div>
          </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- Submit Homework Modal -->
  <div class="modal fade" id="submitHomeworkModal" tabindex="-1" aria-labelledby="submitHomeworkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="submitHomeworkModalLabel">Submit Homework</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="submitHomeworkForm" method="POST" action="server/submit_homework.php" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="error-text" style="
                  background-color: rgba(243, 89, 89, 0.562);
                  border:solid 1px rgba(243, 89, 89, 0.822);
                  color:#fff;
                  padding:6px;
                  border-radius:8px;
                  display:none;">
            </div>
            
            <input type="hidden" name="assignment_uid" id="assignmentUid">
            <input type="hidden" name="student_id" value="<?= $student_id ?>">
            <input type="hidden" name="assignment_type" id="assignmentType">
            
            <div class="mb-3">
              <h6 id="homeworkTitle"></h6>
            </div>
            
            <div class="mb-3">
              <label for="submission_text" class="form-label">Your Answer/Response *</label>
              <textarea class="form-control" name="submission_text" id="submission_text" rows="6" required></textarea>
              <small class="text-muted">Provide your answer or explanation here</small>
            </div>
            
            <div class="mb-3">
              <label for="file_upload" class="form-label">Attach File (Optional)</label>
              <input type="file" class="form-control" name="file_upload" id="file_upload" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
              <small class="text-muted">Supported formats: PDF, DOC, DOCX, TXT, JPG, PNG (Max 5MB)</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="submitBtn">Submit Homework</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View My Submission Modal -->
  <div class="modal fade" id="viewMySubmissionModal" tabindex="-1" aria-labelledby="viewMySubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewMySubmissionModalLabel">My Submission</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="mySubmissionContent">
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

  <?php if(file_exists("./includes/footer.php")) include_once "./includes/footer.php"; ?>

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

</body>

</html>
