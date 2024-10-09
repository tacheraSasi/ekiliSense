<?php
session_start();
include_once "../../../config.php";
include_once "../../functions/timeAgo.php";

if(!isset($_SESSION['teacher_email']) || !isset($_SESSION['School_uid'])){
  header("location:https://auth.ekilie.com/sense/teacher");
}
$teacher_email = "";//////####
$teacher_email = $_SESSION['teacher_email'];

$school_uid = $_SESSION['School_uid'];

#getting google data
$get_google_data = mysqli_query($conn,"SELECT * FROM teachers_google WHERE email = '$teacher_email'");
$isConnectedToGoogle = false;

if (mysqli_num_rows($get_google_data)>0){
  $isConnectedToGoogle = true; #teacher has connected his account to google
  $google_data = mysqli_fetch_assoc($get_google_data);
}

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);

#getting the class teachers details
$get_class_teacher = mysqli_query($conn, "SELECT * FROM teachers WHERE School_unique_id = '$school_uid' AND teacher_email = '$teacher_email'");
$teacher = mysqli_fetch_array($get_class_teacher);
$teacher_id = $teacher['teacher_id'];
$teacher_name = $teacher['teacher_fullname'];
$subjects = mysqli_query($conn,"SELECT * FROM subjects WHERE teacher_id = '$teacher_id'");


