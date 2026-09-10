<?php
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login Admin - API WhatsApp Fonnte</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
  <div class="login-shell">
    <div class="login-hero">
      <div class="brand-lockup">
        <div class="brand-mark">W</div>
        <div>
          <div class="brand-title">API WhatsApp</div>
          <div class="brand-subtitle">Fonnte Admin Panel</div>
        </div>
      </div>

      <h1>Kelola user, notifikasi, dan registrasi lebih mudah.</h1>
      <p>Panel admin untuk monitoring akun pengguna, status pengiriman WhatsApp, serta pengelolaan data registrasi secara real time.</p>

      <ul class="hero-list">
        <li>Monitoring status pengiriman WA</li>
        <li>Kelola data user terdaftar</li>
        <li>Sistem keamanan session</li>
      </ul>
    </div>

    <div class="login-card">
      <div class="login-card-header">
        <span class="mini-tag admin-tag">Admin</span>
        <h2>Login Admin</h2>
      </div>

      <?php if ($error): ?>
        <div class="alert failed"><strong><?= htmlspecialchars($error) ?></strong></div>
      <?php endif; ?>

      <form method="POST" action="login.php" class="login-form">
        <label>Username</label>
        <input type="text" name="username" required autofocus placeholder="admin">

        <label>Password</label>
        <input type="password" name="password" required placeholder="Password">

        <button type="submit" class="modern-btn primary login-btn">Masuk ke Dashboard</button>
      </form>

    

      <p class="switch-auth" style="margin-top: 18px;">
        <a href="user-login.php">Login user</a>
      </p>
    </div>
  </div>
</body>
</html>
