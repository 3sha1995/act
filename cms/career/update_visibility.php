<?php
require_once __DIR__ . '/../db_connection.php';

header('Content-Type: application/json');

if (!isset($_POST['id']) || !isset($_POST['is_visible'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$id = (int)$_POST['id'];
$isVisible = (int)$_POST['is_visible'];

try {
    $stmt = $pdo->prepare("UPDATE af_page_activities SET is_visible = ? WHERE id = ?");
    $success = $stmt->execute([$isVisible, $id]);
    echo json_encode(['success' => $success]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?> 