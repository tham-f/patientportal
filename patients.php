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
$table = "";
$table_array = array();
$search = "";
$error = "Oops! Something went wrong.";

// Prepare fetch statement
$sql = "SELECT * FROM users ORDER BY id ASC";

// Prepare statement
if ($stmt = $pdo->prepare($sql)) {
	// Execute statement
	if ($stmt->execute()) {
		$usertable = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($usertable as $user) {
			$table_array[] = array($user['id'], $user['username'], $user['fname'], $user['lname'], $user['email'], $user['phonenumber'], $user['address'], $user['postalcode']);
		}
		foreach ($table_array as $userout) {
			$table .= "<tr><td><a href='viewpatient.php?PatientID=" . $userout[0] . "'>" . implode("</a></td><td><a href='viewpatient.php?PatientID=" . $userout[0] . "'>", $userout) . "</a></td></tr>";
		}
	} else {
		echo $error;
	}
	unset($stmt);
} else {
	echo $error;
}

// Search for patients when searchbar is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$table = "";
	unset($table_array);
	// Define search variable
	$search = $_POST["searchbar"];

	// Prepare search statement
	$qry = "SELECT * FROM users WHERE fname LIKE '%$search%' or lname LIKE '%$search%'";

	// Prepare fetch
	if ($stmt = $pdo->prepare($qry)) {
		// Attempt to execute query
		if ($stmt->execute()) {
			$usertable = $stmt->fetchAll(PDO::FETCH_ASSOC);
			// Check if search returned any results
			if (empty($usertable)) {
				$table = "Search returned no results. Please try another search term.";
			} else {
				foreach ($usertable as $user) {
					$table_array[] = array($user['id'], $user['username'], $user['fname'], $user['lname'], $user['email'], $user['phonenumber'], $user['address'], $user['postalcode']);
				}
				foreach ($table_array as $userout) {
					$table .= "<tr><td><a href='viewpatient.php?PatientID=" . $userout[0] . "'>" . implode("</a></td><td><a href='viewpatient.php?PatientID=" . $userout[0] . "'>", $userout) . "</a></td></tr>";
				}
			}
		} else {
			echo $error;
		}
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

		a {
			width: 100%;
		}
	</style>
	<title>Patients</title>
</head>

<body>
	<div class="theme-d4 row" id="head">
		<div style="background-color: #294257; width:81px; cursor: pointer;" onclick="menuChange(this); menuToggle();">
			<div class="menu-cont">
				<div class="bar1"></div>
				<div class="bar2"></div>
				<div class="bar3"></div>
			</div>
		</div>

		<div class="title col">
			<h1>Patients</h1>
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
		<a class="side-item" href="admin.php"><em class="fa-solid fa-house"></em>Home</a>
		<a class="side-item"><em class="fa-solid fa-clipboard-user"></em>View Patients</a>
		<a class="side-item" href="add-patient.php"><em class="fa-solid fa-book-medical"></em>Add A Patient</a>
	</div>

	<div id="main-text">
		<div class="container card p-2">
			<form class="form-horizontal" method="post">
				<div class="row">
					<div class="input-group mb-3">
						<input type="search" id="searchbar" name="searchbar" class="form-control" placeholder="Search by patient name">
						<button type="submit" class="btn btn-primary">
							<em class="fas fa-search"></em>
						</button>
					</div>
				</div>
			</form>

			<table class="table table-hover">
				<caption>Patient Search</caption>
				<thead>
					<th>ID</th>
					<th>Healthcard Number</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Address</th>
					<th>Postal Code</th>
				</thead>
				<tbody>
					<?= $table ?>
				</tbody>
			</table>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
	</script>
</body>

</html>