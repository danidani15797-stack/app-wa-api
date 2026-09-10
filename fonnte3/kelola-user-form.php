<?php
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/config/database.php';

requireLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$isEdit = $id !== null;

$user = [
    'nama'   => '',
    'email'  => '',
    'no_hp'  => '',
    'alamat' => '',
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT id, nama, email, no_hp, alamat FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $found = $stmt->fetch();

    if (!$found) {
        header('Location: kelola-user.php?flash=User tidak ditemukan.&flash_type=failed');
        exit;
    }
    $user = $found;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= $isEdit ? 'Edit User' : 'Tambah User' ?> - API WhatsApp Fonnte</title>
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

    <nav class="sidebar-nav">
      <a href="dashboard.php"><span>◉</span> Dashboard</a>
      <a href="kelola-user.php" class="active"><span>◌</span> Data User</a>
      <a href="admin-log.php"><span>◍</span> Riwayat Log</a>
      <a href="admin-accounts.php"><span>◍</span> Kelola Admin</a>
      <a href="admin.php"><span>◍</span> Laporan WA</a>
      <a href="logout.php"><span>↩</span> Logout</a>
    </nav>

    <div class="sidebar-card light-card">
      <small>Manajemen</small>
      <strong>Pengguna</strong>
      <span class="status-pill success">Aktif</span>
    </div>
  </aside>

  <main class="admin-main">
    <header class="topbar admin-topbar">
      <div>
        <p class="eyebrow">Manajemen</p>
        <h1><?= $isEdit ? 'Edit User' : 'Tambah User' ?></h1>
      </div>
      <div class="topbar-actions">
        <a href="kelola-user.php" class="modern-btn secondary">Kembali</a>
      </div>
    </header>

    <section class="panel form-shell">
      <div class="panel-header form-header">
        <h3><?= $isEdit ? 'Perbarui data user' : 'Tambah data user baru' ?></h3>
        <span class="mini-tag"><?= $isEdit ? 'Edit' : 'Baru' ?></span>
      </div>

      <form method="POST" action="api/user_save.php" class="modern-form">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <?php if ($isEdit): ?>
          <input type="hidden" name="id" value="<?= $user['id'] ?>">
        <?php endif; ?>

        <div class="form-grid modern-form-grid">
          <div class="field">
            <label>Nama</label>
            <input type="text" name="nama" required minlength="3" value="<?= htmlspecialchars($user['nama']) ?>">
          </div>

          <div class="field">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
          </div>

          <div class="field full">
            <label>Nomor WhatsApp</label>
            <input type="text" name="no_hp" required value="<?= htmlspecialchars($user['no_hp']) ?>">
          </div>

          <div class="field full">
            <label>Alamat</label>
            <textarea name="alamat" required><?= htmlspecialchars($user['alamat']) ?></textarea>
          </div>

          <div class="field full">
            <label>Password <?= $isEdit ? '<span class="hint-inline">(kosongkan jika tidak diubah)</span>' : '' ?></label>
            <input type="password" name="password" <?= $isEdit ? '' : 'required' ?> minlength="6" placeholder="Minimal 6 karakter">
          </div>
        </div>

        <div class="form-actions modern-form-actions">
          <a href="kelola-user.php" class="modern-btn secondary">Batal</a>
          <button type="submit" class="modern-btn primary action-submit"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah User' ?></button>
        </div>
      </form>
    </section>
  </main>
</div>
</body>
</html>
