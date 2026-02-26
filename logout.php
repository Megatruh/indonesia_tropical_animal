<?php
session_start();

// Hapus semua session
$_SESSION = [];
session_destroy();

// Hapus cookie jika ada
if (isset($_COOKIE['id'])) {
    setcookie('id', '', time() - 3600, '/');
}
if (isset($_COOKIE['key'])) {
    setcookie('key', '', time() - 3600, '/');
}

// Redirect ke login
header("Location: login.php");
exit;