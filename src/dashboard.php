<?php
// dashboard.php
// Shows the TaskFlow dashboard after login.

require 'db.php';

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    // index.php is our login page
    header("Location: index.php");
    exit;
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Load user's tasks from the tasks table
$stmt = $pdo->prepare("
    SELECT *
    FROM tasks
    WHERE user_id = :uid
    ORDER BY created_at DESC
");
$stmt->execute([':uid' => $userId]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TaskFlow Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f7;
        }

        /* Top nav */
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

        /* Page container */
        .container {
            max-width: 1100px;
            margin: 30px auto;
        }

        /* Card style */
        .card {
            background: white;
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }

        h1 {
            margin: 0 0 20px 0;
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

        .btn:hover {
            background: #0052a3;
        }

        /* Two-column layout for main content */
        .layout {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .layout-left,
        .layout-right {
            flex: 1 1 0;
        }

        /* Tasks */
        .task-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .task-title.done {
            text-decoration: line-through;
            color: gray;
        }

        .task-meta {
            font-size: 0.85rem;
            color: #666;
        }

        .actions a {
            margin-left: 15px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .done-link { color: green; }
        .edit-link { color: #0066cc; }
        .delete-link { color: red; }

        .empty-text {
            color: gray;
            font-style: italic;
        }

        /* Stack columns on small screens */
        @media (max-width: 900px) {
            .layout {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-title"><strong>TaskFlow</strong></div>
    <a href="logout.php">Logout</a>
</div>

<div class="container">

    <h1>Welcome, <?php echo htmlspecialchars($username); ?> 👋</h1>

    <div class="layout">
        <!-- LEFT: Add new task -->
        <div class="layout-left">
            <div class="card">
                <h2>Add a New Task</h2>

                <form action="add_task.php" method="POST">

                    <label>Title</label>
                    <input type="text" name="title" required>

                    <label>Description (optional)</label>
                    <textarea name="description" rows="3"></textarea>

                    <label>Category (optional)</label>
                    <!-- For now we use simple numeric IDs (1,2,3).
                         These can map to rows in your categories table. -->
                    <select name="category">
                        <option value="">-- No category --</option>
                        <option value="">School</option>
                        <option value="">Work</option>
                        <option value="">Personal</option>
                    </select>

                    <label>Due date &amp; time (optional)</label>
                    <input type="datetime-local" name="due_date">

                    <button class="btn" type="submit">Add Task</button>
                </form>
            </div>
        </div>

        <!-- RIGHT: Task list -->
        <div class="layout-right">
            <div class="card">
                <h2>Your Tasks</h2>

                <?php if (empty($tasks)) : ?>

                    <p class="empty-text">You have no tasks yet. Add one on the left!</p>

                <?php else: ?>

                    <?php foreach ($tasks as $task): ?>
                        <?php
                            // A task is complete if completed_at is NOT NULL
                            $isDone = !empty($task['completed_at']);

                            // Simple label for category for now
                            $categoryLabel = '';
                            if ($task['category_id'] == 1) $categoryLabel = 'School';
                            elseif ($task['category_id'] == 2) $categoryLabel = 'Work';
                            elseif ($task['category_id'] == 3) $categoryLabel = 'Personal';
                        ?>
                        <div class="task-item">
                            <div>
                                <div class="task-title <?php echo $isDone ? 'done' : ''; ?>">
                                    <?php echo htmlspecialchars($task['title']); ?>
                                </div>

                                <?php if (!empty($categoryLabel) || !empty($task['due_at']) || $isDone): ?>
                                    <div class="task-meta">
                                        <?php
                                            $pieces = [];

                                            if (!empty($categoryLabel)) {
                                                $pieces[] = 'Category: ' . $categoryLabel;
                                            }

                                            if (!empty($task['due_at'])) {
                                                $pieces[] = 'Due: ' . htmlspecialchars($task['due_at']);
                                            }

                                            if ($isDone && !empty($task['completed_at'])) {
                                                $pieces[] = 'Completed: ' . htmlspecialchars($task['completed_at']);
                                            }

                                            echo implode(' • ', $pieces);
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="actions">
                                <?php if (!$isDone): ?>
                                    <a class="done-link" href="complete_task.php?id=<?php echo (int)$task['id']; ?>">Complete</a>
                                <?php else: ?>
                                    <a class="edit-link" href="complete_task.php?id=<?php echo (int)$task['id']; ?>&undo=1">Undo</a>
                                <?php endif; ?>

                                <a class="edit-link" href="edit_task.php?id=<?php echo (int)$task['id']; ?>">Edit</a>
                                <a class="delete-link" href="delete_task.php?id=<?php echo (int)$task['id']; ?>">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

</body>
</html>
