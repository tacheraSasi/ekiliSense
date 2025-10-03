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

// Get existing quiz questions
$questions_query = mysqli_query($conn, "SELECT * FROM quiz_questions 
    WHERE assignment_uid = '$assignment_uid' 
    ORDER BY question_order ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ekiliSense | <?= $school["School_name"] ?> | Quiz Questions</title>

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
        <h1 style="display:inline-block">Quiz Questions <i class="bi bi-arrow-right-short"> </i> <?= htmlspecialchars($assignment['title']) ?></h1>
      </div>
      <div>
        <a href="homework_view.php?id=<?= $assignment_uid ?>" class="btn btn-secondary me-2">
          <i class="bi bi-arrow-left"></i> Back
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
          <i class="bi bi-plus-circle"></i> Add Question
        </button>
      </div>
    </div>

    <section class="section">
      <div class="row">
        <!-- Assignment Info -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Assignment Info</h5>
              <p><strong>Type:</strong> <?= ucfirst($assignment['assignment_type']) ?></p>
              <p><strong>Max Points:</strong> <?= $assignment['max_points'] ?></p>
              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Add quiz questions below to enable auto-grading for this assignment.
                Students will submit answers in JSON format or as "question_id:answer|question_id:answer".
              </div>
            </div>
          </div>
        </div>

        <!-- Quiz Questions List -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Quiz Questions (<?= mysqli_num_rows($questions_query) ?>)</h5>

              <?php if (mysqli_num_rows($questions_query) > 0): ?>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th width="5%">#</th>
                        <th width="50%">Question</th>
                        <th width="15%">Type</th>
                        <th width="10%">Points</th>
                        <th width="20%">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $question_num = 1;
                      mysqli_data_seek($questions_query, 0);
                      while ($question = mysqli_fetch_array($questions_query)):
                      ?>
                        <tr>
                          <td><?= $question_num++ ?></td>
                          <td>
                            <strong><?= htmlspecialchars($question['question_text']) ?></strong>
                            <?php if ($question['question_type'] == 'multiple_choice'): ?>
                              <br><small class="text-muted">
                                A: <?= htmlspecialchars($question['option_a']) ?><br>
                                B: <?= htmlspecialchars($question['option_b']) ?><br>
                                C: <?= htmlspecialchars($question['option_c']) ?><br>
                                D: <?= htmlspecialchars($question['option_d']) ?>
                              </small>
                            <?php endif; ?>
                            <br><small class="text-success">Correct: <?= htmlspecialchars($question['correct_answer']) ?></small>
                          </td>
                          <td><?= ucfirst(str_replace('_', ' ', $question['question_type'])) ?></td>
                          <td><?= $question['points'] ?></td>
                          <td>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteQuestion(<?= $question['question_id'] ?>)">
                              <i class="bi bi-trash"></i>
                            </button>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="alert alert-warning">
                  <i class="bi bi-exclamation-triangle"></i> No quiz questions added yet. Add questions to enable auto-grading.
                </div>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- Add Question Modal -->
  <div class="modal fade" id="addQuestionModal" tabindex="-1" aria-labelledby="addQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addQuestionModalLabel">Add Quiz Question</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addQuestionForm" method="POST" action="server/quiz_questions.php">
          <div class="modal-body">
            <div class="error-text" style="
                  background-color: rgba(243, 89, 89, 0.562);
                  border:solid 1px rgba(243, 89, 89, 0.822);
                  color:#fff;
                  padding:6px;
                  border-radius:8px;
                  display:none;">
            </div>
            
            <input type="hidden" name="form-type" value="add-question">
            <input type="hidden" name="assignment_uid" value="<?= $assignment_uid ?>">
            
            <div class="mb-3">
              <label for="question_text" class="form-label">Question Text *</label>
              <textarea class="form-control" name="question_text" id="question_text" rows="3" required></textarea>
            </div>
            
            <div class="mb-3">
              <label for="question_type" class="form-label">Question Type *</label>
              <select class="form-select" name="question_type" id="question_type" required onchange="toggleOptions()">
                <option value="multiple_choice">Multiple Choice</option>
                <option value="true_false">True/False</option>
                <option value="short_answer">Short Answer</option>
              </select>
            </div>
            
            <div id="multipleChoiceOptions">
              <div class="mb-3">
                <label for="option_a" class="form-label">Option A</label>
                <input type="text" class="form-control" name="option_a" id="option_a">
              </div>
              
              <div class="mb-3">
                <label for="option_b" class="form-label">Option B</label>
                <input type="text" class="form-control" name="option_b" id="option_b">
              </div>
              
              <div class="mb-3">
                <label for="option_c" class="form-label">Option C</label>
                <input type="text" class="form-control" name="option_c" id="option_c">
              </div>
              
              <div class="mb-3">
                <label for="option_d" class="form-label">Option D</label>
                <input type="text" class="form-control" name="option_d" id="option_d">
              </div>
            </div>
            
            <div class="mb-3">
              <label for="correct_answer" class="form-label">Correct Answer *</label>
              <input type="text" class="form-control" name="correct_answer" id="correct_answer" required 
                     placeholder="e.g., A for multiple choice, True for true/false, or the answer text">
              <small class="text-muted">For multiple choice, enter the letter (A, B, C, or D). For true/false, enter True or False.</small>
            </div>
            
            <div class="mb-3">
              <label for="points" class="form-label">Points *</label>
              <input type="number" class="form-control" name="points" id="points" min="1" value="1" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="submitQuestion">Add Question</button>
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
  <script src="js/quiz_questions.js"></script>

  <script>
    function toggleOptions() {
      const questionType = document.getElementById('question_type').value;
      const optionsDiv = document.getElementById('multipleChoiceOptions');
      
      if (questionType === 'multiple_choice') {
        optionsDiv.style.display = 'block';
      } else {
        optionsDiv.style.display = 'none';
      }
    }

    function deleteQuestion(questionId) {
      if (confirm('Are you sure you want to delete this question?')) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "server/quiz_questions.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        
        xhr.onload = function() {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
              let data = xhr.response.trim();
              if (data === "success") {
                window.location.reload();
              } else {
                alert(data);
              }
            }
          }
        };
        
        xhr.send("form-type=delete-question&question_id=" + questionId);
      }
    }
  </script>

</body>

</html>
