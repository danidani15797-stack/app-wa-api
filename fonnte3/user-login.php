<?php
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/fonnte.php';

// Jika sudah login, langsung arahkan ke dashboard user
if (!empty($_SESSION['user_id'])) {
    header('Location: user-dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nama, email, no_hp, password FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Regenerasi session id untuk mencegah session fixation
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_nama']  = $user['nama'];
            $_SESSION['user_email'] = $user['email'];

            $loginPesan = formatHeaderWa('Login berhasil')
                        . "Halo {$user['nama']},\n\n"
                        . "Kegiatan login akun Anda berhasil dilakukan pada sistem kami.\n\n"
                        . "📧 Email: {$user['email']}\n"
                        . "📱 Nomor WhatsApp: {$user['no_hp']}\n"
                        . "🕒 Status: Login berhasil\n\n"
                        . "Jika Anda merasa tidak melakukan login ini, segera hubungi administrator untuk keamanan akun Anda.";

            $hasilLoginWa = kirimWhatsApp($user['no_hp'], $loginPesan);

            $logStmt = $pdo->prepare(
                'INSERT INTO log_whatsapp (user_id, no_tujuan, pesan, status, response)
                 VALUES (:user_id, :no_tujuan, :pesan, :status, :response)'
            );
            $logStmt->execute([
                'user_id'   => $user['id'],
                'no_tujuan' => $user['no_hp'],
                'pesan'     => $loginPesan,
                'status'    => $hasilLoginWa['status'],
                'response'  => $hasilLoginWa['response'],
            ]);

            header('Location: user-dashboard.php');
            exit;
        }

        $error = 'Email atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login User - API WhatsApp Fonnte</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
    padding: 20px;
    position: relative;
    overflow-x: hidden;
  }

  /* Dekorasi background */
  body::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(37, 211, 102, 0.15) 0%, transparent 70%);
    top: -200px;
    right: -200px;
    border-radius: 50%;
    animation: float 8s ease-in-out infinite;
  }

  body::after {
    content: '';
    position: absolute;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
    bottom: -150px;
    left: -150px;
    border-radius: 50%;
    animation: float 10s ease-in-out infinite reverse;
  }

  @keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(30px, -30px) scale(1.1); }
  }

  .login-shell {
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-width: 1100px;
    width: 100%;
    background: rgba(30, 41, 59, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    position: relative;
    z-index: 1;
    animation: slideUp 0.6s ease-out;
  }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Hero Section */
  .login-hero {
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    padding: 50px 40px;
    color: white;
    position: relative;
    overflow: hidden;
  }

  .login-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.5;
  }

  .login-hero > * {
    position: relative;
    z-index: 1;
  }

  .brand-lockup {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 40px;
  }

  .brand-mark {
    width: 55px;
    height: 55px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 700;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    animation: pulseGlow 3s ease-in-out infinite;
  }

  @keyframes pulseGlow {
    0%, 100% {
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15), 0 0 0 0 rgba(255, 255, 255, 0.4);
    }
    50% {
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15), 0 0 0 15px rgba(255, 255, 255, 0);
    }
  }

  .brand-title {
    font-size: 20px;
    font-weight: 700;
    letter-spacing: 0.5px;
  }

  .brand-subtitle {
    font-size: 13px;
    opacity: 0.85;
    font-weight: 300;
    letter-spacing: 1px;
  }

  .login-hero h1 {
    font-size: 28px;
    font-weight: 600;
    line-height: 1.35;
    margin-bottom: 15px;
  }

  .login-hero p {
    font-size: 14px;
    line-height: 1.7;
    opacity: 0.9;
    margin-bottom: 30px;
    font-weight: 300;
  }

  .hero-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .hero-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 400;
    opacity: 0;
    animation: slideInLeft 0.5s ease forwards;
  }

  .hero-list li:nth-child(1) { animation-delay: 0.3s; }
  .hero-list li:nth-child(2) { animation-delay: 0.45s; }
  .hero-list li:nth-child(3) { animation-delay: 0.6s; }

  @keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
  }

  .hero-list li .icon-check {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 50%;
    flex-shrink: 0;
    transition: all 0.3s ease;
  }

  .hero-list li:hover .icon-check {
    background: rgba(255, 255, 255, 0.4);
    transform: scale(1.15) rotate(360deg);
  }

  .hero-list li .icon-check svg {
    width: 14px;
    height: 14px;
    stroke: white;
    stroke-width: 3;
  }

  /* Card Section */
  .login-card {
    padding: 50px 40px;
    background: rgba(15, 23, 42, 0.5);
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .login-card-header {
    margin-bottom: 30px;
  }

  .mini-tag {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 12px;
  }

  .admin-tag {
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
    animation: tagShine 2.5s ease-in-out infinite;
  }

  @keyframes tagShine {
    0%, 100% { box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3); }
    50% { box-shadow: 0 4px 20px rgba(37, 211, 102, 0.6); }
  }

  .login-card-header h2 {
    color: #f1f5f9;
    font-size: 24px;
    font-weight: 600;
    letter-spacing: 0.3px;
  }

  .login-form {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .login-form label {
    color: #94a3b8;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-top: 8px;
    margin-bottom: 4px;
  }

  .input-group {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-group .input-icon {
    position: absolute;
    left: 14px;
    width: 18px;
    height: 18px;
    stroke: #64748b;
    pointer-events: none;
    transition: stroke 0.3s ease, transform 0.3s ease;
  }

  .input-group:focus-within .input-icon {
    stroke: #25D366;
    transform: scale(1.15);
  }

  .login-form input {
    width: 100%;
    padding: 13px 16px 13px 44px;
    background: rgba(255, 255, 255, 0.05);
    border: 1.5px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #f1f5f9;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    transition: all 0.3s ease;
    outline: none;
  }

  .login-form input::placeholder {
    color: #64748b;
    font-weight: 300;
  }

  .login-form input:focus {
    border-color: #25D366;
    background: rgba(37, 211, 102, 0.05);
    box-shadow: 0 0 0 4px rgba(37, 211, 102, 0.1);
  }

  .password-toggle {
    position: absolute;
    right: 14px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
  }

  .password-toggle:hover {
    transform: scale(1.15);
  }

  .password-toggle svg {
    width: 18px;
    height: 18px;
    stroke: #64748b;
    transition: stroke 0.3s ease;
  }

  .password-toggle:hover svg {
    stroke: #25D366;
  }

  .modern-btn {
    margin-top: 20px;
    padding: 15px 24px;
    border: none;
    border-radius: 12px;
    font-family: 'Poppins', sans-serif;
    font-size: 15px;
    font-weight: 600;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
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

  .modern-btn.primary:hover .btn-icon {
    transform: translateX(5px);
  }

  .modern-btn.primary:active {
    transform: translateY(0);
  }

  .modern-btn .btn-icon {
    width: 18px;
    height: 18px;
    stroke: white;
    transition: transform 0.3s ease;
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

  .alert {
    padding: 16px 18px;
    border-radius: 12px;
    font-size: 13px;
    line-height: 1.6;
    margin-bottom: 16px;
    animation: fadeIn 0.4s ease;
    display: flex;
    align-items: flex-start;
    gap: 10px;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .alert-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    margin-top: 2px;
  }

  .alert.success {
    background: rgba(37, 211, 102, 0.1);
    border: 1px solid rgba(37, 211, 102, 0.3);
    color: #86efac;
  }

  .alert.success .alert-icon {
    stroke: #86efac;
  }

  .alert.failed {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
  }

  .alert.failed .alert-icon {
    stroke: #fca5a5;
    animation: shake 0.5s ease;
  }

  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    75% { transform: translateX(4px); }
  }

  .alert strong {
    display: block;
    margin-bottom: 4px;
    font-weight: 600;
  }

  .alert p {
    margin: 3px 0;
  }

  .alert a {
    color: #25D366;
    font-weight: 600;
    text-decoration: none;
    border-bottom: 1px solid currentColor;
  }

  .alert a:hover {
    opacity: 0.8;
  }

  .switch-auth {
    text-align: center;
    margin-top: 16px;
    color: #94a3b8;
    font-size: 13px;
  }

  .switch-auth a {
    color: #25D366;
    font-weight: 600;
    text-decoration: none;
    transition: opacity 0.2s;
    position: relative;
  }

  .switch-auth a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -2px;
    left: 0;
    background: #25D366;
    transition: width 0.3s ease;
  }

  .switch-auth a:hover::after {
    width: 100%;
  }

  /* Responsive */
  @media (max-width: 860px) {
    .login-shell {
      grid-template-columns: 1fr;
      max-width: 480px;
    }

    .login-hero {
      padding: 35px 30px;
    }

    .login-hero h1 {
      font-size: 22px;
    }

    .hero-list {
      display: none;
    }

    .login-card {
      padding: 35px 30px;
    }
  }

  @media (max-width: 480px) {
    body {
      padding: 12px;
    }

    .login-hero,
    .login-card {
      padding: 28px 22px;
    }

    .login-hero h1 {
      font-size: 19px;
    }

    .login-card-header h2 {
      font-size: 20px;
    }
  }
</style>
</head>
<body class="login-body">
  <div class="login-shell">
    <div class="login-hero">
      <div class="brand-lockup">
        <div class="brand-mark">W</div>
        <div>
          <div class="brand-title">API WhatsApp</div>
          <div class="brand-subtitle">User Portal</div>
        </div>
      </div>

      <h1>Kelola profil Anda dengan cepat dan aman.</h1>
      <p>Masuk ke dashboard pengguna untuk melihat data diri, mengganti password, serta mengelola akun Anda dengan lebih mudah.</p>

      <ul class="hero-list">
        <li>
          <span class="icon-check">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </span>
          Kelola profil dan alamat
        </li>
        <li>
          <span class="icon-check">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </span>
          Ubah password dengan keamanan tambahan
        </li>
        <li>
          <span class="icon-check">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </span>
          Akses dashboard personal Anda
        </li>
      </ul>
    </div>

    <div class="login-card">
      <div class="login-card-header">
        <span class="mini-tag admin-tag">User</span>
        <h2>Login</h2>
      </div>

      <?php if ($error): ?>
        <div class="alert failed">
          <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
          <div><strong><?= htmlspecialchars($error) ?></strong></div>
        </div>
      <?php endif; ?>

      <div class="alert success auth-note">
        <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="16" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        <div>
          <strong>Catatan:</strong> login user pakai email. Login admin memakai username dan bisa masuk melalui <a href="login.php">halaman admin</a>.
        </div>
      </div>

      <form method="POST" action="user-login.php" class="login-form">
        <label>Email</label>
        <div class="input-group">
          <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
          </svg>
          <input type="email" name="email" required autofocus placeholder="budi@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <label>Password</label>
        <div class="input-group">
          <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          <input type="password" name="password" id="passwordInput" required placeholder="Password">
          <button type="button" class="password-toggle" id="togglePassword" aria-label="Tampilkan password">
            <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </button>
        </div>

        <button type="submit" class="modern-btn primary login-btn">
          Masuk
          <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </form>

      <p class="switch-auth">Belum punya akun? <a href="index.html">Daftar di sini</a></p>
      <p class="switch-auth"><a href="login.php">Login admin</a></p>
    </div>
  </div>

  <script>
    // Toggle show/hide password
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', () => {
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      eyeIcon.innerHTML = isPassword
        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    });
  </script>
</body>
</html>