<?php
session_start();
include_once "../../../config.php";
include_once "../../../middlwares/teacher_auth.php";

// Get messages for this teacher
$messages_query = mysqli_query($conn, "
    SELECT m.*, 
           s.student_first_name, s.student_last_name,
           CASE 
               WHEN m.sender_type = 'parent' THEN CONCAT(s.student_first_name, ' ', s.student_last_name, ' (Parent)')
               WHEN m.sender_type = 'admin' THEN 'School Admin'
               ELSE 'System'
           END as sender_name
    FROM messages m
    LEFT JOIN students s ON m.student_id = s.student_id
    WHERE (m.recipient_type = 'teacher' AND m.recipient_id = '$teacher_id') 
       OR (m.sender_type = 'teacher' AND m.sender_id = '$teacher_id')
    AND m.school_uid = '$school_uid'
    ORDER BY m.sent_at DESC
");

// Mark messages as read when viewing
mysqli_query($conn, "UPDATE messages SET is_read = 1, read_at = NOW() 
                    WHERE recipient_type = 'teacher' 
                    AND recipient_id = '$teacher_id' 
                    AND school_uid = '$school_uid'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ekiliSense | <?= $school["School_name"] ?> | Messages</title>

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

  <style>
    .message-card {
      border-left: 4px solid #007bff;
      margin-bottom: 1rem;
    }
    .message-card.unread {
      background-color: rgba(0, 123, 255, 0.05);
      border-left-color: #28a745;
    }
    .message-card.sent {
      border-left-color: #6c757d;
      background-color: rgba(108, 117, 125, 0.05);
    }
    .message-card.urgent {
      border-left-color: #dc3545;
      background-color: rgba(220, 53, 69, 0.05);
    }
    .message-meta {
      font-size: 0.875rem;
      color: #6c757d;
    }
    .message-content {
      margin-top: 0.5rem;
    }
  </style>

</head>

<body>

  <?php include_once "./includes/topbar.php"; ?>
  <?php
  $page = "messages";
  include_once "./includes/sidebar.php";
  ?>

  <main id="main" class="main">
    <div class="d-flex justify-content-between flex-wrap" style="margin:1rem auto;">
      <div class="pagetitle" style="display:inline-block">
        <h1 style="display:inline-block">Messages <i class="bi bi-arrow-right-short"> </i> <?= $class_info["Class_name"] ?></h1>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeMessageModal">
        <i class="bi bi-plus-circle"></i> Compose Message
      </button>
    </div>

    <section class="section">
      <div class="row">
        <!-- Message Statistics -->
        <div class="col-lg-12">
          <div class="row">
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Total Messages</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= mysqli_num_rows($messages_query) ?></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-4 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Unread Messages</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-envelope"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $unread_count = 0;
                        mysqli_data_seek($messages_query, 0);
                        while($msg = mysqli_fetch_array($messages_query)) {
                          if(!$msg['is_read'] && $msg['recipient_type'] == 'teacher' && $msg['recipient_id'] == $teacher_id) {
                            $unread_count++;
                          }
                        }
                        echo $unread_count;
                        ?>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-4 col-md-6">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Urgent Messages</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $urgent_count = 0;
                        mysqli_data_seek($messages_query, 0);
                        while($msg = mysqli_fetch_array($messages_query)) {
                          if($msg['is_urgent']) {
                            $urgent_count++;
                          }
                        }
                        echo $urgent_count;
                        ?>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Messages List -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Recent Messages</h5>

              <div class="messages-container">
                <?php
                mysqli_data_seek($messages_query, 0);
                while ($message = mysqli_fetch_array($messages_query)) {
                  $is_sent = ($message['sender_type'] == 'teacher' && $message['sender_id'] == $teacher_id);
                  $is_unread = (!$message['is_read'] && !$is_sent);
                  $is_urgent = $message['is_urgent'];
                  
                  $card_classes = 'message-card card';
                  if($is_unread) $card_classes .= ' unread';
                  if($is_sent) $card_classes .= ' sent';
                  if($is_urgent) $card_classes .= ' urgent';
                ?>
                  <div class="<?= $card_classes ?>">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                          <div class="d-flex align-items-center mb-2">
                            <h6 class="mb-0">
                              <i class="bi bi-<?= $is_sent ? 'arrow-up-right' : 'arrow-down-left' ?>"></i>
                              <?= htmlspecialchars($message['subject']) ?>
                              <?php if($is_urgent): ?>
                                <span class="badge bg-danger ms-2">Urgent</span>
                              <?php endif; ?>
                              <?php if($is_unread): ?>
                                <span class="badge bg-success ms-2">New</span>
                              <?php endif; ?>
                            </h6>
                          </div>
                          <div class="message-meta mb-2">
                            <span class="me-3">
                              <i class="bi bi-person"></i> 
                              <?= $is_sent ? 'To: ' . htmlspecialchars($message['sender_name']) : 'From: ' . htmlspecialchars($message['sender_name']) ?>
                            </span>
                            <span class="me-3">
                              <i class="bi bi-clock"></i> 
                              <?= date('M d, Y h:i A', strtotime($message['sent_at'])) ?>
                            </span>
                            <span class="badge badge-outline-primary">
                              <?= ucfirst($message['message_type']) ?>
                            </span>
                          </div>
                          <div class="message-content">
                            <p class="mb-0"><?= nl2br(htmlspecialchars(substr($message['message_body'], 0, 200))) ?>
                              <?= strlen($message['message_body']) > 200 ? '...' : '' ?>
                            </p>
                          </div>
                        </div>
                        <div class="message-actions ms-3">
                          <button class="btn btn-sm btn-outline-primary" 
                                  onclick="viewMessage('<?= $message['message_uid'] ?>')">
                            <i class="bi bi-eye"></i> View
                          </button>
                          <?php if(!$is_sent): ?>
                            <button class="btn btn-sm btn-outline-secondary" 
                                    onclick="replyToMessage('<?= $message['message_uid'] ?>')">
                              <i class="bi bi-reply"></i> Reply
                            </button>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              </div>

              <?php if(mysqli_num_rows($messages_query) == 0): ?>
                <div class="text-center py-5">
                  <i class="bi bi-chat-dots" style="font-size: 3rem; color: #ccc;"></i>
                  <p class="mt-3 text-muted">No messages yet. Start a conversation with parents!</p>
                </div>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- Compose Message Modal -->
  <div class="modal fade" id="composeMessageModal" tabindex="-1" aria-labelledby="composeMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="composeMessageModalLabel">Compose Message</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="composeMessageForm" method="POST" action="server/messages.php">
          <div class="modal-body">
            <div class="error-text" style="
                  background-color: rgba(243, 89, 89, 0.562);
                  border:solid 1px rgba(243, 89, 89, 0.822);
                  color:#fff;
                  padding:6px;
                  border-radius:8px;
                  display:none;">
            </div>
            
            <input type="hidden" name="form-type" value="send-message">
            <input type="hidden" name="sender_type" value="teacher">
            <input type="hidden" name="sender_id" value="<?= $teacher_id ?>">
            
            <div class="row">
              <div class="col-md-12 mb-3">
                <label for="recipient_type" class="form-label">Send To *</label>
                <select class="form-select" name="recipient_type" id="recipient_type" required onchange="updateRecipientOptions()">
                  <option value="">Select recipient type</option>
                  <option value="parent">Parent</option>
                  <option value="admin">School Admin</option>
                </select>
              </div>
              
              <div class="col-md-12 mb-3" id="student_select_container" style="display:none;">
                <label for="student_id" class="form-label">Student *</label>
                <select class="form-select" name="student_id" id="student_id">
                  <option value="">Select student</option>
                  <?php
                  mysqli_data_seek($students, 0);
                  while ($student = mysqli_fetch_array($students)) {
                    echo "<option value='{$student['student_id']}'>{$student['student_first_name']} {$student['student_last_name']}</option>";
                  }
                  ?>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="message_type" class="form-label">Message Type</label>
                <select class="form-select" name="message_type">
                  <option value="general">General</option>
                  <option value="academic">Academic</option>
                  <option value="attendance">Attendance</option>
                  <option value="behavioral">Behavioral</option>
                  <option value="announcement">Announcement</option>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <div class="form-check mt-4">
                  <input class="form-check-input" type="checkbox" name="is_urgent" id="is_urgent">
                  <label class="form-check-label" for="is_urgent">
                    Mark as Urgent
                  </label>
                </div>
              </div>
              
              <div class="col-md-12 mb-3">
                <label for="subject" class="form-label">Subject *</label>
                <input type="text" class="form-control" name="subject" required placeholder="Message subject">
              </div>
              
              <div class="col-md-12 mb-3">
                <label for="message_body" class="form-label">Message *</label>
                <textarea class="form-control" name="message_body" rows="6" required placeholder="Type your message here..."></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="sendMessage">Send Message</button>
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
  <script src="js/messages.js"></script>

  <script>
    function updateRecipientOptions() {
      const recipientType = document.getElementById('recipient_type').value;
      const studentContainer = document.getElementById('student_select_container');
      
      if (recipientType === 'parent') {
        studentContainer.style.display = 'block';
        document.getElementById('student_id').required = true;
      } else {
        studentContainer.style.display = 'none';
        document.getElementById('student_id').required = false;
      }
    }
    
    function viewMessage(messageUid) {
      // Implementation for viewing full message
      window.location.href = `message_view.php?id=${messageUid}`;
    }
    
    function replyToMessage(messageUid) {
      // Implementation for replying to message
      window.location.href = `message_reply.php?id=${messageUid}`;
    }
  </script>

</body>

</html>