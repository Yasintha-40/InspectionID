<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/photo_resolver.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$nic = trim((string) ($_GET['nic'] ?? ''));

if ($id) {
    $statement = $conn->prepare('SELECT id, officer_id, nic, photo FROM officers WHERE id = ? LIMIT 1');
    $statement->bind_param('i', $id);
} elseif ($nic !== '') {
    $cleanNic = strtoupper(preg_replace('/[\s-]+/', '', $nic));
    $statement = $conn->prepare("SELECT id, officer_id, nic, photo FROM officers WHERE nic_normalized = ? AND deleted_at IS NULL LIMIT 1");
    $statement->bind_param('s', $cleanNic);
} else {
    http_response_code(400);
    exit;
}

$statement->execute();
$officer = $statement->get_result()->fetch_assoc();
$photoPath = $officer ? resolve_officer_photo($officer) : null;

if ($photoPath === null || !is_file($photoPath)) {
    http_response_code(404);
    header('Cache-Control: no-store');
    exit;
}

$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp',
];
$extension = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
if (!isset($mimeTypes[$extension])) {
    http_response_code(415);
    exit;
}

header('Content-Type: ' . $mimeTypes[$extension]);
header('Content-Length: ' . filesize($photoPath));
header('Cache-Control: private, max-age=300');
header('X-Content-Type-Options: nosniff');
readfile($photoPath);
