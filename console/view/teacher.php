<?php
session_start();
include_once "../functions/timeAgo.php";
include_once "../../config.php";
if(!isset($_SESSION['School_uid'])){
  header("location:https://auth.ekilie.com/sense/teacher");
}
$school_uid = $_SESSION['School_uid'];

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);
$school_name = $school["School_name"];

$isError = false;
$isVerified = false;#teacher verification
if(isset($_GET["tid"])){
    #getting the teachers details
    $tid = $_GET["tid"];
    $get_teacher = mysqli_query($conn, "SELECT * FROM teachers WHERE School_unique_id = '$school_uid' AND teacher_id = '$tid'");

    $subjects = mysqli_query($conn,"SELECT * FROM subjects WHERE teacher_id = '$tid'");

    if(mysqli_num_rows($get_teacher)>0){
        $teacher = mysqli_fetch_array($get_teacher);
        $teacher_name = $teacher["teacher_fullname"];
        $isVerified = $teacher['verified']==1?true:false;
    }else{
        $isError = true;
    }
}else{
    #TODO:some sort of error message
    $isError = true;
    exit;
}



#getting subjects info

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title> <?=$isError?"Oops, not found":"$school_name | teacher | $teacher_name"?></title>

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

        <!-- <li class="upgrade-btn-section">
          
        </li> -->

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
        <a class="nav-link " href="../teachers/">
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
        <a class="nav-link collapsed" href="../announcements">
          <i class="bi bi-bell"></i>
          <span>Announcements</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" target="_blank" href="https://convo.ekilie.com">
        <i class="bi bi-camera-video"></i>
          <span>Convo</span>
        </a>
      </li>

      

      <li class="nav-heading">Pages</li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="../profile.php">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li><!-- End Profile Page Nav -->


    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>
        <i class="bi bi-person"></i>
        Teacher
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

              <img src="../../assets/img/user.png" alt="Profile" class="rounded-circle">
              <h2><?=$teacher_name?></h2>
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

                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#delete">Delete <i class="bi bi-trash"></i></button>
                </li>

              </ul>
              <div class="tab-content pt-2">

                <div class="tab-pane fade show active profile-overview" id="profile-overview">
                 
                  <h5 class="card-title"><?=$teacher_name."'s "?> informations</h5>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label ">Full Name</div>
                    <div class="col-lg-9 col-md-8"><?=$teacher_name?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Email</div>
                    <div class="col-lg-9 col-md-8"><?=$teacher['teacher_email'];?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Phone</div>
                    <div class="col-lg-9 col-md-8"><?=$teacher['teacher_active_phone'];?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Address</div>
                    <div class="col-lg-9 col-md-8"><?=$teacher['teacher_home_address'];?></div>
                  </div>

                </div>

                <div class="tab-pane fade profile-edit pt-3" id="profile-edit">

                  <!-- Profile Edit Form -->

                  <?php
                  if(isset($_POST['save-changes'])){
                    editTeacher($conn,$school_uid,$teacher['teacher_id']);
                    echo"
                    <script>window.location.reload</script>
                    ";
                  }
                   
                  if(!$isVerified){
                    #checking if the teacher is not verified
                  ?>
                  <form method="post" action="../server/manage-teacher.php">
                    <!-- hidden inputs -->
                     <input type="hidden" name="teacher" value="<?=$teacher['teacher_id']?>">
                    <div class="row mb-3">
                      <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input  name="fullname" type="text" class="form-control" id="fullName" placeholder="Enter Name" value="<?=$teacher['teacher_fullname'];?>" style="background-color:#444;outline:none;border:none;color:#fff">
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
                      <button type="submit" name="save-changes" class="btn btn-secondary">Save Changes</button>
                    </div>
                  </form><!-- End Profile Edit Form -->
                  <?php 
                  }else{#teacher is verified
                  ?>
                   <div class="col-lg-12">
                       <div class="alert alert-dark bg-dark border-0 text-light alert-dismissible fade show" role="alert">
                       <span class="alert-heading"> ⚠️This user has verified his/her account </span>
                       <p>Therefore all changes will be made in his/her profile account</p>
                       <b><i>Contact <?=$teacher_name?> if changes are needed</i></b>
                       </div>
                   </div>
                   <?php
                  }
                   ?>

                </div>

                <div class="tab-pane fade show delete" id="delete">
                    <div class="col-lg-12">
                       <div class="alert alert-dark bg-dark border-0 text-light alert-dismissible fade show" role="alert">
                       <span class="alert-heading"> ⚠️Danger zone </span>
                       <te>You are about to delete this users account</p>
                       <b><i>Changes made here are irreversible</i></b>
                       </div>
                   </div>
                        <div class="row">
                        <div class="col-lg-3 col-md-4 label ">DELETE THIS TEACHER</div>
                        <div class="col-lg-9 col-md-8">
                        <button class="btn btn-danger"  data-bs-toggle="modal" data-bs-target="#verticalycentered">
                            Delete <i class="bi bi-trash"></i>
                        </button>
                            <div class="modal fade" id="verticalycentered" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content card">
                                <div class="modal-header">
                                    <h5 class="modal-title">⚠️Alert</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h4>Due to security policies you can not delete <?=$teacher_name?>'s account. To change this go to settings</h4>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-success text-light"> ⚙️ <a href="../profile.php">Settings</a></button>
                                    <!-- <button type="button" class="btn btn-danger"><i class="bi bi-trash"> </i> Delete</button> -->
                                </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>        
                </div>



              </div><!-- End Bordered Tabs -->

            </div>
          </div>

        </div>
        <div class="col-lg-12">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Table of all Subjects taught by <?=$teacher_name?></h5>
                
                <!-- Table with stripped rows -->
                <table class="table datatable table-dark">
                  <thead>
                    <tr>
                      <th>S/N</th>
                      <th>Subject name</th>
                      <th>Class</th>
                      <th>Number of students </th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    while ($row_std = mysqli_fetch_array($subjects)) {
                        $class_id = $row_std['class_id'];
                        $q = "select * from students where class_id = '$class_id'";
                        $r = mysqli_query($conn, $q);
                        $num_std = mysqli_num_rows($r);
                        $class_info = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM classes WHERE Class_id = '$class_id' AND school_unique_id = '$school_uid' "));
                        $class_name = $class_info['Class_name']

                      ?>
                      
                        <tr>
                          <td><?=$row_std['subject_id']?></td>
                          <td><?=$row_std['subject_name']?></td>
                          <td><?=$class_name?></td>
                          <td><?=$num_std?></td>
                          
                        </tr>
                      
                    <?php
                    }
                    ?>
                  
                  </tbody>
                </table>
                <!-- End Table with stripped rows -->
  
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

</body>

</html>