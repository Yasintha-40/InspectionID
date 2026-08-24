<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

function respond(array $data, int $status = 200): void { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
function valid_date(string $date): bool { $parsed = DateTime::createFromFormat('!Y-m-d', $date); return $parsed !== false && $parsed->format('Y-m-d') === $date; }

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') respond(['success' => false, 'message' => 'Method not allowed.'], 405);

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$fullName = trim((string) ($_POST['full_name'] ?? ''));
$nickname = trim((string) ($_POST['nickname'] ?? ''));
$nic = strtoupper(preg_replace('/[\s-]+/', '', trim((string) ($_POST['nic'] ?? ''))));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$designation = trim((string) ($_POST['designation'] ?? '')) ?: 'Inspection Officer';
$phone = trim((string) ($_POST['phone'] ?? ''));
$address = trim((string) ($_POST['address'] ?? ''));
$issueDate = trim((string) ($_POST['issue_date'] ?? ''));
$expiryDate = trim((string) ($_POST['expiry_date'] ?? ''));
$status = trim((string) ($_POST['status'] ?? 'Active'));

if (!$id || $fullName === '' || $nic === '') respond(['success' => false, 'message' => 'Name and NIC are required.'], 422);
if (mb_strlen($fullName) > 255 || mb_strlen($nickname) > 100 || mb_strlen($designation) > 100 || mb_strlen($phone) > 30 || mb_strlen($address) > 1000) respond(['success' => false, 'message' => 'One or more values exceed the allowed length.'], 422);
if (!preg_match('/^(?:\d{9}[VX]|\d{12})$/', $nic)) respond(['success' => false, 'message' => 'Enter a valid 12-digit NIC or an old NIC ending in V or X.'], 422);
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) respond(['success' => false, 'message' => 'Enter a valid email address.'], 422);
if ($phone !== '' && !preg_match('/^[0-9+()\-\s]{7,30}$/', $phone)) respond(['success' => false, 'message' => 'Enter a valid phone number.'], 422);
if (!in_array($status, ['Active', 'Inactive', 'Expired', 'Suspended'], true)) respond(['success' => false, 'message' => 'Select a valid status.'], 422);
if (($issueDate === '') !== ($expiryDate === '')) respond(['success' => false, 'message' => 'Provide both registration and expiry dates, or leave both empty.'], 422);
if ($issueDate !== '' && (!valid_date($issueDate) || !valid_date($expiryDate) || $expiryDate <= $issueDate)) respond(['success' => false, 'message' => 'Expiry date must be after the registration date.'], 422);

$duplicate = $conn->prepare("SELECT id FROM officers WHERE deleted_at IS NULL AND id <> ? AND (nic_normalized = ? OR (? <> '' AND LOWER(email) = ?)) LIMIT 1");
if (!$duplicate) respond(['success' => false, 'message' => 'The officer record could not be validated.'], 500);
$duplicate->bind_param('isss', $id, $nic, $email, $email);
$duplicate->execute();
if ($duplicate->get_result()->num_rows > 0) respond(['success' => false, 'message' => 'Another officer already uses this NIC or email address.'], 409);
$duplicate->close();

$sql = "UPDATE officers SET full_name=?, nickname=NULLIF(?, ''), address=NULLIF(?, ''), nic=?, email=NULLIF(?, ''), designation=?, phone=NULLIF(?, ''), issue_date=NULLIF(?, ''), expiry_date=NULLIF(?, ''), status=?, row_version=row_version+1 WHERE id=? AND deleted_at IS NULL";
$stmt = $conn->prepare($sql);
if (!$stmt) respond(['success' => false, 'message' => 'The update could not be prepared.'], 500);
$stmt->bind_param('ssssssssssi', $fullName, $nickname, $address, $nic, $email, $designation, $phone, $issueDate, $expiryDate, $status, $id);
if (!$stmt->execute()) respond(['success' => false, 'message' => 'The record could not be updated.'], 500);
if ($stmt->affected_rows === 0) {
    $exists = $conn->prepare('SELECT id FROM officers WHERE id = ? AND deleted_at IS NULL');
    $exists->bind_param('i', $id); $exists->execute();
    if ($exists->get_result()->num_rows === 0) respond(['success' => false, 'message' => 'Officer record not found.'], 404);
}
respond(['success' => true, 'message' => 'Officer record updated successfully.']);
