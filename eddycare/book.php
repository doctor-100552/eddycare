<?php
require 'config.php';
require_user();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || is_admin()) { header('Location: index.php?page=booking'); exit; }

$doctor_id = (int)($_POST['doctor_id'] ?? 0);
$date      = $_POST['appointment_date'] ?? '';
$time      = $_POST['appointment_time'] ?? '';
$notes     = trim($_POST['notes'] ?? '');

function back($msg) { flash('error', $msg); header('Location: index.php?page=booking'); exit; }

$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date)  back('Please choose a valid date.');
if ($date < date('Y-m-d'))                  back('You cannot book a date in the past.');
if (!in_array($time, all_slots(), true))    back('Please choose a valid time slot.');

$dow = (int)$d->format('N');                 // 1=Mon ... 7=Sun
if ($dow === 7)                             back('The hospital is closed on Sundays.');
if ($dow === 6 && strpos($time, 'PM') !== false) back('On Saturdays we only take morning appointments (9:00 AM - 2:00 PM).');

$doc = $pdo->prepare('SELECT * FROM doctors WHERE id = ?');
$doc->execute([$doctor_id]);
$doctor = $doc->fetch();
if (!$doctor) back('Please select a doctor.');

try {
    $ins = $pdo->prepare('INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, notes) VALUES (?,?,?,?,?)');
    $ins->execute([$_SESSION['user_id'], $doctor_id, $date, $time, $notes]);
    $ref = 'EC-' . str_pad($pdo->lastInsertId(), 5, '0', STR_PAD_LEFT);
    flash('success', '✅ <strong>Appointment booked successfully!</strong><br>Reference: <strong>' . e($ref) . '</strong><br>' .
        'Doctor: ' . e($doctor['name']) . ' (' . e($doctor['specialty']) . ')<br>' .
        'Date: ' . e($d->format('D, d M Y')) . ' at ' . e($time) . '<br>' .
        'Status: <strong>Pending</strong> - our admin will confirm shortly. ' .
        'Track it under <a href="my_appointments.php">My Appointments</a>.');
    header('Location: index.php?page=booking');
} catch (PDOException $ex) {
    if ($ex->getCode() == 23000) back('Sorry, that slot has just been taken. Please pick another time.');
    back('Something went wrong. Please try again.');
}
