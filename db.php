<?php
// Database Configuration
$host = 'localhost';
$db   = 'student_management_system'; // Your DB name
$user = 'root';                  // Default XAMPP user
$pass = '';                      // Default XAMPP password is empty
$charset = 'utf8mb4';

// Data Source Name
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Options for security and error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throws errors if SQL fails
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Returns data as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use actual prepared statements for security
];

try {
    // Create the connection
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Connected successfully"; // Uncomment for testing
} catch (PDOException $e) {
    // If connection fails, show error
    die("Database connection failed: " . $e->getMessage());
}
