<?php /* shared header for login/register pages. Expects $title and optional $base ('' or '../') */
$base = $base ?? ''; $f = get_flash(); ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> - EddyCare</title><link rel="stylesheet" href="<?= $base ?>style.css"></head>
<body>
<header><div class="container header-content"><div class="logo"><h1>🏥 EddyCare</h1></div>
<nav><ul><li><a href="<?= $base ?>index.php">Home</a></li></ul></nav></div></header>
<div class="container"><div class="auth-box">
<?php if ($f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= $f['msg'] ?></div><?php endif; ?>
