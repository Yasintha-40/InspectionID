<?php

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "inspection_system_fixed",
    3307
);

if ($conn->connect_errno) {
    die("MYSQL ERROR: " . $conn->connect_error);
}

echo "SUCCESS: Database connection is working!";

?>