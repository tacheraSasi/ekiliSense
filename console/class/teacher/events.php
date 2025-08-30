<?php
session_start();
include_once "../../../config.php";
include_once "../../../middlwares/teacher_auth.php";

// Get events for this school
$events_query = mysqli_query($conn, "SELECT se.*, 
    COUNT(er.registration_id) as registration_count
    FROM school_events se
    LEFT JOIN event_registrations er ON se.event_uid = er.event_uid
    WHERE se.school_uid = '$school_uid' 
    AND (se.target_audience = 'all' OR se.target_audience = 'teachers' 
         OR (se.target_audience = 'specific_class' AND se.class_id = '$class_id'))
    GROUP BY se.event_id
    ORDER BY se.event_date ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ekiliSense | <?= $school["School_name"] ?> | Events</title>

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

  <?php include_once "./includes/topbar.php"; ?>
  <?php
  $page = "events";
  include_once "./includes/sidebar.php";
  ?>

  <main id="main" class="main">
    <div class="d-flex justify-content-between flex-wrap" style="margin:1rem auto;">
      <div class="pagetitle" style="display:inline-block">
        <h1 style="display:inline-block">School Events <i class="bi bi-arrow-right-short"> </i> <?= $class_info["Class_name"] ?></h1>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
        <i class="bi bi-plus-circle"></i> Create Event
      </button>
    </div>

    <section class="section">
      <div class="row">
        <!-- Event Statistics -->
        <div class="col-lg-12">
          <div class="row">
            <div class="col-xxl-3 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Total Events</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= mysqli_num_rows($events_query) ?></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-3 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Upcoming Events</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-clock"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $upcoming_count = 0;
                        mysqli_data_seek($events_query, 0);
                        while($event = mysqli_fetch_array($events_query)) {
                          if($event['status'] == 'scheduled' && $event['event_date'] >= date('Y-m-d')) {
                            $upcoming_count++;
                          }
                        }
                        echo $upcoming_count;
                        ?>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-3 col-md-6">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Active Events</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-play-circle"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $active_count = 0;
                        mysqli_data_seek($events_query, 0);
                        while($event = mysqli_fetch_array($events_query)) {
                          if($event['status'] == 'active') {
                            $active_count++;
                          }
                        }
                        echo $active_count;
                        ?>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-3 col-md-6">
              <div class="card info-card">
                <div class="card-body">
                  <h5 class="card-title">Total Registrations</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6>
                        <?php
                        $total_registrations = 0;
                        mysqli_data_seek($events_query, 0);
                        while($event = mysqli_fetch_array($events_query)) {
                          $total_registrations += $event['registration_count'];
                        }
                        echo $total_registrations;
                        ?>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Events List -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">School Events</h5>

              <div class="row">
                <?php
                mysqli_data_seek($events_query, 0);
                while ($event = mysqli_fetch_array($events_query)) {
                  // Determine status color
                  $status_class = 'primary';
                  if($event['status'] == 'completed') $status_class = 'success';
                  if($event['status'] == 'cancelled') $status_class = 'danger';
                  if($event['status'] == 'active') $status_class = 'warning';
                  
                  // Determine event type icon
                  $type_icon = 'calendar-event';
                  if($event['event_type'] == 'sports') $type_icon = 'trophy';
                  if($event['event_type'] == 'cultural') $type_icon = 'music-note';
                  if($event['event_type'] == 'meeting') $type_icon = 'people';
                  if($event['event_type'] == 'announcement') $type_icon = 'megaphone';
                ?>
                  <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                          <span class="badge bg-<?= $status_class ?>"><?= ucfirst($event['status']) ?></span>
                          <i class="bi bi-<?= $type_icon ?> text-<?= $status_class ?>"></i>
                        </div>
                        
                        <h6 class="card-title"><?= htmlspecialchars($event['event_title']) ?></h6>
                        <p class="card-text text-muted small">
                          <?= substr(htmlspecialchars($event['event_description']), 0, 100) ?>
                          <?= strlen($event['event_description']) > 100 ? '...' : '' ?>
                        </p>
                        
                        <div class="event-details">
                          <p class="mb-1">
                            <i class="bi bi-calendar"></i> 
                            <?= date('M d, Y', strtotime($event['event_date'])) ?>
                          </p>
                          <?php if($event['start_time']): ?>
                            <p class="mb-1">
                              <i class="bi bi-clock"></i> 
                              <?= date('h:i A', strtotime($event['start_time'])) ?>
                              <?= $event['end_time'] ? ' - ' . date('h:i A', strtotime($event['end_time'])) : '' ?>
                            </p>
                          <?php endif; ?>
                          <?php if($event['location']): ?>
                            <p class="mb-1">
                              <i class="bi bi-geo-alt"></i> 
                              <?= htmlspecialchars($event['location']) ?>
                            </p>
                          <?php endif; ?>
                          <p class="mb-1">
                            <i class="bi bi-tag"></i> 
                            <?= ucfirst($event['event_type']) ?>
                          </p>
                          <?php if($event['registration_required']): ?>
                            <p class="mb-1">
                              <i class="bi bi-people"></i> 
                              <?= $event['registration_count'] ?> registered
                              <?= $event['max_participants'] ? ' / ' . $event['max_participants'] : '' ?>
                            </p>
                          <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                          <?php if($event['registration_required'] && $event['status'] == 'scheduled'): ?>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="registerForEvent('<?= $event['event_uid'] ?>')">
                              <i class="bi bi-person-plus"></i> Register
                            </button>
                          <?php endif; ?>
                          <a href="event_details.php?id=<?= $event['event_uid'] ?>" 
                             class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> View Details
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              </div>

              <?php if(mysqli_num_rows($events_query) == 0): ?>
                <div class="text-center py-5">
                  <i class="bi bi-calendar-x" style="font-size: 3rem; color: #ccc;"></i>
                  <p class="mt-3 text-muted">No events scheduled yet. Create the first event!</p>
                </div>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- Create Event Modal -->
  <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addEventModalLabel">Create New Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addEventForm" method="POST" action="server/events.php">
          <div class="modal-body">
            <div class="error-text" style="
                  background-color: rgba(243, 89, 89, 0.562);
                  border:solid 1px rgba(243, 89, 89, 0.822);
                  color:#fff;
                  padding:6px;
                  border-radius:8px;
                  display:none;">
            </div>
            
            <input type="hidden" name="form-type" value="create-event">
            <input type="hidden" name="created_by" value="<?= $teacher_id ?>">
            
            <div class="row">
              <div class="col-md-12 mb-3">
                <label for="event_title" class="form-label">Event Title *</label>
                <input type="text" class="form-control" name="event_title" required placeholder="e.g., Science Fair 2024">
              </div>
              
              <div class="col-md-12 mb-3">
                <label for="event_description" class="form-label">Description</label>
                <textarea class="form-control" name="event_description" rows="3" placeholder="Event description and details..."></textarea>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="event_type" class="form-label">Event Type</label>
                <select class="form-select" name="event_type">
                  <option value="academic">Academic</option>
                  <option value="sports">Sports</option>
                  <option value="cultural">Cultural</option>
                  <option value="meeting">Meeting</option>
                  <option value="announcement">Announcement</option>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="target_audience" class="form-label">Target Audience</label>
                <select class="form-select" name="target_audience" onchange="toggleClassSelection()">
                  <option value="all">All</option>
                  <option value="students">Students</option>
                  <option value="teachers">Teachers</option>
                  <option value="parents">Parents</option>
                  <option value="specific_class">Specific Class</option>
                </select>
              </div>
              
              <div class="col-md-12 mb-3" id="class_selection" style="display:none;">
                <label for="class_id" class="form-label">Select Class</label>
                <select class="form-select" name="class_id">
                  <option value="<?= $class_id ?>"><?= $class_info["Class_name"] ?></option>
                </select>
              </div>
              
              <div class="col-md-4 mb-3">
                <label for="event_date" class="form-label">Event Date *</label>
                <input type="date" class="form-control" name="event_date" required min="<?= date('Y-m-d') ?>">
              </div>
              
              <div class="col-md-4 mb-3">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="time" class="form-control" name="start_time">
              </div>
              
              <div class="col-md-4 mb-3">
                <label for="end_time" class="form-label">End Time</label>
                <input type="time" class="form-control" name="end_time">
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" name="location" placeholder="e.g., School Auditorium">
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="max_participants" class="form-label">Max Participants</label>
                <input type="number" class="form-control" name="max_participants" min="1" placeholder="Leave empty for unlimited">
              </div>
              
              <div class="col-md-12 mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="registration_required" id="registration_required">
                  <label class="form-check-label" for="registration_required">
                    Require Registration
                  </label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="submitEvent">Create Event</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../../assets/vendor/echarts/echarts.min.js"></script>
  <script src="../../assets/vendor/quill/quill.min.js"></script>
  <script src="../../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../../assets/vendor/php-email-form/validate.js"></script>

  <script src="../../assets/js/main.js"></script>
  <script src="js/events.js"></script>

  <script>
    function toggleClassSelection() {
      const targetAudience = document.querySelector('select[name="target_audience"]').value;
      const classSelection = document.getElementById('class_selection');
      
      if (targetAudience === 'specific_class') {
        classSelection.style.display = 'block';
      } else {
        classSelection.style.display = 'none';
      }
    }
    
    function registerForEvent(eventUid) {
      if (confirm('Register for this event?')) {
        // Implementation for event registration
        fetch('server/events.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `form-type=register-event&event_uid=${eventUid}&participant_type=teacher&participant_id=<?= $teacher_id ?>`
        })
        .then(response => response.text())
        .then(data => {
          if(data === 'success') {
            alert('Registration successful!');
            location.reload();
          } else {
            alert('Error: ' + data);
          }
        });
      }
    }
  </script>

</body>

</html>