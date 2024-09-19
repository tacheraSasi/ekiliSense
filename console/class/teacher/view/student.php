<?php
session_start();
include_once "../../../functions/timeAgo.php";
include_once "../../../../config.php";
if(!(isset($_SESSION['School_uid']) && isset($_SESSION['teacher_email']))){
  header("location:https://auth.ekilie.com/sense/teacher");
}
$school_uid = $_SESSION['School_uid'];  
$teacher_email = $_SESSION['teacher_email'];

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);
$school_name = $school["School_name"];

$isError = false;
if(isset($_GET["stid"])){
    #getting the students details
    $stid = $_GET["stid"];
    $get_student = mysqli_query($conn, "SELECT * FROM students WHERE school_uid = '$school_uid' AND student_id = '$stid'");
    if(mysqli_num_rows($get_student)>0){
        $student = mysqli_fetch_array($get_student);
        $student_name = $student["student_first_name"]." ".$student["student_last_name"];
    }else{
        $isError = true;
    }
}else{
    #TODO:some sort of error message
    $isError = true;
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title> <?=$isError?"Oops, not found":"$school_name | student | $student_name"?></title>

  <!-- Favicons -->
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="icon">
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../../../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../../../assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../../../assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../../../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../../../assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <link href="../../../assets/css/style.css" rel="stylesheet">
  <link href="../../../assets/css/custom.css" rel="stylesheet">

</head>

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

        <li class="nav-item d-block d-lg-none">
        
        <!-- <li>
        <a href="" class="btn btn-success mx-2">Upgrade</a>
        </li> -->
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
                <img src="../../assets/img/messages-1.jpg" alt="" class="rounded-circle">
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
                <img src="../../assets/img/messages-2.jpg" alt="" class="rounded-circle">
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
                <img src="../../assets/img/messages-3.jpg" alt="" class="rounded-circle">
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
            <img src="../../../../assets/img/user.png" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?=$school['School_name']?></span>
        </a><!-- End Profile Iamge Icon -->

        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
            <h6><?=$school['School_name']?></h6>
            </li>
            <li>
            <hr class="dropdown-divider">
            </li>

            
            <li>
            <hr class="dropdown-divider">
            </li>

            <li>
            <a class="dropdown-item d-flex align-items-center" href="../server/logout.php?logout_id=<?php echo $teacher_email?>">
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
            <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->
        <li class="nav-item">
            <a class="nav-link " href="../students.php?class=<?=$class_id?>">
            <i class="bi bi-people"></i>
            <span>Students</span>
            </a>
        </li><!-- End Dashboard Nav -->
        <li class="nav-item">
            <a class="nav-link  collapsed" href="../subjects.php">
            <i class="bi bi-book"></i>
            <span>Subjects</span>
            </a>
        </li><!-- End Dashboard Nav -->
        <li class="nav-item">
            <a class="nav-link  collapsed" href="../attendance.php">
            <i class="bi bi-clock"></i>
            <span>Attendance</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="../results.php">
            <i class="bi bi-emoji-expressionless"></i>
            <span>Results</span>
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
            <a class="nav-link collapsed" href="users-profile.html">
            <i class="bi bi-person"></i>
            <span>Profile</span>
            </a>
        </li><!-- End Profile Page Nav -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="pages-contact.html">
            <i class="bi bi-envelope"></i>
            <span>Help?</span>
            </a>
        </li><!-- End help Page Nav -->

        

        </ul>

    </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>
        <i class="bi bi-person"></i>
        student
      </h1>
      
    </div><!-- End Page Title -->

    <section class="section profile">
      <div class="row">
        <?php
            if(!$isError){
        ?>
        <div class="col-xl-4">

          <div class="card">
            <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">

              <img src="../../../../assets/img/user.png" alt="Profile" class="rounded-circle">
              <h2><?=$student['student_first_name']." ".$student['student_last_name']?></h2>
              <h3><?=$school['School_name']?></h3>
              
            </div>
          </div>

        </div>

        <div class="col-xl-8">

          <div class="card">
            <div class="card-body pt-3">
              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered">

                <li class="nav-item">
                  <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Overview</button>
                </li>

                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit</button>
                </li>

              </ul>
              <div class="tab-content pt-2">

                <div class="tab-pane fade show active profile-overview" id="profile-overview">
                  
                  <h5 class="card-title"><?=$student['student_first_name']."'s "?> informations</h5>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label ">Full Name</div>
                    <div class="col-lg-9 col-md-8"><?=$student['student_first_name']." ".$student['student_last_name']?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Parents email</div>
                    <div class="col-lg-9 col-md-8"><?=$student['parent_email'];?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Phone</div>
                    <div class="col-lg-9 col-md-8"><?=$student['parent_phone'];?></div>
                  </div>

                

                </div>

                <div class="tab-pane fade profile-edit pt-3" id="profile-edit">

                  <!-- Profile Edit Form -->
                  <form>
                    <div class="row mb-3">
                      <label for="fullName" class="col-md-4 col-lg-3 col-form-label">First Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input  
                        name="fname" 
                        type="text" 
                        class="form-control" 
                        id="fullName" 
                        placeholder="<?=$student['student_first_name']."'s"?> first Name" 
                        value="<?=$student['student_first_name'];?>" 
                        style="background-color:#444;outline:none;border:none;color:#fff">
                      </div>
                    </div>
                    <div class="row mb-3">
                      <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Last Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input  
                        name="lname" 
                        type="text" 
                        class="form-control" 
                        id="fullName" 
                        placeholder="<?=$student['student_first_name']."'s"?> last Name" 
                        value="<?=$student['student_last_name'];?>" 
                        style="background-color:#444;outline:none;border:none;color:#fff">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Phone" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                      <div class="col-md-8 col-lg-9">
                        <input  
                        name="phone" 
                        type="text" 
                        class="form-control" 
                        id="Phone"
                        placeholder="Enter Phone"  
                        value="<?=$student['parent_phone'];?>" 
                        style="background-color:#444;outline:none;border:none;color:#fff">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                      <div class="col-md-8 col-lg-9">
                        <input  
                        name="email" 
                        type="email" 
                        class="form-control" 
                        id="Email" 
                        placeholder="Enter Email"  
                        value="<?=$student['parent_email'];?>" 
                        style="background-color:#444;outline:none;border:none;color:#fff">
                      </div>
                    </div>

                    

                    <div class="text-center">
                      <button type="submit" class="btn btn-secondary">Save Changes</button>
                    </div>
                  </form><!-- End Profile Edit Form -->

                </div>
                <div class="tab-pane fade profile-edit pt-3" id="work-attendance">

                  <!-- Profile Edit Form -->
                  <form>
                    <div class="row mb-3">
                      <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input  name="fullName" type="text" class="form-control" id="fullName" placeholder="Enter Name" value="<?=$teacher['teacher_fullname'];?>" style="background-color:#444;outline:none;border:none;color:#fff">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Phone" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                      <div class="col-md-8 col-lg-9">
                        <input  name="phone" type="text" class="form-control" id="Phone"placeholder="Enter Phone"  value="<?=$teacher['teacher_active_phone'];?>" style="background-color:#444;outline:none;border:none;color:#fff">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                      <div class="col-md-8 col-lg-9">
                        <input  name="email" type="email" class="form-control" id="Email" placeholder="Enter Email"  value="<?=$teacher['teacher_email'];?>" style="background-color:#444;outline:none;border:none;color:#fff">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Address" class="col-md-4 col-lg-3 col-form-label">Address</label>
                      <div class="col-md-8 col-lg-9">
                        <input  name="address" type="text" class="form-control" id="Address"placeholder="Enter Address"  value="<?=$teacher['teacher_home_address'];?>" style="background-color:#444;outline:none;border:none;color:#fff">
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                  </form><!-- End Profile Edit Form -->

                </div>


              </div><!-- End Bordered Tabs -->

            </div>
          </div>

        </div>
        <?php
            }else{
        ?>
        <div class="col-lg-12">
            <div class="alert alert-dark bg-dark border-0 text-light alert-dismissible fade show" role="alert">
            <span class="alert-heading"> ⚠️Something Went wrong </span>
            <p>Oops looks like something went Wrong<br>This page is not available ❌😒</p>
            </div>
        </div>
        <?php
            }
        ?>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>ekiliSense<span></strong>. All Rights Reserved
    </div>
    <div class="credits">
    From <a href="https://ekilie.com">ekilie</a>
    </div>
  </footer><!-- End Footer -->
  
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  
  <!-- Vendor JS Files -->
  <script src="../../../assets/js/modal-form.js"></script>
  
  <script src="../../../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../../../assets/vendor/echarts/echarts.min.js"></script>
  <script src="../../../assets/vendor/quill/quill.min.js"></script>
  <script src="../../../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../../../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../../../assets/vendor/php-email-form/validate.js"></script>

  <script src="../../../assets/js/main.js"></script>

</body>

</html>