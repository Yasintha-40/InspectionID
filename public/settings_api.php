<?php

require_once __DIR__ . '/../config/app_settings.php';
header('Content-Type: application/json; charset=utf-8');

function settings_response(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    $paths = load_local_paths();
    settings_response([
        'success' => true,
        'paths' => $paths,
        'status' => [
            'qr_directory' => is_dir($paths['qr_directory']),
            'photo_directory' => is_dir($paths['photo_directory']),
        ],
        'custom' => is_file(LOCAL_SETTINGS_FILE),
    ]);
}

if ($method === 'POST') {
    $action = trim((string) ($_POST['action'] ?? 'update'));
    if ($action === 'reset') {
        if (!reset_local_paths()) settings_response(['success' => false, 'message' => 'Unable to reset the local paths.'], 500);
        settings_response(['success' => true, 'message' => 'Custom paths deleted. Default paths restored.', 'paths' => default_local_paths()]);
    }

    $paths = [
        'qr_directory' => trim((string) ($_POST['qr_directory'] ?? '')),
        'photo_directory' => trim((string) ($_POST['photo_directory'] ?? '')),
    ];
    foreach ($paths as $path) {
        if (!is_absolute_windows_path($path)) settings_response(['success' => false, 'message' => 'Enter a complete Windows path such as D:\\PHOTOS\\QR.'], 422);
    }
    if (!save_local_paths($paths)) settings_response(['success' => false, 'message' => 'Unable to save the local paths.'], 500);

    settings_response([
        'success' => true,
        'message' => 'Local paths updated.',
        'paths' => load_local_paths(),
        'status' => [
            'qr_directory' => is_dir($paths['qr_directory']),
            'photo_directory' => is_dir($paths['photo_directory']),
        ],
    ]);
}

settings_response(['success' => false, 'message' => 'Method not allowed.'], 405);
