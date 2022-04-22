<?php

// ! rebuilds database and tables if not exists

/* Database credentials. Assuming you are running MySQL
  server with default setting (user 'root' with no password) */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'oscar');

// Sets connection to server
$pdo = new PDO("mysql:host=localhost", DB_USERNAME, DB_PASSWORD);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Creates database if not exists
$pdo->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
$pdo->query("use " . DB_NAME); //

/* Attempt to connect to database */
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

$password = bin2hex(random_bytes(10));
echo $password;
$password_hash = password_hash($password, PASSWORD_DEFAULT); // Generates random string that will become username and password for an admin account

// Create tables if not exists
$statements = ["CREATE TABLE IF NOT EXISTS users(
                  id INT AUTO_INCREMENT,
                  username VARCHAR(12) NOT NULL,
                  fname VARCHAR(255) NOT NULL,
                  lname VARCHAR(255) NOT NULL,
                  birthdate VARCHAR(10) NOT NULL,
                  gender TINYTEXT NOT NULL,
                  PASSWORD VARCHAR(255) NOT NULL,
                  address VARCHAR(255) NOT NULL,
                  email VARCHAR(255) NOT NULL,
                  phonenumber VARCHAR(255) NOT NULL,
                  postalcode VARCHAR(7) NOT NULL,
                  biography mediumtext NOT NULL,
                  created_at datetime NOT NULL DEFAULT current_timestamp,
                  PRIMARY KEY(id)
                );",
                "CREATE TABLE IF NOT EXISTS admin(
                  id INT AUTO_INCREMENT,
                  username VARCHAR(255) NOT NULL,
                  PASSWORD VARCHAR(255) NOT NULL,
                  account_created datetime NOT NULL DEFAULT current_timestamp,
                  PRIMARY KEY(id)
                )",
                "CREATE TABLE IF NOT EXISTS jvp(
                  id INT AUTO_INCREMENT,
                  healthnum VARCHAR(12) NOT NULL,
                  fname VARCHAR(255) NOT NULL,
                  lname VARCHAR(255) NOT NULL,
                  weight FLOAT NOT NULL,
                  heartrate FLOAT NOT NULL,
                  jvp FLOAT NOT NULL,
                  comments mediumtext NOT NULL,
                  PRIMARY KEY(id)
                )",
                "CREATE TABLE IF NOT EXISTS medicalhistory(
                  id INT AUTO_INCREMENT,
                  healthnum VARCHAR(12) NOT NULL,
                  fname VARCHAR(255) NOT NULL,
                  lname VARCHAR(255) NOT NULL,
                  chiefcomplaint VARCHAR(255) NOT NULL,
                  card FLOAT NOT NULL,
                  pmh FLOAT NOT NULL,
                  riskfactors INT NOT NULL,
                  hpi MEDIUMTEXT NOT NULL,
                  meds MEDIUMTEXT NOT NULL,
                  PRIMARY KEY(id)
                )",
                "INSERT INTO admin (username, password)
                  VALUES ('$password', '$password_hash')"
              ];

foreach ($statements as $statement) {
    $pdo->exec($statement);
    echo "Table has been built <br>";
}

echo "Redirecting you to the login page...";
echo "<script>
        var timer = setTimeout(function () {
          window.location = 'admin.php'
        }, 3000);
      </script>";
$pdo = null;
