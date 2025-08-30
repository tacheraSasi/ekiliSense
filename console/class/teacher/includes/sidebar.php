  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link  <?=$page=="index"?"":"collapsed"?>" href="./">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
        <a class="nav-link  <?=$page=="students"?"":"collapsed"?>" href="students.php">
          <i class="bi bi-people"></i>
          <span>Students</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
        <a class="nav-link  <?=$page=="subjects"?"":"collapsed"?>" href="subjects.php">
          <i class="bi bi-book"></i>
          <span>Subjects</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#attendance-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-clock"></i><span>Attendance</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="attendance-nav" class="nav-content collapse <?= in_array($page, ['attendance', 'attendance_reports']) ? 'show' : '' ?>" data-bs-parent="#sidebar-nav">
          <li>
            <a href="attendance.php" class="<?= $page=="attendance" ? "active" : "" ?>">
              <i class="bi bi-circle"></i><span>Mark Attendance</span>
            </a>
          </li>
          <li>
            <a href="attendance_reports.php" class="<?= $page=="attendance_reports" ? "active" : "" ?>">
              <i class="bi bi-circle"></i><span>Attendance Reports</span>
            </a>
          </li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link  <?=$page=="homework"?"":"collapsed"?>" href="homework.php">
          <i class="bi bi-journal-text"></i>
          <span>Homework</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link  <?=$page=="exams"?"":"collapsed"?>" href="exams.php">
          <i class="bi bi-clipboard-check"></i>
          <span>Exams</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?=$page=="plans"?"":"collapsed"?>" href="./plans.php">
          <i class="bi bi-pen"></i>
          <span>Teaching plans</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
        <a class="nav-link  <?=$page=="messages"?"":"collapsed"?>" href="messages.php">
          <i class="bi bi-chat-dots"></i>
          <span>Messages</span>
          <?php
          // Count unread messages
          $unread_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM messages 
                                             WHERE recipient_type = 'teacher' 
                                             AND recipient_id = '$teacher_id' 
                                             AND school_uid = '$school_uid' 
                                             AND is_read = 0");
          $unread_count = mysqli_fetch_array($unread_query)['count'];
          if($unread_count > 0):
          ?>
            <span class="badge bg-danger badge-number"><?= $unread_count ?></span>
          <?php endif; ?>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link  <?=$page=="events"?"":"collapsed"?>" href="events.php">
          <i class="bi bi-calendar-event"></i>
          <span>Events</span>
        </a>
      </li>
      <!-- <li class="nav-item">
        <a class="nav-link collapsed" href="send.php">
          <i class="bi bi-send"></i>
          <span>Send</span>
        </a>
      </li> -->
      <li class="nav-item">
        <a class="nav-link <?=$page=="results"?"":"collapsed"?>" href="results.php">
          <i class="bi bi-emoji-expressionless"></i>
          <span>Results</span>
        </a> 
      </li>
      <!-- <li class="nav-item">
        <a class="nav-link collapsed" href="./announcements">
          <i class="bi bi-house"></i>
          <span>Library</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="https://convo.ekilie.com">
        <i class="bi bi-camera-video"></i>
          <span>Convo</span>
        </a>
      </li> -->


      <li class="nav-heading">Pages</li>

      <li class="nav-item">
        <a class="nav-link <?=$page=="profile"?"":"collapsed"?>" href="profile.php">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li><!-- End Profile Page Nav -->

    

      

    </ul>

  </aside><!-- End Sidebar-->

