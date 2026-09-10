<?php
require_once __DIR__ . '/functions/auth.php';

// Hanya hapus session milik user, tidak mengganggu session admin (jika ada)
unset($_SESSION['user_id'], $_SESSION['user_nama'], $_SESSION['user_email']);

header('Location: user-login.php');
exit;
