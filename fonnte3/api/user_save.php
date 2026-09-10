<?php
/**
 * Endpoint: POST /api/user_save.php
 * Menangani CREATE (tanpa "id") dan UPDATE (dengan "id") untuk data user.
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

require_once __DIR__ . '/../functions/fonnte.php'; // untuk formatNomor()

$id       = isset($_POST['id']) ? (int) $_POST['id'] : null;
$isEdit   = $id !== null;
$nama     = trim($_POST['nama'] ?? '');
$email    = trim($_POST['email'] ?? '');
$no_hp    = trim($_POST['no_hp'] ?? '');
$alamat   = trim($_POST['alamat'] ?? '');
$password = $_POST['password'] ?? '';

// -----------------------------------------------------------------
// Validasi input (sama seperti di api/register.php)
// -----------------------------------------------------------------
$errors = [];

if ($nama === '' || mb_strlen($nama) < 3) {
    $errors[] = 'Nama minimal 3 karakter.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid.';
}
if ($no_hp === '' || !preg_match('/^[0-9+\-\s]{9,15}$/', $no_hp)) {
    $errors[] = 'Nomor WhatsApp tidak valid.';
}
if ($alamat === '') {
    $errors[] = 'Alamat wajib diisi.';
}
if (!$isEdit && strlen($password) < 6) {
    $errors[] = 'Password minimal 6 karakter.';
}
if ($isEdit && $password !== '' && strlen($password) < 6) {
    $errors[] = 'Password baru minimal 6 karakter.';
}

if (!empty($errors)) {
    $target = $isEdit ? "../kelola-user-form.php?id={$id}" : '../kelola-user-form.php';
    header('Location: ' . $target . '&flash=' . urlencode(implode(' ', $errors)) . '&flash_type=failed');
    exit;
}

$noHpNormal = formatNomor($no_hp);

try {
    // Cek duplikat email/nomor pada user lain
    $cekSql = 'SELECT id FROM users WHERE (email = :email OR no_hp = :no_hp)';
    $params = ['email' => $email, 'no_hp' => $noHpNormal];
    if ($isEdit) {
        $cekSql .= ' AND id != :id';
        $params['id'] = $id;
    }
    $cek = $pdo->prepare($cekSql . ' LIMIT 1');
    $cek->execute($params);

    if ($cek->fetch()) {
        $target = $isEdit ? "../kelola-user-form.php?id={$id}" : '../kelola-user-form.php';
        header('Location: ' . $target . '&flash=' . urlencode('Email atau nomor WhatsApp sudah dipakai user lain.') . '&flash_type=failed');
        exit;
    }

    if ($isEdit) {
        // ---------------------------------------------------------
        // UPDATE
        // ---------------------------------------------------------
        $oldUserStmt = $pdo->prepare('SELECT nama, email, no_hp, alamat FROM users WHERE id = :id LIMIT 1');
        $oldUserStmt->execute(['id' => $id]);
        $oldUser = $oldUserStmt->fetch();

        if ($password !== '') {
            $stmt = $pdo->prepare(
                'UPDATE users SET nama = :nama, email = :email, no_hp = :no_hp,
                 alamat = :alamat, password = :password WHERE id = :id'
            );
            $stmt->execute([
                'nama'     => $nama,
                'email'    => $email,
                'no_hp'    => $noHpNormal,
                'alamat'   => $alamat,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'id'       => $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE users SET nama = :nama, email = :email, no_hp = :no_hp,
                 alamat = :alamat WHERE id = :id'
            );
            $stmt->execute([
                'nama'   => $nama,
                'email'  => $email,
                'no_hp'  => $noHpNormal,
                'alamat' => $alamat,
                'id'     => $id,
            ]);
        }

        if ($oldUser) {
            $perubahan = [];
            if ($oldUser['nama'] !== $nama) $perubahan[] = "Nama: {$oldUser['nama']} -> {$nama}";
            if ($oldUser['email'] !== $email) $perubahan[] = "Email: {$oldUser['email']} -> {$email}";
            if ($oldUser['no_hp'] !== $noHpNormal) $perubahan[] = "No. WhatsApp: {$oldUser['no_hp']} -> {$noHpNormal}";
            if ($oldUser['alamat'] !== $alamat) $perubahan[] = "Alamat: {$oldUser['alamat']} -> {$alamat}";
            if ($password !== '') $perubahan[] = 'Password: berhasil diubah';

            if (!empty($perubahan)) {
                $daftarPerubahan = implode("\n", array_map(fn($p) => "- {$p}", $perubahan));
                $pesan = formatHeaderWa('Update data akun oleh admin')
                       . "Halo {$nama},\n\n"
                       . "Data akun Anda telah diperbarui secara resmi oleh administrator sistem kami.\n\n"
                       . "📧 Email: {$email}\n"
                       . "📝 Perubahan yang dilakukan:\n"
                       . "{$daftarPerubahan}\n\n"
                       . "Jika Anda tidak melakukan perubahan ini, segera hubungi admin untuk proses verifikasi lebih lanjut.";

                $hasilWa = kirimWhatsApp($noHpNormal, $pesan);

                $logStmt = $pdo->prepare(
                    'INSERT INTO log_whatsapp (user_id, no_tujuan, pesan, status, response)
                     VALUES (:user_id, :no_tujuan, :pesan, :status, :response)'
                );
                $logStmt->execute([
                    'user_id'   => $id,
                    'no_tujuan' => $noHpNormal,
                    'pesan'     => $pesan,
                    'status'    => $hasilWa['status'],
                    'response'  => $hasilWa['response'],
                ]);
            }
        }

        header('Location: ../kelola-user.php?flash=' . urlencode('Data user berhasil diperbarui.') . '&flash_type=success');
        exit;
    }

    // ---------------------------------------------------------
    // CREATE
    // ---------------------------------------------------------
    $stmt = $pdo->prepare(
        'INSERT INTO users (nama, email, no_hp, alamat, password)
         VALUES (:nama, :email, :no_hp, :alamat, :password)'
    );
    $stmt->execute([
        'nama'     => $nama,
        'email'    => $email,
        'no_hp'    => $noHpNormal,
        'alamat'   => $alamat,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    header('Location: ../kelola-user.php?flash=' . urlencode('User baru berhasil ditambahkan.') . '&flash_type=success');
    exit;

} catch (PDOException $e) {
    $target = $isEdit ? "../kelola-user-form.php?id={$id}" : '../kelola-user-form.php';
    header('Location: ' . $target . '&flash=' . urlencode('Terjadi kesalahan server.') . '&flash_type=failed');
    exit;
}
