<?php
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/fonnte.php';
require_once __DIR__ . '/../config/database.php';

requireUserLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../user-delete-account.php');
    exit;
}

if (!csrfValid($_POST['csrf_token'] ?? null)) {
    header('Location: ../user-delete-account.php?flash=' . urlencode('Sesi tidak valid, silakan coba lagi.') . '&flash_type=failed');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$password = $_POST['password'] ?? '';

if ($password === '') {
    header('Location: ../user-delete-account.php?flash=' . urlencode('Password wajib diisi untuk menghapus akun.') . '&flash_type=failed');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, nama, email, no_hp, password FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        header('Location: ../user-delete-account.php?flash=' . urlencode('Password salah.') . '&flash_type=failed');
        exit;
    }

    $pesan = formatHeaderWa('Akun berhasil dihapus')
           . "Halo {$user['nama']},\n\n"
           . "Dengan hormat, akun Anda telah berhasil dihapus dari sistem kami sesuai permintaan yang Anda ajukan.\n\n"
           . "📧 Email: {$user['email']}\n"
           . "📱 Nomor WhatsApp: {$user['no_hp']}\n\n"
           . "Terima kasih telah menjadi bagian dari layanan kami. Semoga hari Anda selalu dalam keadaan baik.";

    $hasilWa = kirimWhatsApp($user['no_hp'], $pesan);

    $logStmt = $pdo->prepare(
        'INSERT INTO log_whatsapp (user_id, no_tujuan, pesan, status, response)
         VALUES (:user_id, :no_tujuan, :pesan, :status, :response)'
    );
    $logStmt->execute([
        'user_id'   => $userId,
        'no_tujuan' => $user['no_hp'],
        'pesan'     => $pesan,
        'status'    => $hasilWa['status'],
        'response'  => $hasilWa['response'],
    ]);

    $deleteStmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $deleteStmt->execute(['id' => $userId]);

    session_unset();
    session_destroy();

    $message = $hasilWa['success']
        ? 'Akun berhasil dihapus. Notifikasi WhatsApp terkirim.'
        : 'Akun berhasil dihapus. Notifikasi WhatsApp gagal dikirim.';

    header('Location: ../index.html?flash=' . urlencode($message) . '&flash_type=' . ($hasilWa['success'] ? 'success' : 'failed'));
    exit;
} catch (PDOException $e) {
    header('Location: ../user-delete-account.php?flash=' . urlencode('Gagal menghapus akun.') . '&flash_type=failed');
    exit;
}
