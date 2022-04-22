<?php
// * Initialize the session
session_start();

// * Check if the user is logged in, if not then redirect to login page
// * Validate admin access
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
} elseif (isset($_SESSION["loggedin"]) && $_SESSION["admin"]) {
    header("location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
  <link rel="manifest" href="favicon/site.webmanifest">
  <link rel="mask-icon" href="favicon/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="theme.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="site.css">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">
  <script type="text/javascript" src="animations.js"></script>
  <script src="https://kit.fontawesome.com/e5e6e4ad79.js" crossorigin="anonymous"></script>
  <style>
    html,
    body,
    form,
    fieldset,
    table,
    tr,
    td,
    img,
    h1 {
      font-family: 'Open Sans', sans-serif;
    }
  </style>
  <title>Contact us!</title>
</head>

<body>


  <div class="theme-d4 row" id="head">
    <div style="background-color: #294257; width:81px; cursor: pointer;" onclick="menuChange(this); menuToggle();">
      <div class="menu-cont">
        <div class="bar1"></div>
        <div class="bar2"></div>
        <div class="bar3"></div>
      </div>
    </div>

    <div class="title col">
      <h1>Contact Us</h1>
    </div>

    <div class="col-sm-1">
      <div class="dropdown btn-group">
        <button type="button" class="theme-d4 dropdown-toggle prof-btn" data-bs-toggle="dropdown">
          My Profile
        </button>
        <ul class="dropdown-menu dropdown-menu-end theme-d3">
          <li>
            <a class="dropdown-item item" href="account-info.php" onmouseover="spin();" onmouseout="spin();">
              <em class="fa-solid fa-gear" id="settings"></em>
              Update Profile
            </a>
          </li>
          <li>
            <a class="dropdown-item item" href="logout.php">
              <em class="fa-solid fa-right-from-bracket"></em> Log Out
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div id="sidebar" class="theme-d3">
    <a class="side-item hover-theme" href="index.php"><em class="fa-solid fa-house"></em>Home</a>
    <a class="side-item hover-theme" href="medhis.php"><em class="fa-regular fa-heart"></em>Medical History</a>
    <a class="side-item hover-theme" href="jvp.php"><em class="fa-solid fa-heart-pulse"></em>Jugular Venous Pressure</a>
    <a class="side-item hover-theme"><em class="fa-solid fa-phone"></em>Contact Us</a>
    <a class="side-item hover-theme" href="cardiac-rehab.php"><em class="fa-solid fa-hand-holding-heart"></em>Cardiac Rehab</a>
  </div>

  <div id="main-text">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <h2>Hours:</h2>
          <p>Monday - Friday: 8:00 a.m - 6:00 p.m</p>
        </div>
        <div class="col-md-6">

        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
  </script>
</body>

</html>