<?php
// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = $login_err = "";
$fname = $lname = "";
$fname_err = $lname_err = "";
$email = $email_err = "";
$phonenum = $phonenum_err = "";
$address = $address_err = "";
$postalcode = $postalcode_err = "";
$alert = "Oops! Something went wrong. Please try again later.";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Validate first and last names
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
  if (empty(trim($_POST["username"]))) {
    $username_err = "Please enter a healthcard number.";
  } elseif (!preg_match('/^[a-zA-Z0-9-]+$/', trim($_POST["username"]))) {
    $username_err = "Healthcard number can only contain numbers and hyphens.";
  } elseif (!is_numeric(str_replace("-", "", $_POST["username"])) || strlen(str_replace("-", "", $_POST["username"])) != 10) {
    $username_err = "Invalid healthcard number.";
  } else {

    // Prepare a select statement
    $sql = "SELECT id FROM users WHERE username = :username";

    if ($stmt = $pdo->prepare($sql)) {
      // Bind variables to the prepared statement as parameters
      $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);

      // Set parameters
      $param_username = trim($_POST["username"]);

      // Attempt to execute the prepared statement
      if ($stmt->execute()) {
        if ($stmt->rowCount() == 1) {
          $username_err = "An account with this healthcard number already exists.";
        } else {
          $username = trim($_POST["username"]);
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
  if (empty(trim($_POST["confirm_password"]))) {
    $confirm_password_err = "Please confirm password.";
  } else {
    $confirm_password = trim($_POST["confirm_password"]);
    if (empty($password_err) && ($password != $confirm_password)) {
      $confirm_password_err = "Password did not match.";
    }
  }

  // Check input errors before inserting in database
  if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {

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
      $param_healthnum = $username;
      $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
      $param_email = $email;
      $param_phonenum = $phonenum;
      $param_address = $address;
      $param_postalcode = $postalcode;

      // Attempt to execute the prepared statement
      if ($stmt->execute()) {
        header("location: login.php");
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
  <meta charset="UTF-8">
  <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
  <link rel="manifest" href="favicon/site.webmanifest">
  <link rel="mask-icon" href="favicon/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">
  <title>Register</title>
  <link rel="stylesheet" href="loginstyle.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="theme.css">
  <script type="text/javascript" src="animations.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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

    body {
      font: 18px sans-serif;
    }

    .wrapper {
      width: 360px;
      padding: 20px;
    }
  </style>
</head>

<body>
  <script>
    (function ($, undefined) {
      "use strict";

      // When ready.
      $(function () {

        var $form = $("#registration");
        var $input1 = $form.find("#healthnum");
        var $input2 = $form.find("#phonenum");
        var $input3 = $form.find("#postalcode");

        $input1.on("keyup", function (event) {

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

          $this.val(function () {
            return chunk.join("-").toUpperCase();
          });

        });

        $input2.on("keyup", function (event) {

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

          $this.val(function () {
            return chunk.join("-").toUpperCase();
          });

        });

        $input3.on("keyup", function (event) {

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

          $this.val(function () {
            return chunk.join(" ").toUpperCase();
          });

        });
      });
    })(jQuery);
  </script>
  <div id="card" style="width: 700px;">
    <div id="card-content">
      <div id="card-title">
        <h2>Patient Portal</h2>
        <h2>REGISTRATION</h2>
        <div class="underline-title" style="width:260px;"></div>
      </div>

      <?php
      if (!empty($login_err)) {
        echo '<div class="alert alert-danger">' . $login_err . '</div>';
      }
      ?>

      <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="form" id="registration"
        name="registration">
        <div class="row">
          <div class="col">
            <label style="padding-top:7px" class="col">
              &nbsp;First Name:
            </label>
            <input type="text" name="fname" class="form-content col
                      <?= (!empty($fname_err)) ? 'is-invalid' : ''; ?>" value="<?= $fname; ?>"
              pattern="[a-zA-Z ]{1,}" required>
            <span class="invalid-feedback"><?= $fname_err; ?></span>
            <div class="form-border"></div>
          </div>

          <div class="col">
            <label style="padding-top:7px" class="col">
              &nbsp;Last Name:
            </label>
            <input type="text" name="lname" class="form-content col
                      <?= (!empty($lname_err)) ? 'is-invalid' : ''; ?>" value="<?= $lname; ?>"
              pattern="[a-zA-Z]{1,}" required>
            <span class="invalid-feedback"><?= $lname_err; ?></span>
            <div class="form-border"></div>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <label style="padding-top:7px" class="col">
              &nbsp;Health Card Number
            </label>
            <input type="text" id="healthnum" name="username" class="form-content col
                      <?= (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?= $username; ?>"
              placeholder="ex. 1234-567-890" maxlength="12"
              pattern="[0-9][0-9][0-9][0-9]-[0-9][0-9][0-9]-[0-9][0-9][0-9]" required>
            <span class="invalid-feedback"><?= $username_err; ?></span>
            <div class="form-border"></div>
          </div>

          <div class="col">
            <label style="padding-top:7px" class="col">
              &nbsp;Phone Number:
            </label>
            <input type="tel" id="phonenum" name="phonenum" class="form-content col
                      <?= (!empty($phonenum_err)) ? 'is-invalid' : ''; ?>" value="<?= $phonenum; ?>"
              placeholder="ex. 123-456-7890" maxlength="12" pattern="\d{3}[\-]\d{3}[\-]\d{4}" required>
            <span class="invalid-feedback"><?= $phonenum_err; ?></span>
            <div class="form-border"></div>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <label style="padding-top:7px" class="col">
              &nbsp;Email:
            </label>
            <input type="email" name="email" class="form-content col
                      <?= (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?= $email; ?>" required>
            <span class="invalid-feedback"><?= $email_err; ?></span>
            <div class="form-border"></div>
          </div>

          <div class="col">
            <label style="padding-top:7px" class="col">
              &nbsp;Postal Code
            </label>
            <input type="text" id="postalcode" name="postalcode" class="form-content col
                      <?= (!empty($postalcode_err)) ? 'is-invalid' : ''; ?>" value="<?= $postalcode; ?>"
              pattern="[A-Za-z][0-9][A-Za-z] [0-9][A-Za-z][0-9]" placeholder="ex. A0A 0A0" maxlength="7" required>
            <span class="invalid-feedback"><?= $postalcode_err; ?></span>
            <div class="form-border"></div>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <label style="padding-top:7px" class="col">
              &nbsp;Address
            </label>
            <input type="text" name="address" class="form-content col
                      <?= (!empty($address_err)) ? 'is-invalid' : ''; ?>" value="<?= $address; ?>"
              required>
            <span class="invalid-feedback"><?= $address_err; ?></span>
            <div class="form-border"></div>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <label style="padding-top:7px" class="col">
              &nbsp;Password
            </label>
            <input type="password" name="password" class="form-content col
                      <?= (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?= $password; ?>"
              required>
            <span class="invalid-feedback"><?= $password_err; ?></span>
            <div class="form-border"></div>
          </div>

          <div class="col">
            <label style="padding-top:7px" class="col">
              &nbsp;Confirm Password
            </label>
            <input type="password" name="confirm_password" class="form-content col
                      <?= (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>"
              value="<?= $confirm_password; ?>" required>
            <span class="invalid-feedback"><?= $confirm_password_err; ?></span>
            <div class="form-border"></div>
          </div>
        </div>

        <br>
        <div style="display: inline;">
          <input type="submit" class="btn btn-primary next" value="SUBMIT" style="width: 49%;">
          <input type="reset" class="btn btn-secondary ml-2" value="RESET" style="width: 49%; float: right;">
        </div>

        <br />
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
      </form>
    </div>
  </div>
</body>

</html>