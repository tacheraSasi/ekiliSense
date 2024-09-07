<?php
session_start();
include_once "../config.php";
if(!(isset($_SESSION['School_uid']))){
  header("location:../auth");
}
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
    header("location:./class/teacher/");
  }else{
    header("location:./teacher/");
  }
}
$school_uid = $_SESSION['School_uid'];

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);

#getting the list of classes
$get_classes = mysqli_query($conn, "SELECT * FROM classes WHERE school_unique_id = '$school_uid'");
$classes_count = mysqli_num_rows($get_classes);

#getting the list of teachers
$get_teachers = mysqli_query($conn, "SELECT * FROM teachers WHERE School_unique_id = '$school_uid'");
$teachers_count = mysqli_num_rows($get_teachers);

#getting the list of students
$get_students = mysqli_query($conn, "SELECT * FROM students WHERE school_uid = '$school_uid'");
$students_count = mysqli_num_rows($get_students);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Console | ekiliSense</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="icon">
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/custom.css" rel="stylesheet">
  
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="/console/" class="logo d-flex align-items-center">
        <span class="d-none d-lg-block">ekiliSense </span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="upgrade-btn-section">
          
        </li>
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
              <i class="bi bi-info-circle text-info"></i>
              <div>
                <h4>New AI Feature</h4>
                <p>AI-powered analytics have been integrated into the platform.</p>
                <p>1 hr. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-check-circle text-success"></i>
              <div>
                <h4>Dashboard Enhancement</h4>
                <p>New reporting tools have been added to the dashboard.</p>
                <p>2 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-calendar-event text-warning"></i>
              <div>
                <h4>Maintenance Notice</h4>
                <p>System maintenance is scheduled for August 5.</p>
                <p>4 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-file-earmark-text text-primary"></i>
              <div>
                <h4>Exam Results Published</h4>
                <p>Results for the latest term exams have been published.</p>
                <p>6 hrs. ago</p>
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

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="assets/img/school-1.png" alt="Profile" class="">
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
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-person"></i>
                <span>Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="#">
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
        <a class="nav-link " href="./">
          <i class="bi bi-grid"></i>
          <span>Home</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="./teachers/">
          <i class="bi bi-people"></i>
          <span>Teachers</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="./classes/">
          <i class="bi bi-buildings"></i>
          <span>Classes</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="./announcements">
          <i class="bi bi-bell"></i>
          <span>Announcements</span>
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
        <a class="nav-link collapsed" href="profile.php">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li><!-- End Profile Page Nav -->


      

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1><i class="bi bi-grid"> </i> Home</h1>
      
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
                  data-bs-target="#add-teacher-modal" 
                  class="manage-btn" 
                  style="background-color: cadetblue;">
                  <i class="bi bi-people"></i>
                  Add teacher
                </button>
                
                <button 
                  type="button" 
                  data-bs-toggle="modal" 
                  data-bs-target="#add-class-modal" 
                  class="manage-btn" 
                  style="background-color: #a0695f;">
                  <i class="bi bi-buildings"></i>
                  Create class
                </button>

                <button 
                  type="button"  
                  data-bs-toggle="modal" 
                  data-bs-target="#add-class-teacher-modal" 
                  class="manage-btn" 
                  style="background-color: #64a05f;">
                  <i class="bi bi-person"></i>
                  Add Class teacher
                </button>

              </div>
            </div>
            <!-- Sales Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card sales-card" style="border-radius: 1rem;">

                <div class="card-body" title="CLICK TO ADD">
                  <h5 class="card-title">Students <span>| all</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?=$students_count?></h6>
                      <!-- <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span>
 -->
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Sales Card -->

            <!-- Revenue Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card revenue-card">

                

                <div class="card-body" title="CLICK TO ADD">
                  <h5 class="card-title">Teachers <span>| all</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-person"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?=$teachers_count?></h6>
                      <!-- <span class="text-success small pt-1 fw-bold">8%</span> <span class="text-muted small pt-2 ps-1">increase</span>
 -->
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Revenue Card -->

            <!-- Customers Card -->
            <div class="col-xxl-4 col-xl-12">

              <div class="card info-card customers-card">

                
                <div class="card-body" title="CLICK TO ADD" >
                  <h5 class="card-title">Classes <span>| all</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-buildings-fill"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= $classes_count?></h6>
                      <!-- <span class="text-danger small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">decrease</span> -->

                    </div>
                  </div>

                </div>
              </div>

            </div><!-- End Customers Card -->
            

            <!-- Reports -->
            <div class="col-12">
              <div class="col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title">Registered users</h5>
      
                    <!-- Bar Chart -->
                    <canvas id="barChart" style="max-height: 400px;"></canvas>
                    <script>
                      document.addEventListener("DOMContentLoaded", () => {
                        new Chart(document.querySelector('#barChart'), {
                          type: 'bar',
                          data: {
                            labels: ['Teachers', 'Students', 'Parents'],
                            datasets: [{
                              label: 'Users',
                              data: [<?=$teachers_count?>, <?=$students_count?>, 0],
                              backgroundColor: [
                                'rgba(255, 99, 132, 0.2)',
                                'rgba(153, 102, 255, 0.2)',
                                'rgba(201, 203, 207, 0.2)'
                              ],
                              borderColor: [
                                'rgb(255, 99, 132)',
                                'rgba(153, 102, 255)',
                                'rgba(201, 203, 207)'
                              ],
                              borderWidth: 1
                            }]
                          },
                          options: {
                            scales: {
                              y: {
                                beginAtZero: true
                              }
                            }
                          }
                        });
                      });
                    </script>
                    <!-- End Bar CHart -->
      
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              
              <div class="card">

                <div class="card-body">
                  <h5 class="card-title">Reports <span>/Today</span></h5>

                  <!-- Line Chart -->
                  <!-- <div id="reportsChart"></div>

                  <script>
                    document.addEventListener("DOMContentLoaded", () => {
                      new ApexCharts(document.querySelector("#reportsChart"), {
                        series: [{
                          name: 'Sales',
                          data: [31, 40, 28, 51, 42, 82, 56],
                        }, {
                          name: 'Revenue',
                          data: [11, 32, 45, 32, 34, 52, 41]
                        }, {
                          name: 'Customers',
                          data: [15, 11, 32, 18, 9, 24, 11]
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
                  </script> -->
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
      <!-- add-teacher -->
      <div class="modal fade " id="add-teacher-modal" tabindex="-1">
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
                    "Enhance teaching with ekiliSense’s advanced tools. 
                    Easily onboard new teachers and explore features that 
                    support educational excellence."
                  </div>
                  
                </div>
                <div class="right">
                  <h1>Add a teacher</h1>
                  <p class="sub-heading">
                    Bringing Artificial intelligence closer to education
                  </p>
                  <form class="modal-form" id="teacher"  action="#" method="POST" enctype="multipart/form-data" autocomplete="off" >
                  <div class="error-text" style="
                        background-color: rgba(243, 89, 89, 0.562);
                        border:solid 1px rgba(243, 89, 89, 0.822);
                        color:#fff;
                        padding:6px;
                        border-radius:8px;">
                      </div>
                    <!-- TODO: add emojis to the plcaholder -->
                    <input type="hidden" name="form-type" value="teacher" >
                    <div class=" field input">
                      <input style="width: 100%;"  type="text" name="name"  placeholder="Teacher's full name " required>
                    </div>
                    <div class=" field input">
                      <input style="width: 100%;"  type="text" name="email"  placeholder="Teacher's email " required>
                    </div>
                    
                    <div class=" field input" >
                      <input style="width: 100%;" type="tel" name="mobile"  placeholder="Teacher's mobile" required>
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
      <!-- add class teacher -->
      <div class="modal fade " id="add-class-teacher-modal" tabindex="-1">
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
                    "Assign teachers to classes effortlessly. 
                    EkiliSense simplifies class management for a 
                    more organized educational environment."
                  </div>
                  
                </div>
                <div class="right">
                  <h1>Add a class teacher</h1>
                  <p class="sub-heading">
                    Bringing Artificial intelligence closer to education
                  </p>
                  <form class="modal-form" id="class-teacher"  action="#" method="POST" enctype="multipart/form-data" autocomplete="off" >
                  <div class="error-text" style="
                        background-color: rgba(243, 89, 89, 0.562);
                        border:solid 1px rgba(243, 89, 89, 0.822);
                        color:#fff;
                        padding:6px;
                        border-radius:8px;">
                      </div>
                    <!-- TODO: add emojis to the plcaholder -->
                    <input type="hidden" name="form-type" value="class-teacher" >
                    <div class=" field input">
                      <label for="choose-class">Select a class</label>
                      <select name="choosen-class" id="choose-class" required>
                        <?php
                          while($row_class = mysqli_fetch_array($get_classes)){
                            $class_name = $row_class['Class_name'];
                            echo "<option value='$class_name'>$class_name</option>";
                          }
                        ?>
                      </select>
                    </div>
                    <div class=" field input">
                      <label for="choose-class-teacher">Choose a teacher</label>
                      <select name="choosen-class-teacher" id="choose-class-teacher">
                        <?php
                          while($row_teacher = mysqli_fetch_array($get_teachers)){
                            $teacher_name = $row_teacher['teacher_fullname'];
                            echo "<option value='$teacher_name'>$teacher_name</option>";
                          }
                        ?>
                      </select>
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
      <!-- add-class -->
      <div class="modal fade " id="add-class-modal" tabindex="-1">
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
                    "Create and manage classes with ease. 
                    ekiliSense’s intuitive interface helps you 
                    set up and organize classes efficiently."
                  </div>
                  
                </div>
                <div class="right">
                  <h1>Create a class</h1>
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

    </section>
    
  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>Ekilie</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
    From <a href="https://tachera.com/Insights/">Insights</a>
    </div>
  </footer><!-- End Footer -->
  
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  
  <!-- Vendor JS Files -->
  <script>
    let assetsAt= "assets"
    const queryString = window.location.search
    const urlParams = new URLSearchParams(queryString)
    let roomId = urlParams.get('u')
    console.log(typeof(roomId))
    if(roomId=='new'){
      window.location.href = 'v1/'
    }
  </script>
  <script src="assets/js/modal-form.js"></script>

  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <script src="assets/js/main.js"></script>

</body>

</html>