<?php
session_start();

// Include config file
require_once "config.php";

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: login.php");
  exit;
} else if (isset($_SESSION["loggedin"]) && $_SESSION["admin"]) {
  header("location: admin.php");
  exit;
}

// Declare variables
$fname = htmlspecialchars($_SESSION["fname"]);
$lname = htmlspecialchars($_SESSION["lname"]);
$name = $fname . " " . $lname;
$hrt = $wgt = "";
$hrt_err = $wgt_err = "";
$id = htmlspecialchars($_SESSION["id"]);
$comments = "";
$healthnum = htmlspecialchars($_SESSION["username"]);
$alert_color = $alert = "";
$alert_msg = "Oops! Something went wrong.";
$numeric_err = "This value can only contain numbers.";
$serialize_csv = "";
$jvp = $jvp_err = "";

// Write query to select JVP info for 
$qry = "SELECT * FROM jvp WHERE id = :id";

if ($stmt = $pdo->prepare($qry)) {
  // Bind healthcard number to parameter
  $stmt->bindParam(":id", $param_id, PDO::PARAM_STR);

  // Set parameters
  $param_id = $id;

  // Attempt to execute query
  if ($stmt->execute()) {
    $userdata = $stmt->fetch();
    $wgt = $userdata['weight'] ?? "";
    $hrt = $userdata['heartrate'] ?? "";
    $comments = $userdata['comments'] ?? "";
    $jvp = $userdata['jvp'] ?? "";

    $csv_arr = array();
    $csv_arr[] = array('Healthcard Number', 'First Name', 'Last Name', 'Weight', 'Heart Rate', 'JVP', 'Comments');
    $csv_arr[] = array($healthnum, $fname, $lname, $wgt, $hrt, $jvp, $comments);
  } else {
    echo "Oops, something went wrong. Try again later.";
  }
  unset($stmt);
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Validate heart rate
  if (empty(trim($_POST["hrt"]))) {
    $hrt_err = "Please enter a heart rate value.";
  } else if (!is_numeric($_POST["hrt"])) {
    $hrt_err = $numeric_err;
  } else {
    $hrt = trim($_POST["hrt"]);
  }

  // Validate weight input
  if (empty(trim($_POST["wgt"]))) {
    $wgt_err = "Please enter a weight value.";
  } else if (!is_numeric($_POST["wgt"])) {
    $wgt_err = $numeric_err;
  } else {
    $wgt = trim($_POST["wgt"]);
  }

  if (empty(trim($_POST["jvp"]))) {
    $jvp_err = "Please enter a jvp value.";
  } else if (!is_numeric($_POST["jvp"])) {
    $jvp_err = $numeric_err;
  } else {
    $jvp = trim($_POST["jvp"]);
  }

  $comments = trim($_POST["comments"]);

  // Insert title of columns into csv array
  $csv_arr = array();
  $csv_arr[] = array('Healthcard Number', 'First Name', 'Last Name', 'Weight', 'Heart Rate', 'JVP', 'Comments');
  $csv_arr[] = array($healthnum, $fname, $lname, $wgt, $hrt, $jvp, $comments);

  // Prepare query statement to update JVP info in database
  $sql = "INSERT INTO jvp (id, healthnum, fname, lname, weight, heartrate, jvp, comments)
          VALUES (:id, :healthnum, :fname, :lname, :wgt, :hrt, :jvp, :comments)
          ON DUPLICATE KEY UPDATE
					healthnum = :healthnum, fname = :fname, lname = :lname, weight = :wgt, heartrate = :hrt, jvp = :jvp, comments = :comments";

  if ($stmt = $pdo->prepare($sql)) {
    // Bind variables to parameters
    $stmt->bindParam(":wgt", $param_wgt, PDO::PARAM_STR);
    $stmt->bindParam(":hrt", $param_hrt, PDO::PARAM_INT);
    $stmt->bindParam(":comments", $param_comments, PDO::PARAM_STR);
    $stmt->bindParam(":id", $param_id, PDO::PARAM_STR);
    $stmt->bindParam(":jvp", $param_jvp, PDO::PARAM_STR);
    $stmt->bindParam(":fname", $param_fname, PDO::PARAM_STR);
    $stmt->bindParam(":lname", $param_lname, PDO::PARAM_STR);
    $stmt->bindParam(":healthnum", $param_healthnum, PDO::PARAM_STR);

    // Set parameters
    $param_healthnum = $healthnum;
    $param_fname = $fname;
    $param_lname = $lname;
    $param_wgt = $wgt;
    $param_hrt = $hrt;
    $param_comments = $comments;
    $param_jvp = $jvp;
    $param_id = $id;

    // Attempt to execute query
    if ($stmt->execute()) {
      // Show success of database update
      $alert = "<strong>Success!</strong> Your changes have been saved!";
      $alert_color = "alert-success";

      // Create .csv file, store in patientcsv folder
      $filename = $fname . $lname . ".csv";
      $target_dir = "patientjvpcsv/";
      $target_file = $target_dir . $filename;

      $file = fopen($target_file, "w") or die("Unable to open file!");

      foreach ($csv_arr as $line) {
        fputcsv($file, $line);
      }
      fclose($file);
    } else {
      echo "<strong>Oops!</strong> Something went wrong. Please try again later.";
      $alert = $alert_msg;
      $alert_color = "alert-danger";
    }

    // Close statement
    unset($stmt);
  }
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
  <title>Jugular Venous Pressure</title>
</head>

<body>
  <!-- Top bar -->
  <div class="theme-d4 row" id="head">
    <div style="background-color: #294257; width:81px; cursor: pointer;" onclick="menuChange(this); menuToggle();">
      <div class="menu-cont">
        <div class="bar1"></div>
        <div class="bar2"></div>
        <div class="bar3"></div>
      </div>
    </div>

    <div class="title col">
      <h1>Jugular Venous Pressure</h1>
    </div>

    <!-- Account dropdown -->
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

  <!-- Sidebar -->
  <div id="sidebar" class="theme-d3">
    <a class="side-item" href="index.php"><em class="fa-solid fa-house"></em>Home</a>
    <a class="side-item" href="medhis.php"><em class="fa-regular fa-heart"></em>Medical History</a>
    <a class="side-item"><em class="fa-solid fa-heart-pulse"></em>Jugular Venous Pressure</a>
    <a class="side-item" href="contact-info.php"><em class="fa-solid fa-phone"></em>Contact Us</a>
  </div>

  <!-- Main body of page -->
  <div id="main-text">
    <div class="p-3 container border">
      <h2>Welcome, <?= $name . " (" . $healthnum . ")" ?>.</h2>
      <form id="jvp-form" class="g-2" method="post">
        <div class="row">
          <div class="col">
            <label>Heart Rate:
              <input type="number" class="form-control" name="hrt" id="hrt" value="<?= $hrt ?>" min="0" required>
            </label>
          </div>

          <div class="col">
            <label>Weight (kg):
              <input type="number" class="form-control" name="wgt" id="wgt" value="<?= $wgt ?>" min="0" required>
            </label>
          </div>
        </div>

        <div class="row">
          <label class="col">Comments:
            <textarea name="comments" class="form-control" id="comm"><?= $comments ?></textarea>
          </label>
        </div>

        <div class="row">
          <div class="col">
            <label>JVP:
              <input type="number" class="form-control" id="jvp" name="jvp" value="<?= $jvp ?>">
            </label>
          </div>

          <div class="col">
          </div>
        </div>

        <div class="row">
          <div class="col">
            <button type="submit" class="btn btn-primary" id="submit">SAVE CHANGES</button>
          </div>

          <div class="col">
            <button type="reset" class="btn btn-secondary" id="reset">RESET</button>
          </div>
        </div>
      </form>
      <br>
      <!-- Show alert when saved -->
      <div class="alert <?= $alert_color ?>">
        <?= $alert ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
  </script>
</body>

</html>