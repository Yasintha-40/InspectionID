<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/photo_resolver.php';
header('Content-Type: application/json; charset=utf-8');

function respond(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    respond(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$image = $_FILES['image'] ?? null;
if (!$id || !$image || (int)($image['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    respond(['success' => false, 'message' => 'A valid officer and image are required.'], 422);
}
if ((int)($image['size'] ?? 0) <= 0 || (int)$image['size'] > 5 * 1024 * 1024) {
    respond(['success' => false, 'message' => 'The photo must be 5 MB or smaller.'], 422);
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($image['tmp_name']);
$extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$dimensions = @getimagesize($image['tmp_name']);
if (!isset($extensions[$mime]) || $dimensions === false) {
    respond(['success' => false, 'message' => 'Only valid JPG, PNG, and WebP images are allowed.'], 422);
}
if (($dimensions[0] * $dimensions[1]) > 20000000) {
    respond(['success' => false, 'message' => 'The image dimensions are too large. Use an image under 20 megapixels.'], 422);
}

$officer = $conn->prepare('SELECT nic, photo FROM officers WHERE id = ? AND deleted_at IS NULL LIMIT 1');
if (!$officer) respond(['success' => false, 'message' => 'The officer could not be validated.'], 500);
$officer->bind_param('i', $id);
$officer->execute();
$row = $officer->get_result()->fetch_assoc();
$officer->close();
if (!$row) respond(['success' => false, 'message' => 'Officer record not found.'], 404);

$safeNic = normalize_photo_identifier($row['nic']);
if ($safeNic === '') respond(['success' => false, 'message' => 'The officer has no valid NIC.'], 422);
if (!is_dir(AUTO_OFFICER_PHOTO_DIRECTORY) && !mkdir(AUTO_OFFICER_PHOTO_DIRECTORY, 0755, true)) {
    respond(['success' => false, 'message' => 'The photo folder could not be created.'], 500);
}

$targetPath = AUTO_OFFICER_PHOTO_DIRECTORY . DIRECTORY_SEPARATOR . $safeNic . '.' . $extensions[$mime];
$temporaryPath = AUTO_OFFICER_PHOTO_DIRECTORY . DIRECTORY_SEPARATOR . '.upload-' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime];
if (!move_uploaded_file($image['tmp_name'], $temporaryPath)) {
    respond(['success' => false, 'message' => 'The photo upload failed.'], 500);
}

$oldPhoto = trim((string) ($row['photo'] ?? ''));
$backupPath = '';
if (is_file($targetPath)) {
    $backupPath = $targetPath . '.backup-' . bin2hex(random_bytes(4));
    if (!rename($targetPath, $backupPath)) {
        @unlink($temporaryPath);
        respond(['success' => false, 'message' => 'The existing photo could not be prepared for replacement.'], 500);
    }
}

if (!rename($temporaryPath, $targetPath)) {
    @unlink($temporaryPath);
    if ($backupPath !== '') @rename($backupPath, $targetPath);
    respond(['success' => false, 'message' => 'The uploaded photo could not be saved.'], 500);
}

$stmt = $conn->prepare('UPDATE officers SET photo = ?, row_version = row_version + 1 WHERE id = ? AND deleted_at IS NULL');
if (!$stmt) {
    @unlink($targetPath);
    if ($backupPath !== '') @rename($backupPath, $targetPath);
    respond(['success' => false, 'message' => 'The photo update could not be prepared.'], 500);
}
$stmt->bind_param('si', $targetPath, $id);
if (!$stmt->execute()) {
    @unlink($targetPath);
    if ($backupPath !== '') @rename($backupPath, $targetPath);
    respond(['success' => false, 'message' => 'The photo record could not be updated.'], 500);
}
$stmt->close();

if ($backupPath !== '') @unlink($backupPath);

if ($oldPhoto !== '' && $oldPhoto !== $targetPath && is_file($oldPhoto)) {
    $resolvedOldPhoto = realpath($oldPhoto);
    $resolvedDirectory = realpath(AUTO_OFFICER_PHOTO_DIRECTORY);
    if ($resolvedOldPhoto !== false && $resolvedDirectory !== false && dirname($resolvedOldPhoto) === $resolvedDirectory) {
        @unlink($resolvedOldPhoto);
    }
}

respond(['success' => true, 'message' => 'Photo updated successfully.', 'photo_url' => 'officer_photo.php?id=' . rawurlencode((string)$id)]);
