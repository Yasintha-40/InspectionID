<?php
// public/qr-generator.php
require_once '../config/database.php';
header('Content-Type: application/json');

$nic = trim($_REQUEST['nic'] ?? '');
$nicClean = preg_replace('/\s+/', '', $nic);

if (empty($nic)) {
    echo json_encode(['status' => 'error', 'message' => 'NIC is required']);
    exit;
}

// Ensure database connection
if (!$conn || $conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// Generate QR Code URL
$qrProfileUrl = 'https://sltda.gov.lk/inspection?nic=' . urlencode($nicClean);
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=5&data=' . urlencode($qrProfileUrl);

$qrDir = __DIR__ . '/../photos/qr_codes/';
if (!is_dir($qrDir)) {
    mkdir($qrDir, 0777, true);
}

$fileName = 'qr_' . $nicClean . '.png';
$filePath = $qrDir . $fileName;

$qrImage = @file_get_contents($qrUrl);
if ($qrImage !== false) {
    file_put_contents($filePath, $qrImage);
    $qrImagePath = 'photos/qr_codes/' . $fileName;
} else {
    $qrImagePath = $qrUrl;
}

// Update Database
$sql = "UPDATE officers SET qr_code = ? WHERE REPLACE(nic, ' ', '') = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $qrImagePath, $nicClean);
$stmt->execute();
$stmt->close();

echo json_encode([
    'status' => 'success',
    'qr_path' => $qrImagePath,
    'qr_profile_url' => $qrProfileUrl
]);
?>
