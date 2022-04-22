<?php
// * Initialize the session
session_start();

require_once "config.php";

// * Check if the user is logged in, if not then redirect to login page
// * Validate admin access
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
} elseif (isset($_SESSION["loggedin"]) && !$_SESSION["admin"]) {
    header("location: index.php");
    exit;
}

// * Declare all necessary variables
$fname = $lname = $healthnum = $phonenum = $email = $postalcode = $address = $biography = "";
$fname_err = $lname_err = $phonenum_err = $email_err = $postalcode_err = $address_err = $biography_err = "";
$password = $confirmpassword = "";
$password_err = $confirmpassword_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $biography = $_POST["biography"];

    // Validate first and last name
    if (empty(trim($_POST["fname"]))) {
        $fname_err = "Please enter your first name.";
    } elseif (!preg_match('/^[a-zA-Z ]+$/', trim($_POST["fname"]))) {
        $fname_err = "First name can only contain letters.";
    } else {
        $fname = trim($_POST["fname"]);
    }

    if (empty(trim($_POST["lname"]))) {
        $lname_err = "Please enter your last name.";
    } elseif (!preg_match('/^[a-zA-Z ]+$/', trim($_POST["lname"]))) {
        $lname_err = "Last name can only contain letters.";
    } else {
        $lname = trim($_POST["lname"]);
    }

    // Validate email address
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email address.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Email is invalid. Please enter a valid email address.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Vaalidate phone number
    if (empty(trim($_POST["phonenum"]))) {
        $phonenum_err = "Please enter a phone number.";
    } elseif (!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/", trim($_POST["phonenum"]))) {
        $phonenum_err = "Please enter a valid phone number.";
    } else {
        $phonenum = trim($_POST["phonenum"]);
    }

    // Validate address
    if (empty(trim($_POST["address"]))) {
        $address_err = "Please enter an address.";
    } else {
        $address = trim($_POST["address"]);
    }

    // Validate postal code
    if (empty(trim($_POST["postalcode"]))) {
        $postalcode_err = "Please enter a postal code.";
    } else {
        $postalcode = trim($_POST["postalcode"]);
    }

    // Validate healthnum
    if (empty(trim($_POST["healthnum"]))) {
        $healthnum_err = "Please enter a healthcard number.";
    } elseif (!preg_match('/^[a-zA-Z0-9-]+$/', trim($_POST["healthnum"]))) {
        $healthnum_err = "Healthcard number can only contain numbers and hyphens.";
    } elseif (!is_numeric(str_replace("-", "", $_POST["healthnum"])) || strlen(str_replace("-", "", $_POST["healthnum"])) != 10) {
        $healthnum_err = "Invalid healthcard number.";
    } else {

    // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = :healthnum";

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":healthnum", $param_healthnum, PDO::PARAM_STR);

            // Set parameters
            $param_healthnum = trim($_POST["healthnum"]);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $healthnum_err = "An account with this healthcard number already exists.";
                } else {
                    $healthnum = trim($_POST["healthnum"]);
                }
            } else {
                echo $alert;
            }

            // Close statement
            unset($stmt);
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have atleast 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirmpassword"]))) {
        $confirmpassword_err = "Please confirm password.";
    } else {
        $confirmpassword = trim($_POST["confirmpassword"]);
        if (empty($password_err) && ($password != $confirmpassword)) {
            $confirmpassword_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err) && empty($confirmpassword_err) && empty($fname_err) && empty($lname_err) && empty($address_err) && empty($email_err) && empty($postalcode_err) && empty($phonenum_err)) {

    // Prepare insert statements for
        $sql = "INSERT INTO users (fname, lname, username, password, email, phonenumber, address, postalcode) 
            VALUES (:fname, :lname, :healthnum, :password, :email, :phonenum, :address, :postalcode);";

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":fname", $param_fname, PDO::PARAM_STR);
            $stmt->bindParam(":lname", $param_lname, PDO::PARAM_STR);
            $stmt->bindParam(":healthnum", $param_healthnum, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $stmt->bindParam(":phonenum", $param_phonenum, PDO::PARAM_STR);
            $stmt->bindParam(":address", $param_address, PDO::PARAM_STR);
            $stmt->bindParam(":postalcode", $param_postalcode, PDO::PARAM_STR);

            // Set parameters
            $param_fname = $fname;
            $param_lname = $lname;
            $param_healthnum = $healthnum;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_email = $email;
            $param_phonenum = $phonenum;
            $param_address = $address;
            $param_postalcode = $postalcode;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                echo "<script>alert('Patient account created!')</script>";
            } else {
                echo $alert;
            }
            // Close statement
            unset($stmt);
        }
    }
    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">

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
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://kit.fontawesome.com/e5e6e4ad79.js" crossorigin="anonymous"></script>
  <title>Add a Patient</title>
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
</head>

