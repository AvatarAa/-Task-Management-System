<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$taskId = (int)($_GET['id'] ?? 0);
$undo   = isset($_GET['undo']);

if ($taskId <= 0) {
    header('Location: dashboard.php');
    exit;
}

if ($undo) {
    // Mark task as NOT complete
    $stmt = $pdo->prepare("
        UPDATE tasks
        SET completed_at = NULL
        WHERE id = :id AND user_id = :uid
    ");
    $stmt->execute([':id' => $taskId, ':uid' => $userId]);
} else {
    // Mark as complete (timestamp now)
    $stmt = $pdo->prepare("
        UPDATE tasks
        SET completed_at = NOW()
        WHERE id = :id AND user_id = :uid
    ");
    $stmt->execute([':id' => $taskId, ':uid' => $userId]);
}

header('Location: dashboard.php');
exit;
