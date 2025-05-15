<?php
require_once __DIR__ . '/../db_connection.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Activity ID is required']);
    exit;
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM af_page_activities WHERE id = ?");
    $stmt->execute([$id]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$activity) {
        http_response_code(404);
        echo json_encode(['error' => 'Activity not found']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode($activity);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?> 