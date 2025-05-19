<?php
$host = "localhost"; // Change if your database is hosted elsewhere
$user = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$database = "hotel_management"; // Database name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
