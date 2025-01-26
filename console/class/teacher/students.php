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

  <title>ekiliSense | <?= $school["School_name"] ?> | Students</title>
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

  <?php
  include_once "./includes/topbar.php";
  $page = "students";
  include_once "./includes/sidebar.php";
  ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Students <i class="bi bi-arrow-right-short"> </i> <?= $class_info[
          "Class_name"
      ] ?></h1>
    </div><!-- End Page Title -->


    <section class="section">
        <div class="row">
          <div class="col-lg-12">

            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Table of all students in <?= $class_info[
                    "Class_name"
                ] ?> class</h5>


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
                      <th>Action </th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $n = 1;
                    while ($row_std = mysqli_fetch_array($students)) { ?>

                        <tr>
                          <td><?= $n ?></td>
                          <td><?= $row_std["student_first_name"] ?></td>
                          <td><?= $row_std["student_last_name"] ?></td>
                          <td><?= $row_std["parent_phone"]
                              ? $row_std["parent_phone"]
                              : "None" ?></td>
                          <td><?= $row_std["parent_email"] ?></td>
                          <td><?= timeAgo(
                              strtotime($row_std["created_at"])
                          ) ?></td>
                          <td>
                            <a href="./view/student.php?stid=<?= $row_std[
                                "student_id"
                            ] ?>"  class="btn btn-secondary">
                              Manage
                            </a>
                          </td>
                        </tr>

                    <?php $n++;}
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
    From <a href="https://tachera.com/Insights/">ekilie</a>
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../../assets/js/modal-form.js"></script>

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
