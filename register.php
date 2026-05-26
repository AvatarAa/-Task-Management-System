<?php
// This file handles user registration.
// It both SHOWS the registration form (HTML)
// and PROCESSES the form when the user submits it.

// Bring in our database connection and start the session.
// db.php must define $pdo and call session_start().
require 'db.php';

// We'll store any error or success messages in these variables
// and display them later in the HTML.
$error = '';
$success = '';

// This block only runs when the form is submitted using POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the values the user typed into the form.
    // trim() removes extra spaces at the beginning/end.
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ---- Basic validation checks ----

    // Check that none of the fields are empty.
    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please fill in all fields.';
    }
    // Check that the email looks like a valid email.
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    }
    // Check that password and confirm password match.
    elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    }
    // Optional: enforce a minimum password length.
    elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // If we reached this point, the input looks okay.

        // Hash the plain password before storing it.
        // We never store the raw password in the database.
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Prepare the SQL INSERT statement.
            // The ? marks are placeholders for the real values.
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password_hash)
                 VALUES (?, ?, ?)"
            );

            // Execute the query with the actual data.
            $stmt->execute([$username, $email, $hash]);

            // If insert works, redirect user to the login page.
            // We pass ?registered=1 so the login page can show a message like
            // "Account created successfully, please log in."
            header('Location: index.php?registered=1');
            exit; // Always exit after sending a header redirect.
        } catch (PDOException $e) {
            // If this INSERT fails, the most likely reason is that the
            // username or email is already taken (because they are UNIQUE).
            $error = 'That username or email is already in use. Please choose another one.';

            // While debugging, you could temporarily show the error:
            // $error .= ' Debug info: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - To-Do App</title>

    <!-- Optional: external CSS file for nicer styling -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* Simple built-in styles so it looks ok even if style.css is empty */
        body {
            font-family: Arial, sans-serif;
            max-width: 480px;
            margin: 40px auto;
            padding: 0 16px;
        }
        h1 {
            text-align: center;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 12px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            box-sizing: border-box;
        }
        button {
            padding: 8px 16px;
            cursor: pointer;
            margin-top: 8px;
        }
        .error {
            color: #b00020;
            background: #ffe5e5;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 12px;
        }
        .success {
            color: #0a6a0a;
            background: #e5ffe5;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 12px;
        }
        .small-text {
            font-size: 0.9rem;
            margin-top: 16px;
        }
    </style>
</head>
<body>

    <h1>Create an Account</h1>

    <?php if (!empty($error)): ?>
        <!-- If the PHP logic set an $error message, show it here. -->
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <!-- Currently not used because we redirect, but left here in case you switch to showing messages. -->
        <div class="success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!--
        The form sends data back to this same file (register.php) using POST.
        The "name" attributes must match the keys we read in $_POST above.
    -->
    <form method="post" action="register.php">
        <label>
            Username
            <input type="text" name="username" required>
        </label>

        <label>
            Email
            <input type="email" name="email" required>
        </label>

        <label>
            Password
            <input type="password" name="password" required>
        </label>

        <label>
            Confirm Password
            <input type="password" name="confirm_password" required>
        </label>

        <button type="submit">Register</button>
    </form>

    <p class="small-text">
        Already have an account?
        <a href="index.php">Log in here</a>.
    </p>

</body>
</html>
