<?php
// Kiểm tra xem có tệp ảnh được tải lên không
if (!empty($_FILES['file']['name'])) {
    $tempFile = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];

    // Thư mục để lưu trữ tệp ảnh
    $uploadDir = 'uploads/';

    // Tạo tên tệp mới để tránh trùng lặp
    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid() . '.' . $fileExt;

    // Đường dẫn đầy đủ của tệp ảnh sau khi tải lên
    $targetFile = $uploadDir . $newFileName;

    // Di chuyển tệp ảnh vào thư mục đích
    if (move_uploaded_file($tempFile, $targetFile)) {
        // Trả về đường dẫn của tệp ảnh đã tải lên
        echo json_encode(['location' => $targetFile]);
    } else {
        // Trả về thông báo lỗi nếu có lỗi xảy ra khi di chuyển tệp
        echo json_encode(['error' => 'Có lỗi xảy ra khi tải lên tệp']);
    }
} else {
    // Trả về thông báo lỗi nếu không có tệp ảnh được tải lên
    echo json_encode(['error' => 'Không có tệp nào được tải lên']);
}
?>