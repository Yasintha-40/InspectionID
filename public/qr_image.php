<?php
require_once __DIR__ . '/../config/database.php';

const OFFICER_QR_DIRECTORY = 'D:\\PHOTOS\\QR';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit; }

$statement = $conn->prepare('SELECT qr_code FROM officers WHERE id = ? AND deleted_at IS NULL LIMIT 1');
$statement->bind_param('i', $id);
$statement->execute();
$row = $statement->get_result()->fetch_assoc();
$statement->close();

$path = trim((string) ($row['qr_code'] ?? ''));
$realPath = $path !== '' && is_file($path) ? realpath($path) : false;
$realDirectory = is_dir(OFFICER_QR_DIRECTORY) ? realpath(OFFICER_QR_DIRECTORY) : false;
if ($realPath === false || $realDirectory === false || dirname($realPath) !== $realDirectory || strtolower(pathinfo($realPath, PATHINFO_EXTENSION)) !== 'png') {
    http_response_code(404);
    header('Cache-Control: no-store');
    exit;
}

header('Content-Type: image/png');
header('Content-Length: ' . filesize($realPath));
header('Cache-Control: private, max-age=300');
header('X-Content-Type-Options: nosniff');
readfile($realPath);
