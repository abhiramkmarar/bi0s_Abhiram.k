<?php
$servername = "user-system-db";   // <-- change from localhost
$username   = "phpuser";
$password   = "PhpUser@12345"; 
$dbname     = "user_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
