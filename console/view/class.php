<?php
session_start();
include_once "../functions/timeAgo.php";
include_once "../../config.php";
if(!isset($_SESSION['School_uid'])){
  header("location:https://auth.ekilie.com/sense/teacher");
}
$school_uid = $_SESSION['School_uid'];
// echo "<pre>";
// var_dump($_SERVER['REQUEST_URI']);
// echo "</pre>";
// die;

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);
$school_name = $school["School_name"];

$isError = false;
if(isset($_GET["class_id"])){
    #getting the teachers details
    $class_id = $_GET["class_id"];
    $get_class = mysqli_query($conn, "SELECT * FROM classes WHERE School_unique_id = '$school_uid' AND class_id = '$class_id'");

    $subjects = mysqli_query($conn,"SELECT * FROM subjects WHERE class_id = '$class_id' AND School_unique_id = '$school_uid'");

    #class teacher's info
    $get_class_teacher_id = mysqli_query($conn,"SELECT * FROM class_teacher WHERE class_id = '$class_id'");
    $class_teacher_id = mysqli_fetch_array($get_class_teacher_id)["teacher_id"];
    $get_class_teacher = mysqli_query($conn,"SELECT * FROM teachers WHERE teacher_id = '$class_teacher_id' AND School_unique_id = '$school_uid'");

    #class teacher data
    $class_teacher_data = mysqli_fetch_array($get_class_teacher);
    $class_teacher_name =isset($class_teacher_data["teacher_fullname"])?$class_teacher_data["teacher_fullname"]:"None Yet.";
    $class_teacher_id =isset($class_teacher_data["teacher_id"])?$class_teacher_data["teacher_id"]:$_GET['class_id'];
    // echo "<pre>";
    // var_dump($class_teacher_data);
    // echo "</pre>";

    #number of students
    $q = "select * from students where class_id = '$class_id' and school_uid = '$school_uid'";
    $r = mysqli_query($conn, $q);
    $num_std = mysqli_num_rows($r);

    if(mysqli_num_rows($get_class)>0){
        $class = mysqli_fetch_array($get_class);
        $class_name = $class["Class_name"];
    }else{
        $isError = true;
    }
}else{
    #TODO:some sort of error message
    $isError = true;
    exit;
}


#getting students info
$students = mysqli_query($conn,"SELECT * FROM students WHERE class_id = '$class_id' AND school_uid = '$school_uid'  ORDER BY `students`.`student_first_name` ASC")

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title> <?=$isError?"Oops, not found":"$school_name | class | $class_name"?></title>

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
        <a class="nav-link collapsed" href="../teachers/">
          <i class="bi bi-people"></i>
          <span>Teachers</span>
        </a>
      </li>
      </li>
      <li class="nav-item">
        <a class="nav-link " href="../classes/">
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
        <i class="bi bi-buildings"></i>
        Class
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

              <h2><?=$class_name?></h2>
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
                 
                  <h5 class="card-title"><?=$class_name." class "?> informations</h5>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label ">Full Name</div>
                    <div class="col-lg-9 col-md-8"><?=$class_name?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Class Teacher</div>
                    <div class="col-lg-9 col-md-8"><?=$class_teacher_name;?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Number of students</div>
                    <div class="col-lg-9 col-md-8"><?=$num_std?></div>
                  </div>

                </div>

                <div class="tab-pane fade profile-edit pt-3" id="profile-edit">

                  <!-- Profile Edit Form -->

              
                  <form method="post" action="../server/manage-class.php">
                    <!-- hidden inputs -->
                     <input type="hidden" name="class" value="<?=$class_id?>">
                    <div class="row mb-3">
                      <label for="className" class="col-md-4 col-lg-3 col-form-label">Class Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input  name="className" type="text" class="form-control" id="className" placeholder="Enter Name" value="<?=$class_name?>" style="background-color:#444;outline:none;border:none;color:#fff">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Phone" class="col-md-4 col-lg-3 col-form-label">Class teacher</label>
                      <div class="col-md-8 col-lg-9">
                        <label for="choose-class-teacher">Choose a teacher</label>
                        <select name="choosen-class-teacher" id="choosen-class-teacher" class="btn btn-secondary" style="text-align: left;" >
                            <option value="<?=$class_teacher_id?>"><?=$class_teacher_name?></option>
                            <?php
                            $get_teachers = mysqli_query($conn, "SELECT * FROM teachers WHERE School_unique_id = '$school_uid'");

                            while($row_teacher = mysqli_fetch_array($get_teachers)){
                                $teacher_name = $row_teacher['teacher_fullname'];
                                $teacher_id = $row_teacher['teacher_id'];
                                echo "<option value='$teacher_id'>$teacher_name</option>";
                            }
                            ?>
                        </select>
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" name="save-changes" class="btn btn-primary">Save Changes</button>
                    </div>
                  </form><!-- End Profile Edit Form -->
                  

                </div>

                <div class="tab-pane fade show delete" id="delete">
                    <div class="col-lg-12">
                       <div class="alert alert-dark bg-dark border-0 text-light alert-dismissible fade show" role="alert">
                       <span class="alert-heading"> ⚠️Danger zone </span>
                       <te>You are about to delete this class</p>
                       <b><i>Changes made here are irreversible</i></b>
                       </div>
                   </div>
                        <div class="row">
                        <div class="col-lg-3 col-md-4 label ">DELETE THIS CLASS</div>
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
                                        <h4>Due to security policies you can not delete <?=$class_name?>. To change this go to settings</h4>
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
                <h5 class="card-title">Table of all students in <?=$class_name?></h5>
                
                <!-- Table with stripped rows -->
                <table class=" table datatable table-dark ">
                  <thead>
                    <tr>
                      <th>S/N</th>
                      <th>first name</th>
                      <th>Last name</th>
                      <th>Parent phone</th>
                      <th>Parent email</th>
                      <th>Added </th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $n = 1;
                    while ($row_std = mysqli_fetch_array($students)) {

                      ?>
                      
                        <tr>
                          <td><?=$n?></td>
                          <td><?=$row_std['student_first_name']?></td>
                          <td><?=$row_std['student_last_name']?></td>
                          <td><?=$row_std['parent_phone']?$row_std['parent_phone']:"None"?></td>
                          <td><?=$row_std['parent_email']?></td>
                          <td><?=timeAgo(strtotime($row_std['created_at']))?></td>
                          
                        </tr>
                      
                    <?php
                    $n++;
                    }
                    ?>
                  
                  </tbody>
                </table>
                <!-- End Table with stripped rows -->
  
              </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Table of all Subjects taught in <?=$class_name?></h5>
                
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