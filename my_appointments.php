<?php
require 'config.php';
require_user();

// Allow the patient to cancel their own pending/confirmed appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $pdo->prepare("UPDATE appointments SET status='Cancelled' WHERE id=? AND user_id=? AND status IN ('Pending','Confirmed')")
        ->execute([(int)$_POST['cancel_id'], $_SESSION['user_id']]);
    flash('success', 'Appointment cancelled.');
    header('Location: my_appointments.php'); exit;
}

$stmt = $pdo->prepare('SELECT a.*, d.name AS doctor, d.specialty FROM appointments a JOIN doctors d ON d.id=a.doctor_id WHERE a.user_id=? ORDER BY a.appointment_date DESC, a.id DESC');
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();
$f = get_flash();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Appointments - EddyCare</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="container header-content"><div class="logo"><h1>🏥 EddyCare</h1></div>
<nav><ul><li><a href="index.php">Home</a></li><li><a href="index.php?page=booking">Book Appointment</a></li><li><a href="logout.php">Logout</a></li></ul></nav></div></header>
<div class="container page active" style="display:block">
  <h1 style="margin-bottom:1rem">My Appointments</h1>
  <?php if ($f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= $f['msg'] ?></div><?php endif; ?>
  <div class="card-3d table-wrap">
  <?php if (!$rows): ?><p>You have no appointments yet. <a href="index.php?page=booking">Book one now</a>.</p><?php else: ?>
    <table>
      <tr><th>Ref</th><th>Doctor</th><th>Date &amp; Time</th><th>Status</th><th>Message from hospital</th><th></th></tr>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td>EC-<?= str_pad($r['id'], 5, '0', STR_PAD_LEFT) ?></td>
        <td><?= e($r['doctor']) ?><br><small><?= e($r['specialty']) ?></small></td>
        <td><?= e(date('D, d M Y', strtotime($r['appointment_date']))) ?><br><?= e($r['appointment_time']) ?></td>
        <td><span class="badge b-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
        <td><?= $r['admin_response'] ? e($r['admin_response']) : '<em>Awaiting response</em>' ?></td>
        <td><?php if (in_array($r['status'], ['Pending','Confirmed'])): ?>
          <form method="post" onsubmit="return confirm('Cancel this appointment?')"><input type="hidden" name="cancel_id" value="<?= (int)$r['id'] ?>"><button class="btn btn-danger btn-sm">Cancel</button></form>
        <?php endif; ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
  </div>
</div>
</body></html>
