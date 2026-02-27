<?php
session_start();

// Hapus semua session
$_SESSION = [];
session_destroy();

setcookie('id', '', time() - 3600);
setcookie('key', '', time() - 3600);

// Redirect ke login
header("Location: login.php");
exit;