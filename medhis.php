<?php
session_start();

// Include config file
require_once "config.php";

// * Check if the user is logged in, if not then redirect to login page
// * Validate admin access
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
	header("location: login.php");
	exit;
} else if (isset($_SESSION["loggedin"]) && $_SESSION["admin"]) {
	header("location: admin.php");
	exit;
}
// Declare variables
$fname = $lname = $name = "";
$cc = $card = $pmh = $rf = "";
$cc_err = $card_err = $pmh_err = $rf_err = "";
$id = htmlspecialchars(trim($_SESSION["id"]));
$healthnum = htmlspecialchars(trim($_SESSION["username"]));
$fname = htmlspecialchars(trim($_SESSION["fname"]));
$lname = htmlspecialchars(trim($_SESSION["lname"]));
$hpi = $meds = "";
$alert_color = $alert = "";
$alert_msg = "Oops! Something went wrong.";
$numeric_err = "This value can only contain numbers.";
$selected = " selected";

// Write query to select JVP info for 
$qry = "SELECT * FROM medicalhistory WHERE id = :id";

if ($stmt = $pdo->prepare($qry)) {
	// Bind healthcard number to parameter
	$stmt->bindParam(":id", $param_id, PDO::PARAM_STR);

	// Set parameters
	$param_id = $id;

	// Attempt to execute query
	if ($stmt->execute()) {
		$userdata = $stmt->fetch();
		$name = $fname . " " . $lname;
		$card = $userdata['card'] ?? "";
		$cc = $userdata['chiefcomplaint'] ?? "";
		$hpi = $userdata['hpi'] ?? "";
		$rf = $userdata['riskfactors'] ?? "";
		$pmh = $userdata['pmh'] ?? "";
		$meds = $userdata['meds'] ?? "";

		$csv_arr = array();
		$csv_arr[] = array('Healthcard Number', 'First Name', 'Last Name', 'chief complaint', 'cardiac history', 'past medical history', 'risk factors', 'HPI', 'Meds');
		$csv_arr[] = array($healthnum, $fname, $lname, $cc, $card, $pmh, $rf, $hpi, $meds);
	} else {
		echo "Oops, something went wrong. Try again later.";
	}
	unset($stmt);
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	// Validate input
	if (empty(trim($_POST["card"]))) {
		$card_err = "Please enter previous cardiac history.";
	} else if (!is_numeric($_POST["card"])) {
		$card_err = $numeric_err;
	} else {
		$card = trim($_POST["card"]);
	}

	if (empty(trim($_POST["pmh"]))) {
		$pmh_err = "Please enter a past medical history.";
	} else if (!is_numeric($_POST["pmh"])) {
		$pmh_err = $numeric_err;
	} else {
		$pmh = trim($_POST["pmh"]);
	}

	$hpi = trim($_POST["hpi"]);
	$cc = trim($_POST["cc"]);
	$rf = trim($_POST["rf"]);

	// Insert title of columns into csv array
	$csv_arr = array();
	$csv_arr[] = array('Healthcard Number', 'First Name', 'Last Name', 'chief complaint', 'cardiac history', 'past medical history', 'risk factors', 'HPI');
	$csv_arr[] = array($healthnum, $fname, $lname, $cc, $card, $pmh, $rf, $hpi);

	// Prepare query statement to update JVP info in database
	$sql = "INSERT INTO medicalhistory (id, healthnum, fname, lname, chiefcomplaint, card, pmh, riskfactors, hpi, meds)
          VALUES (:id, :healthnum, :fname, :lname, :cc, :card, :pmh, :rf, :hpi, :meds)
          ON DUPLICATE KEY UPDATE
					healthnum = :healthnum, fname = :fname, lname = :lname, chiefcomplaint = :cc, card = :card, pmh = :pmh, riskfactors = :rf, hpi = :hpi, meds = :meds";

	if ($stmt = $pdo->prepare($sql)) {
		// Bind variables to parameters
		$stmt->bindParam(":healthnum", $param_healthnum, PDO::PARAM_STR);
		$stmt->bindParam(":fname", $param_fname, PDO::PARAM_STR);
		$stmt->bindParam(":lname", $param_lname, PDO::PARAM_STR);
		$stmt->bindParam(":cc", $param_cc, PDO::PARAM_STR);
		$stmt->bindParam(":card", $param_card, PDO::PARAM_INT);
		$stmt->bindParam(":pmh", $param_pmh, PDO::PARAM_INT);
		$stmt->bindParam(":rf", $param_rf, PDO::PARAM_STR);
		$stmt->bindParam(":hpi", $param_hpi, PDO::PARAM_STR);
		$stmt->bindParam(":meds", $param_meds, PDO::PARAM_STR);
		$stmt->bindParam(":id", $param_id, PDO::PARAM_INT);

		// Set parameters
		$param_healthnum = $healthnum;
		$param_fname = $fname;
		$param_lname = $lname;
		$param_cc = $cc;
		$param_card = $card;
		$param_pmh = $pmh;
		$param_hpi = $hpi;
		$param_rf = $rf;
		$param_meds = $meds;
		$param_id = $id;

		// Attempt to execute query
		if ($stmt->execute()) {
			// Show success of database update
			$alert = "<strong>Success!</strong> Your changes have been saved!";
			$alert_color = "alert-success";

			// Create .csv file, store in patientcsv folder
			$filename = $fname . $lname . ".csv";
			$target_dir = "patientmedhis/";
			$target_file = $target_dir . $filename;

			$file = fopen($target_file, "w") or die("Unable to open file!");

			foreach ($csv_arr as $line) {
				fputcsv($file, $line);
			}
			fclose($file);
		} else {
			echo "<strong>Oops!</strong> Something went wrong. Please try again later.";
			$alert = $alert_msg;
			$alert_color = "alert-danger";
		}

		// Close statement
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
	</style>
	<title>Medical History</title>
</head>

<body>
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
			<h1>Medical History</h1>
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

	<!-- Sidebar -->
	<div id="sidebar" class="theme-d3">
		<a class="side-item" href="index.php"><em class="fa-solid fa-house"></em>Home</a>
		<a class="side-item"><em class="fa-regular fa-heart"></em>Medical History</a>
		<a class="side-item" href="jvp.php"><em class="fa-solid fa-heart-pulse"></em>Jugular Venous Pressure</a>
		<a class="side-item" href="contact-info.php"><em class="fa-solid fa-phone"></em>Contact Us</a>
	</div>

	<!-- Main body of page -->
	<div id="main-text">
		<div class="p-3 container border">
			<h2>Welcome, <?= $name . " (" . $healthnum . ")" ?>.</h2>
			<form id="jvp" class="g-2" method="post">
				<div class="row">


					<div class="col">
						<label>cardiac history:
							<input type="number" class="form-control" name="card" id="card" value="<?= $card ?>" min="0" required>
						</label>
					</div>

					<div class="col">
						<label>past medical history:
							<input type="number" class="form-control" name="pmh" id="pmh" value="<?= $pmh ?>" min="0" required>
						</label>
					</div>

					<div class="col">
						<label>risk factors:
							<input type="number" class="form-control" id="rf" name="rf" value="<?= $rf ?>" required>
						</label>
					</div>

				</div>

				<div class="row">
					<label class="col">chief complaint:
						<select name="cc" class="form-control" id="cc" required>
							<option value="" <?= $cc == "" ? $selected : "" ?>></option>
							<option value="Chest Pain" <?= $cc == "Chest Pain" ? $selected : "" ?>>Chest Pain</option>
							<option value="Dyspnea" <?= $cc == "Dyspnea" ? $selected : "" ?>>Dyspnea</option>
							<option value="Palpitation" <?= $cc == "Palpitation" ? $selected : "" ?>>Palpitation</option>
							<option value="Pre-syncope" <?= $cc == "Pre-syncope" ? $selected : "" ?>>Pre-syncope</option>
						</select>
					</label>
				</div>

				<div class="row">
					<label class="col">HPI:
						<textarea name="hpi" class="form-control" id="hpi"><?= $hpi ?></textarea>
					</label>
				</div>

				<div class="row">
					<div class="col">
						<button type="submit" class="btn btn-primary" id="submit">SAVE CHANGES</button>
					</div>

					<div class="col">
						<button type="reset" class="btn btn-secondary" id="reset">RESET</button>
					</div>
				</div>

				<br>
				<div class="row">
					<div class="col">
					</div>
				</div>
			</form>
			<br>
			<!-- Show alert when saved -->
			<div class="alert <?= $alert_color ?>">
				<?= $alert ?>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
	</script>
</body>

</html>