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
<title>Dashboard Saya - API WhatsApp Fonnte</title>
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

  .modern-btn svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    stroke-width: 2.5;
    transition: transform 0.3s ease;
  }

  .modern-btn:hover svg {
    transform: rotate(-15deg) scale(1.1);
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

  .alert.failed svg { stroke: #fca5a5; }

  /* Welcome Card */
  .welcome-card {
    background: linear-gradient(135deg, rgba(37, 211, 102, 0.15) 0%, rgba(18, 140, 126, 0.08) 100%);
    border: 1px solid rgba(37, 211, 102, 0.2);
    border-radius: 20px;
    padding: 32px 36px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
  }

  .welcome-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(37, 211, 102, 0.2) 0%, transparent 70%);
    border-radius: 50%;
    animation: floatBg 8s ease-in-out infinite;
  }

  .welcome-card:hover {
    border-color: rgba(37, 211, 102, 0.4);
    transform: translateY(-2px);
    box-shadow: 0 20px 40px -15px rgba(37, 211, 102, 0.3);
  }

  .welcome-card > div {
    position: relative;
    z-index: 1;
  }

  .eyebrow.accent {
    color: #25D366;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    margin-bottom: 8px;
  }

  .welcome-card h2 {
    font-size: 24px;
    font-weight: 700;
    color: #f1f5f9;
    margin-bottom: 8px;
    letter-spacing: 0.3px;
  }

  .welcome-card p {
    color: #94a3b8;
    font-size: 14px;
    line-height: 1.6;
    max-width: 500px;
  }

  .welcome-badge {
    padding: 10px 20px;
    background: rgba(37, 211, 102, 0.15);
    border: 1px solid rgba(37, 211, 102, 0.35);
    border-radius: 30px;
    font-size: 12.5px;
    font-weight: 600;
    color: #25D366;
    letter-spacing: 0.5px;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
    z-index: 1;
  }

  .welcome-badge::before {
    content: '';
    width: 8px;
    height: 8px;
    background: #25D366;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
    animation: onlinePulse 2s ease-in-out infinite;
  }

  /* Stats Grid */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 18px;
  }

  .stat-card {
    background: rgba(30, 41, 59, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 20px 22px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    cursor: pointer;
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    transition: width 0.3s ease;
  }

  .stat-card::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.4s ease;
  }

  .stat-card:hover {
    transform: translateY(-4px);
    border-color: rgba(255, 255, 255, 0.15);
  }

  .stat-card:hover::after {
    opacity: 0.4;
  }

  .stat-card:hover::before {
    width: 6px;
  }

  .stat-card .stat-label {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    letter-spacing: 1.2px;
    text-transform: uppercase;
  }

  .stat-card strong {
    font-size: 16px;
    font-weight: 600;
    color: #f1f5f9;
    word-break: break-word;
    line-height: 1.4;
  }

  .stat-green::before { background: linear-gradient(180deg, #25D366, #128C7E); }
  .stat-green::after { background: radial-gradient(circle, rgba(37, 211, 102, 0.3) 0%, transparent 70%); }
  .stat-green:hover { box-shadow: 0 15px 30px -10px rgba(37, 211, 102, 0.3); }

  .stat-blue::before { background: linear-gradient(180deg, #3b82f6, #1e40af); }
  .stat-blue::after { background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, transparent 70%); }
  .stat-blue:hover { box-shadow: 0 15px 30px -10px rgba(59, 130, 246, 0.3); }

  .stat-purple::before { background: linear-gradient(180deg, #a855f7, #7e22ce); }
  .stat-purple::after { background: radial-gradient(circle, rgba(168, 85, 247, 0.3) 0%, transparent 70%); }
  .stat-purple:hover { box-shadow: 0 15px 30px -10px rgba(168, 85, 247, 0.3); }

  .stat-orange::before { background: linear-gradient(180deg, #f59e0b, #d97706); }
  .stat-orange::after { background: radial-gradient(circle, rgba(245, 158, 11, 0.3) 0%, transparent 70%); }
  .stat-orange:hover { box-shadow: 0 15px 30px -10px rgba(245, 158, 11, 0.3); }

  /* Content Grid */
  .content-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 20px;
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
    transform: translateY(-2px);
    box-shadow: 0 15px 30px -15px rgba(0, 0, 0, 0.5);
  }

  .panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  }

  .panel-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: #f1f5f9;
    letter-spacing: 0.3px;
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

  .info-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .info-list > div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 12px 14px;
    border-radius: 10px;
    transition: all 0.25s ease;
  }

  .info-list > div:hover {
    background: rgba(37, 211, 102, 0.06);
    transform: translateX(4px);
  }

  .info-list > div span {
    font-size: 12.5px;
    color: #94a3b8;
    font-weight: 500;
    letter-spacing: 0.3px;
  }

  .info-list > div strong {
    font-size: 13.5px;
    color: #f1f5f9;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
  }

  .feature-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .feature-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 10px;
    font-size: 13.5px;
    color: #cbd5e1;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .feature-list li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, #25D366, #128C7E);
    transform: scaleY(0);
    transform-origin: center;
    transition: transform 0.3s ease;
  }

  .feature-list li:hover {
    background: rgba(37, 211, 102, 0.08);
    border-color: rgba(37, 211, 102, 0.2);
    color: #f1f5f9;
    transform: translateX(4px);
  }

  .feature-list li:hover::before {
    transform: scaleY(1);
  }

  .feature-list li svg {
    width: 16px;
    height: 16px;
    stroke: #25D366;
    flex-shrink: 0;
    transition: transform 0.3s ease;
  }

  .feature-list li:hover svg {
    transform: scale(1.2) rotate(10deg);
  }

  /* Responsive */
  @media (max-width: 1024px) {
    .content-grid {
      grid-template-columns: 1fr;
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

    .welcome-card {
      flex-direction: column;
      align-items: flex-start;
      padding: 24px;
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

    .welcome-card h2 {
      font-size: 20px;
    }

    .stats-grid {
      grid-template-columns: 1fr;
    }

    .info-list > div {
      flex-direction: column;
      align-items: flex-start;
      gap: 4px;
    }

    .info-list > div strong {
      text-align: left;
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
      <a href="user-dashboard.php" class="active">
        <span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="9"></rect>
            <rect x="14" y="3" width="7" height="5"></rect>
            <rect x="14" y="12" width="7" height="9"></rect>
            <rect x="3" y="16" width="7" height="5"></rect>
          </svg>
        </span>
        Dashboard Siswa
      </a>
      <a href="user-data.php">
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
      <small>Status Akun</small>
      <strong>Aktif</strong>
      <span class="status-pill success">Online</span>
    </div>
  </aside>

  <main class="user-main">
    <header class="topbar">
      <div>
        <p class="eyebrow">Selamat datang</p>
        <h1>Dashboard Siswa</h1>
      </div>
      <a href="user-data.php" class="modern-btn primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
        Edit Profil
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

    <section class="welcome-card">
      <div>
        <p class="eyebrow accent">Halo, <?= htmlspecialchars($user['nama']) ?></p>
        <h2>Semangat bekerja hari ini!</h2>
        <p>Kelola profil, keamanan, dan informasi akun Anda dengan lebih mudah dan aman.</p>
      </div>
      <div class="welcome-badge">WhatsApp Ready</div>
    </section>

    <section class="stats-grid">
      <article class="stat-card stat-green">
        <span class="stat-label">Nama</span>
        <strong><?= htmlspecialchars($user['nama']) ?></strong>
      </article>
      <article class="stat-card stat-blue">
        <span class="stat-label">Email</span>
        <strong><?= htmlspecialchars($user['email']) ?></strong>
      </article>
      <article class="stat-card stat-purple">
        <span class="stat-label">Nomor WA</span>
        <strong><?= htmlspecialchars($user['no_hp']) ?></strong>
      </article>
      <article class="stat-card stat-orange">
        <span class="stat-label">Terdaftar</span>
        <strong><?= htmlspecialchars($created) ?></strong>
      </article>
    </section>

    <section class="content-grid">
      <article class="panel panel-info">
        <div class="panel-header">
          <h3>Informasi Akun</h3>
          <span class="mini-tag">Detail</span>
        </div>
        <div class="info-list">
          <div><span>Nama</span><strong><?= htmlspecialchars($user['nama']) ?></strong></div>
          <div><span>Email</span><strong><?= htmlspecialchars($user['email']) ?></strong></div>
          <div><span>Nomor WhatsApp</span><strong><?= htmlspecialchars($user['no_hp']) ?></strong></div>
          <div><span>Alamat</span><strong><?= htmlspecialchars($user['alamat']) ?></strong></div>
          <div><span>Terdaftar sejak</span><strong><?= htmlspecialchars($created) ?></strong></div>
        </div>
      </article>

      <article class="panel panel-quick">
        <div class="panel-header">
          <h3>Fitur</h3>
          <span class="mini-tag">Cepat</span>
        </div>
        <ul class="feature-list">
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Kelola profil akun
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            Ubah password aman
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
            </svg>
            Notifikasi WhatsApp otomatis
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            Data akun tersimpan rapi
          </li>
        </ul>
      </article>
    </section>
  </main>
</div>
</body>
</html>