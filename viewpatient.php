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
$fname = $lname = "";
$id = $healthnum = "";
$email = $phonenum = $address = $postalcode = "";
$biography = "Biography here...";
$account_created = "";
$id = "";

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
					DELETE FROM jvp WHERE id = :id";

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
	<title><?= $name ?></title>
</head>

<body>
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
		<a class="side-item" href="admin.php"><em class="fa-solid fa-house"></em>Home</a>
		<a class="side-item" href="patients.php"><em class="fa-solid fa-clipboard-user"></em>View Patients</a>
		<a class="side-item" href="add-patient.php"><em class="fa-solid fa-book-medical"></em>Add A Patient</a>
	</div>

	<!-- Main body of page -->
	<div id="main-text">
		<div class="container">
			<div class="row g-5">

				<!-- Picture of patient -->
				<div class="col-sm container p-3 text-center">
					<img src='images/default.jpg' alt='<?= $fname . " " . $lname; ?>' class="prof-pic img-thumbnail float-right">
				</div>

				<!-- Patient info-->
				<div class="col-sm">
					<div class="row container p-3 border-0">
						<h1><?= $fname . " " . $lname ?></h1>
						<h3><?= $healthnum ?></h3>
						<p>Account Created: <?= $account_created ?></p>
						<form>
							<button type="button" id="admin" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete-patient">
								<em class="fa-solid fa-trash-can"></em> Delete this account</button>
						</form>
					</div>

					<div class="row container p-3 border-0">
						<h4 class="card-title">Contact Info:</h4>
						<br>
						<div class="row">
							<p class="col card-text">Phone Number:</p>
							<p class="col card-text"><?= $phonenum ?></p>
						</div>

						<div class="row">
							<p class="col card-text">Email Address:</p>
							<p class="col card-text"><?= $email ?></p>
						</div>

						<div class="row">
							<p class="col card-text">Address:</p>
							<p class="col card-text"><?= $address ?></p>
						</div>

						<div class="row">
							<p class="col card-text">Postal Code:</p>
							<p class="col card-text"><?= $postalcode ?></p>
						</div>

						<br>
						<h4 class="card-title">Biography:</h4>

						<p class="card-text"><?= $biography ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
	</script>
</body>

</html>