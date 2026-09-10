<?php
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/config/database.php';

requireLogin();

$sql = "
    SELECT
        u.id,
        u.nama,
        u.email,
        u.no_hp,
        u.created_at,
        lw.status   AS wa_status,
        lw.pesan    AS wa_pesan,
        lw.created_at AS wa_created_at
    FROM users u
    LEFT JOIN (
        SELECT l1.*
        FROM log_whatsapp l1
        INNER JOIN (
            SELECT user_id, MAX(id) AS max_id
            FROM log_whatsapp
            GROUP BY user_id
        ) l2 ON l1.user_id = l2.user_id AND l1.id = l2.max_id
    ) lw ON lw.user_id = u.id
    ORDER BY u.created_at DESC
";

$rows = $pdo->query($sql)->fetchAll();
$admin = currentAdmin();

function badgeStatus(?string $status): string
{
    return match ($status) {
        'success' => '<span class="badge success">Terkirim</span>',
        'failed'  => '<span class="badge failed">Gagal</span>',
        'pending' => '<span class="badge pending">Pending</span>',
        default   => '<span class="badge pending">-</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Notifikasi WhatsApp - Admin</title>
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
      <a href="admin-accounts.php"><span>◍</span> Kelola Admin</a>
      <a href="admin.php" class="active"><span>◍</span> Laporan WA</a>
      <a href="logout.php"><span>↩</span> Logout</a>
    </nav>

    <div class="sidebar-card light-card">
      <small>Notifikasi</small>
      <strong>WhatsApp</strong>
      <span class="status-pill success">Live</span>
    </div>
  </aside>

  <main class="admin-main">
    <header class="topbar admin-topbar">
      <div>
        <p class="eyebrow">Monitoring</p>
        <h1>Laporan Notifikasi</h1>
      </div>
      <div class="topbar-actions">
        <a href="dashboard.php" class="modern-btn secondary">Kembali</a>
      </div>
    </header>

    <section class="panel">
      <div class="panel-header">
        <h3>Status Pengiriman WhatsApp</h3>
        <span class="mini-tag">Realtime</span>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Nomor WhatsApp</th>
            <th>Tanggal Registrasi</th>
            <th>Registrasi</th>
            <th>Status WhatsApp</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr><td colspan="6">Belum ada data registrasi.</td></tr>
          <?php else: ?>
            <?php foreach ($rows as $i => $r): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($r['nama']) ?></td>
                <td><?= htmlspecialchars($r['no_hp']) ?></td>
                <td><?= htmlspecialchars($r['created_at']) ?></td>
                <td><span class="badge success">Berhasil</span></td>
                <td><?= badgeStatus($r['wa_status']) ?></td>
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