<body>
  <script>
    // JQuery function to autoformat healthcard number, phone number, and postal code
    (function($, undefined) {
      "use strict";

      // When ready.
      $(function() {

        var $form = $("#addpatient");
        var $input1 = $form.find("#healthnum");
        var $input2 = $form.find("#phonenum");
        var $input3 = $form.find("#postalcode");

        $input1.on("keyup", function(event) {

          // When user select text in the document, also abort.
          var selection = window.getSelection().toString();
          if (selection !== '') {
            return;
          }

          // When the arrow keys are pressed, abort.
          if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
            return;
          }

          var $this = $(this);
          var input = $this.val();
          input = input.replace(/[\W\D\s\._\-]+/g, '');

          var split = 4;
          var chunk = [];

          for (var i = 0, len = input.length; i < len; i += split) {
            split = (i >= 4 && i <= 16) ? 3 : 4;
            chunk.push(input.substr(i, split));
          }

          $this.val(function() {
            return chunk.join("-").toUpperCase();
          });

        });

        $input2.on("keyup", function(event) {

          // When user select text in the document, also abort.
          var selection = window.getSelection().toString();
          if (selection !== '') {
            return;
          }

          // When the arrow keys are pressed, abort.
          if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
            return;
          }

          var $this = $(this);
          var input = $this.val();
          input = input.replace(/[\W\D\s\._\-]+/g, '');

          var split = 4;
          var chunk = [];

          for (var i = 0, len = input.length; i < len; i += split) {
            split = (i >= 4 && i <= 16) ? 4 : 3;
            chunk.push(input.substr(i, split));
          }

          $this.val(function() {
            return chunk.join("-").toUpperCase();
          });

        });

        $input3.on("keyup", function(event) {

          // When user select text in the document, also abort.
          var selection = window.getSelection().toString();
          if (selection !== '') {
            return;
          }

          // When the arrow keys are pressed, abort.
          if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
            return;
          }

          var $this = $(this);
          var input = $this.val();
          input = input.replace(/[\W\s\._\-]+/g, '');

          var split = 4;
          var chunk = [];

          for (var i = 0, len = input.length; i < len; i += split) {
            split = (i >= 4 && i <= 16) ? 4 : 3;
            chunk.push(input.substr(i, split));
          }

          $this.val(function() {
            return chunk.join(" ").toUpperCase();
          });

        });
      });
    })(jQuery);
  </script>

  <div class="theme-d4 row" id="head">
    <div style="background-color: #294257; width:81px; cursor: pointer;" onclick="menuChange(this); menuToggle();">
      <div class="menu-cont">
        <div class="bar1"></div>
        <div class="bar2"></div>
        <div class="bar3"></div>
      </div>
    </div>

    <div class="title col">
      <h1>Add a Patient</h1>
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
    <a class="side-item hover-theme" href="admin.php"><em class="fa-solid fa-house"></em>Home</a>
    <a class="side-item hover-theme" href="patients.php"><em class="fa-solid fa-clipboard-user"></em>View Patients</a>
    <a class="side-item hover-theme"><em class="fa-solid fa-book-medical"></em>Add A Patient</a>
  </div>

  <div id="main-text">
    <div class="container border">
      <div class="card-header">
        <h1 class="text-center">Input patient information:</h1>
      </div>

      <form method="post" id="addpatient" class="p-3 needs-validation">
        <div class="row">
          <div class="col-sm-3">
            <label>First Name:</label>
            <input type="text" name="fname" class="form-control" value="<?= $fname; ?>" required>
          </div>

          <div class="col-sm-3">
            <label>Last Name:</label>
            <input type="text" name="lname" class="form-control" value="<?= $lname; ?>" required>
          </div>

          <div class="col-sm-3">
            <label>Health card number:</label>
            <input type="text" name="healthnum" id="healthnum" class="form-control" value="<?= $healthnum; ?>" maxlength="12" required>
          </div>

          <div class="col-sm-3">
            <label>Phone Number:</label>
            <input type="text" name="phonenum" id="phonenum" class="form-control" value="<?= $phonenum; ?>" maxlength="12" required>
          </div>
        </div>

        <br>
        <div class="row">
          <div class="col-sm-4">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" value="<?= $email; ?>" required>
          </div>

          <div class="col-sm-4">
            <label>Address:</label>
            <input type="text" name="address" class="form-control" value="<?= $address; ?>" required>
          </div>

          <div class="col-sm-4">
            <label>Postal Code:</label>
            <input type="text" name="postalcode" id="postalcode" class="form-control" value="<?= $postalcode; ?>" maxlength="7" required>
          </div>
        </div>
        <br>

        <div class="row">
          <div class="col">
            <label>Biography: </label>
            <textarea class="form-control col" name="biography" value="<?= $biography; ?>"></textarea>
          </div>
        </div>
        <br>
        <br>
        <div class="row border-bottom"></div>
        <br>

        <div class="row g-1">
          <div class="col-sm-5">
            <label>Password:</label>
            <input type="password" name="password" class="form-control" value="<?= $password ?>" required>
          </div>

          <div class="col-sm-2"></div>

          <div class="col-sm-5">
            <label>Confirm Password:</label>
            <input type="password" name="confirmpassword" class="form-control" value="<?= $confirmpassword ?>" required>
          </div>
        </div>

        <br>

        <div class="row g-1">
          <button type="submit" class="btn btn-primary col-sm-5">Create patient account</button>
          <div class="col-sm-2"></div>
          <button type="reset" class="btn btn-secondary col-sm-5">Reset</button>
        </div>
      </form>
    </div>
  </div>]
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
  </script>

</body>

</html>