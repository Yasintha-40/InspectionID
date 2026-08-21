<?php
// public/update_photo.php
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $id = $_POST['id'] ?? null;
    $officer_id = $_POST['officer_id'] ?? null;
    $image = $_FILES['image'] ?? null;

    if ($id && $officer_id && $image) {
        $folder = '../photos';
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        $fileName = $officer_id . '.' . $ext;
        $targetPath = $folder . '/' . $fileName;

        if (move_uploaded_file($image['tmp_name'], $targetPath)) {
            // Update the path in the database
            // Note: we store just the path relative to the public dir or full path, let's stick to D:\PHOTOS\... format used in original, or actually just use the local path.
            // Since original data had D:\PHOTOS\..., let's just store the local relative path so it renders easily.
            $sql = "UPDATE officers SET photo = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $targetPath, $id);
            $stmt->execute();
            $stmt->close();

            echo json_encode(['success' => true, 'new_path' => $targetPath]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Upload failed.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing data.']);
    }
}
?>
