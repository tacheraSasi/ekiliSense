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
        <a class="nav-link  <?=$page=="students"?"":"collapsed"?>" href="students.php?class=<?=$class_id?>">
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
        <a class="nav-link  <?=$page=="attendance"?"":"collapsed"?>" href="attendance.php">
          <i class="bi bi-clock"></i>
          <span>Attendance</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?=$page=="plans"?"":"collapsed"?>" href="./plans.php">
          <i class="bi bi-pen"></i>
          <span>Teaching plans</span>
        </a>
      </li><!-- End Dashboard Nav -->
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
