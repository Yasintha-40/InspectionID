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

// Pass request context to audit triggers on this exact MySQL connection.
// REMOTE_ADDR is intentionally used instead of untrusted forwarding headers.
$auditIp = $_SERVER['REMOTE_ADDR'] ?? null;
if ($auditIp !== null && filter_var($auditIp, FILTER_VALIDATE_IP) === false) {
    $auditIp = null;
}
$auditUserAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
$auditActor = 'web:anonymous';
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['username'])) {
    $auditActor = trim((string) $_SESSION['username']) ?: $auditActor;
}
$auditActor = substr($auditActor, 0, 150);

$auditContext = $conn->prepare('SET @audit_ip = ?, @audit_user_agent = ?, @audit_actor = ?');
if ($auditContext) {
    $auditContext->bind_param('sss', $auditIp, $auditUserAgent, $auditActor);
    $auditContext->execute();
    $auditContext->close();
}

?>
