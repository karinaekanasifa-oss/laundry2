<?php
// Pastikan sesi sudah aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah fungsi get_nama_outlet_aktif sudah ada, jika belum definisikan
if (!function_exists('get_nama_outlet_aktif')) {
    function get_nama_outlet_aktif($pdo) {
        $id_outlet = $_SESSION['id_outlet'] ?? 1;
        try {
            $stmt = $pdo->prepare("SELECT nama FROM tb_outlet WHERE id = :id");
            $stmt->execute(['id' => $id_outlet]);
            $outlet = $stmt->fetch();
            return $outlet['nama'] ?? 'Outlet Utama';
        } catch (PDOException $e) {
            return 'Outlet Utama';
        }
    }
}

// Ambil variabel yang dibutuhkan untuk topbar
$nama_outlet_aktif = isset($pdo) ? get_nama_outlet_aktif($pdo) : 'Outlet Utama';
$role_user_topbar = $_SESSION['role'] ?? 'kasir';
?>

<!-- Top Navbar dengan pengunci tinggi dan flexbox agar presisi -->
<nav class="navbar navbar-top navbar-expand mb-4 bg-white border-bottom shadow-sm w-100 custom-topbar px-0">
    <div class="container-fluid px-4 h-100 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <?php if ($role_user_topbar === 'admin' && isset($pdo)): 
                try {
                    $stmt_all_o = $pdo->query("SELECT * FROM tb_outlet ORDER BY nama ASC");
                    $list_o = $stmt_all_o->fetchAll();
                } catch (PDOException $e) {
                    $list_o = [];
                }
            ?>
                <div class="dropdown">
                    <button class="btn btn-sm bg-burgundy-soft dropdown-toggle px-3 py-2 rounded-pill fw-semibold border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($nama_outlet_aktif); ?>
                    </button>
                    <ul class="dropdown-menu shadow-sm border-0 rounded-4 p-2">
                        <li><h6 class="dropdown-header small text-muted">Pindah Posisi Outlet:</h6></li>
                        <?php foreach ($list_o as $lo): ?>
                            <li>
                                <a class="dropdown-item rounded-2 py-2 <?= ($_SESSION['id_outlet'] == $lo['id']) ? 'active bg-danger text-white' : ''; ?>" href="ganti_outlet.php?id=<?= $lo['id']; ?>&redirect=<?= urlencode(basename($_SERVER['PHP_SELF'])); ?>">
                                    <i class="bi bi-shop me-2"></i> <?= htmlspecialchars($lo['nama']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <span class="badge bg-burgundy-soft px-3 py-2 rounded-pill">
                    <i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($nama_outlet_aktif); ?>
                </span>
            <?php endif; ?>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <span class="d-block fw-bold text-dark small"><?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna'); ?></span>
                <span class="badge bg-secondary text-uppercase" style="font-size: 10px;"><?= htmlspecialchars($role_user_topbar); ?></span>
            </div>
            <div class="bg-burgundy-soft rounded-circle d-flex align-items-center justify-content-center fw-bold text-danger" style="width: 40px; height: 40px;">
                <?= strtoupper(substr($_SESSION['nama'] ?? 'P', 0, 1)); ?>
            </div>
        </div>
    </div>
</nav>