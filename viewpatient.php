<?php
// Start session 
session_start();

// Include config file  
require_once "config.php";

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
	header("location: login.php");
	exit;
} else if (isset($_SESSION["loggedin"]) && !$_SESSION["admin"]) {
	header("location: index.php");
	exit;
}

// Declare variables 
$fname = $lname = $gender = $bday = "";
$id = $healthnum = "";
$email = $phonenum = $address = $postalcode = "";
$biography = "Biography here...";
$account_created = "";
$id = "";
$error = "Oops! Something went wrong.";
$inputvalid = "is-valid";
$inputinvalid = "is-invalid";
$selected = " selected";

if (is_numeric(trim($_GET['PatientID']))) {
	$id = trim($_GET['PatientID']);
} else {
	http_response_code(404);
	include('err-404.php'); // provide your own HTML for the error page
	die();
}

$sql = "SELECT * FROM users WHERE id = " . $id;

if ($stmt = $pdo->prepare($sql)) {
	if ($stmt->execute()) {
		$patientinfo = $stmt->fetch();
		// Checks if query successfully returned an existing patient
		if ($patientinfo != "") {
			$id = $patientinfo['id'];
			$fname = $patientinfo['fname'];
			$lname = $patientinfo['lname'];
			$healthnum = $patientinfo['username'];
			$email = $patientinfo['email'];
			$phonenum = $patientinfo['phonenumber'];
			$address = $patientinfo['address'];
			$postalcode = $patientinfo['postalcode'];
			$account_created = $patientinfo['created_at'];
			$biography = $patientinfo['biography'];
			$name = $fname . " " . $lname;
		} else {
			// * Show error 404 message, redirect to home page if PatientID is out of bounds
			http_response_code(404);
			include('err-404.php'); // Shows error 404 page that will redirect to admin home after some time
			die(); // Stops the reading of the rest of this file
		}
	} else {
		echo "Oops! Something went wrong.";
	}
}

