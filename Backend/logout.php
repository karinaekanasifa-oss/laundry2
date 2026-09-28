<?php
// Backend/logout.php
require_once __DIR__ . '/components/koneksi.php';

// Menghapus semua variabel session yang aktif
$_SESSION = array();

// Menghapus cookie session jika browser menyimpannya
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Menghancurkan session sepenuhnya demi keamanan (mencegah Session Hijacking)
session_destroy();

// Redirect atau lempar kembali ke halaman login dengan pesan
header("Location: login.php?error=Anda telah berhasil logout");
exit();
?>