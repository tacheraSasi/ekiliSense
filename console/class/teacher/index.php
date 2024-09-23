<?php
session_start();
include_once "../../../config.php";
if(!(isset($_SESSION['School_uid']) && isset($_SESSION['teacher_email']))){
  header("location:https://auth.ekilie.com/sense/teacher");
}
$school_uid = $_SESSION['School_uid'];  
$_SESSION['teacher_email'] = "tacherasasi@gmail.com"; #hard-coded value will change 
$teacher_email = $_SESSION['teacher_email'];

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);

#getting the class teachers details
$get_class_teacher = mysqli_query($conn, "SELECT * FROM teachers WHERE School_unique_id = '$school_uid' AND teacher_email = '$teacher_email'");
$teacher = mysqli_fetch_array($get_class_teacher);
$teacher_id = $teacher['teacher_id'];
$teacher_name = $teacher['teacher_fullname'];


$get_teachers = mysqli_query($conn, "SELECT * FROM teachers WHERE School_unique_id = '$school_uid'");


#getting the class info
$get_class_id = mysqli_query($conn, "SELECT * FROM class_teacher WHERE school_unique_id = '$school_uid' AND teacher_id = '$teacher_id'");

if(mysqli_num_rows($get_class_id) == 0){
  header("location:../../teacher");
}else{
  $class_id = mysqli_fetch_array($get_class_id)['Class_id'];

}

$class_info = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM classes WHERE Class_id = '$class_id' AND school_unique_id = '$school_uid' "));

#getting students info
$students = mysqli_query($conn,"SELECT * FROM students WHERE class_id = '$class_id'  ORDER BY `students`.`student_first_name` ASC");
$student_num = mysqli_num_rows($students);

