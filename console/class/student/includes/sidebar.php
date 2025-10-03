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
        <a class="nav-link  <?=$page=="homework"?"":"collapsed"?>" href="homework.php">
          <i class="bi bi-journal-text"></i>
          <span>My Homework</span>
        </a>
      </li><!-- End Homework Nav -->

      <li class="nav-item">
        <a class="nav-link  <?=$page=="grades"?"":"collapsed"?>" href="grades.php">
          <i class="bi bi-clipboard-data"></i>
          <span>My Grades</span>
        </a>
      </li><!-- End Grades Nav -->

      <li class="nav-item">
        <a class="nav-link  <?=$page=="attendance"?"":"collapsed"?>" href="attendance.php">
          <i class="bi bi-clock"></i>
          <span>My Attendance</span>
        </a>
      </li><!-- End Attendance Nav -->

      <li class="nav-item">
        <a class="nav-link  <?=$page=="exams"?"":"collapsed"?>" href="exams.php">
          <i class="bi bi-clipboard-check"></i>
          <span>Exams</span>
        </a>
      </li><!-- End Exams Nav -->

      <li class="nav-item">
        <a class="nav-link  <?=$page=="timetable"?"":"collapsed"?>" href="timetable.php">
          <i class="bi bi-calendar3"></i>
          <span>Timetable</span>
        </a>
      </li><!-- End Timetable Nav -->

      <li class="nav-item">
        <a class="nav-link  <?=$page=="resources"?"":"collapsed"?>" href="resources.php">
          <i class="bi bi-book"></i>
          <span>Resources</span>
        </a>
      </li><!-- End Resources Nav -->

      <li class="nav-heading">Communication</li>

      <li class="nav-item">
        <a class="nav-link  <?=$page=="messages"?"":"collapsed"?>" href="messages.php">
          <i class="bi bi-chat-dots"></i>
          <span>Messages</span>
        </a>
      </li><!-- End Messages Nav -->

      <li class="nav-item">
        <a class="nav-link  <?=$page=="announcements"?"":"collapsed"?>" href="announcements.php">
          <i class="bi bi-megaphone"></i>
          <span>Announcements</span>
        </a>
      </li><!-- End Announcements Nav -->

      <li class="nav-heading">Account</li>

      <li class="nav-item">
        <a class="nav-link <?=$page=="profile"?"":"collapsed"?>" href="profile.php">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li><!-- End Profile Page Nav -->

    </ul>

  </aside><!-- End Sidebar-->

