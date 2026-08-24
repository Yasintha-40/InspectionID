<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/photo_resolver.php';
header('Content-Type: application/json; charset=utf-8');

function respond(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function valid_date(string $date): bool {
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    respond(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$fullName = trim((string) ($_POST['full_name'] ?? ''));
$nic = strtoupper(preg_replace('/[\s-]+/', '', trim((string) ($_POST['nic'] ?? ''))));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$nickname = trim((string) ($_POST['nickname'] ?? ''));
$designation = trim((string) ($_POST['designation'] ?? '')) ?: 'Inspection Officer';
$phone = trim((string) ($_POST['phone'] ?? ''));
$status = trim((string) ($_POST['status'] ?? 'Active'));
$address = trim((string) ($_POST['address'] ?? ''));
$issueDate = trim((string) ($_POST['issue_date'] ?? ''));
$expiryDate = trim((string) ($_POST['expiry_date'] ?? ''));

if ($fullName === '' || $nic === '') respond(['success' => false, 'message' => 'Full name and NIC number are required.'], 422);
if (mb_strlen($fullName) > 255 || mb_strlen($nic) > 30) respond(['success' => false, 'message' => 'One or more values exceed the allowed length.'], 422);
if (!preg_match('/^(?:\d{9}[VX]|\d{12})$/', $nic)) respond(['success' => false, 'message' => 'Enter a valid 12-digit NIC or an old NIC ending in V or X.'], 422);
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) respond(['success' => false, 'message' => 'Enter a valid email address.'], 422);
if ($phone !== '' && !preg_match('/^[0-9+()\-\s]{7,30}$/', $phone)) respond(['success' => false, 'message' => 'Enter a valid phone number.'], 422);
if (!in_array($status, ['Active', 'Inactive', 'Expired', 'Suspended'], true)) respond(['success' => false, 'message' => 'Select a valid status.'], 422);
if (($issueDate === '') !== ($expiryDate === '')) respond(['success' => false, 'message' => 'Provide both registration and expiry dates, or leave both empty.'], 422);
if ($issueDate !== '' && (!valid_date($issueDate) || !valid_date($expiryDate) || $expiryDate <= $issueDate)) respond(['success' => false, 'message' => 'Expiry date must be after the registration date.'], 422);

$duplicate = $conn->prepare("SELECT nic_normalized, email FROM officers WHERE deleted_at IS NULL AND (nic_normalized = ? OR (? <> '' AND LOWER(email) = ?)) LIMIT 1");
if (!$duplicate) respond(['success' => false, 'message' => 'The officer record could not be validated.'], 500);
$duplicate->bind_param('sss', $nic, $email, $email);
$duplicate->execute();
$existing = $duplicate->get_result()->fetch_assoc();
$duplicate->close();
if ($existing) {
    $message = $existing['nic_normalized'] === $nic ? 'An officer already uses this NIC number.' : 'An officer already uses this email address.';
    respond(['success' => false, 'message' => $message], 409);
}

$photoPath = '';
$photo = $_FILES['photo'] ?? null;
if ($photo && (int) $photo['error'] !== UPLOAD_ERR_NO_FILE) {
    if ((int) $photo['error'] !== UPLOAD_ERR_OK) respond(['success' => false, 'message' => 'The photo upload failed.'], 422);
    if ((int) $photo['size'] > 5 * 1024 * 1024) respond(['success' => false, 'message' => 'The photo must be 5 MB or smaller.'], 422);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($photo['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    if (!isset($extensions[$mime])) respond(['success' => false, 'message' => 'Only JPG and PNG photos are allowed.'], 422);
    if (!is_dir(AUTO_OFFICER_PHOTO_DIRECTORY) && !mkdir(AUTO_OFFICER_PHOTO_DIRECTORY, 0755, true)) respond(['success' => false, 'message' => 'The local photo folder could not be created.'], 500);
    $photoPath = AUTO_OFFICER_PHOTO_DIRECTORY . DIRECTORY_SEPARATOR . $nic . '.' . $extensions[$mime];
    if (file_exists($photoPath)) respond(['success' => false, 'message' => 'A photo already exists for this NIC number.'], 409);
}

$lockAcquired = false;
$photoSaved = false;
try {
    $lockResult = $conn->query("SELECT GET_LOCK('inspection_officer_id', 5) AS acquired");
    $lockAcquired = $lockResult && (int) $lockResult->fetch_assoc()['acquired'] === 1;
    if (!$lockAcquired) throw new RuntimeException('Officer number allocation is busy.');
    $conn->begin_transaction();
    $nextResult = $conn->query("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(officer_id, '-', -1) AS UNSIGNED)), 0) + 1 AS next_id FROM officers");
    if (!$nextResult) throw new RuntimeException($conn->error);
    $next = (int) $nextResult->fetch_assoc()['next_id'];
    $officerId = 'INS-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    $sql = 'INSERT INTO officers (officer_id, full_name, nickname, address, nic, email, photo, designation, phone, issue_date, expiry_date, status) VALUES (?, ?, NULLIF(?, \'\'), NULLIF(?, \'\'), ?, NULLIF(?, \'\'), NULLIF(?, \'\'), ?, NULLIF(?, \'\'), NULLIF(?, \'\'), NULLIF(?, \'\'), ?)';
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new RuntimeException($conn->error);
    $stmt->bind_param('ssssssssssss', $officerId, $fullName, $nickname, $address, $nic, $email, $photoPath, $designation, $phone, $issueDate, $expiryDate, $status);
    if (!$stmt->execute()) throw new RuntimeException($stmt->error);
    $recordId = $conn->insert_id;
    if ($photoPath !== '') {
        if (!move_uploaded_file($photo['tmp_name'], $photoPath)) throw new RuntimeException('Unable to move the uploaded photo.');
        $photoSaved = true;
    }
    $conn->commit();
    $stmt->close();
    $conn->query("SELECT RELEASE_LOCK('inspection_officer_id')");
    respond(['success' => true, 'message' => 'Officer added successfully.', 'officer_id' => $officerId, 'id' => $recordId], 201);
} catch (Throwable $error) {
    $conn->rollback();
    if ($photoSaved && is_file($photoPath)) unlink($photoPath);
    if ($lockAcquired) $conn->query("SELECT RELEASE_LOCK('inspection_officer_id')");
    error_log('Add officer failed: ' . $error->getMessage());
    respond(['success' => false, 'message' => 'The officer could not be saved. Please try again.'], 500);
}
