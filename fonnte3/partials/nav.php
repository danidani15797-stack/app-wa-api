<?php
/** Partial navbar - dipakai di dashboard.php, kelola-user.php, admin.php */
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$admin = currentAdmin();
?>
<nav class="navbar">
  <div class="navbar-brand">API WhatsApp Fonnte</div>
  <div class="navbar-links">
    <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="kelola-user.php" class="<?= $currentPage === 'kelola-user.php' ? 'active' : '' ?>">Kelola User</a>
    <a href="admin.php" class="<?= $currentPage === 'admin.php' ? 'active' : '' ?>">Laporan Notifikasi</a>
  </div>
  <div class="navbar-user">
    <span><?= htmlspecialchars($admin['username'] ?? '') ?></span>
    <a href="logout.php" class="btn-logout">Logout</a>
  </div>
</nav>
