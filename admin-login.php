<?php
// Include config file
require_once "config.php";

// Declare variables for login check
$username = $password = "";
$username = trim($_POST['adminusername']);
$password = trim($_POST["adminpassword"]);

// Prepare query statement
$select = "SELECT id, username, password FROM admin WHERE username = :username";

if ($stmt = $pdo->prepare($select)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);

    // Set parameters
    $param_username = trim($_POST["adminusername"]);

    // Attempt to execute query
    if ($stmt->execute()) {
        // Check if username exists, if yes then verify password
        if ($stmt->rowCount() == 1) {
            if ($row = $stmt->fetch()) {
                $id = $row["id"];
                $username = $row["username"];
                $hashed_password = $row["password"];
                if (password_verify($password, $hashed_password)) {
                    // Password is correct, so start a new session
                    session_start();

                    // Store data in session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $username;
                    $_SESSION["admin"] = true;
                    $_SESSION["adminaccess"] = true;

                    // Redirect user to admin page
                    header("location: admin.php");
                } else {
                    // Password is not valid
                    echo '<script>alert(' . 'Invalid admin password, redirecting back to login page' . ')</script>';
                    header("location: login.php");
                }
            }
        } else {
            // Invalid credentials
            echo '<script>alert(' . 'Invalid credentials, redirecting back to login page' . ')</script>';
            header("location: login.php");
        }
    }
}