#getting subjects info
$subjects = mysqli_query($conn,"SELECT * FROM subjects WHERE class_id = '$class_id'");
$subject_num = mysqli_num_rows($subjects);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Console | ekiliSense</title>
  
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
  <?php $page = "index"; include_once "./includes/sidebar.php"?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Class <i class="bi bi-arrow-right-short"> </i> <?=$class_info['Class_name']?></h1>
    </div><!-- End Page Title -->
    
    
    <section class="section dashboard">
      <div class="row">
       
        <!-- Left side columns -->
        <div class="col-lg-8">
          <div class="row">
            <div class="manage-btn-container">
              <div class="manage-content">
                <button 
                  type="button" 
                  data-bs-toggle="modal" 
                  data-bs-target="#add-student-modal" 
                  class="manage-btn" 
                  style="background-color: #a0845f;">
                  <i class="bi bi-people"></i>
                  Add a student
                </button>
                
                <button 
                  type="button" 
                  data-bs-toggle="modal" 
                  data-bs-target="#add-subject-modal" 
                  class="manage-btn" 
                  style="background-color: #815fa0;">
                  <i class="bi bi-book"></i>
                 Add a subject
                </button>

                <!-- <button 
                  type="button"  
                  data-bs-toggle="modal" 
                  data-bs-target="#add-class-teacher-modal" 
                  class="manage-btn" 
                  style="background-color: #64a05f;">
                  <i class="bi bi-person"></i>
                  Add Class teacher
                </button> -->

              </div>
            </div>
            <!-- Sales Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card sales-card" style="border-radius: 1rem;">

                <div class="card-body" >
                  <h5 class="card-title">Students <span>| This class</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?=$student_num?></h6>
                      <!-- <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span>
 -->
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End of Card -->

            <!-- Revenue Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card revenue-card">

                <div class="card-body">
                  <h5 class="card-title">Subjects <span>| This class</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-person"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?=$subject_num?></h6>
                      <!-- <span class="text-success small pt-1 fw-bold">8%</span> <span class="text-muted small pt-2 ps-1">increase</span>
 -->
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End of Card -->

            <div class="col-12">
              
              <div class="card">

                <div class="card-body">
                  <h5 class="card-title">Attendance <span>/This class</span></h5>

                  <!-- Line Chart -->
                  <div id="reportsChart"></div>

                  <script>
                    document.addEventListener("DOMContentLoaded", () => {
                      new ApexCharts(document.querySelector("#reportsChart"), {
                        series: [{
                          name: 'Teachers',
                          data: [31, 40, 28, 51, 42, 82, 56],
                        }, {
                          name: 'Students',
                          data: [11, 32, 45, 32, 34, 52, 41]
                        }],
                        chart: {
                          theme:'dark',//NOTE:i changed the theme names in the packages code
                          height: 350,
                          type: 'area',
                          toolbar: {
                            show: false
                          },
                        },
                        markers: {
                          size: 4
                        },
                        colors: ['#4154f1', '#2eca6a', '#ff771d'],
                        fill: {
                          type: "gradient",
                          gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.3,
                            opacityTo: 0.4,
                            stops: [0, 90, 100]
                          }
                        },
                        dataLabels: {
                          enabled: false
                        },
                        stroke: {
                          curve: 'smooth',
                          width: 2
                        },
                        xaxis: {
                          type: 'datetime',
                          categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
                        },
                        tooltip: {
                          x: {
                            format: 'dd/MM/yy HH:mm'
                          },
                        }
                      }).render();
                    });
                  </script>
                  <!-- End Line Chart -->

                </div>

              </div>
            </div><!-- End Reports -->



          </div>
        </div><!-- End Left side columns -->

        <!-- Right side columns -->
        <div class="col-lg-4">

        <div class="alert alert-dark bg-dark border-0 text-light alert-dismissible fade show" role="alert">
          <h4 class="alert-heading"><i class="bi bi-lightbulb"></i> ekiliSense </h4>
          <p>😉 AI-powered education management is here. Seamless integration and real-time analytics.</p>
          <hr style="background-color: var(--border-color);">
          <p class="mb-0"><!-- <i class="bi bi-rocket"></i> -->🚀 Transform your school's management with ekiliSense!</p>
          <button type="button" class="btn-close text-light" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>


          <!-- ekiliSense Updates -->
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">ekiliSense Updates <span>| Recent</span></h5>
              <div class="activity">
                <div class="activity-item d-flex">
                  <div class="activite-label">1 hr</div>
                  <i class='bi bi-circle-fill activity-badge text-success align-self-start'></i>
                  <div class="activity-content">
                    <a href="#" class="fw-bold ">New AI Feature</a> for real-time student analytics
                  </div>
                </div><!-- End activity item-->

                <div class="activity-item d-flex">
                  <div class="activite-label">3 hrs</div>
                  <i class='bi bi-circle-fill activity-badge text-primary align-self-start'></i>
                  <div class="activity-content">
                    Updated <a href="#" class="fw-bold ">Dashboard</a> with advanced reporting tools
                  </div>
                </div><!-- End activity item-->

                <div class="activity-item d-flex">
                  <div class="activite-label">1 day</div>
                  <i class='bi bi-circle-fill activity-badge text-info align-self-start'></i>
                  <div class="activity-content">
                    New <a href="#" class="fw-bold ">Integration</a> with Google Classroom
                  </div>
                </div><!-- End activity item-->

                <div class="activity-item d-flex">
                  <div class="activite-label">2 days</div>
                  <i class='bi bi-circle-fill activity-badge text-warning align-self-start'></i>
                  <div class="activity-content">
                    Scheduled <a href="#" class="fw-bold ">System Maintenance</a> on August 5
                  </div>
                </div><!-- End activity item-->

                <div class="activity-item d-flex">
                  <div class="activite-label">1 week</div>
                  <i class='bi bi-circle-fill activity-badge text-muted align-self-start'></i>
                  <div class="activity-content">
                    <a href="#" class="fw-bold ">Term Exam Results</a> now available
                  </div>
                </div><!-- End activity item-->
              </div>
            </div>
          </div><!-- End ekiliSense Updates -->


          <!-- System Usage -->
          <!-- <div class="card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>

            <div class="card-body pb-0">
              <h5 class="card-title">System Usage <span>| Today</span></h5>
              <div id="usageChart" style="min-height: 400px;" class="echart"></div>
              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  echarts.init(document.querySelector("#usageChart")).setOption({
                    tooltip: {
                      trigger: 'item'
                    },
                    legend: {
                      top: '5%',
                      left: 'center'
                    },
                    series: [{
                      name: 'Usage Source',
                      type: 'pie',
                      radius: ['40%', '70%'],
                      avoidLabelOverlap: false,
                      label: {
                        show: false,
                        position: 'center'
                      },
                      emphasis: {
                        label: {
                          show: true,
                          fontSize: '18',
                          fontWeight: 'bold'
                        }
                      },
                      labelLine: {
                        show: false
                      },
                      data: [
                        { value: 1048, name: 'Student Portal' },
                        { value: 735, name: 'Teacher Portal' },
                        { value: 580, name: 'Admin Portal' },
                        { value: 484, name: 'Parent Portal' },
                        { value: 300, name: 'Mobile App' }
                      ]
                    }]
                  });
                });
              </script>
            </div>
          </div> -->
          <!-- End System Usage -->

        </div><!-- End Right side columns -->

      </div>
      <!-- modals -->
      <!-- add-student -->
      <div class="modal fade " id="add-student-modal" tabindex="-1">
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
                        <img src="../../../../assets/img/favicon.jpeg" alt="" class="logo">
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
                  <h1>Add a student</h1>
                  <p class="sub-heading">
                    Bringing Artificial intelligence closer to education
                  </p>
                  <form class="modal-form" id="student"  action="#" method="POST" enctype="multipart/form-data" autocomplete="off" >
                  <div class="error-text" style="
                        background-color: rgba(243, 89, 89, 0.562);
                        border:solid 1px rgba(243, 89, 89, 0.822);
                        color:#fff;
                        padding:6px;
                        border-radius:8px;">
                      </div>
                    <!-- TODO: add emojis to the placeholder -->
                    <input type="hidden" name="form-type" value="student" >
                    <input type="hidden" name="class" value="<?=$class_id?>" >
                    <div class=" field input">
                      <input style="width: 100%;"  type="text" name="fname"  placeholder="Student's first name " required>
                    </div>
                    <div class=" field input">
                      <input style="width: 100%;"  type="text" name="lname"  placeholder="Student's last name " required>
                    </div>
                    <div class=" field input">
                      <input style="width: 100%;"  type="text" name="email"  placeholder="Parent's email " >
                    </div>
                    
                    <div class=" field input" >
                      <input style="width: 100%;" type="tel" name="mobile"  placeholder="Parent's mobile" >
                    </div>
                    
                    <div class="input-container field button">
                        <button  id="submit" title="add teacher" type="submit">ADD</button>
                    </div>
                    <div class="link" style="color:lightgrey">Need help?
                      <a href="../onboarding/" style="color:#33995d;text-decoration:none">
                       Contact Support
                      </a>
                    </div> 
          
                  </form>
                  
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
     
      <!-- add-subject -->
      <div class="modal fade " id="add-subject-modal" tabindex="-1">
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
                        <img src="../../../../assets/img/favicon.jpeg" alt="" class="logo">
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
                  <h1>Create a subject</h1>
                  <p class="sub-heading">
                    Bringing Artificial intelligence closer to education
                  </p>
                  <form class="modal-form" id="subject"  action="#" method="POST" enctype="multipart/form-data" autocomplete="off" >
                  <div class="error-text" style="
                        background-color: rgba(243, 89, 89, 0.562);
                        border:solid 1px rgba(243, 89, 89, 0.822);
                        color:#fff;
                        padding:6px;
                        border-radius:8px;">
                      </div>
                    <!-- TODO: add emojis to the plcaholder -->
                    <input type="hidden" name="form-type" value="subject" >
                    <input type="hidden" name="class" value="<?=$class_id?>" >
                    <div class=" field input">
                      <input style="width: 100%;" id="input-not-hidden"  type="text" name="subject-name"  placeholder="Write the subject name here" required>
                    </div>
                    <div class=" field input">
                      <select name="choosen-subject-teacher" id="choose-class-teacher">
                        <option value="Choose a teacher">Choose a teacher</option>
                        <?php
                          while($row_teacher = mysqli_fetch_array($get_teachers)){
                            $teacher_name = $row_teacher['teacher_fullname'];
                            $teacher_id = $row_teacher['teacher_id'];
                            echo "<option value='$teacher_id'>$teacher_name</option>";
                          }
                        ?>
                      </select>
                    </div>
                  
                    <div class="input-container field button">
                        <button  id="submit" title="create class" type="submit">CREATE</button>
                    </div>
                    <!-- <div class="link" style="color:lightgrey">Need help?
                      <a href="../onboarding/" style="color:#33995d;text-decoration:none">
                       Contact Support
                      </a>
                    </div>  -->
          
                   
          
                  </form>
                  
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
      &copy; Copyright <strong><span>ekiliSense</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
    From <a href="https://tachera.com/Insights/">ekilie</a>
    </div>
  </footer><!-- End Footer -->
  
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  
  <!-- Vendor JS Files -->
  <script src="js/modal-form.js"></script>
    
  <a href="https://www.producthunt.com/posts/ekilisense?embed=true&utm_source=badge-featured&utm_medium=badge&utm_souce=badge-ekilisense" target="_blank"><img src="https://api.producthunt.com/widgets/embed-image/v1/featured.svg?post_id=481333&theme=dark" alt="ekiliSense - AI&#0045;powered&#0032;school&#0032;management&#0032;made&#0032;easy&#0032;as&#0032;service | Product Hunt" style="width: 250px; height: 54px;" width="250" height="54" /></a>
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