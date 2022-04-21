<!-- Form to create a new admin account -->

<?php
// Initialize the session 
session_start();

// Include config file
require_once "config.php";

if (!isset($_SESSION['adminaccess']) || !$_SESSION['adminaccess']) {
	header('Location: request-admin.php');
} else if (!$_SESSION['adminaccess']) {
	header('Location: index.php');
}

// Declare variables 
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";
$error_msg = "Oops! Something went wrong. Please try again.";

// Carry out action when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Validate username
	if (empty(trim($_POST['username']))) {
		$username_err = "Please enter a username.";
	} elseif (!preg_match('/^[a-zA-Z0-9]+$/', trim($_POST["username"]))) {
		$username_err = "Username can only contain letters and numbers.";
	} else {
		// Prepare a select statement
		$sql = "SELECT id FROM admin WHERE username = :username";

		if ($stmt = $pdo->prepare($sql)) {
			// Bind variables to the prepared statement as parameters
			$stmt->bindParam(":username", $param_username, PDO::PARAM_STR);

			// Set parameters
			$param_username = trim($_POST["username"]);

			// Attempt to execute the prepared statement
			if ($stmt->execute()) {
				if ($stmt->rowCount() > 0) {
					$username_err = "An account with this username already exists.";
				} else {
					$username = trim($_POST["username"]);
				}
			} else {
				echo $alert;
			}

			// Close statement
			unset($stmt);
			unset($sql);
		}
	}

	// Validate password
	if (empty(trim($_POST['password']))) {
		$password_err = "Please enter a password.";
	} elseif (strlen(trim($_POST['password'])) < 6) {
		$password_err = "Password must be at least 6 characters.";
	} else {
		$password = trim($_POST['password']);
	}

	// Validate confirm password
	if (empty(trim($_POST["confirmpassword"]))) {
		$confirm_password_err = "Please confirm password.";
	} else {
		$confirm_password = trim($_POST["confirmpassword"]);
		if (empty($password_err) && ($password != $confirm_password)) {
			$confirm_password_err = "Password did not match.";
		}
	}

	// Check input errors before inserting in database
	if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
		// Prepare insert statement
		$sql = "INSERT INTO admin (username, password)
						VALUES (:username, :password)";

		if ($stmt = $pdo->prepare($sql)) {
			// Bind variables to parameters
			$stmt->bindParam(':username', $param_username, PDO::PARAM_STR);
			$stmt->bindParam(':password', $param_password, PDO::PARAM_STR);

			// Give values to parameters
			$param_username = $username;
			$param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

			// Attempt to execute query
			if ($stmt->execute()) {
				// Redirect to login page
				header('location: login.php');
				session_destroy();
			} else {
				echo $error_msg;
			}
		} else {
			echo $error_msg;
		}
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
	<title>Create an Admin Account</title>
</head>

<body>
	<div id="main align-items-center">
		<div id="card">
			<div id="card-content">
				<div id="card-title">
					<h2>Admin Portal</h2>
					<h2>Create Account</h2>
					<div class="underline-title"></div>
				</div>

				<form method="post" class="form" id="login" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
					<label style="padding-top:13px">
						&nbsp;Username
					</label>
					<input id="username" class="form-content
                        <?= (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?= $username; ?>" type="text" name="username" required>
					<span class="invalid-feedback"><?= $username_err; ?></span>
					<div class="form-border"></div>

					<label style="padding-top:22px">
						&nbsp;Password
					</label>
					<input id="password" class="form-content
                        <?= (!empty($password_err)) ? 'is-invalid' : ''; ?>" type="password" name="password" required>
					<span class="invalid-feedback"><?= $password_err; ?></span>
					<div class="form-border"></div>

					<label style="padding-top:22px">
						&nbsp;Confirm Password
					</label>
					<input id="confirmpassword" class="form-content
                        <?= (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" type="password" name="confirmpassword" required>
					<span class="invalid-feedback"><?= $confirm_password_err; ?></span>
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