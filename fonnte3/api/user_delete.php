<?php
/**
 * Endpoint: POST /api/user_delete.php
 * Menangani DELETE untuk data user.
 */

require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../kelola-user.php');
    exit;
}

if (!csrfValid($_POST['csrf_token'] ?? null)) {
    header('Location: ../kelola-user.php?flash=Sesi tidak valid, silakan coba lagi.&flash_type=failed');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header('Location: ../kelola-user.php?flash=' . urlencode('User tidak valid.') . '&flash_type=failed');
    exit;
}

try {
    $userStmt = $pdo->prepare('SELECT id, nama, email, no_hp FROM users WHERE id = :id LIMIT 1');
    $userStmt->execute(['id' => $id]);
    $user = $userStmt->fetch();

    if (!$user) {
        header('Location: ../kelola-user.php?flash=' . urlencode('User tidak ditemukan.') . '&flash_type=failed');
        exit;
    }

    require_once __DIR__ . '/../functions/fonnte.php';

    $pesan = formatHeaderWa('Akun dihapus oleh admin')
           . "Halo {$user['nama']},\n\n"
           . "Informasi resmi dari administrator, akun Anda telah dihapus dari sistem kami.\n\n"
           . "📧 Email: {$user['email']}\n"
           . "📱 Nomor WhatsApp: {$user['no_hp']}\n\n"
           . "Jika Anda merasa ada kesalahan atau perubahan yang tidak sesuai, segera hubungi administrator untuk tindak lanjut yang diperlukan.";

    $hasilWa = kirimWhatsApp($user['no_hp'], $pesan);

    $logStmt = $pdo->prepare(
        'INSERT INTO log_whatsapp (user_id, no_tujuan, pesan, status, response)
         VALUES (:user_id, :no_tujuan, :pesan, :status, :response)'
    );
    $logStmt->execute([
        'user_id'   => $user['id'],
        'no_tujuan' => $user['no_hp'],
        'pesan'     => $pesan,
        'status'    => $hasilWa['status'],
        'response'  => $hasilWa['response'],
    ]);

    $deleteStmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $deleteStmt->execute(['id' => $id]);

    $message = $deleteStmt->rowCount() > 0
        ? 'User berhasil dihapus. Notifikasi WhatsApp terkirim.'
        : 'User tidak ditemukan.';

    header('Location: ../kelola-user.php?flash=' . urlencode($message) . '&flash_type=' . ($deleteStmt->rowCount() > 0 ? 'success' : 'failed'));
    exit;

} catch (PDOException $e) {
    header('Location: ../kelola-user.php?flash=' . urlencode('Gagal menghapus user.') . '&flash_type=failed');
    exit;
}
