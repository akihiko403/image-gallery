<?php
require_once 'config.php';

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['filename'])) {
        $id = intval($_POST['id']);
        $filename = $_POST['filename'];
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM images WHERE id = ?");
        
        if ($stmt->execute([$id]) && $stmt->rowCount() > 0) {
            // Delete image file
            $image_path = storagePath('images', $filename);
            $thumb_path = storagePath('thumbs', $filename);
            
            if (file_exists($image_path)) {
                unlink($image_path);
            }
            
            if (file_exists($thumb_path)) {
                unlink($thumb_path);
            }
            
            $response['success'] = true;
            $response['message'] = 'Image deleted successfully!';
        } else {
            $response['message'] = 'Image not found or already deleted.';
        }
    } else {
        $response['message'] = 'Invalid request.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error while deleting the image.';
}

if (!$response['success']) {
    http_response_code(400);
}

echo json_encode($response);
?>
