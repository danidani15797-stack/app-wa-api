<?php
/**
 * Endpoint: POST /api/user_profile_update.php
 *
 * User mengedit profilnya sendiri (nama/username, email, password).
 * Setelah berhasil disimpan, sistem mengirim notifikasi WhatsApp
 * yang merangkum perubahan apa saja yang terjadi.
 */

require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../config/database.php';

requireUserLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../user-dashboard.php');
    exit;
}

if (!csrfValid($_POST['csrf_token'] ?? null)) {
    header('Location: ../user-dashboard.php?flash=' . urlencode('Sesi tidak valid, silakan coba lagi.') . '&flash_type=failed');
    exit;
}

require_once __DIR__ . '/../functions/fonnte.php';

$userId      = (int) $_SESSION['user_id'];
$nama        = trim($_POST['nama'] ?? '');
$email       = trim($_POST['email'] ?? '');
$alamat      = trim($_POST['alamat'] ?? '');
$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

// -----------------------------------------------------------------
// Validasi input
// -----------------------------------------------------------------
$errors = [];

if ($nama === '' || mb_strlen($nama) < 3) {
    $errors[] = 'Nama minimal 3 karakter.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid.';
}
if ($alamat === '') {
    $errors[] = 'Alamat wajib diisi.';
}
if ($newPassword !== '' && strlen($newPassword) < 6) {
    $errors[] = 'Password baru minimal 6 karakter.';
}

if (!empty($errors)) {
    header('Location: ../user-data.php?flash=' . urlencode(implode(' ', $errors)) . '&flash_type=failed');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT nama, email, no_hp, alamat, password FROM users WHERE id = :id');
    $stmt->execute(['id' => $userId]);
    $old = $stmt->fetch();

    if (!$old) {
        header('Location: ../user-logout.php');
        exit;
    }

    if ($newPassword !== '') {
        if ($oldPassword === '') {
            header('Location: ../user-data.php?flash=' . urlencode('Password lama wajib diisi sebelum mengganti password baru.') . '&flash_type=failed');
            exit;
        }

        if (!password_verify($oldPassword, $old['password'])) {
            header('Location: ../user-data.php?flash=' . urlencode('Password lama tidak sesuai.') . '&flash_type=failed');
            exit;
        }
    }

    $cek = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
    $cek->execute(['email' => $email, 'id' => $userId]);
    if ($cek->fetch()) {
        header('Location: ../user-data.php?flash=' . urlencode('Email sudah dipakai akun lain.') . '&flash_type=failed');
        exit;
    }

    $perubahan = [];
    if ($old['nama'] !== $nama) {
        $perubahan[] = "Nama: {$old['nama']} → {$nama}";
    }
    if ($old['email'] !== $email) {
        $perubahan[] = "Email: {$old['email']} → {$email}";
    }
    if ($old['alamat'] !== $alamat) {
        $perubahan[] = "Alamat: {$old['alamat']} → {$alamat}";
    }
    if ($newPassword !== '') {
        $perubahan[] = 'Password: berhasil diganti';
    }

    if (empty($perubahan)) {
        header('Location: ../user-data.php?flash=' . urlencode('Tidak ada perubahan data.') . '&flash_type=failed');
        exit;
    }

    if ($newPassword !== '') {
        $stmt = $pdo->prepare('UPDATE users SET nama = :nama, email = :email, alamat = :alamat, password = :password WHERE id = :id');
        $stmt->execute([
            'nama'     => $nama,
            'email'    => $email,
            'alamat'   => $alamat,
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id'       => $userId,
        ]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET nama = :nama, email = :email, alamat = :alamat WHERE id = :id');
        $stmt->execute([
            'nama'   => $nama,
            'email'  => $email,
            'alamat' => $alamat,
            'id'     => $userId,
        ]);
    }

    $_SESSION['user_nama']  = $nama;
    $_SESSION['user_email'] = $email;

    $daftarPerubahan = implode("\n", array_map(fn($p) => "- {$p}", $perubahan));

    $pesan = formatHeaderWa('Pembaruan data akun')
           . "Halo {$nama},\n\n"
           . "Data akun Anda telah diperbarui secara resmi oleh sistem kami.\n\n"
           . "📧 Email: {$email}\n"
           . "📝 Perubahan yang dilakukan:\n"
           . "{$daftarPerubahan}\n\n"
           . "Jika Anda tidak melakukan perubahan ini, segera hubungi administrator untuk verifikasi lebih lanjut.";

    $hasilWa = kirimWhatsApp($old['no_hp'], $pesan);

    $logStmt = $pdo->prepare(
        'INSERT INTO log_whatsapp (user_id, no_tujuan, pesan, status, response)
         VALUES (:user_id, :no_tujuan, :pesan, :status, :response)'
    );
    $logStmt->execute([
        'user_id'   => $userId,
        'no_tujuan' => $old['no_hp'],
        'pesan'     => $pesan,
        'status'    => $hasilWa['status'],
        'response'  => $hasilWa['response'],
    ]);

    $flashMsg = $hasilWa['success']
        ? 'Profil berhasil diperbarui. Notifikasi WhatsApp telah dikirim.'
        : 'Profil berhasil diperbarui, namun notifikasi WhatsApp gagal dikirim.';

    header('Location: ../user-data.php?flash=' . urlencode($flashMsg) . '&flash_type=' . ($hasilWa['success'] ? 'success' : 'failed'));
    exit;

} catch (PDOException $e) {
    header('Location: ../user-data.php?flash=' . urlencode('Terjadi kesalahan server.') . '&flash_type=failed');
    exit;
}
