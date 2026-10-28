<?php
require '../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['appt_id'])) {
        $status = in_array($_POST['status'], ['Pending','Confirmed','Cancelled','Completed']) ? $_POST['status'] : 'Pending';
        $pdo->prepare('UPDATE appointments SET status=?, admin_response=? WHERE id=?')
            ->execute([$status, trim($_POST['admin_response'] ?? '') ?: null, (int)$_POST['appt_id']]);
        flash('success', 'Appointment updated. The patient will see your response.');
    } elseif (isset($_POST['delete_feedback'])) {
        $pdo->prepare('DELETE FROM feedback WHERE id=?')->execute([(int)$_POST['delete_feedback']]);
        flash('success', 'Feedback deleted.');
    }
    header('Location: dashboard.php' . (isset($_POST['delete_feedback']) ? '?tab=feedback' : '')); exit;
}

$tab = ($_GET['tab'] ?? '') === 'feedback' ? 'feedback' : 'appointments';
$filter = $_GET['status'] ?? '';
$counts = $pdo->query("SELECT status, COUNT(*) c FROM appointments GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();

$sql = 'SELECT a.*, u.full_name, u.email, u.phone, d.name AS doctor, d.specialty
        FROM appointments a JOIN users u ON u.id=a.user_id JOIN doctors d ON d.id=a.doctor_id';
$params = [];
if (in_array($filter, ['Pending','Confirmed','Cancelled','Completed'])) { $sql .= ' WHERE a.status=?'; $params[] = $filter; }
$stmt = $pdo->prepare($sql . ' ORDER BY a.appointment_date DESC, a.id DESC');
$stmt->execute($params);
$appts = $stmt->fetchAll();

$fbs = $pdo->query('SELECT f.*, d.name AS doctor FROM feedback f LEFT JOIN doctors d ON d.id=f.doctor_id ORDER BY f.created_at DESC')->fetchAll();
$f = get_flash();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - EddyCare</title><link rel="stylesheet" href="../style.css"></head>
<body>
<header><div class="container header-content"><div class="logo"><h1>🏥 EddyCare Admin</h1></div>
<nav><ul>
  <li><a href="dashboard.php" class="<?= $tab==='appointments'?'active':'' ?>">Appointments</a></li>
  <li><a href="dashboard.php?tab=feedback" class="<?= $tab==='feedback'?'active':'' ?>">Feedback</a></li>
  <li><a href="../index.php">View Site</a></li>
  <li><a href="../logout.php">Logout</a></li>
</ul></nav></div></header>

<div class="container" style="padding:2rem 20px">
  <?php if ($f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= $f['msg'] ?></div><?php endif; ?>
  <div class="stats">
    <div class="stat"><b><?= (int)array_sum($counts) ?></b>Total appointments</div>
    <div class="stat"><b><?= (int)($counts['Pending'] ?? 0) ?></b>Pending</div>
    <div class="stat"><b><?= (int)($counts['Confirmed'] ?? 0) ?></b>Confirmed</div>
    <div class="stat"><b><?= (int)$totalUsers ?></b>Registered patients</div>
  </div>

<?php if ($tab === 'appointments'): ?>
  <div class="card-3d">
    <h2>Appointments</h2>
    <p style="margin:.5rem 0 1rem">Filter:
      <a href="dashboard.php">All</a> |
      <?php foreach (['Pending','Confirmed','Cancelled','Completed'] as $s): ?><a href="?status=<?= $s ?>"><?= $s ?></a> | <?php endforeach; ?>
    </p>
    <div class="table-wrap">
    <table>
      <tr><th>Ref</th><th>Patient</th><th>Doctor</th><th>Date &amp; Time</th><th>Notes</th><th>Status &amp; response to patient</th></tr>
      <?php if (!$appts): ?><tr><td colspan="6">No appointments found.</td></tr><?php endif; ?>
      <?php foreach ($appts as $a): ?>
      <tr>
        <td>EC-<?= str_pad($a['id'], 5, '0', STR_PAD_LEFT) ?></td>
        <td><strong><?= e($a['full_name']) ?></strong><br><?= e($a['email']) ?><br><?= e($a['phone']) ?></td>
        <td><?= e($a['doctor']) ?><br><small><?= e($a['specialty']) ?></small></td>
        <td><?= e(date('D, d M Y', strtotime($a['appointment_date']))) ?><br><?= e($a['appointment_time']) ?></td>
        <td><?= e($a['notes']) ?></td>
        <td>
          <form method="post">
            <input type="hidden" name="appt_id" value="<?= (int)$a['id'] ?>">
            <select name="status" class="form-control" style="margin-bottom:.4rem">
              <?php foreach (['Pending','Confirmed','Cancelled','Completed'] as $s): ?>
                <option <?= $a['status']===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
            <input name="admin_response" class="form-control" style="margin-bottom:.4rem" placeholder="Message to patient" value="<?= e($a['admin_response']) ?>">
            <button class="btn btn-secondary btn-sm">Save</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table></div>
  </div>
<?php else: ?>
  <div class="card-3d">
    <h2>Patient Feedback</h2>
    <div class="table-wrap"><table>
      <tr><th>Date</th><th>Name</th><th>Doctor</th><th>Rating</th><th>Message</th><th></th></tr>
      <?php if (!$fbs): ?><tr><td colspan="6">No feedback yet.</td></tr><?php endif; ?>
      <?php foreach ($fbs as $fb): ?>
      <tr>
        <td><?= e(date('d M Y', strtotime($fb['created_at']))) ?></td>
        <td><?= e($fb['name']) ?></td>
        <td><?= e($fb['doctor'] ?? '-') ?></td>
        <td class="stars"><?= str_repeat('★', (int)$fb['rating']) ?></td>
        <td><?= e($fb['message']) ?></td>
        <td><form method="post" onsubmit="return confirm('Delete this feedback?')"><input type="hidden" name="delete_feedback" value="<?= (int)$fb['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form></td>
      </tr>
      <?php endforeach; ?>
    </table></div>
  </div>
<?php endif; ?>
</div>
</body></html>
