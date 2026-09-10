<?php
require_once __DIR__ . '/functions/auth.php';

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
