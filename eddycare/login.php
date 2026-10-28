<?php
require 'config.php';
if (is_logged_in()) { header('Location: ' . (is_admin() ? 'admin/dashboard.php' : 'index.php')); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'user'");
    $stmt->execute([strtolower(trim($_POST['email'] ?? ''))]);
    $u = $stmt->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['name']    = $u['full_name'];
        $_SESSION['email']   = $u['email'];
        $_SESSION['role']    = 'user';
        header('Location: index.php?page=booking'); exit;
    }
    $error = 'Incorrect email or password.';
}
$title = 'Login'; include '_auth_head.php'; ?>
<div class="card-3d">
  <h2>Patient Login</h2>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" style="margin-top:1rem">
    <div class="form-group"><label>Email</label><input type="email" class="form-control" name="email" required></div>
    <div class="form-group"><label>Password</label><input type="password" class="form-control" name="password" required></div>
    <button class="btn" type="submit">Login</button>
  </form>
  <p style="margin-top:1rem">New here? <a href="register.php">Create an account</a> &middot; <a href="admin/login.php">Admin login</a></p>
</div>
<?php include '_auth_foot.php';
