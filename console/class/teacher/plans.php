<?php
session_start();
include_once "../../../config.php";
include_once "../../functions/timeAgo.php";

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
$get_class_teacher = mysqli_query($conn, "SELECT * FROM teachers WHERE School_unique_id = '$school_uid' AND teacher_email = '$teacher_email'");
$teacher = mysqli_fetch_array($get_class_teacher);
$teacher_id = $teacher['teacher_id'];
$teacher_name = $teacher['teacher_fullname'];



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
  <?php $page = "plans"; include_once "./includes/sidebar.php"?>

  <main id="main" class="main">
    <div class="d-flex justify-content-between flex-wrap" 
    style="margin:1rem auto;">
        <div class="pagetitle">
        <h1>
            <i class="bi bi-pen"></i>
            My plans
        </h1>
        </div><!-- End Page Title -->
        <div>
          <button 
          type="submit" 
          class="btn btn-secondary"
          data-bs-toggle="modal" 
          data-bs-target="#add-plan">
            <i class="bi bi-clipboard-plus"></i>
            <span>Add Plan</span>
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
                        <!-- TODO: add emojis to the plcaholder -->
                        <input type="hidden" name="form-type" value="plan" >
                        <input type="hidden" name="owner" value="<?=$teacher_email?>" >
                        <div class=" field input">
                          <input class="plan-input" style="width: 100%;"  type="text" name="plan-title"  placeholder="Plan Title " required>
                        </div>
                        <div class=" field input">
                          <textarea class="plan-input" name="plan-desc" id="desc" rows="4" placeholder="Plan description"></textarea>
                        </div>
                        <div class="field input">
                          <label for="progress">Progress</label>
                          <input style="width: 100%;" type="range" name="progress" id="progress" value="10">
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
      <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-dark bg-dark text-light border-0 alert-dismissible fade show" role="alert">
                The school's ekiliSense admin will be able to access and see your teaching plans
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div class="plans-list" id="plans-list">
              <div class="spinner-grow" role="status"></div>
                <span class="">Loading...</span>
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
  <script src="js/plans.js"></script>
  
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