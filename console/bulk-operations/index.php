<?php
session_start();
include_once "../../config.php";
include_once "../../middlwares/school_auth.php";

// Get recent import/export history
$recent_operations = mysqli_query($conn, "
  SELECT * FROM bulk_operations 
  WHERE school_uid = '$school_uid' 
  ORDER BY created_at DESC 
  LIMIT 10
") or null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Bulk Operations | ekiliSense</title>
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="icon">
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i" rel="stylesheet">
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
      <a href="/console/" class="logo d-flex align-items-center">
        <span class="d-none d-lg-block">ekiliSense</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>
    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="../assets/img/school-1.png" alt="Profile" class="">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?=$school['School_name']?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header"><h6><?=$school['School_name']?></h6><span>ekiliSense</span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="../profile.php"><i class="bi bi-person"></i><span>Profile</span></a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="../logout.php?ref=<?=$school_uid?>"><i class="bi bi-box-arrow-right"></i><span>Sign Out</span></a></li>
          </ul>
        </li>
      </ul>
    </nav>
  </header>
  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav">
      <li class="nav-item"><a class="nav-link collapsed" href="../"><i class="bi bi-grid"></i><span>Home</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="../teachers/"><i class="bi bi-people"></i><span>Teachers</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="../classes/"><i class="bi bi-buildings"></i><span>Classes</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="../reports/"><i class="bi bi-bar-chart"></i><span>Reports</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="../attendance/"><i class="bi bi-calendar-check"></i><span>Attendance</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="../events/"><i class="bi bi-calendar-event"></i><span>Events</span></a></li>
      <li class="nav-heading">Management</li>
      <li class="nav-item"><a class="nav-link" href="./"><i class="bi bi-arrow-repeat"></i><span>Bulk Operations</span></a></li>
      <li class="nav-item"><a class="nav-link collapsed" href="../settings/"><i class="bi bi-gear"></i><span>Settings</span></a></li>
    </ul>
  </aside>
  <main id="main" class="main">
    <div class="pagetitle">
      <h1><i class="bi bi-arrow-repeat"></i> Bulk Operations</h1>
      <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="../">Home</a></li><li class="breadcrumb-item active">Bulk Operations</li></ol></nav>
    </div>
    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> <strong>Bulk Operations</strong> allow you to import, export, and manage data in bulk. Use CSV or Excel files for efficient data management.
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Import Data</h5>
              <p class="text-muted">Upload CSV or Excel files to import data into the system</p>
              <div class="list-group">
                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
                        data-bs-toggle="modal" data-bs-target="#importTeachersModal">
                  <span><i class="bi bi-people-fill text-primary"></i> Import Teachers</span>
                  <i class="bi bi-chevron-right"></i>
                </button>
                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                        data-bs-toggle="modal" data-bs-target="#importStudentsModal">
                  <span><i class="bi bi-person-fill text-success"></i> Import Students</span>
                  <i class="bi bi-chevron-right"></i>
                </button>
                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                        data-bs-toggle="modal" data-bs-target="#importClassesModal">
                  <span><i class="bi bi-building text-info"></i> Import Classes</span>
                  <i class="bi bi-chevron-right"></i>
                </button>
                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                        data-bs-toggle="modal" data-bs-target="#importSubjectsModal">
                  <span><i class="bi bi-book text-warning"></i> Import Subjects</span>
                  <i class="bi bi-chevron-right"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Export Data</h5>
              <p class="text-muted">Download your data in CSV or Excel format</p>
              <div class="list-group">
                <a href="server/export.php?type=teachers" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-download text-primary"></i> Export Teachers (<?=$teachers_count?>)</span>
                  <i class="bi bi-chevron-right"></i>
                </a>
                <a href="server/export.php?type=students" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-download text-success"></i> Export Students (<?=$students_count?>)</span>
                  <i class="bi bi-chevron-right"></i>
                </a>
                <a href="server/export.php?type=classes" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-download text-info"></i> Export Classes (<?=$classes_count?>)</span>
                  <i class="bi bi-chevron-right"></i>
                </a>
                <a href="server/export.php?type=attendance" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-download text-warning"></i> Export Attendance Records</span>
                  <i class="bi bi-chevron-right"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Batch Operations</h5>
              <div class="row">
                <div class="col-md-4">
                  <div class="card text-center">
                    <div class="card-body">
                      <i class="bi bi-envelope" style="font-size: 2.5rem; color: #4154f1;"></i>
                      <h6 class="mt-3">Send Bulk Messages</h6>
                      <p class="text-muted small">Send emails to multiple recipients</p>
                      <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#bulkMessageModal">
                        <i class="bi bi-send"></i> Send Messages
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card text-center">
                    <div class="card-body">
                      <i class="bi bi-trash" style="font-size: 2.5rem; color: #dc3545;"></i>
                      <h6 class="mt-3">Bulk Delete</h6>
                      <p class="text-muted small">Delete multiple records at once</p>
                      <button class="btn btn-sm btn-danger" onclick="alert('Bulk delete feature coming soon')">
                        <i class="bi bi-trash"></i> Delete Records
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card text-center">
                    <div class="card-body">
                      <i class="bi bi-arrow-up-circle" style="font-size: 2.5rem; color: #28a745;"></i>
                      <h6 class="mt-3">Bulk Update</h6>
                      <p class="text-muted small">Update multiple records simultaneously</p>
                      <button class="btn btn-sm btn-success" onclick="alert('Bulk update feature coming soon')">
                        <i class="bi bi-arrow-up"></i> Update Records
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Download Templates</h5>
              <p>Download CSV templates to ensure proper data formatting for imports</p>
              <div class="btn-group" role="group">
                <a href="../assets/templates/teachers_template.csv" class="btn btn-outline-primary" download>
                  <i class="bi bi-file-earmark-spreadsheet"></i> Teachers Template
                </a>
                <a href="../assets/templates/students_template.csv" class="btn btn-outline-success" download>
                  <i class="bi bi-file-earmark-spreadsheet"></i> Students Template
                </a>
                <a href="../assets/templates/classes_template.csv" class="btn btn-outline-info" download>
                  <i class="bi bi-file-earmark-spreadsheet"></i> Classes Template
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Import Teachers Modal -->
  <div class="modal fade" id="importTeachersModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Import Teachers</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form action="../server/import-teachers.php" method="POST" enctype="multipart/form-data" id="importTeachersForm">
            <input type="hidden" name="school_uid" value="<?=$school_uid?>">
            <div class="mb-3">
              <label class="form-label">Select CSV File</label>
              <input type="file" class="form-control" name="file" accept=".csv,.xlsx" required>
              <small class="text-muted">Upload a CSV or Excel file with teacher data</small>
            </div>
            <div class="alert alert-info">
              <strong>Required columns:</strong> teacher_fullname, teacher_email, teacher_phone
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" form="importTeachersForm" class="btn btn-primary">
            <i class="bi bi-upload"></i> Import Teachers
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Import Students Modal -->
  <div class="modal fade" id="importStudentsModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Import Students</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form action="server/import-students.php" method="POST" enctype="multipart/form-data" id="importStudentsForm">
            <input type="hidden" name="school_uid" value="<?=$school_uid?>">
            <div class="mb-3">
              <label class="form-label">Select CSV File</label>
              <input type="file" class="form-control" name="file" accept=".csv,.xlsx" required>
            </div>
            <div class="alert alert-info">
              <strong>Required columns:</strong> student_name, student_email, class_id
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" form="importStudentsForm" class="btn btn-primary">
            <i class="bi bi-upload"></i> Import Students
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bulk Message Modal -->
  <div class="modal fade" id="bulkMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Send Bulk Messages</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="bulkMessageForm">
            <div class="mb-3">
              <label class="form-label">Recipients</label>
              <select class="form-select" name="recipients" required>
                <option value="">Select Recipients</option>
                <option value="all_teachers">All Teachers (<?=$teachers_count?>)</option>
                <option value="all_students">All Students (<?=$students_count?>)</option>
                <option value="all_parents">All Parents</option>
                <option value="specific_class">Specific Class</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Message Type</label>
              <select class="form-select" name="message_type" required>
                <option value="email">Email</option>
                <option value="sms">SMS</option>
                <option value="notification">In-App Notification</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Subject</label>
              <input type="text" class="form-control" name="subject" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Message</label>
              <textarea class="form-control" name="message" rows="5" required></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" form="bulkMessageForm" class="btn btn-primary">
            <i class="bi bi-send"></i> Send Messages
          </button>
        </div>
      </div>
    </div>
  </div>

  <footer id="footer" class="footer">
    <div class="copyright">&copy; Copyright <strong><span>ekiliSense</span></strong>. All Rights Reserved</div>
    <div class="credits">From <a href="https://ekilie.com">ekilie</a></div>
  </footer>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
  <script>
    document.getElementById('importTeachersForm')?.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch(this.action, {method: 'POST', body: formData})
        .then(r => r.text())
        .then(data => {
          alert(data.includes('success') ? 'Teachers imported successfully!' : 'Import failed: ' + data);
          if(data.includes('success')) window.location.reload();
        });
    });
  </script>
</body>
</html>