// Delete patient from all tables in database if delete button is pressed
if (isset($_POST['delete-patient'])) {
	// Write delete query from users and jvp tables
	$sql = "DELETE FROM users WHERE id = :id;
					DELETE FROM jvp WHERE id = :id;
					DELETE FROM medicalhistory WHERE id = :id;";

	if ($stmt = $pdo->prepare($sql)) {
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			echo '<script>
							alert("Patient deleted! Redirecting you to the patient list...");
							var timer = setTimeout(function () {
								window.location = "patients.php"
							}, 1000);
						</script>';
		}
	}
} else if (isset($_POST["edit"])) {
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

	// Validate date of birth
	if (empty(trim($_POST["bday"]))) {
		$bday_err = "Please enter your birth date";
	} else {
		$bday = trim($_POST["bday"]);
		$bday_err = "";
		$bday_valid = $inputvalid;
	}

	// Validate gender input
	if (empty(trim($_POST["gender"]))) {
		$gender_err = "Please enter your gender";
	} else {
		$gender = trim($_POST["gender"]);
		$gender_err = "";
		$gender_valid = $inputvalid;
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
				echo $error;
			}
			// Close statement
			unset($stmt);
		}
	}

	// Save user inputs to variables
	$address = trim($_POST["address"]);
	$biography = trim($_POST["biography"]);

	// * Check if there are any errors in user inputs
	if (empty($fname_err) && empty($lname_err) && empty($healthnum_err) && empty($email_err) && empty($address_err) && empty($phonenum_err) && empty($postalcode_err)) {
		// * Write update query for user, jvp, and medicalhistory tables
		$sql = "UPDATE users
						SET fname = :fname, lname = :lname, username = :healthnum, birthdate = :bday, gender = :gender, email = :email, phonenumber = :phonenum, address = :address, postalcode = :postalcode, biography = :biography
						WHERE id = :id;
						UPDATE jvp
						SET healthnum = :healthnum, fname = :fname, lname = :lname
						WHERE id = :id;
						UPDATE medicalhistory
						SET healthnum = :healthnum, fname = :fname, lname = :lname
						WHERE id = :id;";

		// * Prepare statement
		if ($stmt = $pdo->prepare($sql)) {
			// * Bind parameters to variables
			$stmt->bindParam(':id', $param_id, PDO::PARAM_INT);
			$stmt->bindParam(':fname', $param_fname, PDO::PARAM_STR);
			$stmt->bindParam(':lname', $param_lname, PDO::PARAM_STR);
			$stmt->bindParam(':healthnum', $param_healthnum, PDO::PARAM_STR);
			$stmt->bindParam(':email', $param_email, PDO::PARAM_STR);
			$stmt->bindParam(':phonenum', $param_phonenum, PDO::PARAM_STR);
			$stmt->bindParam(':address', $param_address, PDO::PARAM_STR);
			$stmt->bindParam(':postalcode', $param_postalcode, PDO::PARAM_STR);
			$stmt->bindParam(':biography', $param_biography, PDO::PARAM_STR);
			$stmt->bindParam(':bday', $param_bday, PDO::PARAM_STR);
			$stmt->bindParam(':gender', $param_gender, PDO::PARAM_STR);

			// * Give paramters values
			$param_id = $id;
			$param_fname = $fname;
			$param_lname = $lname;
			$param_healthnum = $healthnum;
			$param_email = $email;
			$param_phonenum = $phonenum;
			$param_address = $address;
			$param_postalcode = $postalcode;
			$param_biography = $biography;
			$param_gender = $gender;
			$param_bday = $bday;

			if ($stmt->execute()) {
				echo "<script>alert('Changes to this profile have been saved!')</script>";
			} else {
				echo $error;
			}
		} else {
			echo $error;
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
	<title><?= $name ?></title>
</head>

<body>
	<script>
		// JQuery function to autoformat healthcard number, phone number, and postal code
		(function($, undefined) {
			"use strict";

			// When ready.
			$(function() {

				var $form = $("#patient-info");
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
	<!-- Delete patient confirmation modal -->
	<div class="modal" id="delete-patient">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">

				<!-- Modal Header -->
				<div class="modal-header">
					<h4 class="modal-title">Are you sure you want to delete this patient?</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>

				<!-- Modal body -->
				<div class="modal-body">
					<form method="post" class="form-horizontal">
						<button type="submit" name="delete-patient" class="btn btn-danger container-fluid">Yes, I'm sure</button>
					</form>
				</div>
			</div>
		</div>
	</div>

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
			<h1><?= $name ?></h1>
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

	<!-- sidebar -->
	<div id="sidebar" class="theme-d3">
		<a class="side-item hover-theme" href="admin.php"><em class="fa-solid fa-house"></em>Home</a>
		<a class="side-item hover-theme" href="patients.php"><em class="fa-solid fa-clipboard-user"></em>View Patients</a>
		<a class="side-item hover-theme" href="add-patient.php"><em class="fa-solid fa-book-medical"></em>Add A Patient</a>
	</div>

	<!-- Main body of page -->
	<div id="main-text">
		<div class="container">
			<div class="row g-5">

				<!-- Picture of patient -->
				<div class="col-sm container p-3 text-center">
					<img src='images/default.jpg' alt='<?= $fname . " " . $lname; ?>' class="prof-pic img-thumbnail float-right">
					<p>Account Created: <?= $account_created ?></p>
				</div>

				<!-- Patient info-->
				<div class="col-sm">
					<form method='post' id="patient-info">
						<div class="row container p-3 border-0">
							<div class="form-group row g-1">
								<div class="form-floating mb-3 mt-3 col">
									<input type="text" class="form-control form-control-lg" id="fname" placeholder="First Name" name="fname" value="<?= $fname; ?>" required>
									<label for="fname">First Name</label>
								</div>

								<div class="form-floating mb-3 mt-3 col">
									<input type="text" class="form-control form-control-lg" id="lname" placeholder="Last Name" name="lname" value="<?= $lname; ?>" required>
									<label for="lname">Last Name</label>
								</div>
							</div>

							<div class="form-group row g-1">
								<div class="form-floating mb-3 mt-3 col">
									<input type="date" class="form-control form-control-lg" id="bday" placeholder="Birth Date" name="bday" value="<?= $bday; ?>" required>
									<label for="bday">Birth Date</label>
								</div>

								<div class="form-floating mb-3 mt-3 col">
									<select name="gender" class="form-control col <?= (!empty($gender_err)) ? 'is-invalid' : ''; ?>" required>
										<option <?= $gender == "" ? $selected : "" ?>></option>
										<option value="male" <?= $gender == "male" ? $selected : "" ?>>Male</option>
										<option value="female" <?= $gender == "female" ? $selected : "" ?>>Female</option>
										<option value="other" <?= $gender == "other" ? $selected : "" ?>>Other</option>
									</select>
									<label for="gender">Gender</label>
								</div>
							</div>

							<div class="form-floating mb-3 mt-3 col g-2">
								<input type="text" class="form-control form-control-lg" id="healthnum" placeholder="Healthcard Number" name="healthnum" value="<?= $healthnum; ?>" maxlength="12" required>
								<label for="healthnum">Healthcard Number</label>
							</div>
							<h3></h3>

							<div class="input-group row g-1">
								<button type="button" id="delete" class="btn btn-danger col" data-bs-toggle="modal" data-bs-target="#delete-patient">
									<em class="fa-solid fa-trash-can"></em> Delete this account
								</button>
								<button type="submit" id="edit" name="edit" class="btn btn-primary col">
									<em class="fa-solid fa-floppy-disk"></em> Save this patient profile
								</button>
							</div>
						</div>

						<div class="row container p-3 border-0">
							<h4 class="card-title">Contact Info:</h4>
							<br>
							<div class="row g-2">
								<div class="form-floating mb-3 mt-3 col">
									<input type="text" class="form-control form-control-sm" id="phonenum" placeholder="Phone Number" name="phonenum" value="<?= $phonenum; ?>" maxlength="12" required>
									<label for="phonenum">Phone Number</label>
								</div>
							</div>

							<div class="row g-2">
								<div class="form-floating mb-3 mt-3 col">
									<input type="text" class="form-control form-control-sm" id="email" placeholder="Email" name="email" value="<?= $email; ?>" required>
									<label for="email">Email Address</label>
								</div>
							</div>

							<div class="row g-2">
								<div class="form-floating mb-3 mt-3 col">
									<input type="text" class="form-control form-control-sm" id="address" placeholder="Address" name="address" value="<?= $address; ?>" required>
									<label for="address">Address</label>
								</div>
							</div>

							<div class="row g-2">
								<div class="form-floating mb-3 mt-3 col">
									<input type="text" class="form-control form-control-sm" id="postalcode" placeholder="Postal Code" name="postalcode" value="<?= $postalcode; ?>" maxlength="7" required>
									<label for="postalcode">Postal Code</label>
								</div>
							</div>

							<br>

							<div class="row g-2">
								<div class="form-floating mb-3 mt-3 col">
									<textarea class="form-control" id="biography" placeholder="Biography" name="biography" value="<?= $biography; ?>" style="height:120px"></textarea>
									<label for="biography">Biography</label>
								</div>
							</div>
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