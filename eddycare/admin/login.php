<?php
require '../config.php';

// First run: create the default admin if none exists (admin@eddycare.com / Admin@123)
if (!$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn()) {
    $pdo->prepare("INSERT INTO users (full_name,email,phone,password,role) VALUES ('Administrator','admin@eddycare.com','0000000000',?, 'admin')")
        ->execute([password_hash('Admin@123', PASSWORD_DEFAULT)]);
}
if (is_admin()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([strtolower(trim($_POST['email'] ?? ''))]);
    $u = $stmt->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['name']    = $u['full_name'];
        $_SESSION['email']   = $u['email'];
        $_SESSION['role']    = 'admin';
        header('Location: dashboard.php'); exit;
    }
    $error = 'Incorrect admin email or password.';
}
$title = 'Admin Login'; $base = '../'; include '../_auth_head.php'; ?>
<div class="card-3d">
  <h2>Admin Login</h2>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" style="margin-top:1rem">
    <div class="form-group"><label>Admin Email</label><input type="email" class="form-control" name="email" required></div>
    <div class="form-group"><label>Password</label><input type="password" class="form-control" name="password" required></div>
    <button class="btn btn-secondary" type="submit">Login as Admin</button>
  </form>
  <p style="margin-top:1rem"><a href="../login.php">Patient login</a></p>
</div>
<?php include '../_auth_foot.php';
