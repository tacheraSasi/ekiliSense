<?php
if (isset($_SESSION['School_uid'])) {
  header("location:../console");
}

if (isset($_GET['from'])) {
  header("location:desktop.html");
}
// var_dump($_SESSION);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Sign in to your ekiliSense Console.">

  <meta name="keywords" content="Sign in to ekiliSense, SaaS, online learning, AI,ekili, ekilie,ekiliSense,ekilieconvo management system, education, Tanzania">

  <meta name="author" content="EkiliSense - Empowering learning with AI">

  <meta property="og:title" content="EkiliSense - Empowering Education with AI">
  <meta property="og:description" content="Explore EkiliSense, an AI-powered SaaS platform revolutionizing online learning management. Join the community for insightful discussions and innovative educational experiences.">
  <meta property="og:image" content="https://www.ekilie.com/assets/img/favicon.jpeg">
  <meta property="og:url" content="https://www.ekilie.com">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="EkiliSense - Empowering Education with AI">
  <meta name="twitter:description" content="Explore EkiliSense, an AI-powered SaaS platform revolutionizing online learning management. Join the community for insightful discussions and innovative educational experiences.">
  <meta name="twitter:image" content="https://www.ekilie.com/assets/img/favicon.jpeg">
  <!-- Favicons -->
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="icon">
  <link href="https://www.ekilie.com/assets/img/favicon.jpeg" rel="apple-touch-icon">

  <meta name="robots" content="index, follow">

  <meta name="googlebot" content="index, follow">

  <meta name="application-name" content="EkiliSense">
  <meta name="msapplication-TileColor" content="#201f1f">
  <meta name="msapplication-TileImage" content="https://www.ekilie.com/assets/img/favicon.jpeg">

  <meta name="creator" content="Tachera W. Sasi">
  <meta name="ceo" content="Tachera W. Sasi">

  <link rel="canonical" href="https://www.ekilie.com">
  <link rel="icon" href="https://www.ekilie.com/assets/img/favicon.jpeg" type="image/png">
  <link rel="apple-touch-icon" href="https://www.ekilie.com/assets/img/favicon.jpeg">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,300;0,400;0,500;1,100;1,400;1,600;1,800&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="../assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/auth/auth.css">
  <title>ekiliSense Login</title>

</head>

<body>
  <div class="login">
    <div class="card">
      <div class="left">
        <div class="top-container">
          <div style="
          display: flex;
          justify-content: flex-start;">
            <div class="logo-container">
              <img src="../assets/img/favicon.jpeg" alt="" class="logo">
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
        <h1>Sign in</h1>
        <p class="sub-heading">
          Unlock the Gates of Insight:
          Traverse the Digital Landscapes of Tomorrow's Learning Odyssey
        </p>
        <form action="#" method="POST" enctype="multipart/form-data" autocomplete="off" class="create-groove-form">
          <div class="error-text" style="
              background-color: rgba(243, 89, 89, 0.562);
              border:solid 1px rgba(243, 89, 89, 0.822);
              color:#fff;
              padding:6px;
              border-radius:8px">
          </div>
          <div class=" field input">
            <input style="width: 100%;" type="text" name="email" placeholder="email" required>
          </div>
          <div class=" field input">
            <input style="width: 100%;" type="password" name="password" placeholder="password" required>
          </div>


          <div class="input-container field button">
            <button id="submit" title="sign in " type="submit">Sign in</button>
          </div>
          <div class="link" style="color:lightgrey">Haven't joined ekiliSense yet?
            <a href="../onboarding/" style="color:#33995d;text-decoration:none">
              Join
            </a>
          </div>

          <div class="link" style="color:lightgrey">Forgot your password?
            <a href="../onboarding/" style="color:#33995d;text-decoration:none">
              Click here to reset
            </a>
          </div>

        </form>
        <!-- <div class="google">
          <button><i class="bi bi-google"> </i> Continue with google</button>
        </div> -->
      </div>
    </div>
  </div>

  <script>
    // JavaScript logic
    document.addEventListener("DOMContentLoaded", function() {
      const typingTextElement = document.getElementById("typingText");
      const originalText = "Utilize management with ekiliSense for better productivity and ultimate performance";
      let currentIndex = 0;

      function typingAnimation() {
        typingTextElement.textContent = originalText.substring(0, currentIndex);
        currentIndex++;

        if (currentIndex <= originalText.length) {
          setTimeout(typingAnimation, 150);
        }
      }

      typingAnimation();
    });
  </script>
  <script src="/auth/js/login.js"></script>
</body>

</html>