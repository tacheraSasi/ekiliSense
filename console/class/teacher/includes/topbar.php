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
        <!-- <li class="nav-item">
          <button id="ekilie-ai-btn" class=" nav-link nav-icon d-flex align-items-center justify-content-center" 
              data-bs-toggle="modal" data-bs-target="#modalAI-ekiliSense"
              style="border:none;outline:none;border-radius:50%">
              <i class="bi bi-robot"></i>
          </button>
        </li> -->

        <li class="nav-item">
          <button id="connect-google"  class="d-flex align-items-center gap-2 mx-2  btn btn-secondary" >
              <i class="bi bi-google"></i>  
              Connect with google
          </button>
        </li>
        <?php 
          // require '../../../parseEnv.php';
          // parseEnv('../../../.env');

        ?>
        
        <script> 
          document.getElementById("connect-google").addEventListener("click",()=>{
            const OAuthGoogleUrl = "https://sense.ekilie.com/OAuth/google.php"
            window.location.href =OAuthGoogleUrl
          })
        </script>

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="<?=$isConnectedToGoogle?$google_data['picture_url']:'../../../assets/img/user.png'?>" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?=$school['School_name']?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?=$school['School_name']?></h6>
              <span><?=$teacher_name?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="profile.php">
                <i class="bi bi-person"></i>
                <span>Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="server/logout.php?logout_id=<?php echo $teacher_email?>">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->
