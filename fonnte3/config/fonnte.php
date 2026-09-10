<?php
/**
 * Konfigurasi API Fonnte
 *
 * PENTING (Aturan Keamanan - Bagian S dokumen tugas):
 * - JANGAN taruh token asli langsung di file ini kalau file ini akan
 *   di-commit ke Git publik. Sebaiknya set melalui environment variable
 *   (misalnya lewat file .env + package vlucas/phpdotenv, atau
 *   lewat pengaturan environment variable di server/hosting).
 * - Token TIDAK PERNAH boleh dikirim ke browser / ditulis di HTML/JS.
 */

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $value = trim($parts[1]);
            if ($name === '') {
                continue;
            }

            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Ambil token dari environment variable jika ada.
// Jika belum diset, fallback ke placeholder (WAJIB diganti saat deploy).
$FONNTE_TOKEN = getenv('FONNTE_TOKEN') ?: 'GANTI_DENGAN_TOKEN_FONNTE_KALIAN';

// Endpoint resmi Fonnte untuk mengirim pesan.
// Catatan: selalu cek dokumentasi resmi https://fonnte.com/
// karena endpoint/parameter API dapat berubah sewaktu-waktu.
$FONNTE_ENDPOINT = 'https://api.fonnte.com/send';

return [
    'token'    => $FONNTE_TOKEN,
    'endpoint' => $FONNTE_ENDPOINT,
];
