<?php
session_start();
include_once "../../../config.php";
include_once "../../../middlwares/teacher_auth.php";
 
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ekiliSense | <?=$school['School_name']?> | attendance</title>
  

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
  <?php $page = "attendance"; include_once "./includes/sidebar.php"?>

  <main id="main" class="main">
    <div class="d-flex justify-content-between flex-wrap" 
    style="margin:1rem auto;">
      <div class="pagetitle" style="display:inline-block">
        <h1 style="display:inline-block">Attendance <i class="bi bi-arrow-right-short"> </i> <?=$class_info['Class_name']?></h1>
      </div><!-- End Page Title -->
      <form method="post" class="attendance-mark" id="all">
        <input type="hidden" name="class" value="<?=$class_id?>">
        <input type="hidden" name="form-type" value="all">
        <button type="submit" 
          class="btn btn-secondary"
        >
          <i class="bi bi-check-square-fill"></i>
          <span>Mark all</span>
        </button>
      </form>
    </div>
    
    <section class="section">
        <div class="row">
          <div class="col-lg-12">
  
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Table of all students in <?=$class_info['Class_name']?> class</h5>
                
                <!-- Table with stripped rows -->
                <table class="table  table-dark  table-hover datatable" style="overflow:auto">
                  <thead>
                    <tr>
                      <th>S/N</th>
                      <th>Student</th>
                      <th>Attended today</th>
                      <th>view</th>
                      <th>parents</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $n = 1;
                    while ($row_std = mysqli_fetch_array($students)) {
                      $student_id = $row_std['student_id'];
                      $now = date('Y-m-d');
                      // echo $now;
                      $is_marked = false;
                      $check = mysqli_query($conn,"select * from student_attendance where 
                      student_id = '$student_id' and attendance_date = '$now'");
                      if(mysqli_num_rows($check) > 0) {
                          $is_marked = true;
                          $student_fname = $row_std['student_first_name'];
                      }
                      ?>
                      
                        <tr>
                          <td><?=$n?></td>
                          <td><?=$row_std['student_first_name']." ".$row_std['student_last_name']?></td>
                          <td>
                            <form method="post" class="attendance-mark" id="<?=$row_std['student_id']?>">
                              <input type="hidden" name="student" value="<?=$row_std['student_id']?>">
                              <input type="hidden" name="class" value="<?=$row_std['class_id']?>">
                              <input type="hidden" name="form-type" value="single">
                              <button type="submit" 
                                class="btn btn-secondary"
                                <?=$is_marked?"
                                style='background-color:var(--btn-bg)' 
                                data-bs-toggle='tooltip' 
                                data-bs-placement='top'
                                title='To unmark visit $student_fname s student profile page'
                                ":""?>
                              >
                                <i class="bi bi-check-square-fill"></i>
                                <span><?=$is_marked?"Marked":"Mark present"?></span>
                              </button>
                            </form>
                          </td>
                          <td>
                            <a href="./view/student.php?stid=<?=$row_std['student_id']?>"  class="btn btn-secondary">
                              view
                            </a>
                          </td>
                          <td>
                            <a href="./view/student.php?stid=<?=$row_std['student_id']?>"  class="btn btn-success">
                              <i class="bi bi-phone"></i> contact
                            </a>
                          </td>
                        </tr>
                      
                    <?php
                    $n=$n+1;
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

  
  
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  
  <!-- Vendor JS Files -->
  <script src="js/attendance.js"></script>
  
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