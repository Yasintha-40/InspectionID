<?php
// public/update_officer.php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$fullName = trim($_POST['full_name'] ?? '');
$nic = strtoupper(preg_replace('/[\s-]+/', '', trim($_POST['nic'] ?? '')));
$province = trim($_POST['province'] ?? '');
$designation = trim($_POST['designation'] ?? '');
$address = trim($_POST['address'] ?? '');
$issueDate = trim($_POST['issue_date'] ?? '');
$expiryDate = trim($_POST['expiry_date'] ?? '');

function valid_date(string $date): bool
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

if (!$id || $fullName === '' || $nic === '' || !valid_date($issueDate) || !valid_date($expiryDate)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Name, NIC, and valid dates are required.']);
    exit;
}

if ($expiryDate <= $issueDate) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Expiry date must be after the registration date.']);
    exit;
}

$duplicate = $conn->prepare("SELECT id FROM officers WHERE nic_normalized = ? AND id <> ? LIMIT 1");
$duplicate->bind_param('si', $nic, $id);
$duplicate->execute();
if ($duplicate->get_result()->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Another officer already uses this NIC number.']);
    exit;
}
$duplicate->close();

$sql = 'UPDATE officers SET full_name = ?, nic = ?, province = ?, designation = ?, address = ?, issue_date = ?, expiry_date = ?, row_version = row_version + 1 WHERE id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssssssi', $fullName, $nic, $province, $designation, $address, $issueDate, $expiryDate, $id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'The record could not be updated.']);
    exit;
}

if ($stmt->affected_rows === 0) {
    $exists = $conn->prepare('SELECT id FROM officers WHERE id = ?');
    $exists->bind_param('i', $id);
    $exists->execute();
    if ($exists->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Officer record not found.']);
        exit;
    }
}

echo json_encode(['success' => true, 'message' => 'Officer record updated successfully.']);
?>
