<?php
// Database configuration
$db_host = 'localhost';       // Host (usually localhost for local dev)
$db_user = 'adnan';            // Default XAMPP/WAMP user
$db_pass = 'Adnan@66202';                // Default XAMPP/WAMP password (empty)
$db_name = 'eventpulse_db';   // Database name from schema

// Establish connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Optional: Set charset to avoid encoding issues
mysqli_set_charset($conn, "utf8");

?>