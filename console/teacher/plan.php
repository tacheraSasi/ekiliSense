<?php
session_start();
include_once "../../config.php";
include_once "../functions/timeAgo.php";

if(!isset($_SESSION['teacher_email']) || !isset($_SESSION['School_uid'])){
  header("location:https://auth.ekilie.com/sense/teacher");
}
// $teacher_email = "";//////####
$teacher_email = $_SESSION['teacher_email'];

$school_uid = $_SESSION['School_uid'];

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);

#getting the teachers information
$get_teacher = mysqli_query($conn, "SELECT * FROM teachers WHERE school_unique_id = '$school_uid' AND teacher_email = '$teacher_email'");
$teacher = mysqli_fetch_array($get_teacher);

#checking if the url is correct
$isValidUrl = false;
if(isset($_GET["ref"])){
  #getting the teachers details
  $ref = $_GET["ref"];
  $get_plan = mysqli_query($conn, "SELECT * FROM plans WHERE school_uid = '$school_uid' AND uid = '$ref'");
  if(mysqli_num_rows($get_plan)>0){
      $plan = mysqli_fetch_array($get_plan);
      $title = $plan["title"];
      $progress = $plan["progress"];
      $desc = $plan["description"];
      $uid = $plan["uid"];
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

  <title> <?=$school['School_name']?> | <?=$teacher['teacher_fullname'];?></title>
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

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="../../assets/img/user.png" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?=$school['School_name']?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?=$school['School_name']?></h6>
              <span><?=$teacher['teacher_fullname']?></span>
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
              <a class="dropdown-item d-flex align-items-center" href="./logout.php?logout_id=<?php echo $teacher_email?>">
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
        <a class="nav-link collapsed" href="./">
          <i class="bi bi-grid"></i>
          <span>Home</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
        <a class="nav-link " href="./plans.php">
          <i class="bi bi-pen"></i>
          <span>Teaching plans</span>
        </a>
      </li><!-- End Dashboard Nav -->
    
    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">
  <div class="d-flex justify-content-between flex-wrap" 
    style="margin:1rem auto;">
        <button 
          type="submit" 
          class="btn  btn-danger"
          data-bs-toggle="modal" 
          data-bs-target="#delete-plan">
            <i class="bi bi-trash"></i>
            <span>Delete</span>
          </button>
          <!-- The delete modal -->
          <div class="modal fade" id="delete-plan" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content card">
                <div class="modal-header">
                  <h5 class="modal-title">⚠️Alert</h5>
                  <button type="button" class="btn-close" style="color:white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <h2>Are you sure you want to delete this🤷🤷‍♂️?</h2>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="button" class="btn btn-danger"><i class="bi bi-trash"> </i> Delete</button>
                </div>
              </div>
            </div>
          </div>
          <button 
          type="submit" 
          class="btn btn-secondary"
          data-bs-toggle="modal" 
          data-bs-target="#add-plan">
            <i class="bi bi-pen"></i>
            <span>Edit</span>
          </button>
          
          <div class="modal fade " id="add-plan" tabindex="-1">
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
                        Embark on an Odyssey of Technological Marvels with EkiliSense:
                        Traverse the Digital Frontiers of AI-Driven Education
                        Immerse Yourself in the Wonders of Machine Learning and Automation
                        Uncover Hidden Gems and Revolutionary Insights
                        Together, Let's Forge a Brighter Future for Learning!
                      </div>
                      
                    </div>
                    <div class="right">
                      <h1>Add a teaching plan</h1>
                      <p class="sub-heading">
                        Bringing Artificial intelligence closer to education
                      </p>
                      <form class="modal-form" id="plan"  action="#" method="POST" enctype="multipart/form-data" autocomplete="off" >
                          <div class="error-text" style="
                            background-color: rgba(243, 89, 89, 0.562);
                            border:solid 1px rgba(243, 89, 89, 0.822);
                            color:#fff;
                            padding:6px;
                            border-radius:8px;">
                          </div>
                        <!-- TODO: add emojis to the placeholder -->
                        <input type="hidden" name="form-type" value="edit-plan" >
                        <input type="hidden" name="uid" value="<?=$uid?>" >
                        <input type="hidden" name="owner" value="<?=$teacher_email?>" >
                        <div class=" field input">
                          <input class="plan-input" style="width: 100%;"  type="text" name="plan-title" value="<?=$title?>"  placeholder="Plan Title " required>
                        </div>
                        <div class=" field input">
                          <textarea class="plan-input" name="plan-desc" id="desc" rows="4" placeholder="Plan description"><?=$desc?></textarea>
                        </div>
                        <div class="field input">
                          <label for="progress">Progress</label>
                          <input style="width: 100%;" type="range" name="progress" id="progress" value="<?=$progress?>">
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
    </div>
 
             
    <section class="section profile">
        <?php
            if(!$isValidUrl){
        ?>
      <div class="row">
        <div class="col-lg-12">
            
            <div class="plans-list">
              <div style="color:inherit" class="plan-single" >
                  
                  <div class="plan-body d-flex justify-between flex-column">
                      <strong><b><?=$title?></b></strong><br>
                      <pre><?=$desc?></pre>
                      <div class="progress mt-3">
                          <div class="progress-bar bg-success"  role="progressbar" style="width: <?=$progress?>%" aria-valuenow="<?=$progress?>" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    
                  </div>
              </div>
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
  <script src="js/plans.js"></script>
  
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