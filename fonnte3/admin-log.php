<?php
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/config/database.php';

requireLogin();

$admin = currentAdmin();

$stmt = $pdo->query(
    'SELECT l.id, u.nama, u.email, l.no_tujuan, l.pesan, l.status, l.response, l.created_at
     FROM log_whatsapp l
     LEFT JOIN users u ON u.id = l.user_id
     ORDER BY l.created_at DESC'
);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Log Admin - API WhatsApp Fonnte</title>
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
      <a href="admin-log.php" class="active"><span>◍</span> Riwayat Log</a>
      <a href="admin-accounts.php"><span>◍</span> Kelola Admin</a>
      <a href="admin.php"><span>◍</span> Laporan WA</a>
      <a href="logout.php"><span>↩</span> Logout</a>
    </nav>

    <div class="sidebar-card light-card">
      <small>Riwayat</small>
      <strong><?= count($logs) ?> log</strong>
      <span class="status-pill info">Semua User</span>
    </div>
  </aside>

  <main class="admin-main">
    <header class="topbar admin-topbar">
      <div>
        <p class="eyebrow">Monitoring</p>
        <h1>Riwayat Log Semua User</h1>
      </div>
      <div class="topbar-actions">
        <a href="dashboard.php" class="modern-btn secondary">Kembali</a>
      </div>
    </header>

    <section class="panel">
      <div class="panel-header">
        <h3>Log Notifikasi WhatsApp</h3>
        <span class="mini-tag">Semua transaksi</span>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama User</th>
            <th>Email</th>
            <th>Tujuan</th>
            <th>Pesan</th>
            <th>Status</th>
            <th>Waktu</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr><td colspan="7">Belum ada riwayat log.</td></tr>
          <?php else: ?>
            <?php foreach ($logs as $i => $log): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($log['nama'] ?? '-') ?></td>
                <td><?= htmlspecialchars($log['email'] ?? '-') ?></td>
                <td><?= htmlspecialchars($log['no_tujuan']) ?></td>
                <td><?= nl2br(htmlspecialchars($log['pesan'])) ?></td>
                <td>
                  <?php
                    $status = strtolower((string)($log['status'] ?? 'pending'));
                    $label = match ($status) {
                        'success' => 'Terkirim',
                        'failed' => 'Gagal',
                        'pending' => 'Pending',
                        default => 'Pending',
                    };
                    $class = match ($status) {
                        'success' => 'success',
                        'failed' => 'failed',
                        'pending' => 'pending',
                        default => 'pending',
                    };
                  ?>
                  <span class="badge <?= $class ?>"><?= $label ?></span>
                </td>
                <td><?= htmlspecialchars($log['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>
</div>
</body>
</html>
