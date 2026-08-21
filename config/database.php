<?php

$host = "localhost";
$user = "root";
$password = "";
$dbname = "inspection_system_fixed";
$port = 3307;

mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli(
    $host,
    $user,
    $password,
    $dbname,
    $port
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

?>