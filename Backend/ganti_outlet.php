<?php
// ganti_outlet.php
require_once __DIR__ . '/components/koneksi.php';

if (isset($_GET['id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $id_outlet_baru = intval($_GET['id']);
    
    // Validasi apakah outlet benar-benar ada di database
    $stmt = $pdo->prepare("SELECT id FROM tb_outlet WHERE id = :id");
    $stmt->execute(['id' => $id_outlet_baru]);
    if ($stmt->fetch()) {
        // Update session id_outlet aktif secara global
        $_SESSION['id_outlet'] = $id_outlet_baru;
    }
}

// Redirect kembali ke halaman asal user berada (misal: dashboard.php, user.php, dll)
$redirect_page = $_GET['redirect'] ?? 'dashboard.php';
header("Location: " . $redirect_page);
exit();
?>