<?php
$host = 'localhost';  // Your database host
$dbname = 'recipe_website';
$username = 'root';   // Your database username
$password = '';       // Your database password

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
