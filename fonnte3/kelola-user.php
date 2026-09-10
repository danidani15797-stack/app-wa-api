<?php
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/config/database.php';

requireLogin();

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $stmt = $pdo->prepare(
        'SELECT id, nama, email, no_hp, alamat, created_at
         FROM users
         WHERE nama LIKE :q OR email LIKE :q OR no_hp LIKE :q
         ORDER BY created_at DESC'
    );
    $stmt->execute(['q' => "%{$q}%"]);
} else {
    $stmt = $pdo->query(
        'SELECT id, nama, email, no_hp, alamat, created_at FROM users ORDER BY created_at DESC'
    );
}

$users = $stmt->fetchAll();
$flash = $_GET['flash'] ?? '';
$flashType = $_GET['flash_type'] ?? 'success';
$admin = currentAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data User Admin - API WhatsApp Fonnte</title>
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
      <a href="kelola-user.php" class="active"><span>◌</span> Data User</a>
      <a href="admin-log.php"><span>◍</span> Riwayat Log</a>
      <a href="admin-accounts.php"><span>◍</span> Kelola Admin</a>
      <a href="admin.php"><span>◍</span> Laporan WA</a>
      <a href="logout.php"><span>↩</span> Logout</a>
    </nav>

    <div class="sidebar-card light-card">
      <small>Data User</small>
      <strong><?= count($users) ?> pengguna</strong>
      <span class="status-pill success">Terdaftar</span>
    </div>
  </aside>

  <main class="admin-main">
    <header class="topbar admin-topbar">
      <div>
        <p class="eyebrow">Manajemen</p>
        <h1>Data User</h1>
      </div>
      <div class="topbar-actions">
        <a href="kelola-user-form.php" class="modern-btn primary">+ Tambah User</a>
      </div>
    </header>

    <?php if ($flash): ?>
      <div class="alert <?= $flashType === 'success' ? 'success' : 'failed' ?>">
        <strong><?= htmlspecialchars($flash) ?></strong>
      </div>
    <?php endif; ?>

    <section class="panel search-panel">
      <form method="GET" class="search-form admin-search">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama, email, atau nomor WhatsApp...">
        <button type="submit" class="modern-btn secondary search-btn">Cari</button>
      </form>
    </section>

    <section class="panel">
      <div class="panel-header">
        <h3>Daftar User</h3>
        <span class="mini-tag">Terbaru</span>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>No. WhatsApp</th>
            <th>Alamat</th>
            <th>Terdaftar</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
            <tr><td colspan="6">Tidak ada data user.</td></tr>
          <?php else: ?>
            <?php foreach ($users as $u): ?>
              <tr>
                <td><?= htmlspecialchars($u['nama']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['no_hp']) ?></td>
                <td><?= htmlspecialchars($u['alamat']) ?></td>
                <td><?= htmlspecialchars($u['created_at']) ?></td>
                <td class="actions">
                  <a href="kelola-user-form.php?id=<?= $u['id'] ?>" class="btn-edit">Edit</a>
                  <form method="POST" action="api/user_delete.php" class="inline-form" onsubmit="return confirm('Yakin ingin menghapus user ini? Log WhatsApp terkait juga akan terpengaruh.');">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <button type="submit" class="btn-delete">Hapus</button>
                  </form>
                </td>
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
