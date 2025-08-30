<?php
session_start();
include_once "../../../config.php";
include_once "../../../middlwares/teacher_auth.php";

// Get homework assignments for this teacher
$homework_query = mysqli_query($conn, "SELECT ha.*, s.subject_name, c.Class_name 
    FROM homework_assignments ha 
    LEFT JOIN subjects s ON ha.subject_id = s.subject_id 
    LEFT JOIN classes c ON ha.class_id = c.Class_id 
    WHERE ha.school_uid = '$school_uid' AND ha.teacher_id = '$teacher_id' 
    ORDER BY ha.due_date ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ekiliSense | <?= $school["School_name"] ?> | Homework</title>

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
        <h1 style="display:inline-block">Homework Management <i class="bi bi-arrow-right-short"> </i> <?= $class_info["Class_name"] ?></h1>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHomeworkModal">
        <i class="bi bi-plus-circle"></i> Assign Homework
      </button>
    </div>

    <section class="section">
      <div class="row">
        <!-- Homework Statistics -->
        <div class="col-lg-12">
          <div class="row">
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Total Assignments</h5>
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
                  <h5 class="card-title">Active Assignments</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-clock"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $active_count = 0;
                        mysqli_data_seek($homework_query, 0);
                        while($hw = mysqli_fetch_array($homework_query)) {
                          if($hw['status'] == 'active' && $hw['due_date'] >= date('Y-m-d')) {
                            $active_count++;
                          }
                        }
                        echo $active_count;
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
                  <h5 class="card-title">Overdue</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $overdue_count = 0;
                        mysqli_data_seek($homework_query, 0);
                        while($hw = mysqli_fetch_array($homework_query)) {
                          if($hw['status'] == 'active' && $hw['due_date'] < date('Y-m-d')) {
                            $overdue_count++;
                          }
                        }
                        echo $overdue_count;
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
                  <h5 class="card-title">Pending Review</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-clipboard-check"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $pending_review = mysqli_query($conn, "SELECT COUNT(*) as count FROM homework_submissions 
                            WHERE school_uid = '$school_uid' AND status = 'submitted'");
                        $pending_count = mysqli_fetch_array($pending_review)['count'];
                        echo $pending_count;
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
              <h5 class="card-title">Your Homework Assignments</h5>

              <!-- Table with stripped rows -->
              <table class="table table-dark table-hover datatable" style="overflow:auto">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Due Date</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Submissions</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  mysqli_data_seek($homework_query, 0);
                  while ($homework = mysqli_fetch_array($homework_query)) {
                    // Count submissions for this assignment
                    $submission_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM homework_submissions 
                        WHERE assignment_uid = '{$homework['assignment_uid']}'");
                    $submissions = mysqli_fetch_array($submission_count)['count'];
                    
                    // Determine status color
                    $status_class = 'badge-primary';
                    if($homework['due_date'] < date('Y-m-d') && $homework['status'] == 'active') {
                      $status_class = 'badge-danger';
                      $status_text = 'Overdue';
                    } else {
                      $status_text = ucfirst($homework['status']);
                      if($homework['status'] == 'active') $status_class = 'badge-success';
                      if($homework['status'] == 'closed') $status_class = 'badge-secondary';
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
                      </td>
                      <td><span class="badge badge-outline-primary"><?= ucfirst($homework['assignment_type']) ?></span></td>
                      <td><span class="badge <?= $status_class ?>"><?= $status_text ?></span></td>
                      <td>
                        <span class="badge badge-info"><?= $submissions ?></span>
                        <small class="text-muted">/ <?= $student_num ?></small>
                      </td>
                      <td>
                        <a href="homework_view.php?id=<?= $homework['assignment_uid'] ?>" class="btn btn-sm btn-outline-primary">
                          <i class="bi bi-eye"></i> View
                        </a>
                        <a href="homework_submissions.php?id=<?= $homework['assignment_uid'] ?>" class="btn btn-sm btn-outline-success">
                          <i class="bi bi-clipboard-check"></i> Submissions
                        </a>
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

  <!-- Add Homework Modal -->
  <div class="modal fade" id="addHomeworkModal" tabindex="-1" aria-labelledby="addHomeworkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addHomeworkModalLabel">Assign New Homework</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addHomeworkForm" method="POST" action="server/homework.php">
          <div class="modal-body">
            <div class="error-text" style="
                  background-color: rgba(243, 89, 89, 0.562);
                  border:solid 1px rgba(243, 89, 89, 0.822);
                  color:#fff;
                  padding:6px;
                  border-radius:8px;
                  display:none;">
            </div>
            
            <input type="hidden" name="form-type" value="add-homework">
            <input type="hidden" name="class_id" value="<?= $class_id ?>">
            <input type="hidden" name="teacher_id" value="<?= $teacher_id ?>">
            
            <div class="row">
              <div class="col-md-12 mb-3">
                <label for="subject_id" class="form-label">Subject *</label>
                <select class="form-select" name="subject_id" required>
                  <option value="">Select Subject</option>
                  <?php
                  mysqli_data_seek($subjects, 0);
                  while ($subject = mysqli_fetch_array($subjects)) {
                    echo "<option value='{$subject['subject_id']}'>{$subject['subject_name']}</option>";
                  }
                  ?>
                </select>
              </div>
              
              <div class="col-md-12 mb-3">
                <label for="title" class="form-label">Assignment Title *</label>
                <input type="text" class="form-control" name="title" required placeholder="e.g., Math Exercise Chapter 5">
              </div>
              
              <div class="col-md-12 mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="4" placeholder="Provide detailed instructions for the assignment..."></textarea>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="assignment_type" class="form-label">Type</label>
                <select class="form-select" name="assignment_type">
                  <option value="homework">Homework</option>
                  <option value="project">Project</option>
                  <option value="essay">Essay</option>
                  <option value="quiz">Quiz</option>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="max_points" class="form-label">Max Points</label>
                <input type="number" class="form-control" name="max_points" value="100" min="1" max="1000">
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="due_date" class="form-label">Due Date *</label>
                <input type="date" class="form-control" name="due_date" required min="<?= date('Y-m-d') ?>">
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="due_time" class="form-label">Due Time</label>
                <input type="time" class="form-control" name="due_time">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="submitHomework">Assign Homework</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

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