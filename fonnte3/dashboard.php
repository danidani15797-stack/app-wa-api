<?php
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/config/database.php';

requireLogin();

$totalUser = (int) $pdo->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'];
$totalTerkirim = (int) $pdo->query("SELECT COUNT(*) AS c FROM log_whatsapp WHERE status = 'success'")->fetch()['c'];
$totalGagal = (int) $pdo->query("SELECT COUNT(*) AS c FROM log_whatsapp WHERE status = 'failed'")->fetch()['c'];
$totalPending = (int) $pdo->query("SELECT COUNT(*) AS c FROM log_whatsapp WHERE status = 'pending'")->fetch()['c'];
$userTerbaru = $pdo->query('SELECT nama, email, no_hp, created_at FROM users ORDER BY created_at DESC LIMIT 5')->fetchAll();
$admin = currentAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin - API WhatsApp Fonnte</title>
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
      <a href="dashboard.php" class="active"><span>◉</span> Dashboard</a>
      <a href="kelola-user.php"><span>◌</span> Data User</a>
      <a href="admin-log.php"><span>◍</span> Riwayat Log</a>
      <a href="admin-accounts.php"><span>◍</span> Kelola Admin</a>
      <a href="admin.php"><span>◍</span> Laporan WA</a>
      <a href="logout.php"><span>↩</span> Logout</a>
    </nav>

    <div class="sidebar-card light-card">
      <small>Status Sistem</small>
      <strong>Online</strong>
      <span class="status-pill success">Aktif</span>
    </div>
  </aside>

  <main class="admin-main">
    <header class="topbar admin-topbar">
      <div>
        <p class="eyebrow">Selamat datang</p>
        <h1>Dashboard Admin</h1>
      </div>
      <div class="topbar-actions">
        <a href="kelola-user.php" class="modern-btn primary">Kelola User</a>
      </div>
    </header>

    <section class="stats-grid admin-stats">
      <article class="stat-card stat-green">
        <span class="stat-label">Total User</span>
        <strong class="stat-number"><?= $totalUser ?></strong>
      </article>
      <article class="stat-card stat-blue">
        <span class="stat-label">WA Terkirim</span>
        <strong class="stat-number"><?= $totalTerkirim ?></strong>
      </article>
      <article class="stat-card stat-red">
        <span class="stat-label">WA Gagal</span>
        <strong class="stat-number"><?= $totalGagal ?></strong>
      </article>
      <article class="stat-card stat-orange">
        <span class="stat-label">WA Pending</span>
        <strong class="stat-number"><?= $totalPending ?></strong>
      </article>
    </section>

    <section class="content-grid admin-grid">
      <article class="panel">
        <div class="panel-header">
          <h3>Registrasi Terbaru</h3>
          <span class="mini-tag">Update</span>
        </div>
        <table class="data-table">
          <thead>
            <tr><th>Nama</th><th>Email</th><th>No. WhatsApp</th><th>Tanggal</th></tr>
          </thead>
          <tbody>
            <?php if (empty($userTerbaru)): ?>
              <tr><td colspan="4">Belum ada data.</td></tr>
            <?php else: ?>
              <?php foreach ($userTerbaru as $u): ?>
                <tr>
                  <td><?= htmlspecialchars($u['nama']) ?></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><?= htmlspecialchars($u['no_hp']) ?></td>
                  <td><?= htmlspecialchars($u['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </article>

      <article class="panel" style="min-height: 100%;">
        <div class="panel-header">
          <h3>Quick Actions</h3>
          <span class="mini-tag">Menu</span>
        </div>
        <div class="quick-actions">
          <a href="kelola-user.php" class="action-card">
            <span>👥</span>
            <strong>Kelola User</strong>
          </a>
          <a href="admin.php" class="action-card">
            <span>📲</span>
            <strong>Laporan WA</strong>
          </a>
          <a href="logout.php" class="action-card danger-card">
            <span>⎋</span>
            <strong>Logout</strong>
          </a>
        </div>
      </article>
    </section>
  </main>
</div>
</body>
</html>
