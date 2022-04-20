<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
	header("location: login.php");
	exit;
} else if (isset($_SESSION["loggedin"]) && $_SESSION["admin"]) {
	header("location: admin.php");
	exit;
}

// Include config file
require_once "config.php";

// Declare variables
$fname = $lname = "";
$fname_err = $lname_err = $phonenum_err = $email_err = $address_err = $postalcode_err = $healthnum_err = "";
$fname_valid = $lname_valid = $phonenum_valid = $email_valid = $address_valid = $postalcode_valid = $healthnum_valid = "is-valid";
$old_password = $new_password = $confirm_new_password = $hashed_password = "";
$old_password_err = $new_password_err = $confirm_new_password_err = "Please fill in to change your password.";
$old_pass_validation = $new_pass_validation = $confirm_new_pass_validation = "is-invalid";
$old_pass_feedback = $new_pass_feedback = $confirm_new_pass_feedback = "invalid-feedback";
$phonenum = $biography = "";
$email = $phone_num = "";
$address = $postalcode = "";
$alert_color = $alert = "";
$pass_alert_color = $pass_alert = "";
$healthnum = "";
$id = "";
$error = "Oops! Something went wrong.";
$inputvalid = "is-valid";
$inputinvalid = "is-invalid";

// Select all from user database
$qry = "SELECT * FROM users WHERE id = :id";

// Prepare statement
if ($stmt = $pdo->prepare($qry)) {
	// Bind healthcard number to parameter
	$stmt->bindParam(":id", $param_id, PDO::PARAM_STR);

	// Set parameters
	$param_id = htmlspecialchars($_SESSION["id"]);

	// Attempt to execute query
	if ($stmt->execute()) {
		$userdata = $stmt->fetch();
		$healthnum = $userdata['username'];
		$id = $userdata['id'];
		$fname = $userdata['fname'];
		$lname = $userdata['lname'];
		$name = $fname . " " . $lname;
		$email = $userdata['email'];
		$phonenum = $userdata['phonenumber'];
		$address = $userdata['address'];
		$postalcode = $userdata['postalcode'];
		$biography = $userdata['biography'];
		$hashed_password = $userdata['PASSWORD'];
	} else {
		echo "Oops, something went wrong. Try again later.";
	}
	unset($stmt);
}
unset($qry);

// * Update database on form submit

