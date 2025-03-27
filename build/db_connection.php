<?php
// Database connection configuration
$host = 'localhost';
$username = 'clothes_app';
$password = 'clothes_password'; // Using the dedicated user instead of root
$database = '306_project';

// Establish connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?> 