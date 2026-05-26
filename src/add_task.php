<?php
// add_task.php
// This file handles the "Add a New Task" form from dashboard.php.

require 'db.php';

// Make sure only logged-in users can add tasks.
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// We only expect this page to be hit via POST from the form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If someone accesses this directly via GET, just send them back to the dashboard.
    header('Location: dashboard.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get form values.
$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$categoryId  = $_POST['category_id'] ?? '';
$dueAt       = $_POST['due_at'] ?? '';

// Basic validation: title is required.
if ($title === '') {
    // Redirect back with a simple query flag. 
    // (You can later show a proper error message in dashboard.php if you want.)
    header('Location: dashboard.php?error=missing_title');
    exit;
}

// If category_id is empty string, treat it as NULL.
if ($categoryId === '') {
    $categoryId = null;
}

// If due_at is empty string, treat it as NULL.
// Otherwise, we keep the string as-is; it should be in "YYYY-MM-DDTHH:MM" format
// from the datetime-local input. MySQL DATETIME can parse "YYYY-MM-DD HH:MM:SS",
// so you could also transform it if needed.
if ($dueAt === '') {
    $dueAt = null;
}

// Insert the new task row.
$stmt = $pdo->prepare(
    "INSERT INTO tasks (user_id, category_id, title, description, due_at)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->execute([$userId, $categoryId, $title, $description, $dueAt]);

// After inserting, send the user back to the dashboard to see their new task.
header('Location: dashboard.php');
exit;
