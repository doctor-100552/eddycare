<?php
require 'config.php';
header('Content-Type: application/json');
$stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status <> 'Cancelled'");
$stmt->execute([(int)($_GET['doctor_id'] ?? 0), $_GET['date'] ?? '']);
echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
