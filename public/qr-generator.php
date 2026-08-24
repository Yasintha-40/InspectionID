<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app_settings.php';
header('Content-Type: application/json; charset=utf-8');

define('OFFICER_QR_DIRECTORY', local_path('qr_directory'));

function respond(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    respond(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) respond(['success' => false, 'message' => 'A valid officer ID is required.'], 422);

$statement = $conn->prepare('SELECT id, officer_id, full_name, nickname, address, nic, email, designation, phone, issue_date, expiry_date, status FROM officers WHERE id = ? AND deleted_at IS NULL LIMIT 1');
if (!$statement) respond(['success' => false, 'message' => 'Unable to prepare the officer lookup.'], 500);
$statement->bind_param('i', $id);
$statement->execute();
$officer = $statement->get_result()->fetch_assoc();
$statement->close();
if (!$officer) respond(['success' => false, 'message' => 'Officer record not found.'], 404);

$qrText = implode("\n", [
    'INSPECTION OFFICER',
    'ID: ' . $officer['officer_id'],
    'Name: ' . $officer['full_name'],
    'Nickname: ' . ($officer['nickname'] ?: '-'),
    'NIC: ' . ($officer['nic'] ?: '-'),
    'Designation: ' . ($officer['designation'] ?: '-'),
    'Phone: ' . ($officer['phone'] ?: '-'),
    'Email: ' . ($officer['email'] ?: '-'),
    'Address: ' . ($officer['address'] ?: '-'),
    'Issued: ' . ($officer['issue_date'] ?: '-'),
    'Expires: ' . ($officer['expiry_date'] ?: '-'),
    'Status: ' . $officer['status'],
]);

$serviceUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&margin=20&format=png&ecc=H&data=' . rawurlencode($qrText);
$context = stream_context_create(['http' => ['timeout' => 20], 'https' => ['timeout' => 20]]);
$png = @file_get_contents($serviceUrl, false, $context);
if ($png === false || strncmp($png, "\x89PNG\r\n\x1a\n", 8) !== 0 || @getimagesizefromstring($png) === false) {
    respond(['success' => false, 'message' => 'QR image generation failed. Check the internet connection and try again.'], 502);
}

if (!is_dir(OFFICER_QR_DIRECTORY) && !mkdir(OFFICER_QR_DIRECTORY, 0755, true)) {
    respond(['success' => false, 'message' => 'Unable to create the QR storage folder.'], 500);
}

$safeOfficerId = preg_replace('/[^A-Za-z0-9_-]/', '_', $officer['officer_id']);
$filePath = OFFICER_QR_DIRECTORY . DIRECTORY_SEPARATOR . $safeOfficerId . '.png';
$temporaryPath = $filePath . '.tmp-' . bin2hex(random_bytes(4));
if (file_put_contents($temporaryPath, $png, LOCK_EX) === false || !rename($temporaryPath, $filePath)) {
    if (is_file($temporaryPath)) unlink($temporaryPath);
    respond(['success' => false, 'message' => 'Unable to save the generated QR image.'], 500);
}

$update = $conn->prepare('UPDATE officers SET qr_code = ?, row_version = row_version + 1 WHERE id = ?');
if (!$update) {
    unlink($filePath);
    respond(['success' => false, 'message' => 'Unable to prepare the QR database update.'], 500);
}
$update->bind_param('si', $filePath, $id);
if (!$update->execute()) {
    unlink($filePath);
    respond(['success' => false, 'message' => 'Unable to save the QR path in the database.'], 500);
}
$update->close();

respond([
    'success' => true,
    'message' => 'QR code generated and saved.',
    'qr_url' => 'qr_image.php?id=' . rawurlencode((string) $id),
    'file_name' => basename($filePath),
]);
