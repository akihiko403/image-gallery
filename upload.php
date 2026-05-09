<?php
session_start();
require_once 'config.php';

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['image']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $response['message'] = 'Only JPG, PNG, GIF, and WebP images are allowed.';
                echo json_encode($response);
                exit;
            }
            
            // Generate unique filename
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $upload_path = storagePath('images', $filename);
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Get other form data
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $category = $_POST['category'] ?? '';
                
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO images (filename, title, description, category) VALUES (?, ?, ?, ?)");
                
                if ($stmt->execute([$filename, $title, $description, $category])) {
                    $response['success'] = true;
                    $response['message'] = 'Image uploaded successfully!';
                    $response['filename'] = $filename;
                } else {
                    $response['message'] = 'Database error while saving image metadata.';
                    // Remove uploaded file if database insert failed
                    unlink($upload_path);
                }
            } else {
                $response['message'] = 'Failed to upload image.';
            }
        } else {
            $response['message'] = 'No image selected or upload error.';
        }
    } else {
        $response['message'] = 'Invalid request method.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error while saving image metadata.';
}

if (!$response['success']) {
    http_response_code(400);
}

echo json_encode($response);
?>
