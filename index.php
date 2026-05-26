<?php
// index.php
// Main entry page for the site.
// If the user is already logged in, send them to dashboard.php.
// Otherwise, show the login form.

require 'db.php'; // gives us $pdo and session_start()

// If user is already logged in, no need to show login again
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// We'll store messages here
$error = '';
$success = '';

// Run this only when the login form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pull out what the user typed
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic check: did they leave something empty?
    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        // Look for this user in the database
        $stmt = $pdo->prepare("
            SELECT id, username, email, password_hash
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // If user found, check the password
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login success: save info in the session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['user_email'] = $user['email'];

            // Go to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            // Either user not found or wrong password
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome - To TaskFlow Website</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: #111827;
            color: #f9fafb;
            padding: 12px 20px;
        }

        .navbar h1 {
            margin: 0;
            font-size: 1.3rem;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
        }

        .card {
            background: #ffffff;
            border-radius: 6px;
            padding: 18px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
        }

        .card-left {
            flex: 1 1 320px;
        }

        .card-right {
            flex: 1 1 260px;
        }

        h2 {
            margin-top: 0;
            font-size: 1.2rem;
        }

        p {
            color: #4b5563;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 10px;
        }

        label {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 7px 8px;
            border-radius: 4px;
            border: 1px solid #d1d5db;
            font-size: 0.95rem;
        }

        button {
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 0.9rem;
            cursor: pointer;
            background: #2563eb;
            color: #ffffff;
        }

        .small-text {
            font-size: 0.85rem;
            margin-top: 8px;
        }

        a {
            color: #2563eb;
            text-decoration: none;
        }

        .message {
            font-size: 0.9rem;
            margin-bottom: 10px;
            padding: 8px 10px;
            border-radius: 4px;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>TaskFlow.</h1>
</div>

<div class="container">
    <!-- Left card: intro text -->
    <div class="card card-left">
        <h2>Welcome</h2>
        <p>
            This is our simple to-do website made by our group 10.
             After you log in, you can create tasks,
            mark them as complete, and manage your daily work from one place.
        </p>
        <p>
            The backend is built with PHP and MySQL.<br>
            Each user has their own tasks, which are stored in the database.
        </p>
        <p class="small-text">
            Don’t have an account yet?
            <a href="register.php">Create one here</a>.
        </p>
    </div>

    <!-- Right card: login form -->
    <div class="card card-right">
        <h2>Login</h2>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >
            </div>

            <button type="submit">Login</button>

            <p class="small-text">
                New here? <a href="register.php">Register instead</a>.
            </p>
        </form>
    </div>
</div>

</body>
</html>

