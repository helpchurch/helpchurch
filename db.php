<?php
// db.php
$host = 'localhost';
$user = 'root'; // Replace with your DB username
$pass = '';     // Replace with your DB password
$db   = 'mightychurch_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>