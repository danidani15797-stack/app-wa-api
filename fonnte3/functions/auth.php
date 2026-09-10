<?php
/**
 * Helper autentikasi sederhana berbasis session untuk halaman admin
 * (Dashboard, Kelola User, Laporan Notifikasi).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Pastikan admin sudah login, jika belum redirect ke halaman login.
 */
function requireLogin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Ambil data admin yang sedang login.
 */
function currentAdmin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    return [
        'id'       => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'] ?? '',
    ];
}

/**
 * Pastikan USER (bukan admin) sudah login, jika belum redirect ke halaman
 * login user. Session key sengaja dibuat berbeda dari admin ($_SESSION['user_id']
 * vs $_SESSION['admin_id']) supaya kedua sesi tidak saling bentrok.
 */
function requireUserLogin(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: user-login.php');
        exit;
    }
}

/**
 * Ambil data user yang sedang login.
 */
function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'nama'  => $_SESSION['user_nama'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
    ];
}

/**
 * Buat token CSRF sederhana untuk melindungi form POST (tambah/edit/hapus user).
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi token CSRF yang dikirim dari form.
 */
function csrfValid(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
