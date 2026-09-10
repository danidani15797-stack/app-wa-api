<?php
/**
 * Endpoint: POST /api/register.php
 *
 * Urutan proses (sesuai Tahap 6 dokumen tugas):
 * 1. Terima data dari form
 * 2. Validasi data
 * 3. Cek email/nomor sudah terdaftar atau belum
 * 4. Simpan ke database
 * 5. Pastikan INSERT berhasil
 * 6. Buat pesan WhatsApp dinamis
 * 7. Kirim melalui Fonnte
 * 8. Simpan log pengiriman
 * 9. Tampilkan hasil registrasi ke user
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/fonnte.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

// -----------------------------------------------------------------
// 1. Ambil data dari form (Tahap 4)
// -----------------------------------------------------------------
$nama     = trim($_POST['nama'] ?? '');
$email    = trim($_POST['email'] ?? '');
$no_hp    = trim($_POST['no_hp'] ?? '');
$alamat   = trim($_POST['alamat'] ?? '');
$password = $_POST['password'] ?? '';

// -----------------------------------------------------------------
// 2. Validasi input - JANGAN langsung percaya $_POST (Bagian S.3)
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

if (strlen($password) < 6) {
    $errors[] = 'Password minimal 6 karakter.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $errors]);
    exit;
}

$noHpNormal = formatNomor($no_hp);

try {
    // -------------------------------------------------------------
    // 3. Cek apakah email / nomor WhatsApp sudah terdaftar
    // -------------------------------------------------------------
    $cek = $pdo->prepare('SELECT id FROM users WHERE email = :email OR no_hp = :no_hp LIMIT 1');
    $cek->execute(['email' => $email, 'no_hp' => $noHpNormal]);

    if ($cek->fetch()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Email atau nomor WhatsApp sudah terdaftar.',
        ]);
        exit; // Registrasi ditolak, WhatsApp TIDAK dikirim (Pengujian 4)
    }

    // -------------------------------------------------------------
    // 4. Simpan ke database. Password di-hash, bukan teks biasa (Bagian S.4)
    // -------------------------------------------------------------
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (nama, email, no_hp, alamat, password)
         VALUES (:nama, :email, :no_hp, :alamat, :password)'
    );

    $insertBerhasil = $stmt->execute([
        'nama'     => $nama,
        'email'    => $email,
        'no_hp'    => $noHpNormal,
        'alamat'   => $alamat,
        'password' => $passwordHash,
    ]);

    // -------------------------------------------------------------
    // 5. Pastikan INSERT benar-benar berhasil sebelum lanjut kirim WA
    // -------------------------------------------------------------
    if (!$insertBerhasil) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data registrasi.']);
        exit;
    }

    $userId = (int) $pdo->lastInsertId();

    // -------------------------------------------------------------
    // 6. Buat pesan WhatsApp dinamis (Tahap 5)
    // -------------------------------------------------------------
    $pesan = formatHeaderWa('Registrasi akun berhasil')
           . "Halo {$nama},\n\n"
           . "Selamat datang di sistem kami. Registrasi akun Anda telah berhasil dilakukan dengan baik.\n\n"
           . "📧 Email: {$email}\n"
           . "📍 Alamat: {$alamat}\n"
           . "📱 Nomor WhatsApp: {$noHpNormal}\n\n"
           . "Terima kasih atas kepercayaan Anda untuk mendaftar dan menjadi bagian dari layanan kami.";

    // -------------------------------------------------------------
    // 7. Kirim WhatsApp HANYA jika database sudah berhasil (Tahap 6 & 8)
    // -------------------------------------------------------------
    $hasilWa = kirimWhatsApp($noHpNormal, $pesan);

    // -------------------------------------------------------------
    // 8. Simpan log pengiriman WhatsApp (Fitur tambahan Q.2 / Tantangan X)
    // -------------------------------------------------------------
    $logStmt = $pdo->prepare(
        'INSERT INTO log_whatsapp (user_id, no_tujuan, pesan, status, response)
         VALUES (:user_id, :no_tujuan, :pesan, :status, :response)'
    );
    $logStmt->execute([
        'user_id'   => $userId,
        'no_tujuan' => $noHpNormal,
        'pesan'     => $pesan,
        'status'    => $hasilWa['status'],
        'response'  => $hasilWa['response'],
    ]);

    // -------------------------------------------------------------
    // 9. Tampilkan hasil ke user.
    //    Registrasi tetap dianggap sukses walau notifikasi WA gagal,
    //    karena keduanya adalah dua proses yang berbeda (Tahap 8).
    // -------------------------------------------------------------
    echo json_encode([
        'success' => true,
        'message' => 'Registrasi berhasil.',
        'data' => [
            'id'    => $userId,
            'nama'  => $nama,
            'email' => $email,
            'no_hp' => $noHpNormal,
        ],
        'notifikasi_whatsapp' => [
            'status' => $hasilWa['status'], // success | failed
            'info'   => $hasilWa['success']
                ? 'Notifikasi WhatsApp berhasil dikirim.'
                : 'Registrasi berhasil, namun notifikasi WhatsApp gagal dikirim.',
        ],
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan server saat memproses registrasi.',
    ]);
}
