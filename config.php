<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'eddycare');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed. Check config.php and that you imported database.sql.');
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function flash($type, $msg) { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }

function get_flash() {
    if (!empty($_SESSION['flash'])) { $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f; }
    return null;
}

function is_logged_in() { return !empty($_SESSION['user_id']); }
function is_admin() { return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin'; }

function require_user() {
    if (!is_logged_in()) { flash('error', 'Please log in first.'); header('Location: login.php'); exit; }
}
function require_admin() {
    if (!is_admin()) { header('Location: login.php'); exit; }
}

// Time slots offered (must match Mon-Fri 8-6, Sat 9-2 hospital hours)
function all_slots() { return ['09:00 AM','10:00 AM','11:00 AM','02:00 PM','03:00 PM','04:00 PM']; }
