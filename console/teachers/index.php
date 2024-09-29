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

#getting the list of classes
$get_teachers = mysqli_query($conn, "SELECT * FROM teachers WHERE school_unique_id = '$school_uid'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ekiliSense | <?=$school['School_name']?> | teachers</title>
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

        <li class="upgrade-btn-section">
          
        </li>

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
              <a class="dropdown-item d-flex align-items-center" href="profile.php">
                <i class="bi bi-person"></i>
                <span>Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="../logout.php?ref=<?=$school_uid?>">
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
        <a class="nav-link " href="./">
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
        <a class="nav-link collapsed" href="../performance">
          <i class="bi bi-rocket-takeoff"></i>
          <span>Perfomance</span>
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
      <h1><i class="bi bi-people"> </i> Teachers</h1>
    </div><!-- End Page Title -->
    
    
    <section class="section">
        <div class="row">
          <div class="col-lg-12">
  
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Table of all teachers</h5>
                
                <!-- Table with stripped rows -->
                <table class="table datatable table-dark">
                  <thead>
                    <tr>
                      <th>S/N</th>
                      <th>
                        <b>N</b>ame
                      </th>
                      <th>Email</th>
                      <th>Phone</th>
                      <th>Added </th>
                      <th>Action </th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    while ($row_class = mysqli_fetch_array($get_teachers)) {

                      ?>
                      
                        <tr>
                          <td><?=$row_class['teacher_id']?></td>
                          <td><?=$row_class['teacher_fullname']?></td>
                          <td><?=$row_class['teacher_email']?></td>
                          <td><?=$row_class['teacher_active_phone']?></td>
                          <td><?=timeAgo(strtotime($row_class['created_at']))?></td>
                          <td>
                            <a href="../view/teacher.php?tid=<?=$row_class['teacher_id']?>"  rel="noopener noreferrer" class="btn btn-secondary">
                              view
                            </a>
                          </td>
                        </tr>
                      
                    <?php
                    }
                    ?>
                  
                  </tbody>
                </table>
                <!-- End Table with stripped rows -->
  
              </div>
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

  
  
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  
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

</body>

</html>