// Determine which button is pressed
if (isset($_POST['save-profile'])) {
	// Validate first and last names
	if (empty(trim($_POST["fname"]))) {
		$fname_err = "Please enter your first name.";
		$fname_valid = $inputinvalid;
	} elseif (!preg_match('/^[a-zA-Z ]+$/', trim($_POST["fname"]))) {
		$fname_err = "First name can only contain letters.";
		$fname_valid = $inputinvalid;
	} else {
		$fname = trim($_POST["fname"]);
		$fname_err = "";
		$fname_valid = $inputvalid;
	}

	if (empty(trim($_POST["lname"]))) {
		$lname_err = "Please enter your last name.";
		$lname_valid = $inputinvalid;
	} elseif (!preg_match('/^[a-zA-Z ]+$/', trim($_POST["lname"]))) {
		$lname_err = "Last name can only contain letters.";
		$lname_valid = $inputinvalid;
	} else {
		$lname = trim($_POST["lname"]);
		$lname_err = "";
		$lname_valid = $inputvalid;
	}

	// Validate email address
	if (empty(trim($_POST["email"]))) {
		$email_err = "Please enter an email address.";
		$email_valid = $inputinvalid;
	} elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
		$email_err = "Email is invalid. Please enter a valid email address.";
		$email_valid = $inputinvalid;
	} else {
		$email = trim($_POST["email"]);
		$email_err = "";
		$email_valid = $inputvalid;
	}

	// Vaalidate phone number
	if (empty(trim($_POST["phonenum"]))) {
		$phonenum_err = "Please enter a phone number.";
		$phonenum_valid = $inputinvalid;
	} elseif (!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/", trim($_POST["phonenum"]))) {
		$phonenum_err = "Please enter a valid phone number.";
		$phonenum_valid = $inputinvalid;
	} else {
		$phonenum = trim($_POST["phonenum"]);
		$phonenum_err = "";
		$phonenum_valid = $inputvalid;
	}

	// Validate address
	if (empty(trim($_POST["address"]))) {
		$address_err = "Please enter an address.";
		$address_valid = $inputinvalid;
	} else {
		$address = trim($_POST["address"]);
		$address_err = "";
		$address_valid = $inputvalid;
	}

	// Validate postal code
	if (empty(trim($_POST["postalcode"]))) {
		$postalcode_err = "Please enter a postal code.";
		$postalcode_valid = $inputinvalid;
	} else {
		$postalcode = trim($_POST["postalcode"]);
		$postalcode_err = "";
		$postalcode_valid = $inputvalid;
	}

	// Validate healthnum
	if (empty(trim($_POST["healthnum"]))) {
		$healthnum_err = "Please enter a healthcard number.";
		$healthnum_valid = $inputinvalid;
	} elseif (!preg_match('/^[a-zA-Z0-9-]+$/', trim($_POST["healthnum"]))) {
		$healthnum_err = "Healthcard number can only contain numbers and hyphens.";
		$healthnum_valid = $inputinvalid;
	} elseif (!is_numeric(str_replace("-", "", $_POST["healthnum"])) || strlen(str_replace("-", "", $_POST["healthnum"])) != 10) {
		$healthnum_err = "Invalid healthcard number.";
		$healthnum_valid = $inputinvalid;
	} else {

		// Prepare a select statement
		$sql = "SELECT id FROM users WHERE username = :healthnum AND id NOT IN ( :id )";

		if ($stmt = $pdo->prepare($sql)) {
			// Bind variables to the prepared statement as parameters
			$stmt->bindParam(":healthnum", $param_healthnum, PDO::PARAM_STR);
			$stmt->bindParam(":id", $id, PDO::PARAM_INT);

			// Set parameters
			$param_healthnum = trim($_POST["healthnum"]);

			// Attempt to execute the prepared statement
			if ($stmt->execute()) {
				if ($stmt->rowCount() > 0) {
					$healthnum_err = "An existing account already has this healthcard number.";
					$healthnum_valid = $inputinvalid;
				} else {
					$healthnum = trim($_POST["healthnum"]);
					$healthnum_err = "";
					$healthnum_valid = $inputvalid;
				}
			} else {
				echo $alert;
			}
			// Close statement
			unset($stmt);
		}
	}

	// Save user inputs to variables
	$address = trim($_POST["address"]);
	$biography = $_POST["biography"];

	// * Check if there are any errors in user inputs
	if (empty($fname_err) && empty($lname_err) && empty($healthnum_err) && empty($email_err) && empty($address_err) && empty($phonenum_err) && empty($postalcode_err)) {
		// * Write query statement
		$sql = "UPDATE users
						SET fname = :fname, lname = :lname, username = :healthnum, email = :email, phonenumber = :phonenum, address = :address, postalcode = :postalcode, biography = :biography
						WHERE id = :id;
						UPDATE jvp
						SET healthnum = :healthnum, fname = :fname, lname = :lname
						WHERE id = :id;
						UPDATE medicalhistory
						SET healthnum = :healthnum, fname = :fname, lname = :lname
						WHERE id = :id;";

		if ($stmt = $pdo->prepare($sql)) {
			// * Bind parameters to variables
			$stmt->bindParam(":id", $param_id, PDO::PARAM_INT);
			$stmt->bindParam(":fname", $param_fname, PDO::PARAM_STR);
			$stmt->bindParam(":lname", $param_lname, PDO::PARAM_STR);
			$stmt->bindParam(":healthnum", $param_healthnum, PDO::PARAM_STR);
			$stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
			$stmt->bindParam(":phonenum", $param_phonenum, PDO::PARAM_STR);
			$stmt->bindParam(":address", $param_address, PDO::PARAM_STR);
			$stmt->bindParam(":postalcode", $param_postalcode, PDO::PARAM_STR);
			$stmt->bindParam(":biography", $param_biography, PDO::PARAM_STR);

			// * Give parameters values
			$param_id = $id;
			$param_fname = $fname;
			$param_lname = $lname;
			$param_healthnum = $healthnum;
			$param_email = $email;
			$param_phonenum = $phonenum;
			$param_address = $address;
			$param_postalcode = $postalcode;
			$param_biography = $biography;

			// * Attempt to execute query 
			if ($stmt->execute()) {
				// Show success of database update.
				$alert = "<strong>Success!</strong> Your changes have been saved!";
				$alert_color = "alert-success";

				$_SESSION["username"] = $healthnum;
				$_SESSION["fname"] = $fname;
				$_SESSION["lname"] = $lname;
			}
			unset($stmt);
		} else {
			echo $error;
		}
		unset($stmt);
	}
} else if (isset($_POST['change-password'])) {
	// * Give variables values
	$old_password = $_POST['old-password'];
	$new_password = $_POST['new-password'];
	$confirm_new_password = $_POST['confirm-password'];

	// Verify old password
	if (password_verify($old_password, $hashed_password)) {
		$old_password_err = "";
		$old_pass_validation = $inputvalid;
		echo "pp";
	} else {
		$old_password_err = "Password is incorrect.";
		$old_pass_validation = $inputinvalid;
	}

	// Validate new password
	if (strlen($new_password) < 6) {
		$new_password_err = 'Password must be at least 6 characters';
		$new_pass_validation = $inputinvalid;
	} else if (password_verify($new_password, $hashed_password)) {
		$new_password_err = 'Password cannot be your previous password';
		$new_pass_validation = $inputinvalid;
	} else {
		$new_password_err = "";
		$new_pass_validation = $inputvalid;
	}

	// Confirm that passwords match
	if ($new_password != $confirm_new_password) {
		$confirm_new_password_err = "Passwords do not match.";
		$confirm_new_pass_validation = $inputinvalid;
	} else {
		$confirm_new_password_err = "";
		$confirm_new_pass_validation = $inputvalid;
	}

	// Execute change password query if error variables are empty => means that all fields are valid
	if (empty($confirm_new_password_err) && empty($new_password_err) && empty($old_password_err)) {
		$sql = "UPDATE users
							SET PASSWORD = :oldpassword 
							WHERE id = :id";

		if ($stmt = $pdo->prepare($sql)) {
			// Bind parameters to variables
			$stmt->bindParam(':id', $param_id, PDO::PARAM_INT);
			$stmt->bindParam(':oldpassword', $param_password, PDO::PARAM_STR);

			// Give values to parameters
			$param_id = $id;
			$param_password = password_hash($new_password, PASSWORD_DEFAULT); // Hashes the new password

			// Attempt to execute query
			if ($stmt->execute()) {
				$pass_alert = "Your password has been changed!";
				$pass_alert_color = "alert-success";

				$old_password = $new_password = $confirm_new_password = "";
			}
		} else {
			echo "Oops! Something went wrong.";
		}
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
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
	<title>Account Details</title>
</head>

<body>
	<script>
		// JQuery function to autoformat healthcard number, phone number, and postal code
		(function($, undefined) {
			"use strict";

			// When ready.
			$(function() {

				var $form = $("#account-form");
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
			<h1>Account Details</h1>
		</div>

		<div class="col-sm-1">
			<div class="dropdown btn-group">
				<button type="button" class="theme-d4 dropdown-toggle prof-btn" data-bs-toggle="dropdown">
					My Profile
				</button>
				<ul class="dropdown-menu dropdown-menu-end theme-d3">
					<li>
						<a class="dropdown-item item" onmouseover="spin();" onmouseout="spin();">
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
		<a class="side-item" href="index.php"><em class="fa-solid fa-house"></em>Home</a>
		<a class="side-item"><em class="fa-regular fa-heart"></em>Medical History</a>
		<a class="side-item" href="jvp.php"><em class="fa-solid fa-heart-pulse"></em>Jugular Venous Pressure</a>
		<a class="side-item"><em class="fa-solid fa-phone"></em>Contact Us</a>
	</div>

	<div id="main-text">
		<div class="container border p-1">
			<div class="card-header">
				<h2>View and edit your account details</h2>
			</div>
			<div class="card-content p-6 row">
				<div class="col-md-6 p-4">
					<form method="post" id="account-form" class="needs-validation">
						<h3>Profile</h3>
						<div class="row">
							<div class="col-sm-6">
								<label>First Name:</label>
								<input type="text" name="fname" class="form-control <?= $fname_valid ?>" value="<?= $fname; ?>" required>
								<div class="valid-feedback"></div>
								<div class="invalid-feedback"><?= $fname_err ?></div>
							</div>

							<div class="col-sm-6">
								<label>Last Name:</label>
								<input type="text" name="lname" class="form-control <?= $lname_valid ?>" value="<?= $lname; ?>" required>
								<div class="valid-feedback"></div>
								<div class="invalid-feedback"><?= $lname_err ?></div>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-6">
								<label>Email Address:</label>
								<input type="email" name="email" class="form-control  <?= $email_valid ?>" value="<?= $email; ?>" placeholder="example@email.com" required>
								<div class="valid-feedback"></div>
								<div class="invalid-feedback"><?= $email_err ?></div>
							</div>

							<div class="col-sm-6">
								<label>Healthcard Number:</label>
								<input type="text" name="healthnum" id="healthnum" class="form-control <?= $healthnum_valid ?>" value="<?= $healthnum; ?>" maxlength="12" pattern="[0-9][0-9][0-9][0-9]-[0-9][0-9][0-9]-[0-9][0-9][0-9]" placeholder="ex. 1234-567-890" required>
								<div class="valid-feedback"></div>
								<div class="invalid-feedback"><?= $healthnum_err ?></div>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-6">
								<label>Phone Number:</label>
								<input type="text" name="phonenum" class="form-control <?= $phonenum_valid ?>" value="<?= $phonenum ?>" required>
								<div class="valid-feedback"></div>
								<div class="invalid-feedback"><?= $phonenum_err ?></div>
							</div>

							<div class="col-sm-6">
								<label>Postal Code:</label>
								<input type="text" id="postalcode" name="postalcode" class="form-control <?= $postalcode_valid ?>" value="<?= $postalcode; ?>" maxlength="7" pattern="[A-Za-z][0-9][A-Za-z] [0-9][A-Za-z][0-9]" placeholder="ex. A0A 0A0" required>
								<div class="valid-feedback"></div>
								<div class="invalid-feedback"><?= $postalcode_err ?></div>
							</div>
						</div>

						<div class="row g-1">
							<div class="col-sm">
								<label>Address:</label>
								<input type="text" name="address" class="form-control <?= $address_valid ?>" value="<?= $address ?>" required>
								<div class="valid-feedback"></div>
								<div class="invalid-feedback"><?= $address_err ?></div>
							</div>
						</div>

						<div class="row g-1">
							<label>Biography:</label>
							<textarea type="text" name="biography" class="form-control is-valid"><?= $biography; ?></textarea>
						</div>

						<br>

						<div class="input-group row g-1">
							<button type="submit" id="save-profile" name="save-profile" class="btn btn-primary col">
								Save Changes
							</button>
							<button type="reset" id="edit" name="edit" class="btn btn-secondary col">
								Reset
							</button>
						</div>

						<br>

						<div class="alert <?= $alert_color ?>">
							<?= $alert ?>
						</div>
					</form>
				</div>

				<div class="col-md-6 p-4 border-start">
					<form method="post" id="password-reset" class="needs-validation">
						<h3>Change your password</h3>
						<div class="row g-1">
							<label class="col-sm-6">Old Password:</label>
							<input type="password" class="form-control col-sm-6 <?= $old_pass_validation ?>" name="old-password" value="<?= $old_password ?>" required>
							<span class="valid-feedback">Password is correct!</span>
							<span class="invalid-feedback"><?= $old_password_err ?></span>
						</div>

						<div class="row g-1">
							<label class="col-sm-6">New Password:</label>
							<input type="password" class="form-control col-sm-6 <?= $new_pass_validation ?>" name="new-password" value="<?= $new_password ?>" required>
							<div class="valid-feedback">Password is valid!</div>
							<div class="invalid-feedback"><?= $new_password_err ?></div>
						</div>

						<div class="row g-1">
							<label class="col-sm-6">Confirm New Password:</label>
							<input type="password" class="form-control col-sm-6 <?= $confirm_new_pass_validation ?>" name="confirm-password" value="<?= $confirm_new_password ?>" required>
							<div class="valid-feedback">Password matches!</div>
							<div class="invalid-feedback"><?= $confirm_new_password_err ?></div>
						</div>

						<br>

						<div class="row mt-auto g-1" id="change-pass">
							<button class="btn btn-primary" name="change-password">CHANGE PASSWORD</button>
						</div>
						<br>
						<div class="alert <?= $pass_alert_color ?>">
							<?= $pass_alert ?>
						</div>
					</form>

				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
	</script>
</body>

</html>