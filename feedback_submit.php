<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php?page=feedback'); exit; }

$name    = trim($_POST['name'] ?? '');
$rating  = (int)($_POST['rating'] ?? 0);
$message = trim($_POST['message'] ?? '');
$doctor  = (int)($_POST['doctor_id'] ?? 0) ?: null;

if ($name === '' || $message === '' || $rating < 1 || $rating > 5) {
    flash('error', 'Please fill in your name, a rating and your feedback.');
} else {
    $pdo->prepare('INSERT INTO feedback (user_id, doctor_id, name, rating, message) VALUES (?,?,?,?,?)')
        ->execute([$_SESSION['user_id'] ?? null, $doctor, $name, $rating, $message]);
    flash('success', 'Thank you for your feedback! We appreciate your input.');
}
header('Location: index.php?page=feedback');
