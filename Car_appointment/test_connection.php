<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    echo "Database connection successful!<br>";
    
    // Check if tables exist
    $tables = ['users', 'admins', 'mechanics', 'appointments'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "Table '$table' exists<br>";
        } else {
            echo "Table '$table' does not exist<br>";
        }
    }
    
    // Check if admin user exists
    $result = $conn->query("SELECT * FROM admins WHERE username = 'admin'");
    if ($result->num_rows > 0) {
        echo "Admin user exists<br>";
    } else {
        echo "Admin user does not exist<br>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 