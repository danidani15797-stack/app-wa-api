<?php
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/config/database.php';

requireUserLogin();

$stmt = $pdo->prepare('SELECT id, nama, email, no_hp, alamat, created_at FROM users WHERE id = :id');
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: user-logout.php');
    exit;
}

$flash = $_GET['flash'] ?? '';
$flashType = $_GET['flash_type'] ?? 'success';
$created = date('d M Y', strtotime($user['created_at']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data User - API WhatsApp Fonnte</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body.user-body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
    min-height: 100vh;
    color: #f1f5f9;
    position: relative;
    overflow-x: hidden;
  }

  body.user-body::before {
    content: '';
    position: fixed;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(37, 211, 102, 0.1) 0%, transparent 70%);
    top: -250px;
    right: -250px;
    border-radius: 50%;
    animation: floatBg 12s ease-in-out infinite;
    pointer-events: none;
    z-index: 0;
  }

  body.user-body::after {
    content: '';
    position: fixed;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);
    bottom: -200px;
    left: -200px;
    border-radius: 50%;
    animation: floatBg 15s ease-in-out infinite reverse;
    pointer-events: none;
    z-index: 0;
  }

  @keyframes floatBg {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(40px, -40px) scale(1.1); }
  }

  .user-shell {
    display: grid;
    grid-template-columns: 280px 1fr;
    min-height: 100vh;
    position: relative;
    z-index: 1;
  }

  /* Sidebar */
  .sidebar {
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-right: 1px solid rgba(255, 255, 255, 0.08);
    padding: 28px 22px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
  }

  .sidebar::-webkit-scrollbar {
    width: 6px;
  }

  .sidebar::-webkit-scrollbar-thumb {
    background: rgba(37, 211, 102, 0.3);
    border-radius: 3px;
  }

  .brand-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }

  .brand-mark {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 700;
    color: white;
    box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4);
    animation: pulseGlow 3s ease-in-out infinite;
  }

  @keyframes pulseGlow {
    0%, 100% {
      box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4), 0 0 0 0 rgba(37, 211, 102, 0.5);
    }
    50% {
      box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4), 0 0 0 15px rgba(37, 211, 102, 0);
    }
  }

  .brand-title {
    font-size: 15px;
    font-weight: 700;
    color: #f1f5f9;
    letter-spacing: 0.3px;
  }

  .brand-subtitle {
    font-size: 11px;
    color: #64748b;
    font-weight: 400;
    letter-spacing: 0.5px;
  }

  .profile-mini {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: rgba(37, 211, 102, 0.08);
    border: 1px solid rgba(37, 211, 102, 0.15);
    border-radius: 14px;
    transition: all 0.3s ease;
  }

  .profile-mini:hover {
    background: rgba(37, 211, 102, 0.12);
    border-color: rgba(37, 211, 102, 0.3);
    transform: translateY(-2px);
  }

  .avatar {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
  }

  .profile-mini strong {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #f1f5f9;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
  }

  .profile-mini small {
    font-size: 11px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    max-width: 160px;
  }

  .sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
  }

  .sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    color: #94a3b8;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 500;
    border-radius: 10px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .sidebar-nav a span {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    transition: transform 0.3s ease;
  }

  .sidebar-nav a span svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
  }

  .sidebar-nav a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, #25D366, #128C7E);
    transform: scaleY(0);
    transition: transform 0.3s ease;
    border-radius: 0 3px 3px 0;
  }

  .sidebar-nav a:hover {
    background: rgba(255, 255, 255, 0.04);
    color: #f1f5f9;
  }

  .sidebar-nav a:hover span {
    transform: scale(1.15);
  }

  .sidebar-nav a:hover::before {
    transform: scaleY(1);
  }

  .sidebar-nav a.active {
    background: linear-gradient(135deg, rgba(37, 211, 102, 0.15) 0%, rgba(18, 140, 126, 0.1) 100%);
    color: #25D366;
    border: 1px solid rgba(37, 211, 102, 0.2);
  }

  .sidebar-nav a.active::before {
    transform: scaleY(1);
  }

  .sidebar-nav a.active span svg {
    stroke: #25D366;
  }

  .sidebar-card {
    background: linear-gradient(135deg, rgba(37, 211, 102, 0.1) 0%, rgba(18, 140, 126, 0.05) 100%);
    border: 1px solid rgba(37, 211, 102, 0.2);
    border-radius: 14px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .sidebar-card small {
    font-size: 11px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 500;
  }

  .sidebar-card strong {
    font-size: 15px;
    font-weight: 600;
    color: #f1f5f9;
  }

  .status-pill {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    align-self: flex-start;
  }

  .status-pill.success {
    background: rgba(37, 211, 102, 0.2);
    color: #86efac;
    border: 1px solid rgba(37, 211, 102, 0.3);
    position: relative;
    padding-left: 22px;
  }

  .status-pill.success::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 7px;
    height: 7px;
    background: #25D366;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
    animation: onlinePulse 2s ease-in-out infinite;
  }

  @keyframes onlinePulse {
    0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
    70% { box-shadow: 0 0 0 8px rgba(37, 211, 102, 0); }
    100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
  }

  /* Main */
  .user-main {
    padding: 32px 40px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    animation: fadeInUp 0.6s ease-out;
  }

  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
  }

  .topbar .eyebrow {
    font-size: 12px;
    color: #94a3b8;
    font-weight: 500;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    margin-bottom: 6px;
  }

  .topbar h1 {
    font-size: 26px;
    font-weight: 700;
    color: #f1f5f9;
    letter-spacing: 0.3px;
  }

  .modern-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 22px;
    border: none;
    border-radius: 12px;
    font-family: 'Poppins', sans-serif;
    font-size: 13.5px;
    font-weight: 600;
    letter-spacing: 0.4px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .modern-btn.primary {
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: white;
    box-shadow: 0 8px 20px rgba(37, 211, 102, 0.35);
  }

  .modern-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(37, 211, 102, 0.5);
  }

  .modern-btn.secondary {
    background: rgba(255, 255, 255, 0.05);
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.1);
  }

  .modern-btn.secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    color: #f1f5f9;
    transform: translateY(-2px);
  }

  .modern-btn svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    stroke-width: 2.5;
    transition: transform 0.3s ease;
  }

  .modern-btn:hover svg {
    transform: scale(1.15) rotate(-10deg);
  }

  .modern-btn.secondary:hover svg {
    transform: translateX(-4px);
  }

  .modern-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s ease;
  }

  .modern-btn:hover::before {
    left: 100%;
  }

  /* Alert */
  .alert {
    padding: 16px 20px;
    border-radius: 14px;
    font-size: 13.5px;
    line-height: 1.6;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: fadeIn 0.4s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .alert svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
  }

  .alert.success {
    background: rgba(37, 211, 102, 0.1);
    border: 1px solid rgba(37, 211, 102, 0.3);
    color: #86efac;
  }

  .alert.success svg { stroke: #86efac; }

  .alert.failed {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
  }

  .alert.failed svg {
    stroke: #fca5a5;
    animation: shake 0.5s ease;
  }

  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    75% { transform: translateX(4px); }
  }

  /* Settings Grid */
  .settings-grid {
    display: grid;
    grid-template-columns: 1.7fr 1fr;
    gap: 20px;
    align-items: start;
  }

  .panel {
    background: rgba(30, 41, 59, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 18px;
    padding: 26px 28px;
    transition: all 0.3s ease;
  }

  .panel:hover {
    border-color: rgba(255, 255, 255, 0.15);
    box-shadow: 0 15px 30px -15px rgba(0, 0, 0, 0.5);
  }

  .panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  }

  .panel-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: #f1f5f9;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .panel-header h3 svg {
    width: 18px;
    height: 18px;
    stroke: #25D366;
    stroke-width: 2;
  }

  .mini-tag {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    background: rgba(37, 211, 102, 0.15);
    color: #25D366;
    border: 1px solid rgba(37, 211, 102, 0.25);
  }

  /* User Form */
  .user-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
  }

  .field {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .field.full {
    grid-column: 1 / -1;
  }

  .field label {
    font-size: 12px;
    font-weight: 600;
    color: #94a3b8;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .field label svg {
    width: 14px;
    height: 14px;
    stroke: #64748b;
    stroke-width: 2;
    transition: stroke 0.3s ease;
  }

  .field:focus-within label svg {
    stroke: #25D366;
  }

  .field input,
  .field textarea {
    width: 100%;
    padding: 13px 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1.5px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #f1f5f9;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    transition: all 0.3s ease;
    outline: none;
  }

  .field input::placeholder,
  .field textarea::placeholder {
    color: #64748b;
    font-weight: 300;
  }

  .field input:focus,
  .field textarea:focus {
    border-color: #25D366;
    background: rgba(37, 211, 102, 0.05);
    box-shadow: 0 0 0 4px rgba(37, 211, 102, 0.1);
  }

  .field textarea {
    resize: vertical;
    min-height: 90px;
  }

  .form-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
  }

  .submit-btn {
    padding: 13px 28px;
    font-size: 14px;
  }

  /* Account Box */
  .panel-side {
    position: sticky;
    top: 32px;
  }

  .account-box {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .account-row {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 14px 16px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .account-row::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, #25D366, #128C7E);
    transform: scaleY(0);
    transition: transform 0.3s ease;
  }

  .account-row:hover {
    background: rgba(37, 211, 102, 0.06);
    border-color: rgba(37, 211, 102, 0.2);
    transform: translateX(4px);
  }

  .account-row:hover::before {
    transform: scaleY(1);
  }

  .account-row span {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    letter-spacing: 0.8px;
    text-transform: uppercase;
  }

  .account-row strong {
    font-size: 14px;
    font-weight: 600;
    color: #f1f5f9;
    word-break: break-word;
    line-height: 1.4;
  }

  /* Responsive */
  @media (max-width: 1024px) {
    .settings-grid {
      grid-template-columns: 1fr;
    }

    .panel-side {
      position: relative;
      top: 0;
    }
  }

  @media (max-width: 860px) {
    .user-shell {
      grid-template-columns: 1fr;
    }

    .sidebar {
      position: relative;
      height: auto;
      border-right: none;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .sidebar-nav {
      flex-direction: row;
      flex-wrap: wrap;
    }

    .sidebar-nav a {
      flex: 1 1 auto;
      min-width: 140px;
    }

    .sidebar-nav a::before {
      top: auto;
      bottom: 0;
      left: 0;
      right: 0;
      width: 100%;
      height: 3px;
      transform: scaleX(0);
    }

    .sidebar-nav a:hover::before,
    .sidebar-nav a.active::before {
      transform: scaleX(1);
    }

    .sidebar-card {
      display: none;
    }

    .user-main {
      padding: 24px 20px;
    }

    .form-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 480px) {
    .topbar {
      flex-direction: column;
      align-items: flex-start;
    }

    .topbar h1 {
      font-size: 22px;
    }

    .panel {
      padding: 22px 20px;
    }

    .form-actions {
      justify-content: stretch;
    }

    .submit-btn {
      width: 100%;
      justify-content: center;
    }
  }
</style>
</head>
<body class="user-body">
<div class="user-shell">
  <aside class="sidebar">
    <div class="brand-wrap">
      <div class="brand-mark">W</div>
      <div>
        <div class="brand-title">API WhatsApp</div>
        <div class="brand-subtitle">Fonnte</div>
      </div>
    </div>

    <div class="profile-mini">
      <div class="avatar"><?= strtoupper(substr($user['nama'], 0, 1)) ?></div>
      <div>
        <strong><?= htmlspecialchars($user['nama']) ?></strong>
        <small><?= htmlspecialchars($user['email']) ?></small>
      </div>
    </div>

    <nav class="sidebar-nav">
      <a href="user-dashboard.php">
        <span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="9"></rect>
            <rect x="14" y="3" width="7" height="5"></rect>
            <rect x="14" y="12" width="7" height="9"></rect>
            <rect x="3" y="16" width="7" height="5"></rect>
          </svg>
        </span>
        Dashboard
      </a>
      <a href="user-data.php" class="active">
        <span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </span>
        Data User
      </a>
      <a href="user-log.php">
        <span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
        </span>
        Riwayat Log
      </a>
      <a href="user-delete-account.php">
        <span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            <line x1="10" y1="11" x2="10" y2="17"></line>
            <line x1="14" y1="11" x2="14" y2="17"></line>
          </svg>
        </span>
        Hapus Akun
      </a>
      <a href="user-logout.php">
        <span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
        </span>
        Logout
      </a>
    </nav>

    <div class="sidebar-card">
      <small>Keamanan</small>
      <strong>Proteksi aktif</strong>
      <span class="status-pill success">Verified</span>
    </div>
  </aside>

  <main class="user-main">
    <header class="topbar">
      <div>
        <p class="eyebrow">Pengaturan akun</p>
        <h1>Data User</h1>
      </div>
      <a href="user-dashboard.php" class="modern-btn secondary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Kembali
      </a>
    </header>

    <?php if ($flash): ?>
      <div class="alert <?= $flashType === 'success' ? 'success' : 'failed' ?>">
        <?php if ($flashType === 'success'): ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
        <?php else: ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
        <?php endif; ?>
        <strong><?= htmlspecialchars($flash) ?></strong>
      </div>
    <?php endif; ?>

    <section class="settings-grid">
      <article class="panel form-panel">
        <div class="panel-header">
          <h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Update Profil
          </h3>
          <span class="mini-tag">Akun</span>
        </div>

        <form method="POST" action="api/user_profile_update.php" class="user-form">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

          <div class="form-grid">
            <div class="field">
              <label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Nama
              </label>
              <input type="text" name="nama" required minlength="3" value="<?= htmlspecialchars($user['nama']) ?>">
            </div>

            <div class="field">
              <label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                Email
              </label>
              <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
            </div>

            <div class="field full">
              <label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                Alamat
              </label>
              <textarea name="alamat" required><?= htmlspecialchars($user['alamat']) ?></textarea>
            </div>

            <div class="field">
              <label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Password Lama
              </label>
              <input type="password" name="old_password" placeholder="Wajib jika ingin ganti password">
            </div>

            <div class="field">
              <label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                Password Baru
              </label>
              <input type="password" name="new_password" minlength="6" placeholder="Minimal 6 karakter">
            </div>
          </div>

          <div class="form-actions modern-actions">
            <button type="submit" class="modern-btn primary submit-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              Simpan Perubahan
            </button>
          </div>
        </form>
      </article>

      <article class="panel panel-side">
        <div class="panel-header">
          <h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            Informasi Akun
          </h3>
          <span class="mini-tag">Ringkas</span>
        </div>

        <div class="account-box">
          <div class="account-row">
            <span>Nama</span>
            <strong><?= htmlspecialchars($user['nama']) ?></strong>
          </div>
          <div class="account-row">
            <span>Email</span>
            <strong><?= htmlspecialchars($user['email']) ?></strong>
          </div>
          <div class="account-row">
            <span>Nomor WA</span>
            <strong><?= htmlspecialchars($user['no_hp']) ?></strong>
          </div>
          <div class="account-row">
            <span>Terdaftar</span>
            <strong><?= htmlspecialchars($created) ?></strong>
          </div>
        </div>
      </article>
    </section>
  </main>
</div>
</body>
</html>