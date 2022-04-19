<?php
// ! THIS NEEDS A MAIL SERVER TO FUNCTION
// ! HERE IS A WEBSITE WHERE YOU CAN SET ONE UP:
// ! https://sendpulse.com/features/smtp

// Initialize the session
session_start();

// Include config file
require_once "config.php";

// Define variables, initialize with empty values
$email = $healthnum = "";

// Process form data when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $healthnum = trim($_POST["healthnum"]);
  $email = trim($_POST["email"]);

  // * Check if email and healthcard number exist and are of same user

  // Prepare sql update query
  $query = "SELECT * FROM users WHERE username = :healthnum AND email = :email";

  if ($stmt = $pdo->prepare($query)) {
    // Bind paramters to prepared statement
    $stmt->bindParam(":healthnum", $param_healthnum, PDO::PARAM_STR);
    $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);

    // Set parameters
    $param_healthnum = $healthnum;
    $param_email = $email;

    // Attempt to execute statement
    if ($stmt->execute()) {
      // Check if row exists
      if ($stmt->rowCount() > 0) {
        // Row exists
        // Fetch healthcard number and email
        $userdata = $stmt->fetchAll();
        $var_healthnum = $userdata[0]["username"];
        $var_email = $userdata[0]["email"];
        $temp_pass = bin2hex(random_bytes(10));
        $hash_pass = password_hash($temp_pass, PASSWORD_DEFAULT);

        // Send email to user with a temporary password
        mail($var_email, 
            "Patient Portal Password Reset", 
            "You have recently requested a password reset. \n
             Your temporary password is : " . $temp_pass
            );

        // Write query to update password to temp password
        $qry = "UPDATE users
                SET password = '$hash_pass'
                WHERE username = '$var_healthnum'";
        
        // Execute query
        $stmt = $pdo->prepare($qry);
        $stmt->execute();
      } else {
        //
      }
    } else {
      echo "Oops! Something went wrong. Please try again later.";
    }

    // Close statement
    unset($stmt);
  }
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
    (function($, undefined) {
      "use strict";

      // When ready.
      $(function() {

        var $form = $("#reset");
        var $input = $form.find("#healthnum");

        $input.on("keyup", function(event) {

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
      });
    })(jQuery);
  </script>
  <div id="main align-items-center">
    <div id="card">
      <div id="card-content">
        <div id="card-title">
          <h2>RESET PASSWORD</h2>
          <div class="underline-title"></div>
        </div>
        <form method="post" id="reset">
          <div class="form-group">
            <label>Email Address:</label>
            <input type="email" name="email" class="form-content" value="<?= $email; ?>">
            <div class="form-border"></div>
          </div>
          <br>
          <div class="form-group">
            <label>Healthcard Number:</label>
            <input type="text" id="healthnum" name="healthnum" class="form-content" value="<?= $healthnum; ?>" maxlength="12">
            <div class="form-border"></div>
          </div>
          <br>
          <div class="form-group mx-auto">
            <input type="submit" class="btn btn-primary" value="Send Email">
            <a class="btn btn-link ml-2" href="login.php">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>

</html>