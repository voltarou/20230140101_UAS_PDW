<?php
// Set error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// SQL Server Connection Details
$serverName = "VOLTAROU"; // Ganti dengan nama server SQL Server Anda, misalnya: "SERVERKU\SQLEXPRESS"
$databaseName = "SIMPRAK_DB";
$uid = "sa"; // Ganti dengan user SQL Server Anda
$pwd = "123"; // Ganti dengan password SQL Server Anda

try {
    // Connection string for SQL Server
    $pdo = new PDO("sqlsrv:Server=$serverName;Database=$databaseName", $uid, $pwd);

    // Set attributes for error reporting and default fetch mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // You might want to set a timeout for the connection
    // $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}