<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$taskId = (int)($_GET['id'] ?? 0);

if ($taskId <= 0) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare("
    DELETE FROM tasks
    WHERE id = :id AND user_id = :uid
");
$stmt->execute([
    ':id'  => $taskId,
    ':uid' => $userId
]);

header('Location: dashboard.php');
exit;
