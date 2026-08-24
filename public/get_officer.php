<?php
// public/get_officer.php
require_once '../config/database.php';
header('Content-Type: application/json');

$nic = $_GET['nic'] ?? '';

// Clean the ID (remove spaces)
$cleanNic = strtoupper(preg_replace('/[\s-]+/', '', $nic));

if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$sql = "SELECT * FROM officers WHERE nic_normalized = ? AND deleted_at IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cleanNic);
$stmt->execute();
$query_result = $stmt->get_result();

if ($query_result->num_rows > 0) {
    $row = $query_result->fetch_assoc();
    
    // Map database columns to the format our frontend template expects
    $result = [
        'id' => $row['id'],
        'officer_id' => $row['officer_id'],
        'NAME' => $row['full_name'],
        'full_name' => $row['full_name'],
        'nickname' => $row['nickname'] ?? '',
        'qr' => $row['qr_code'],
        'qr_url' => !empty($row['qr_code']) ? 'qr_image.php?id=' . rawurlencode((string) $row['id']) : '',
        'ADD' => $row['address'],
        'NIC' => $row['nic'],
        'EMAIL' => $row['email'],
        'email' => $row['email'] ?? '',
        'photo' => $row['photo'],
        'photo_url' => 'officer_photo.php?id=' . rawurlencode((string) $row['id']),
        'designation' => $row['designation'],
        'phone' => $row['phone'] ?? '',
        'issue_date' => $row['issue_date'] ?? '',
        'expiry_date' => $row['expiry_date'] ?? '',
        'status' => $row['status'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
    echo json_encode(['success' => true, 'officer' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'No record found for the provided NIC.']);
}
$stmt->close();
?>
