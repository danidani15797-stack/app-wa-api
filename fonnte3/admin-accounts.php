<?php
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/config/database.php';

requireLogin();

$admin = currentAdmin();
$flash = $_GET['flash'] ?? '';
$flashType = $_GET['flash_type'] ?? 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid($_POST['csrf_token'] ?? null)) {
        header('Location: admin-accounts.php?flash=' . urlencode('Sesi tidak valid, silakan coba lagi.') . '&flash_type=failed');
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || strlen($username) < 3) {
        header('Location: admin-accounts.php?flash=' . urlencode('Username minimal 3 karakter.') . '&flash_type=failed');
        exit;
    }

    if (strlen($password) < 6) {
        header('Location: admin-accounts.php?flash=' . urlencode('Password minimal 6 karakter.') . '&flash_type=failed');
        exit;
    }

    try {
        $cek = $pdo->prepare('SELECT id FROM admins WHERE username = :username LIMIT 1');
        $cek->execute(['username' => $username]);

        if ($cek->fetch()) {
            header('Location: admin-accounts.php?flash=' . urlencode('Username admin sudah digunakan.') . '&flash_type=failed');
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO admins (username, password) VALUES (:username, :password)');
        $stmt->execute([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        header('Location: admin-accounts.php?flash=' . urlencode('Akun admin baru berhasil ditambahkan.') . '&flash_type=success');
        exit;
    } catch (PDOException $e) {
        header('Location: admin-accounts.php?flash=' . urlencode('Gagal menambahkan akun admin.') . '&flash_type=failed');
        exit;
    }
}

$stmt = $pdo->query('SELECT id, username, created_at FROM admins ORDER BY created_at DESC');
$admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kelola Admin - API WhatsApp Fonnte</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="brand-wrap">
      <div class="brand-mark">W</div>
      <div>
        <div class="brand-title">API WhatsApp</div>
        <div class="brand-subtitle">Admin Panel</div>
      </div>
    </div>

    <div class="profile-mini admin-profile-mini">
      <div class="avatar admin-avatar">A</div>
      <div>
        <strong><?= htmlspecialchars($admin['username'] ?? 'Admin') ?></strong>
        <small>Super Administrator</small>
      </div>
    </div>

    <nav class="sidebar-nav">
      <a href="dashboard.php"><span>◉</span> Dashboard</a>
      <a href="kelola-user.php"><span>◌</span> Data User</a>
      <a href="admin-log.php"><span>◍</span> Riwayat Log</a>
      <a href="admin-accounts.php" class="active"><span>◍</span> Kelola Admin</a>
      <a href="admin.php"><span>◍</span> Laporan WA</a>
      <a href="logout.php"><span>↩</span> Logout</a>
    </nav>

    <div class="sidebar-card light-card">
      <small>Admin</small>
      <strong><?= count($admins) ?> akun</strong>
      <span class="status-pill success">Aktif</span>
    </div>
  </aside>

  <main class="admin-main">
    <header class="topbar admin-topbar">
      <div>
        <p class="eyebrow">Manajemen</p>
        <h1>Kelola Admin</h1>
      </div>
      <div class="topbar-actions">
        <a href="dashboard.php" class="modern-btn secondary">Kembali</a>
      </div>
    </header>

    <?php if ($flash): ?>
      <div class="alert <?= $flashType === 'success' ? 'success' : 'failed' ?>">
        <strong><?= htmlspecialchars($flash) ?></strong>
      </div>
    <?php endif; ?>

    <section class="content-grid admin-grid">
      <article class="panel">
        <div class="panel-header">
          <h3>Tambah Admin Baru</h3>
          <span class="mini-tag">Baru</span>
        </div>

        <form method="POST" action="admin-accounts.php" class="modern-form form-shell">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

          <div class="field">
            <label for="username">Username Admin</label>
            <input type="text" id="username" name="username" placeholder="contoh: admin2" required>
          </div>

          <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
          </div>

          <div class="modern-form-actions">
            <button type="submit" class="modern-btn primary action-submit">Tambah Admin</button>
          </div>
        </form>
      </article>

      <article class="panel">
        <div class="panel-header">
          <h3>Daftar Admin</h3>
          <span class="mini-tag">Terdaftar</span>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Username</th>
              <th>Dibuat</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($admins)): ?>
              <tr><td colspan="3">Belum ada admin.</td></tr>
            <?php else: ?>
              <?php foreach ($admins as $i => $a): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td><?= htmlspecialchars($a['username']) ?></td>
                  <td><?= htmlspecialchars($a['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </article>
    </section>
  </main>
</div>
</body>
</html>
