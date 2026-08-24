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
if (!isset($extensions[$mime]) || @getimagesize($image['tmp_name']) === false) {
    respond(['success' => false, 'message' => 'Only valid JPG, PNG, and WebP images are allowed.'], 422);
}

$officer = $conn->prepare('SELECT nic FROM officers WHERE id = ? AND deleted_at IS NULL LIMIT 1');
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
if (!move_uploaded_file($image['tmp_name'], $targetPath)) {
    respond(['success' => false, 'message' => 'The photo upload failed.'], 500);
}

$stmt = $conn->prepare('UPDATE officers SET photo = ?, row_version = row_version + 1 WHERE id = ? AND deleted_at IS NULL');
if (!$stmt) {
    @unlink($targetPath);
    respond(['success' => false, 'message' => 'The photo update could not be prepared.'], 500);
}
$stmt->bind_param('si', $targetPath, $id);
if (!$stmt->execute()) {
    @unlink($targetPath);
    respond(['success' => false, 'message' => 'The photo record could not be updated.'], 500);
}
$stmt->close();

respond(['success' => true, 'photo_url' => 'officer_photo.php?id=' . rawurlencode((string)$id)]);
