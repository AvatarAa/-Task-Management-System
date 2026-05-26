<?php
// edit_task.php
// Shows a form to edit an existing task and saves the changes.

require 'db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // index.php is the login page
    exit;
}

$userId = $_SESSION['user_id'];

// Get task id from query string
$taskId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($taskId <= 0) {
    header('Location: dashboard.php');
    exit;
}

/*
 * STEP 1: Handle form submission (POST)
 * --------------------------------------
 * If the form is submitted, update the task and redirect to dashboard.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $dueDate     = trim($_POST['due_date'] ?? '');

    if ($title === '') {
        // Very simple validation: title required.
        // You could add error messages, but for now just reload the form.
        header("Location: edit_task.php?id=" . $taskId);
        exit;
    }

    // Convert optional fields
    $description = $description === '' ? null : $description;
    $dueDate     = $dueDate === '' ? null : $dueDate;
    $categoryId  = $category === '' ? null : (int)$category;

    // Update only if the task belongs to this user
    $stmt = $pdo->prepare("
        UPDATE tasks
        SET title = :title,
            description = :descr,
            category_id = :cat,
            due_at = :due
        WHERE id = :id AND user_id = :uid
    ");
    $stmt->execute([
        ':title' => $title,
        ':descr' => $description,
        ':cat'   => $categoryId,
        ':due'   => $dueDate,
        ':id'    => $taskId,
        ':uid'   => $userId,
    ]);

    header('Location: dashboard.php');
    exit;
}

/*
 * STEP 2: If GET request, fetch current task data to fill the form.
 */
$stmt = $pdo->prepare("
    SELECT *
    FROM tasks
    WHERE id = :id AND user_id = :uid
");
$stmt->execute([
    ':id'  => $taskId,
    ':uid' => $userId,
]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

// If no task found (wrong id or not yours), go back
if (!$task) {
    header('Location: dashboard.php');
    exit;
}

// Helper: format due_at for <input type="datetime-local">
$dueValue = '';
if (!empty($task['due_at'])) {
    // due_at is like "2025-10-25 14:30:00"
    // datetime-local expects "2025-10-25T14:30"
    $dueValue = str_replace(' ', 'T', substr($task['due_at'], 0, 16));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task - TaskFlow</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f7;
        }
        .navbar {
            background: #0a0f24;
            padding: 15px 25px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: #ff4d4d;
            font-weight: bold;
            text-decoration: none;
        }
        .container {
            max-width: 700px;
            margin: 30px auto;
        }
        .card {
            background: white;
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }
        h1, h2 {
            margin-top: 0;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .btn {
            padding: 10px 15px;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn.secondary {
            background: #ccc;
            color: #222;
            margin-left: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0052a3;
        }
        .btn.secondary:hover {
            background: #b3b3b3;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div><strong>TaskFlow</strong></div>
    <a href="dashboard.php">Back to Dashboard</a>
</div>

<div class="container">
    <div class="card">
        <h1>Edit Task</h1>

        <form action="edit_task.php?id=<?php echo $taskId; ?>" method="POST">
            <label>Title</label>
            <input
                type="text"
                name="title"
                required
                value="<?php echo htmlspecialchars($task['title']); ?>"
            >

            <label>Description (optional)</label>
            <textarea name="description" rows="3"><?php
                echo htmlspecialchars($task['description'] ?? '');
            ?></textarea>

            <label>Category (optional)</label>
            <select name="category">
                <option value="">-- No category --</option>
                <option value="1" <?php echo ($task['category_id'] == 1) ? 'selected' : ''; ?>>School</option>
                <option value="2" <?php echo ($task['category_id'] == 2) ? 'selected' : ''; ?>>Work</option>
                <option value="3" <?php echo ($task['category_id'] == 3) ? 'selected' : ''; ?>>Personal</option>
            </select>

            <label>Due date &amp; time (optional)</label>
            <input
                type="datetime-local"
                name="due_date"
                value="<?php echo $dueValue; ?>"
            >

            <button class="btn" type="submit">Save Changes</button>
            <a class="btn secondary" href="dashboard.php">Cancel</a>
        </form>
    </div>
</div>

</body>
</html>
