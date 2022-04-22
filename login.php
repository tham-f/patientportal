<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["admin"]) {
    header("location: admin.php");
    exit;
} elseif (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

session_destroy();

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter health card number.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (strlen($username) < 12) {
        $username_err = "Health card number is not complete!";
    } elseif (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT * FROM users WHERE username = :username";

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);

            // Set parameters
            $param_username = trim($_POST["username"]);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Check if username exists, if yes then verify password
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetchAll()) {
                        $id = $row[0]["id"];
                        $username = $row[0]["username"];
                        $fname = $row[0]["fname"];
                        $lname = $row[0]["lname"];
                        $hashed_password = $row[0]["PASSWORD"];
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["admin"] = false;
                            $_SESSION["fname"] = $fname;
                            $_SESSION["lname"] = $lname;

                            // Redirect user to welcome page
                            header("location: index.php");
                        } else {
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid healthcard number or password!";
                        }
                    }
                } else {
                    // username doesn't exist, display a generic error message
                    $login_err = "Invalid healthcard number or password!";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
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
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
  <link rel="manifest" href="favicon/site.webmanifest">
  <link rel="mask-icon" href="favicon/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">
  <link rel="stylesheet" href="loginstyle.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
  </style>
  <title>Login to your Account</title>
</head>

<body>
  <script>
    (function ($, undefined) {
      "use strict";

      // When ready.
      $(function () {

        var $form = $("#login");
        var $input = $form.find("#healthnum");

        $input.on("keyup", function (event) {

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
      });
    })(jQuery);
  </script>
  <div id="main align-items-center">
    <button type="button" id="admin" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#adminlogin">Admin
      Login</button>

    <div class="modal" id="adminlogin">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

          <!-- Modal Header -->
          <div class="modal-header">
            <h4 class="modal-title">Admin Login</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <!-- Modal body -->
          <div class="modal-body">
            <form method="post" action="admin-login.php" class="form-horizontal">
              <div class="form-floating mb-3 mt-3">
                <input type="text" class="form-control" id="adminusername" placeholder="Admin Username"
                  name="adminusername">
                <label for="adminusername">Admin Username</label>
              </div>

              <div class="form-floating mb-3 mt-3">
                <input type="password" class="form-control" id="adminpassword" placeholder="Password"
                  name="adminpassword">
                <label for="adminpassword">Password</label>
              </div>

              <button type="submit" class="btn btn-primary">Login</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div id="card">
      <div id="card-content">
        <div id="card-title">
          <h2>Patient Portal</h2>
          <h2>LOGIN</h2>
          <div class="underline-title"></div>
        </div>

        <?php
        if (!empty($login_err)) {
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>

        <form method="post" class="form" id="login" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
          <label style="padding-top:13px">
            &nbsp;Health Card Number
          </label>
          <input id="healthnum" class="form-content
                        <?= (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?= $username; ?>"
            type="text" name="username" autocomplete="on" placeholder="ex. 1234-567-890" maxlength="12" required>
          <span class="invalid-feedback"><?= $username_err; ?></span>
          <div class="form-border"></div>

          <label style="padding-top:22px">
            &nbsp;Password
          </label>
          <input id="password" class="form-content
                        <?= (!empty($password_err)) ? 'is-invalid' : ''; ?>" type="password" name="password"
            required>
          <span class="invalid-feedback"><?= $password_err; ?></span>
          <div class="form-border"></div>

          <a href="reset-password.php" id="change-pass">
            <legend id="forgot-pass">Forgot password?</legend>
          </a>

          <input type="submit" id="submit-btn" name="login" value="LOGIN">
          <a href="register.php" id="signup">Don't have account yet?</a>
        </form>
      </div>
    </div>
  </div>
</body>

</html>