<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app_settings.php';
header('Content-Type: application/json; charset=utf-8');

function respond(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function valid_date(string $date): bool
{
    $value = DateTime::createFromFormat('Y-m-d', $date);
    return $value && $value->format('Y-m-d') === $date;
}

function generate_qr_png(array $officer): string
{
    $text = implode("\n", [
        'INSPECTION OFFICER',
        'ID: ' . $officer['officer_id'],
        'Name: ' . $officer['full_name'],
        'Nickname: ' . ($officer['nickname'] ?: '-'),
        'NIC: ' . ($officer['nic'] ?: '-'),
        'Designation: ' . ($officer['designation'] ?: '-'),
        'Phone: ' . ($officer['phone'] ?: '-'),
        'Email: ' . ($officer['email'] ?: '-'),
        'Address: ' . ($officer['address'] ?: '-'),
        'Issued: ' . $officer['issue_date'],
        'Expires: ' . $officer['expiry_date'],
        'Status: ' . $officer['status'],
    ]);
    $url = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&margin=20&format=png&ecc=H&data=' . rawurlencode($text);
    $context = stream_context_create(['http' => ['timeout' => 20], 'https' => ['timeout' => 20]]);
    $png = @file_get_contents($url, false, $context);
    if ($png === false || strncmp($png, "\x89PNG\r\n\x1a\n", 8) !== 0 || @getimagesizefromstring($png) === false) {
        throw new RuntimeException('QR image generation failed. Check the internet connection and try again.');
    }
    return $png;
}

function has_valid_qr(string $path, string $directory): bool
{
    $path = trim($path);
    $realPath = $path !== '' && is_file($path) ? realpath($path) : false;
    $realDirectory = is_dir($directory) ? realpath($directory) : false;
    if ($realPath === false || $realDirectory === false || dirname($realPath) !== $realDirectory) return false;
    if (strtolower(pathinfo($realPath, PATHINFO_EXTENSION)) !== 'png') return false;
    $image = @getimagesize($realPath);
    return $image !== false && ($image[2] ?? null) === IMAGETYPE_PNG;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') respond(['success' => false, 'message' => 'Method not allowed.'], 405);

$raw = trim((string) ($_POST['officer_ids'] ?? ''));
$ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $raw)), fn($id) => $id > 0)));
$issue = trim((string) ($_POST['issue_date'] ?? ''));
$expiry = trim((string) ($_POST['expiry_date'] ?? ''));
if (!$ids || count($ids) > 15) respond(['success' => false, 'message' => 'Select between 1 and 15 guides.'], 422);
if (!valid_date($issue) || !valid_date($expiry) || $expiry <= $issue) respond(['success' => false, 'message' => 'Enter valid dates; expiry must be after issued date.'], 422);

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));
$lookup = $conn->prepare("SELECT * FROM officers WHERE id IN ($placeholders) AND deleted_at IS NULL ORDER BY officer_id");
$lookup->bind_param($types, ...$ids);
$lookup->execute();
$officers = $lookup->get_result()->fetch_all(MYSQLI_ASSOC);
if (count($officers) !== count($ids)) respond(['success' => false, 'message' => 'One or more selected guides no longer exist.'], 404);

$update = $conn->prepare("UPDATE officers SET issue_date=?, expiry_date=?, row_version=row_version+1 WHERE id IN ($placeholders) AND deleted_at IS NULL");
$params = array_merge([$issue, $expiry], $ids);
$update->bind_param('ss' . $types, ...$params);
if (!$update->execute()) respond(['success' => false, 'message' => 'Unable to save the selected dates.'], 500);

$directory = local_path('qr_directory');
if (!is_dir($directory) && !mkdir($directory, 0755, true)) respond(['success' => false, 'message' => 'Dates were saved, but the QR storage folder could not be created.'], 500);

$saveQr = $conn->prepare('UPDATE officers SET qr_code=?, row_version=row_version+1 WHERE id=?');
$generated = 0;
try {
    foreach ($officers as $officer) {
        if (has_valid_qr((string) ($officer['qr_code'] ?? ''), $directory)) continue;
        $officer['issue_date'] = $issue;
        $officer['expiry_date'] = $expiry;
        $png = generate_qr_png($officer);
        $safeId = preg_replace('/[^A-Za-z0-9_-]/', '_', $officer['officer_id']);
        $path = $directory . DIRECTORY_SEPARATOR . $safeId . '.png';
        $temporary = $path . '.tmp-' . bin2hex(random_bytes(4));
        if (file_put_contents($temporary, $png, LOCK_EX) === false || !rename($temporary, $path)) {
            if (is_file($temporary)) unlink($temporary);
            throw new RuntimeException('Unable to save a generated QR image.');
        }
        $officerId = (int) $officer['id'];
        $saveQr->bind_param('si', $path, $officerId);
        if (!$saveQr->execute()) {
            unlink($path);
            throw new RuntimeException('Unable to save a generated QR code.');
        }
        $generated++;
    }
} catch (Throwable $error) {
    respond(['success' => false, 'message' => 'Dates were saved. ' . $error->getMessage()], 502);
}

$existing = count($officers) - $generated;
respond([
    'success' => true,
    'message' => sprintf('Saved %d guide(s). Generated %d new QR code(s); kept %d existing.', count($officers), $generated, $existing),
    'generated' => $generated,
    'existing' => $existing,
]);
