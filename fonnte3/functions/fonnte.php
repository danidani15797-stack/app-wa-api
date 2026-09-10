<?php
/**
 * Kumpulan function untuk integrasi WhatsApp Gateway (Fonnte)
 * - formatNomor()   : Tahap 7 - Normalisasi nomor WhatsApp
 * - kirimWhatsApp()  : Tahap 2 & 3 - Kirim pesan via HTTP Request (cURL) ke API Fonnte
 *
 * Function ini dibuat reusable, sehingga bisa dipakai ulang untuk fitur lain
 * seperti reset password, pembayaran, pengumuman, dsb.
 */

/**
 * Menormalisasi nomor WhatsApp ke format yang umum diminta API (62xxxxxxxxxx).
 * Contoh: 081234567890 -> 6281234567890
 *
 * Catatan: sesuaikan kembali dengan format yang diminta dokumentasi resmi
 * Fonnte pada akun kalian, karena beberapa API menerima format +62 atau 62.
 *
 * @param string $nomor
 * @return string
 */
function formatNomor(string $nomor): string
{
    // Buang semua karakter selain angka (spasi, strip, tanda kurung, dll)
    $nomor = preg_replace('/[^0-9]/', '', $nomor);

    // Ubah awalan 0 menjadi 62
    if (substr($nomor, 0, 1) === '0') {
        $nomor = '62' . substr($nomor, 1);
    }

    // Jika user memasukkan tanpa awalan 0 dan tanpa 62 (misal: 8123...)
    if (substr($nomor, 0, 2) !== '62') {
        $nomor = '62' . $nomor;
    }

    return $nomor;
}

/**
 * Memvalidasi apakah nomor WhatsApp yang sudah dinormalisasi terlihat valid.
 * Validasi sederhana: harus berupa angka, diawali 62, panjang wajar.
 *
 * @param string $nomorTernormalisasi
 * @return bool
 */
function isNomorValid(string $nomorTernormalisasi): bool
{
    return (bool) preg_match('/^62[0-9]{8,14}$/', $nomorTernormalisasi);
}

function formatWaktuWaIndo(?DateTimeInterface $tanggal = null): string
{
    $dt = $tanggal ?? new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
    $bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $bulanIndex = (int) $dt->format('n') - 1;

    return $dt->format('d') . ' ' . $bulan[$bulanIndex] . ' ' . $dt->format('Y')
        . ' / ' . $dt->format('H:i');
}

function formatHeaderWa(string $judul): string
{
    return "━━━━━━━━━━━━━━━━━━━━━━\n📣 " . strtoupper($judul) . "\n━━━━━━━━━━━━━━━━━━━━━━\nWaktu: " . formatWaktuWaIndo() . "\n\n";
}

/**
 * Mengirim pesan WhatsApp melalui API Fonnte menggunakan HTTP POST (cURL).
 *
 * @param string $nomor  Nomor tujuan (boleh format 08xx, akan dinormalisasi otomatis)
 * @param string $pesan  Isi pesan yang akan dikirim
 * @return array{success: bool, status: string, response: string}
 */
function kirimWhatsApp(string $nomor, string $pesan): array
{
    $config = require __DIR__ . '/../config/fonnte.php';
    $token    = $config['token'];
    $endpoint = $config['endpoint'];

    $target = formatNomor($nomor);

    // Validasi nomor sebelum request dikirim, supaya tidak boros kuota API
    if (!isNomorValid($target)) {
        return [
            'success'  => false,
            'status'   => 'failed',
            'response' => 'Nomor WhatsApp tidak valid: ' . $nomor,
        ];
    }

    if (empty($token) || $token === 'GANTI_DENGAN_TOKEN_FONNTE_KALIAN') {
        return [
            'success'  => false,
            'status'   => 'failed',
            'response' => 'Token Fonnte belum diset. Pastikan file .env berisi FONNTE_TOKEN yang valid.',
        ];
    }

    // Data yang dikirim ke API Fonnte.
    // Sesuaikan nama parameter (target/message/dll) dengan dokumentasi resmi
    // Fonnte yang berlaku saat pengerjaan, karena spesifikasi dapat berubah.
    $data = [
        'target'  => $target,
        'message' => $pesan,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $token,
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Tangani kesalahan koneksi (Tahap 8 - Menangani Kesalahan)
    if ($response === false) {
        return [
            'success'  => false,
            'status'   => 'failed',
            'response' => 'cURL error: ' . $curlError,
        ];
    }

    $decoded = json_decode($response, true);
    $success = ($httpCode >= 200 && $httpCode < 300);

    if (is_array($decoded)) {
        $bodyStatus = $decoded['status'] ?? $decoded['success'] ?? null;

        if (is_bool($bodyStatus)) {
            $success = $bodyStatus;
        } elseif (is_string($bodyStatus)) {
            $statusLower = strtolower($bodyStatus);
            if (in_array($statusLower, ['success', 'ok', 'accepted', 'queued'], true)) {
                $success = true;
            } elseif (in_array($statusLower, ['failed', 'error', 'false'], true)) {
                $success = false;
            }
        }
    }

    return [
        'success'  => $success,
        'status'   => $success ? 'success' : 'failed',
        'response' => $response,
    ];
}
