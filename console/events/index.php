<?php
session_start();
include_once "../../config.php";
include_once "../functions/timeAgo.php";
include_once "../../middlwares/school_auth.php";

// Get upcoming and past events
$upcoming_events = mysqli_query($conn, "
  SELECT * FROM school_events 
  WHERE school_uid = '$school_uid' 
  AND event_date >= CURDATE()
  ORDER BY event_date ASC, event_time ASC
  LIMIT 20
");

$past_events = mysqli_query($conn, "
  SELECT * FROM school_events 
  WHERE school_uid = '$school_uid' 
  AND event_date < CURDATE()
  ORDER BY event_date DESC
  LIMIT 10
");

// Get event statistics
$event_stats = mysqli_query($conn, "
  SELECT 
    COUNT(*) as total_events,
    SUM(CASE WHEN event_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming,
    SUM(CASE WHEN event_date < CURDATE() THEN 1 ELSE 0 END) as past
  FROM school_events
  WHERE school_uid = '$school_uid'
");
$stats = mysqli_fetch_assoc($event_stats);

// Get event registrations count
$registration_counts = mysqli_query($conn, "
  SELECT e.event_id, e.event_title, COUNT(er.registration_id) as registration_count
  FROM school_events e
  LEFT JOIN event_registrations er ON e.event_id = er.event_id
  WHERE e.school_uid = '$school_uid' AND e.event_date >= CURDATE()
  GROUP BY e.event_id, e.event_title
  ORDER BY e.event_date ASC
  LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Events & Calendar | ekiliSense</title>
  
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
  <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="../assets/css/custom.css" rel="stylesheet">
</head>

<body>

  <!-- ======= Header ======= -->
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
            <li class="dropdown-header">
              <h6><?=$school['School_name']?></h6>
              <span>ekiliSense</span>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="../profile.php">
                <i class="bi bi-person"></i>
                <span>Profile</span>
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="../logout.php?ref=<?=$school_uid?>">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
  </header>

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
        <a class="nav-link collapsed" href="../teachers/">
          <i class="bi bi-people"></i>
          <span>Teachers</span>
        </a>
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
          <span>Performance</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../reports/">
          <i class="bi bi-bar-chart"></i>
          <span>Reports & Analytics</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../attendance/">
          <i class="bi bi-calendar-check"></i>
          <span>Attendance</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./">
          <i class="bi bi-calendar-event"></i>
          <span>Events & Calendar</span>
        </a>
      </li>
      <li class="nav-heading">Pages</li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../profile.php">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li>
    </ul>
  </aside>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1><i class="bi bi-calendar-event"></i> Events & Calendar</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="../">Home</a></li>
          <li class="breadcrumb-item active">Events</li>
        </ol>
      </nav>
    </div>

    <section class="section">
      
      <!-- Action Buttons -->
      <div class="row mb-3">
        <div class="col-lg-12">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
            <i class="bi bi-plus-circle"></i> Add New Event
          </button>
        </div>
      </div>

      <!-- Event Statistics -->
      <div class="row">
        <div class="col-xxl-4 col-md-6">
          <div class="card info-card sales-card">
            <div class="card-body">
              <h5 class="card-title">Total Events</h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                  <i class="bi bi-calendar-event"></i>
                </div>
                <div class="ps-3">
                  <h6><?=$stats['total_events'] ?? 0?></h6>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xxl-4 col-md-6">
          <div class="card info-card revenue-card">
            <div class="card-body">
              <h5 class="card-title">Upcoming Events</h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                  <i class="bi bi-calendar-plus"></i>
                </div>
                <div class="ps-3">
                  <h6><?=$stats['upcoming'] ?? 0?></h6>
                  <span class="text-success small">Scheduled</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xxl-4 col-md-6">
          <div class="card info-card customers-card">
            <div class="card-body">
              <h5 class="card-title">Past Events</h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                  <i class="bi bi-calendar-check"></i>
                </div>
                <div class="ps-3">
                  <h6><?=$stats['past'] ?? 0?></h6>
                  <span class="text-muted small">Completed</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Upcoming Events -->
      <div class="row">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Upcoming Events</h5>
              
              <?php if(mysqli_num_rows($upcoming_events) > 0): ?>
                <div class="activity">
                  <?php while($event = mysqli_fetch_assoc($upcoming_events)): 
                    $days_until = floor((strtotime($event['event_date']) - time()) / (60 * 60 * 24));
                    $urgency_class = $days_until <= 3 ? 'text-danger' : ($days_until <= 7 ? 'text-warning' : 'text-primary');
                  ?>
                  <div class="activity-item d-flex">
                    <div class="activite-label <?=$urgency_class?>" style="width: 100px;">
                      <?=date('M d, Y', strtotime($event['event_date']))?>
                    </div>
                    <i class='bi bi-circle-fill activity-badge <?=$urgency_class?> align-self-start'></i>
                    <div class="activity-content">
                      <strong><?=$event['event_title']?></strong>
                      <?php if($event['event_type']): ?>
                        <span class="badge bg-secondary"><?=ucfirst($event['event_type'])?></span>
                      <?php endif; ?>
                      <p class="text-muted mb-1"><?=$event['description']?></p>
                      <?php if($event['location']): ?>
                        <small class="text-muted"><i class="bi bi-geo-alt"></i> <?=$event['location']?></small>
                      <?php endif; ?>
                      <?php if($event['event_time']): ?>
                        <small class="text-muted ms-2"><i class="bi bi-clock"></i> <?=date('g:i A', strtotime($event['event_time']))?></small>
                      <?php endif; ?>
                      <div class="mt-2">
                        <button class="btn btn-sm btn-primary" onclick="editEvent(<?=$event['event_id']?>)">
                          <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteEvent(<?=$event['event_id']?>)">
                          <i class="bi bi-trash"></i> Delete
                        </button>
                      </div>
                    </div>
                  </div>
                  <?php endwhile; ?>
                </div>
              <?php else: ?>
                <div class="alert alert-info">
                  <i class="bi bi-info-circle"></i> No upcoming events scheduled.
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Event Registrations -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Event Registrations</h5>
              
              <?php if(mysqli_num_rows($registration_counts) > 0): ?>
                <ul class="list-group">
                  <?php while($reg = mysqli_fetch_assoc($registration_counts)): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?=substr($reg['event_title'], 0, 30)?>...
                    <span class="badge bg-primary rounded-pill"><?=$reg['registration_count']?></span>
                  </li>
                  <?php endwhile; ?>
                </ul>
              <?php else: ?>
                <p class="text-muted">No registrations yet.</p>
              <?php endif; ?>
            </div>
          </div>

          <!-- Quick Calendar View -->
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">This Month</h5>
              <div class="text-center">
                <h2 class="text-primary"><?=date('F Y')?></h2>
                <p class="text-muted">View full calendar coming soon</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Past Events -->
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Past Events</h5>
              
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Event Title</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  if(mysqli_num_rows($past_events) > 0):
                    while($event = mysqli_fetch_assoc($past_events)): 
                  ?>
                  <tr>
                    <td><?=date('M d, Y', strtotime($event['event_date']))?></td>
                    <td><?=$event['event_title']?></td>
                    <td><span class="badge bg-secondary"><?=ucfirst($event['event_type'] ?? 'General')?></span></td>
                    <td><?=$event['location'] ?? '-'?></td>
                    <td>
                      <button class="btn btn-sm btn-info" title="View Details">
                        <i class="bi bi-eye"></i>
                      </button>
                    </td>
                  </tr>
                  <?php 
                    endwhile;
                  else:
                  ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted">No past events to display.</td>
                  </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </section>
  </main>

  <!-- Add Event Modal -->
  <div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="addEventForm" action="server/add_event.php" method="POST">
            <input type="hidden" name="school_uid" value="<?=$school_uid?>">
            
            <div class="mb-3">
              <label for="event_title" class="form-label">Event Title *</label>
              <input type="text" class="form-control" id="event_title" name="event_title" required>
            </div>

            <div class="mb-3">
              <label for="event_type" class="form-label">Event Type</label>
              <select class="form-select" id="event_type" name="event_type">
                <option value="general">General</option>
                <option value="academic">Academic</option>
                <option value="sports">Sports</option>
                <option value="cultural">Cultural</option>
                <option value="meeting">Meeting</option>
                <option value="holiday">Holiday</option>
                <option value="exam">Exam</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="event_date" class="form-label">Event Date *</label>
                <input type="date" class="form-control" id="event_date" name="event_date" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="event_time" class="form-label">Event Time</label>
                <input type="time" class="form-control" id="event_time" name="event_time">
              </div>
            </div>

            <div class="mb-3">
              <label for="location" class="form-label">Location</label>
              <input type="text" class="form-control" id="location" name="location" placeholder="e.g., School Hall, Sports Field">
            </div>

            <div class="mb-3">
              <label for="organizer" class="form-label">Organizer</label>
              <input type="text" class="form-control" id="organizer" name="organizer" placeholder="e.g., Academic Department">
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="requires_registration" name="requires_registration" value="1">
              <label class="form-check-label" for="requires_registration">
                Requires Registration
              </label>
            </div>

          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" form="addEventForm" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Event
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>ekiliSense</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      From <a href="https://ekilie.com">ekilie</a>
    </div>
  </footer>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../assets/js/main.js"></script>

  <script>
    function editEvent(eventId) {
      alert('Edit functionality coming soon for event ID: ' + eventId);
    }

    function deleteEvent(eventId) {
      if(confirm('Are you sure you want to delete this event?')) {
        window.location.href = 'server/delete_event.php?event_id=' + eventId;
      }
    }

    // Set minimum date for event creation to today
    document.getElementById('event_date').min = new Date().toISOString().split('T')[0];
  </script>

</body>
</html>
