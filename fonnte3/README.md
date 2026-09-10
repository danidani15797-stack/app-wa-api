# Project API WhatsApp - Fonnte

## Deskripsi
Project ini merupakan pengembangan API registrasi dengan penambahan fitur
notifikasi WhatsApp otomatis. Setiap kali seorang pengguna berhasil
melakukan registrasi, sistem secara otomatis mengirimkan pesan WhatsApp
konfirmasi ke nomor yang didaftarkan, melalui layanan WhatsApp Gateway
[Fonnte](https://fonnte.com/).

## Teknologi
- HTML
- CSS
- PHP
- MySQL
- REST API
- Fonnte

## Fitur
- Registrasi pengguna
- Penyimpanan database
- Validasi data (server-side)
- Password disimpan dengan hashing (`password_hash`)
- Normalisasi nomor WhatsApp (08xx → 62xx)
- Notifikasi WhatsApp otomatis setelah registrasi
- Integrasi API Fonnte via cURL (HTTP POST)
- Log setiap pengiriman WhatsApp (tabel `log_whatsapp`)
- **Login admin** berbasis session (dengan proteksi CSRF token & session fixation)
- **Dashboard** admin: ringkasan total user, status notifikasi WA, registrasi terbaru
- **Kelola User (CRUD)**: Tambah, Lihat/Cari, Ubah, dan Hapus data user
- Halaman admin: laporan status registrasi & notifikasi WhatsApp

## Alur Sistem
```
User → Form Registrasi → Validasi → Database
     → (jika sukses) → Fonnte API → WhatsApp → Notifikasi diterima User
```

## Struktur Project
```
project-api-fonnte/
│
├── config/
│   ├── database.php      # Koneksi PDO ke MySQL
│   └── fonnte.php        # Token & endpoint Fonnte
│
├── api/
│   ├── register.php      # Endpoint proses registrasi publik + kirim WA
│   ├── user_save.php     # Handler CREATE & UPDATE user (dipakai kelola-user-form.php)
│   └── user_delete.php   # Handler DELETE user (dipakai kelola-user.php)
│
├── functions/
│   ├── fonnte.php        # kirimWhatsApp(), formatNomor(), isNomorValid()
│   └── auth.php          # requireLogin(), currentAdmin(), csrfToken(), csrfValid()
│
├── partials/
│   └── nav.php            # Navbar bersama untuk halaman admin
│
├── database/
│   └── schema.sql        # Struktur tabel users, log_whatsapp, admins (+ seed admin default)
│
├── assets/css/style.css
├── index.html             # Form registrasi publik
├── login.php               # Login admin
├── logout.php              # Logout admin
├── dashboard.php           # Dashboard admin (statistik ringkas)
├── kelola-user.php         # Kelola User - daftar + cari + tombol edit/hapus (Read)
├── kelola-user-form.php    # Kelola User - form tambah/edit (Create/Update)
├── admin.php                # Laporan notifikasi WhatsApp
├── .env.example
└── README.md
```

## Login & Dashboard User (Akun Hasil Registrasi)

Setelah mendaftar lewat `index.html`, user bisa **login sendiri** (terpisah
dari login admin) memakai email & password yang sama saat registrasi, lalu
masuk ke dashboard pribadi untuk mengubah **username (nama), email, dan
password**. Setiap kali profil diubah, sistem otomatis mengirim **notifikasi
WhatsApp** berisi ringkasan perubahan ke nomor yang terdaftar.

| Halaman | URL | Keterangan |
|---|---|---|
| Login User | `user-login.php` | Login pakai email + password hasil registrasi |
| Dashboard User | `user-dashboard.php` | Lihat & edit profil (nama, email, password) |
| Logout User | `user-logout.php` | Keluar dari sesi user |
| Handler Update | `api/user_profile_update.php` | Simpan perubahan + kirim notifikasi WA |

Alurnya:
```
Login (email + password)
        ↓
Dashboard User
        ↓
Edit nama / email / password
        ↓
Simpan ke database
        ↓
Kirim notifikasi WhatsApp berisi ringkasan perubahan
        ↓
Log pengiriman dicatat di tabel log_whatsapp
```

Catatan:
- Nomor WhatsApp tidak bisa diubah dari dashboard ini, karena nomor tersebut
  yang dipakai sebagai tujuan pengiriman notifikasi.
- Password kosong pada form berarti "tidak diganti".
- Sesi user (`$_SESSION['user_id']`) dan sesi admin (`$_SESSION['admin_id']`)
  memakai key yang berbeda, sehingga tidak saling bentrok.
- Sistem login admin (`login.php`, `dashboard.php` versi admin, `kelola-user.php`,
  `admin.php`) tetap ada di project ini untuk pengembangan lanjutan, namun
  untuk saat ini alur utama yang dipakai adalah login user di atas.

## Halaman Admin (Login, Dashboard, Kelola User) — belum dipakai saat ini

Bagian ini tersedia di project untuk pengembangan lanjutan (misalnya jika
nanti butuh admin memantau/mengelola semua user), tapi **belum menjadi alur
utama** sesuai permintaan terbaru.

| Halaman | URL | Keterangan |
|---|---|---|
| Login | `login.php` | Akun default: `admin` / `admin123` (**segera ganti**) |
| Dashboard | `dashboard.php` | Statistik total user & status notifikasi WA |
| Kelola User | `kelola-user.php` | List + cari user, tombol Tambah/Edit/Hapus |
| Form User | `kelola-user-form.php` | Form tambah user baru / edit user (Create & Update) |
| Laporan Notifikasi | `admin.php` | Status registrasi vs status pengiriman WA per user |

Semua halaman di atas (kecuali `login.php`) dilindungi oleh `requireLogin()`
dari `functions/auth.php` — jika belum login, otomatis diarahkan ke
`login.php`. Setiap form Tambah/Edit/Hapus user juga dilengkapi **CSRF
token** untuk mencegah pengiriman form dari luar aplikasi.

**CRUD Kelola User:**
- **Create** — tombol "+ Tambah User" di `kelola-user.php` → form di `kelola-user-form.php` → disimpan lewat `api/user_save.php`.
- **Read** — tabel di `kelola-user.php`, bisa dicari berdasarkan nama/email/nomor WA.
- **Update** — tombol "Edit" pada tiap baris → form yang sama terisi otomatis → password boleh dikosongkan jika tidak ingin diganti.
- **Delete** — tombol "Hapus" dengan konfirmasi, diproses lewat `api/user_delete.php`. Log WhatsApp milik user tersebut tidak ikut terhapus (`user_id` diset `NULL`), sesuai `ON DELETE SET NULL` pada `schema.sql`.

## Cara Menjalankan
1. Import `database/schema.sql` ke MySQL (akan membuat database
   `api_wa_fonnte`, tabel `users`, `log_whatsapp`, `admins`, dan otomatis
   membuat akun admin default `admin` / `admin123`).
2. Salin `.env.example` menjadi `.env` (atau set environment variable secara
   manual di server), lalu isi `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
3. Buka [fonnte.com](https://fonnte.com/), buat akun, hubungkan perangkat
   WhatsApp, lalu salin API Token ke environment variable `FONNTE_TOKEN`
   (jangan ditulis langsung di source code yang akan dipublikasikan).
4. Jalankan server PHP, misalnya:
   ```
   php -S localhost:8000
   ```
5. Buka `http://localhost:8000/index.html` untuk melakukan registrasi (publik).
6. Buka `http://localhost:8000/login.php` untuk masuk sebagai admin, lalu
   kelola data lewat Dashboard, Kelola User (CRUD), dan Laporan Notifikasi.
7. Lakukan pengujian sesuai skenario pada bagian "Pengujian" di bawah.

## Pengujian
Lakukan pengujian dengan minimal 5 akun berbeda:

| No | Skenario | Nomor Input | Hasil yang Diharapkan |
|----|----------|-------------|------------------------|
| 1 | Registrasi normal | 081234567890 | Data tersimpan + WA diterima |
| 2 | Nomor WA berbeda | 08129xxxxxxx | Data tersimpan + WA diterima |
| 3 | Format nomor 0812xxxxxxxx | 0812xxxxxxxx | Nomor dinormalisasi ke 62xxxxxxxxxx |
| 4 | Email sudah terdaftar | (email lama) | Registrasi ditolak, WA TIDAK dikirim |
| 5 | Nomor WA tidak valid | abc123 / terlalu pendek | Registrasi ditolak / WA gagal dengan status tercatat di log |

> Isi tabel di atas dengan hasil aktual (screenshot) saat kalian menjalankan pengujian.

## Catatan Keamanan
API Token tidak disertakan dalam repository publik. Token diambil dari
environment variable (`FONNTE_TOKEN`), bukan ditulis langsung di kode
HTML/JavaScript yang dikirim ke browser. Permintaan ke API Fonnte selalu
dilakukan dari server (`functions/fonnte.php`), bukan langsung dari browser.

## Pertanyaan Analisis

**1. Apa yang dimaksud dengan API?**
API (Application Programming Interface) adalah perantara yang memungkinkan
dua aplikasi berkomunikasi dan bertukar data, tanpa masing-masing pihak
perlu mengetahui detail implementasi internal satu sama lain.

**2. Apa fungsi Fonnte dalam project ini?**
Fonnte berperan sebagai WhatsApp Gateway, yaitu layanan pihak ketiga yang
menjembatani aplikasi (server kita) dengan WhatsApp, sehingga server dapat
mengirim pesan WhatsApp secara otomatis tanpa perlu membuka aplikasi
WhatsApp secara manual.

**3. Mengapa API Token tidak boleh diletakkan di JavaScript frontend?**
Karena kode JavaScript frontend dapat dilihat siapa saja lewat "View
Source" atau DevTools browser. Jika token terekspos di sana, orang lain
dapat memakainya untuk mengirim pesan atas nama akun WhatsApp kita, atau
bahkan menyalahgunakannya.

**4. Apa perbedaan antara proses registrasi dan proses pengiriman WhatsApp?**
Registrasi adalah proses menyimpan data pengguna baru ke database.
Pengiriman WhatsApp adalah proses terpisah yang memanggil API pihak
ketiga (Fonnte) untuk mengirim notifikasi. Keduanya independen — registrasi
bisa berhasil walau pengiriman WhatsApp gagal, dan sebaliknya kita tidak
boleh mengirim WA jika registrasi belum tersimpan.

**5. Mengapa WhatsApp baru dikirim setelah data berhasil disimpan?**
Agar data pengguna tidak hilang meskipun terjadi gangguan pada API Fonnte,
dan agar pesan WhatsApp yang dikirim benar-benar berisi data yang valid
dan sudah tercatat di sistem (menghindari notifikasi palsu untuk data yang
sebenarnya gagal tersimpan).

**6. Apa yang terjadi jika API Fonnte mengalami gangguan?**
Permintaan `kirimWhatsApp()` akan gagal (timeout, error koneksi, atau
response error dari Fonnte). Karena ditangani dengan try/catch dan
pengecekan status, sistem tetap menampilkan bahwa registrasi berhasil,
namun notifikasi WhatsApp berstatus "Gagal dikirim", dan status ini
dicatat pada tabel `log_whatsapp`.

**7. Mengapa nomor 081234567890 perlu dinormalisasi?**
Karena API pihak ketiga seperti Fonnte umumnya membutuhkan format
internasional (misalnya `62xxxxxxxxxx`), sedangkan pengguna Indonesia
biasanya menuliskan nomor dengan awalan `0`. Normalisasi memastikan pesan
dikirim ke nomor yang benar-benar valid menurut API.

**8. Apa fungsi HTTP method POST dalam integrasi API?**
POST digunakan untuk mengirim data (seperti nomor tujuan dan isi pesan)
dari server kita ke server Fonnte dengan aman di dalam body request, agar
data tersebut dapat diproses dan menghasilkan aksi (pengiriman pesan) di
sisi API Fonnte.

**9. Apa fungsi response dari API Fonnte?**
Response memberi tahu kita apakah permintaan pengiriman pesan
berhasil diterima/diproses oleh Fonnte atau tidak, termasuk pesan
error jika terjadi kegagalan. Response inilah yang digunakan sistem
untuk menentukan status "success" atau "failed" pada log pengiriman.

**10. Bagaimana cara membuat sistem agar pesan WhatsApp dapat digunakan kembali pada fitur lain?**
Dengan membungkus logika pengiriman ke dalam satu function reusable,
yaitu `kirimWhatsApp($nomor, $pesan)`, yang menerima parameter dinamis.
Function ini dapat dipanggil dari fitur mana saja (registrasi, reset
password, pembayaran, pengumuman, dll) cukup dengan mengganti nomor dan
isi pesannya saja.