function edit($conn,$school_uid,$teacher_id){
    $name = mysqli_real_escape_string($conn,$_POST['fullname']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $phone = mysqli_real_escape_string($conn,$_POST['phone']);
    $address = mysqli_real_escape_string($conn,$_POST['address']);
  
    $query = "UPDATE `teachers` SET `teacher_fullname` = '$name',`teacher_email` = '$email', 
    `teacher_active_phone` = '$phone', `teacher_home_address` = '$address' WHERE 
    `teachers`.`teacher_id` = '$teacher_id' AND `teachers`.`School_unique_id` = '$school_uid'";
    #TODO:Check if verified
  
    $update = mysqli_query($conn,$query);
    $isUpdated = false;
    if($update){
      $isUpdated = true;
    }
    
  
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title> <?=$school['School_name']?> | teacher | <?=$teacher['teacher_fullname'];?></title>
  <meta content="" name="description">
  <meta content="" name="keywords">
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

  <?php include_once "./includes/topbar.php"?>
  <?php $page = "profile"; include_once "./includes/sidebar.php"?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>
        <img 
        src="../../../assets/img/user.png" 
        alt="Profile" 
        class="rounded-circle"
        style="width: 25px;height:25px">
        Me
      </h1>
      
    </div><!-- End Page Title -->

    <section class="section profile">
      <div class="row">
        <div class="col-xl-4">

          <div class="card">
            <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">

              <img src="<?=$isConnectedToGoogle?$google_data['picture_url']:'../../../assets/img/user.png'?>" alt="Profile" class="rounded-circle">
              <h2><?=$teacher['teacher_fullname']?></h2>
              <h3><?=$school['School_name']?></h3>
              <!-- <div class="social-links mt-2">
                <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
              </div> -->
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
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#work-attendance">Work Attendance</button>
                </li>

              </ul>
              <div class="tab-content pt-2">

                <div class="tab-pane fade show active profile-overview" id="profile-overview">
                  <h5 class="card-title">About</h5>
                  <p class="small fst-italic">I LOVE EKILISENSE</p>

                  <h5 class="card-title">My informations</h5>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label ">Full Name</div>
                    <div class="col-lg-9 col-md-8"><?=$teacher['teacher_fullname']?></div>
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
                    <div class="col-lg-9 col-md-8"><?=$teacher['teacher_home_address']?$teacher['teacher_home_address']:"Not added yet."?></div>
                  </div>

                </div>

                <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
                  <?php
                  if(isset($_POST['save-changes'])){
                      edit($conn,$school_uid,$teacher['teacher_id']);
                      echo"
                      <script>window.location.reload</script>
                      ";
                  }
                  ?>
                  <!-- Profile Edit Form -->
                  <form method="post">
                    <div class="row mb-3">
                      <label for="fullname" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input  name="fullname" type="text" class="form-control" id="fullName" placeholder="Enter Name" value="<?=$teacher['teacher_fullname'];?>" style="background-color:#444;outline:none;border:none;color:#b8eeab">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Phone" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                      <div class="col-md-8 col-lg-9">
                        <input  name="phone" type="text" class="form-control" id="Phone"placeholder="Enter Phone"  value="<?=$teacher['teacher_active_phone'];?>" style="background-color:#444;outline:none;border:none;color:#b8eeab">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                      <div class="col-md-8 col-lg-9">
                        <input  name="email" type="email" class="form-control" id="Email" placeholder="Enter Email"  value="<?=$teacher['teacher_email'];?>" style="background-color:#444;outline:none;border:none;color:#b8eeab">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Address" class="col-md-4 col-lg-3 col-form-label">Address</label>
                      <div class="col-md-8 col-lg-9">
                        <input  name="address" type="text" class="form-control" id="Address"placeholder="Enter Home Address"  value="<?=$teacher['teacher_home_address'];?>" style="background-color:#444;outline:none;border:none;color:#b8eeab">
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" name="save-changes" class="btn btn-primary">Save Changes</button>
                    </div>
                  </form><!-- End Profile Edit Form -->

                </div>
                <div class="tab-pane fade profile-edit pt-3" id="work-attendance">
                  <div class="alert alert-dark bg-dark text-light border-0 alert-dismissible fade show" role="alert">
                      To sign/mark your work attendance today you need to be within 100m of your workspace <br>
                      <div class="mt-2">
                        <strong>Present</strong>
                        <button 
                            type="submit" 
                            class="btn btn-secondary mx-2"
                            data-bs-toggle="modal" 
                            data-bs-target="#present">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Sign</span>
                        </button>
                    </div>
                  </div>
                  <div class="modal fade " id="present" tabindex="-1">
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
                                    <img src="../../../assets/img/favicon.jpeg" alt="" class="logo">
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
                                Embark on an Odyssey of Technological Marvels with EkiliSense:
                                Traverse the Digital Frontiers of AI-Driven Education
                                Immerse Yourself in the Wonders of Machine Learning and Automation
                                Uncover Hidden Gems and Revolutionary Insights
                                Together, Let's Forge a Brighter Future for Learning!
                            </div>
                            
                            </div>
                            <div class="right">
                            <h3><span class="badge bg-danger">beta</span></h3>                            <h1>Sign your attendance</h1>
                            <div class="alert alert-dark bg-dark text-light border-0 alert-dismissible fade show" role="alert">
                                Allow geolocation to give us your current location <br>
                                So that we make sure you are really at work
                            
                            </div>
                            
                            <form class="modal-form" id="sign-attendance"  action="#" method="POST" enctype="multipart/form-data" autocomplete="off" >
                                <div class="error-text" style="
                                    background-color: rgba(243, 89, 89, 0.562);
                                    border:solid 1px rgba(243, 89, 89, 0.822);
                                    color:#fff;
                                    padding:6px;
                                    border-radius:8px;">
                                </div>
                                <!-- TODO: add emojis to the placeholder -->
                                <input type="hidden" name="form-type" value="staff-attendance" >
                                <input type="hidden" name="latitude" value="" >
                                <input type="hidden" name="longitude" value="" >
                                <input type="hidden" name="owner" value="<?=$teacher_email?>" >
                                
                            
                                <div class="input-container field button">
                                    <button  id="sign" title="Sign" type="submit">PROCEED</button>
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


              </div><!-- End Bordered Tabs -->

            </div>
          </div>

        </div>
        <div class="col-lg-12">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Subjects you are assigned to teach</h5>
                
                <!-- Table with stripped rows -->
                <table class="table datatable table-dark">
                  <thead>
                    <tr>
                      <th>S/N</th>
                      <th>Subject name</th>
                      <th>Class</th>
                      <th>Number of students </th>
                      <th>view</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $n = 1;
                    while ($row_std = mysqli_fetch_array($subjects)) {
                        $class_id = $row_std['class_id'];
                        $q = "select * from students where class_id = '$class_id'";
                        $r = mysqli_query($conn, $q);
                        $num_std = mysqli_num_rows($r);
                        $class_info = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM classes WHERE Class_id = '$class_id' AND school_unique_id = '$school_uid' "));
                        $class_name = $class_info['Class_name']

                      ?>
                      
                        <tr>
                          <td><?=$n?></td>
                          <td><?=$row_std['subject_name']?></td>
                          <td><?=$class_name?></td>
                          <td><?=$num_std?></td>
                          <td>
                            <a href="" class="btn btn-secondary">view</a>
                          </td>
                          
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
  <script src="../../assets/js/stuff-attendance.js"></script>
  <script src="js/modal-form.js"></script>
  
  <script src="../../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../../assets/vendor/echarts/echarts.min.js"></script>
  <script src="../../assets/vendor/quill/quill.min.js"></script>
  <script src="../../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../../assets/vendor/php-email-form/validate.js"></script>

  <script src="../../assets/js/main.js"></script>

</body>

</html>