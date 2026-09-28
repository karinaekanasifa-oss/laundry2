<?php
// Backend/partials/sidebar.php
$role = $_SESSION['role'] ?? 'admin';
$current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$menus = [
    ['url' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'roles' => ['admin', 'owner']],
    ['url' => 'outlet.php', 'label' => 'Outlet', 'icon' => 'bi-shop', 'roles' => ['admin']],
    ['url' => 'member.php', 'label' => 'Member', 'icon' => 'bi-people', 'roles' => ['admin']],
    ['url' => 'paket.php', 'label' => 'Paket Cucian', 'icon' => 'bi-tag', 'roles' => ['admin']],
    ['url' => 'user.php', 'label' => 'Pengguna / Kasir', 'icon' => 'bi-person-badge', 'roles' => ['admin']],
    ['url' => 'transaksi.php', 'label' => 'Transaksi', 'icon' => 'bi-cart-check', 'roles' => ['admin']],
    ['url' => 'laporan.php', 'label' => 'Laporan', 'icon' => 'bi-file-earmark-text', 'roles' => ['admin', 'owner']],
];
?>
<nav id="sidebar" class="d-none d-md-block">
    <style>
        #sidebar .nav-link {
            color: #495057;
            font-weight: 500;
            padding: 0.7rem 1.2rem;
            border-radius: 0;
            margin: 0;
            transition: all 0.2s ease-in-out;
            border-left: 4px solid transparent;
        }
        /* Menu yang sedang aktif */
        #sidebar .nav-link.active {
            background-color: #fcf1f3 !important;
            color: #800020 !important;
            font-weight: 600;
            border-left: 4px solid #800020;
        }
        #sidebar .nav-link.active i {
            color: #800020 !important;
        }
        /* Efek saat kursor menelusuri/hover menu */
        #sidebar .nav-link:hover:not(.active) {
            background-color: #fcf1f3;
            color: #800020;
            border-left: 4px solid #800020;
        }
        #sidebar .nav-link:hover:not(.active) i {
            color: #800020;
        }
    </style>
    <!-- Bagian Logo dengan tinggi presisi 70px agar garisnya menyambung rata dengan topbar -->
    <div class="px-4 d-flex align-items-center" style="height: 70px; color: #800020; font-family: 'Inter', sans-serif; font-weight: 700; font-size: 1.1rem; border-bottom: 1px solid #ebd3d7; box-sizing: border-box;">
        <i class="bi bi-basket3-fill me-2 fs-4"></i> LaundryApp
    </div>
    <ul class="nav flex-column mt-2">
        <?php foreach ($menus as $m): ?>
            <?php if (in_array($role, $m['roles'])): ?>
                <li class="nav-item">
                    <a href="<?= $m['url']; ?>" class="nav-link <?= ($current_page == $m['url']) ? 'active' : ''; ?>">
                        <i class="bi <?= $m['icon']; ?> fs-5 me-3"></i> <?= $m['label']; ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
        <li class="nav-item mt-4 px-3">
            <a href="logout.php" class="nav-link text-danger rounded-3" onclick="return confirm('Apakah Anda yakin ingin keluar dari aplikasi?');">
                <i class="bi bi-box-arrow-right fs-5 me-3"></i> Logout
            </a>
        </li>
    </ul>
</nav>