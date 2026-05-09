<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request.'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = intval($_POST['id']);

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE images SET views = views + 1 WHERE id = ?");

            if ($stmt->execute([$id]) && $stmt->rowCount() > 0) {
                $view_stmt = $conn->prepare("SELECT views FROM images WHERE id = ?");
                $view_stmt->execute([$id]);
                $image = $view_stmt->fetch();

                $total_views = (int) $conn->query("SELECT COALESCE(SUM(views), 0) AS total_views FROM images")->fetchColumn();

                $response = [
                    'success' => true,
                    'message' => 'View count updated.',
                    'views' => intval($image['views'] ?? 0),
                    'total_views' => $total_views
                ];
            } else {
                $response['message'] = 'Unable to update view count.';
            }
        } else {
            $response['message'] = 'Invalid image ID.';
        }
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error while updating the view count.';
}

if (!$response['success']) {
    http_response_code(400);
}

echo json_encode($response);
?>
