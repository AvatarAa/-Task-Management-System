<?php
// db.php — database connection + session starter
session_start();

$DB_HOST = '127.0.0.1';
$DB_NAME = 'todo_app';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default is empty — change this if you have a password

try {
  $pdo = new PDO(
    "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
} catch (PDOException $e) {
  // In development we show the error so you can fix it.
  // In production you'd log this instead.
  die("Database connection failed: " . $e->getMessage());
}
