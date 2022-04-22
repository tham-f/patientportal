<?php
// Include config file
require_once "config.php";

// Declare variables
$password = "";
$password_err = "";
$alert = "";

// Write query to fetch admin password
$sql = "SELECT password FROM admin WHERE id = 1";

// Prepare and execute query
$stmt = $pdo->prepare($sql);
$stmt->execute();
$hashed_password = $stmt->fetch()[0];

// When form is submitted, verify password, redirect to appropriate page
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST["password"];

    // Verify password with stored password
    if (password_verify($password, $hashed_password)) {
        $alert = "";
        // Password is correct, initialize the session
        session_start();

        $_SESSION["adminaccess"] = true;
        // Redirect user to create-admin page
        header("location: create-admin.php");
    } else {
        // Password is not valid
        $alert = "<div class='alert alert-danger' role='alert'>
                Password is incorrect.
              </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

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
  <title>Enter the Admin Password</title>
</head>

<body>
  <div id="main align-items-center">
    <div id="card">
      <div id="card-content">
        <div id="card-title">
          <h2>Admin Portal</h2>
          <h2>Enter the Admin Password</h2>
          <div class="underline-title"></div>
        </div>

        <?= $alert ?>

        <form method="post" class="form" id="login" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
          <input id="password" class="form-content
                        <?= (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?= $password; ?>" type="password" name="password" required>
          <span class="invalid-feedback"><?= $password_err; ?></span>
          <div class="form-border"></div>

          <a href="login.php" id="login">
            <legend id="forgot-pass">Have an account? Login.</legend>
          </a>

          <input type="submit" id="submit-btn" name="create-account" value="SUBMIT">
        </form>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
  </script>
</body>

</html>