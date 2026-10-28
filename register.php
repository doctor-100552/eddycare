<?php
require 'config.php';
if (is_logged_in()) { header('Location: index.php'); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if ($name === '')                                $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = 'Enter a valid email address.';
    if ($phone === '')                               $errors[] = 'Phone number is required.';
    if (strlen($pass) < 6)                           $errors[] = 'Password must be at least 6 characters.';
    if ($pass !== ($_POST['confirm'] ?? ''))         $errors[] = 'Passwords do not match.';
    if (!$errors) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $chk->execute([$email]);
        if ($chk->fetch()) $errors[] = 'That email is already registered.';
        else {
            $pdo->prepare('INSERT INTO users (full_name,email,phone,password) VALUES (?,?,?,?)')
                ->execute([$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT)]);
            flash('success', 'Account created! You can now log in.');
            header('Location: login.php'); exit;
        }
    }
}
$title = 'Register'; include '_auth_head.php'; ?>
<div class="card-3d">
  <h2>Create an Account</h2>
  <?php foreach ($errors as $er): ?><div class="alert alert-error"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" style="margin-top:1rem">
    <div class="form-group"><label>Full Name</label><input class="form-control" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>" required></div>
    <div class="form-group"><label>Email</label><input type="email" class="form-control" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></div>
    <div class="form-group"><label>Phone</label><input type="tel" class="form-control" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" required></div>
    <div class="form-group"><label>Password</label><input type="password" class="form-control" name="password" required></div>
    <div class="form-group"><label>Confirm Password</label><input type="password" class="form-control" name="confirm" required></div>
    <button class="btn" type="submit">Register</button>
  </form>
  <p style="margin-top:1rem">Already have an account? <a href="login.php">Log in</a></p>
</div>
<?php include '_auth_foot.php';
