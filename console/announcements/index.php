<?php
session_start();
include_once "../../config.php";
include_once "../functions/timeAgo.php";
if(!(isset($_SESSION['School_uid']))){
  header("location:../../auth");
}
$school_uid = $_SESSION['School_uid'];

#if the user is a teacher 
if(isset($_SESSION['teacher_email'])){
  $t_email = $_SESSION['teacher_email'];
  $suid = $_SESSION['School_uid'];
  #getting the teacher's id using the teacher's name
  $get_teacher_id = mysqli_fetch_assoc(mysqli_query($conn, 
  "SELECT * FROM teachers WHERE teacher_email = '$t_email' AND School_unique_id = '$suid'"));
  $t_id = $get_teacher_id['teacher_id'];

  $check_is_class_teacher = mysqli_query($conn,
  "SELECT * FROM class_teacher WHERE school_unique_id = '$suid' AND teacher_id = '$t_id'");
  
  if(mysqli_num_rows($check_is_class_teacher) > 0){
    header("location:../class/teacher/");
  }else{
    header("location:../teacher/");
  }
}

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);
$school_email = $school['School_email'];

#getting the list of classes
$get_classes = mysqli_query($conn, "SELECT * FROM classes WHERE school_unique_id = '$school_uid'");

#getting the class teachers name
function getClassTeacher($conn,$class_id){
  $q = mysqli_fetch_array(mysqli_query($conn,
  "SELECT * FROM class_teacher WHERE Class_id = '$class_id'"));
  $teacher_id = null;
  if(isset($q['teacher_id'])){
    $teacher_id = $q['teacher_id'];
    $get_name = mysqli_fetch_array(mysqli_query($conn,"
    SELECT * FROM `teacherS` WHERE `teacher_id` = '$teacher_id'"));
    return $get_name['teacher_fullname'];
  }else{
    return "Not assigned yet";
  }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ekiliSense | <?=$school['School_name']?> | classes</title>
  <meta content="" name="description">
  <meta content="" name="keywords">
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

  <style>
    form .input{
      background-color:transparent;
      border:1.5px solid var(--border-color);
      color:var(--text-white)
    }
    form .input:active,form .input:focus{
      background-color: transparent;
    }
    .form-control{
      background-color:transparent;
      border:2px solid var(--border-color);
      color:var(--text-white)
    }
    .form-control:active,.form-control:focus{
      background-color: transparent;
    }
  </style>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="#" class="logo d-flex align-items-center">
        <span class="d-none d-lg-block">ekiliSense </span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->


    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="upgrade-btn-section">
          
        </li>
        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">4</span>
          </a><!-- End Notification Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">
              You have 4 new notifications
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>Lorem Ipsum</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>30 min. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-x-circle text-danger"></i>
              <div>
                <h4>Atque rerum nesciunt</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>1 hr. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-check-circle text-success"></i>
              <div>
                <h4>Sit rerum fuga</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>2 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-info-circle text-primary"></i>
              <div>
                <h4>Dicta reprehenderit</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>4 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>
            <li class="dropdown-footer">
              <a href="#">Show all notifications</a>
            </li>

          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-chat-left-text"></i>
            <span class="badge bg-success badge-number">3</span>
          </a><!-- End Messages Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
            <li class="dropdown-header">
              You have 3 new messages
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-1.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Maria Hudson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>4 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-2.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Anna Nelson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>6 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-3.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>David Muldon</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>8 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="dropdown-footer">
              <a href="#">Show all messages</a>
            </li>

          </ul><!-- End Messages Dropdown Items -->

        </li><!-- End Messages Nav -->
        
        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="../assets/img/school-1.png" alt="Profile" class="">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?=$school['School_name']?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?=$school['School_name']?></h6>
              <span>ekiliSense</span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-person"></i>
                <span>Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-gear"></i>
                <span>Account Settings</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
                <i class="bi bi-question-circle"></i>
                <span>Need Help?</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="#">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link collapsed" href="../">
          <i class="bi bi-grid"></i>
          <span>Home</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../teachers">
          <i class="bi bi-people"></i>
          <span>Teachers</span>
        </a>
      </li>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../classes/">
          <i class="bi bi-buildings"></i>
          <span>Classes</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link " href="./announcements">
          <i class="bi bi-bell"></i>
          <span>Announcements</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="https://convo.ekilie.com">
        <i class="bi bi-camera-video"></i>
          <span>Convo</span>
        </a>
      </li>

      

      <li class="nav-heading">Pages</li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="../users-profile.html">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li><!-- End Profile Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="/console/pages-contact.html">
          <i class="bi bi-envelope"></i>
          <span>Help?</span>
        </a>
      </li><!-- End help Page Nav -->

      

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1><i class="bi bi-bell"> </i> Send notifications</h1>
      
    </div><!-- End Page Title -->
    
    <section class="section">
        <div class="row">
          <div class="col-lg-12">
  
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Notification</h5>

              <!-- send form -->
              <form class="row g-3" id="send-form" method="post">
                <div class="col-12">
                  <label for="inputName4" class="form-label">Title</label>
                  <input type="text" name="title" required class="form-control" id="inputName4" style="color:var(--text-white)">
                </div>
                <div class="col-12">
                  <label for="inputEmail4" class="form-label">Message</label>
                  <textarea name="message" required id="inputMessage" class="form-control" style="color:var(--text-white)"></textarea>
                </div>
                <fieldset class="row mb-3 mt-2">
                  <legend class="col-form-label col-sm-2 pt-0">Notification Type</legend>
                  <div class="col-sm-10">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="notificationType" id="emailOption" value="email" checked>
                      <label class="form-check-label" for="emailOption">
                        Email
                      </label>
                    </div>
                    <div class="form-check disabled">
                      <input class="form-check-input" disabled type="radio" name="notificationType" id="smsOption" value="sms">
                      <label class="form-check-label" disabled for="smsOption">
                        SMS (Premium)
                      </label>
                    </div>
                    <div class="form-check disabled">
                      <input class="form-check-input" disabled type="radio" name="notificationType" id="bothOption" value="both">
                      <label class="form-check-label" disabled for="bothOption">
                        BOTH (Premium)
                      </label>
                    </div>
                  </div>
                </fieldset>
                <fieldset class="row mb-3">
                  <legend class="col-form-label col-sm-2 pt-0">Send To</legend>
                  <div class="col-sm-10">
                    <div class="form-check">
                      <input class="form-check-input" name="recipients[]" type="checkbox" id="teacherOption" value="teachers">
                      <label class="form-check-label" for="teacherOption">
                        Teacher
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" name="recipients[]" type="checkbox" id="parentOption" value="parents">
                      <label class="form-check-label" for="parentOption">
                        Parent
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" name="recipients[]" type="checkbox" id="classTeacherOption" value="classTeachers">
                      <label class="form-check-label" for="classTeacherOption">
                        Class Teacher
                      </label>
                    </div>
                  </div>
                </fieldset>
                <div class="form-check mb-3">
                      <input class="form-check-input" 
                      type="radio" 
                      name="use-school-email" 
                      id="useSchoolEmail" 
                      value="<?=$school_email?>" 
                      disabled >
                      <label class="form-check-label" for="useSchoolEmail">
                        Use the school email to send (PREMIUM)
                      </label>
                </div>
                <div class="text-center">
                  <button type="submit" name="send" class="btn btn-success" id="submit-btn" style="width: 100%;">Send Notification</button>
                  <button class="btn btn-success " style="width: 100%;display:none" id="spinner-btn" type="button" disabled>
                    <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                    Sending...
                  </button>
                </div>
                <p style="opacity: 0.45;"><i>Powered by <span><a href="https://relay.ekilie.com" target="_blank">ekiliRelay</a></span></i></p>
              </form>
              <!-- send form -->

              <!-- toasts -->
              <div class="alert alert-primary alert-dismissible fade  toast-alert" id="toast-alert-success"  role="alert">
                <i class="bi bi-check-circle me-1"></i>
                <span>A simple primary alert with icon—check it out!</span>
                <!-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> -->
              </div>

              <div class="alert alert-danger alert-dismissible fade  toast-alert" id="toast-alert-error"  role="alert">
              <i class="bi bi-exclamation-octagon me-1"></i>
                <span>A simple secondary alert with icon—check it out!</span>
                <!-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> -->
              </div>
               <!-- end of toasts -->

            </div>
            <!-- upgrade btn -->
            <div class="modal fade " id="upgrade" tabindex="-1">
              <div class="modal-dialog modal-xl ">
                <div class="modal-content card">
                  <div class="add">
                    <div class="add-card">
                      <div class="left">
                        <div class="top-container">
                          <div style="
                          display: flex;
                          justify-content: flex-start;">
                            <div class="logo-container">
                              <img src="../../assets/img/favicon.jpeg" alt="" class="logo">
                              <div class="logo-text">ekilie.</div>
                            </div>
                          </div>
                          <div class="middle-content">
                            <h1>ekiliSense</h1>
                            <h2 id="typingText" style="display: inline;"></h2>
                            <span class="cursor"></span>
                          </div>
                        </div>
                        <div class="bottom-container">
                          "Unlock premium features with ekiliSense. 
                          Gain access to advanced tools and insights to 
                          elevate your educational experience."
                        </div>
                        
                      </div>
                      <div class="right">
                        <h1>Upgrade to ekiliSense premium</h1>
                        <p class="sub-heading">
                          Bringing Artificial intelligence closer to education
                        </p>
                        <form class="modal-form" id="class"  action="#" method="POST" enctype="multipart/form-data" autocomplete="off" >
                        <div class="error-text" style="
                              background-color: rgba(243, 89, 89, 0.562);
                              border:solid 1px rgba(243, 89, 89, 0.822);
                              color:#fff;
                              padding:6px;
                              border-radius:8px;">
                            </div>
                          <!-- TODO: add emojis to the plcaholder -->
                          <input type="hidden" name="form-type" value="class" >
                          <div class=" field input">
                            <input style="width: 100%;"  type="text" name="class-name"  placeholder="Write the class name here " required>
                          </div>
                          
                        
                          <div class="input-container field button">
                              <button  id="submit" title="create class" type="submit">CREATE</button>
                          </div>
                          
                        </form>
                        
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    
  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>ekiliSense<span></strong>. All Rights Reserved
    </div>
    <div class="credits">
    From <a href="https://tachera.com/Insights/">ekilie</a>
    </div>
  </footer><!-- End Footer -->
  
  <a href="#" class="back-to-top d-flex s-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  
  <!-- Vendor JS Files -->
  <script>
    let assetsAt= "../assets"
  </script>
  <script src="../assets/js/modal-form.js"></script>
  
  <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../assets/vendor/echarts/echarts.min.js"></script>
  <script src="../assets/vendor/quill/quill.min.js"></script>
  <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../assets/vendor/php-email-form/validate.js"></script>

  <script src="../assets/js/main.js"></script>
  <script src="../assets/js/send.js"></script>

</body>

</html>