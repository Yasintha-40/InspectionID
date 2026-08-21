<?php
// public/update_officer.php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $issue_date = $_POST['issue_date'] ?? null;
    $expiry_date = $_POST['expiry_date'] ?? null;
    $status = 'Printed';

    if ($id && $issue_date && $expiry_date) {
        if (!$conn || $conn->connect_error) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
            exit;
        }

        $sql = "UPDATE officers SET issue_date = ?, expiry_date = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $issue_date, $expiry_date, $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'ID Generated Successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    }
}
?>
