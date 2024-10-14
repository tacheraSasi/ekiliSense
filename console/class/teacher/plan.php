<?php
session_start();
include_once "../../../config.php";
include_once "../../functions/timeAgo.php";
include_once "../../../middlwares/teacher_auth.php";

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
      $timeAgo = timeAgo(strtotime($plan['created_at']));
        $status = "pending";

        #TOstatus later on put this in a separate file
        if($progress > 80){
          $status = "almost_done";
        }else if ($progress > 20 && $progress < 40){
          $status = "not_moving";
        }else if ($progress > 40 && $progress < 60){
          $status = "pending";
        }else if ($progress >= 60){
          $status = "in_progress";
        }else{
          $status = "started";
        }

        #bg-colors
        if($status == "almost_done"){
          $bg_color = "bg-success";
        }else if ($status == "not_moving"){
          $bg_color = "bg-warning";
        }else if ($status == "pending"){
          $bg_color = "bg-info";
        }else if ($status == "in_progress"){
          $bg_color = "bg-secondary";
        }else{
          $bg_color = "bg-primary";
        }

        #text color
        $textColor = $bg_color == "bg-info" || $bg_color == "bg-warning" ? "text-dark":""; 

  }else{
      $isValidUrl = true;
  }
}else{
  #TODO:some sort of error message
  $isValidUrl = true;
  exit;
}


#deleting the plan
if(isset($_POST['delete-plan'])){
  delPlan($conn,$school_uid,$_POST['plan-ref']);
};

function delPlan($conn,$school_uid,$plan_ref){
  $delete_query = mysqli_query($conn,
  "DELETE FROM plans WHERE `plans`.`uid` = '$plan_ref' AND school_uid = '$school_uid'");

  if($delete_query){
    header("location:./plans.php");
  }else{
    $delError = "Something went wrong, Failed to delete";
  }

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
    <?php
        if(!$isValidUrl){
    ?>
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
                  <button type="button" class="custom-btn-close" style="color:white" data-bs-dismiss="modal" >X</button>
                </div>
                <div class="modal-body">
                  <h2>Are you sure you want to delete this🤷 🤷‍♂️?</h2>
                </div>
                <form class="modal-footer" method="post" >
                  <input type="hidden" name="plan-ref" value="<?=$_GET["ref"]?>">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" name="delete-plan" class="btn btn-danger"><i class="bi bi-trash"> </i> Delete</button>
                </form>
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
                      <h1>Edit plan</h1>
                      <p class="sub-heading">
                        Bringing Artificial intelligence closer to education
                      </p>
                      <form class="modal-form-edit" id="edit-plan"  action="server/manage-plans.php" method="POST" enctype="multipart/form-data" autocomplete="off" >
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
                            <button  id="submit" name="edit-plan" title="create class" type="submit">Edit Plan</button>
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
          <?php
            if(isset($delError)):
          ?>
          <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show" role="alert">
            <?=$delError?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php endif ?>
            
            <div class="plans-list">
              <div style="color:inherit" class="plan-single" >
                  
                  <div class="plan-body d-flex justify-between flex-column">
                    <div class="plan-top d-flex justify-between">
                      <span class="badge rounded-pill <?=$bg_color?> <?=$textColor?> "><?=$status?></span>
                      <span class="flex-end timeago"><?=$timeAgo?></span>
                    </div>
                      <strong><b><?=$title?></b></strong><br>
                      
                      <pre><?=$desc?></pre>
                      <div class="progress mt-3">
                      <div class="progress-bar <?=$bg_color?>"  role="progressbar" style="width: <?=$progress?>%" aria-valuenow="<?=$progress?>" aria-valuemin="0" aria-valuemax="100"><?=$progress?>%</div>
                      </div>
                    
                  </div>
              </div>
            </div>
              
            
        </div>
      </div>
        
    </section>
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
  </main><!-- End #main -->

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