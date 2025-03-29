<?php

// Define the server details for SQL Server connection
$serverName = "DESKTOP-VEAUE82\\SQLEXPRESS01,51684"; // Your SQL Server instance and dynamic port
$connectionOptions = array(
    "Database" => "laravel_project", // Replace with your database name
    "Uid" => "", // Leave empty for Windows Authentication
    "PWD" => "", // Leave empty for Windows Authentication
);

// Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

// Check if the connection is successful
if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
} else {
    echo "Connection successful!";
}

?>
