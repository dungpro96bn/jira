<?php
header('Content-Type: application/json');

// Hàm trả về base URL
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443
        ? "https://"
        : "http://";

    return $protocol . $_SERVER['HTTP_HOST'];
}

// Kiểm tra file upload
if (!isset($_FILES['file'])) {
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];

// Validate type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['error' => 'Invalid file type']);
    exit;
}

// Tạo folder nếu chưa tồn tại
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Tạo tên file mới
$fileName = time() . '_' . basename($file['name']);
$targetPath = $uploadDir . $fileName;

// Di chuyển file
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    $url = getBaseUrl() . '/uploads/' . $fileName;

    // TinyMCE cần field "location"
    echo json_encode(['location' => $url]);
} else {
    echo json_encode(['error' => 'Upload failed']);
}