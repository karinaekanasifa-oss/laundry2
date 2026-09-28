<?php
// Mengambil file koneksi database dan memulai session agar aplikasi bisa mengenali user yang sedang login[cite: 11]
require_once __DIR__ . '/components/koneksi.php';

// Mengecek apakah user sudah login atau belum. Kalau belum, dipaksa balik ke halaman login[cite: 11]
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Silakan login terlebih dahulu!");
    exit();
}

// Menyiapkan variabel hak akses (role) user, default-nya diset sebagai admin[cite: 11]
$role_user = $_SESSION['role'] ?? 'admin';

// BAGIAN 1: Proses saat tombol ubah status cucian diklik dari tabel dashboard[cite: 11]
if (isset($_GET['ubah_status']) && isset($_GET['id'])) {
    $id = $_GET['id']; // Mengambil ID transaksi yang mau diubah[cite: 11]
    $status_baru = $_GET['ubah_status']; // Mengambil status baru (baru/proses/selesai/diambil)[cite: 11]
    try {
        // Jika status diubah menjadi 'diambil', sistem otomatis mengubah status pembayaran jadi 'dibayar' dan mencatat tanggal bayar[cite: 11]
        if ($status_baru === 'diambil') {
            $stmt = $pdo->prepare("UPDATE tb_transaksi SET status = :status, dibayar = 'dibayar', tgl_bayar = NOW() WHERE id = :id");
        } else {
            // Jika statusnya selain diambil (misal proses/selesai), hanya status cuciannya saja yang di-update[cite: 11]
            $stmt = $pdo->prepare("UPDATE tb_transaksi SET status = :status WHERE id = :id");
        }
        // Menjalankan perintah query update ke database[cite: 11]
        $stmt->execute(['status' => $status_baru, 'id' => $id]);
        header("Location: dashboard.php?pesan=status_sukses");
        exit();
    } catch (PDOException $e) {
        $pesan_error = "Gagal memperbarui status transaksi.";
    }
}

// BAGIAN 2: Menghitung statistik ringkasan yang muncul di kartu atas (Card Dashboard)[cite: 11]
try {
    // Menghitung total jumlah pelanggan yang terdaftar[cite: 11]
    $total_pelanggan = $pdo->query("SELECT COUNT(*) FROM tb_member")->fetchColumn();
    // Menghitung jumlah cucian yang statusnya masih baru atau sedang diproses[cite: 11]
    $cucian_diproses = $pdo->query("SELECT COUNT(*) FROM tb_transaksi WHERE status = 'proses' OR status = 'baru'")->fetchColumn();
    // Menghitung jumlah cucian yang sudah selesai dikerjakan[cite: 11]
    $cucian_siap     = $pdo->query("SELECT COUNT(*) FROM tb_transaksi WHERE status = 'selesai'")->fetchColumn();
} catch (PDOException $e) {
    // Kalau gagal ambil data, nilai statistiknya diset nol agar tidak error[cite: 11]
    $total_pelanggan = 0;
    $cucian_diproses = 0;
    $cucian_siap = 0;
}

// BAGIAN 3: Mengambil data riwayat transaksi terbaru untuk ditampilkan ke dalam tabel[cite: 11]
try {
    // Menggunakan teknik JOIN untuk menggabungkan data transaksi dengan tabel member, user/kasir, detail transaksi, dan paket cuci[cite: 11]
    // COALESCE: Berfungsi sebagai penyelamat/cadangan. 
// Jika data aslinya ada, tampilkan. Jika sudah terhapus di database, 
// tampilkan teks pengganti (misal: 'Member Umum / Terhapus') agar website tidak error.
    $query = "SELECT t.*, 
             COALESCE(m.nama, 'Member Umum / Terhapus') AS nama_member, 
             COALESCE(u.nama, 'Administrator') AS nama_user, 
             COALESCE(pk.nama_paket, 'Paket Manual') AS nama_paket, 
             COALESCE(pk.harga, 0) AS harga_paket,
             COALESCE(dt.qty, 1) AS qty
             FROM tb_transaksi t
             LEFT JOIN tb_member m ON t.id_member = m.id
             LEFT JOIN tb_user u ON t.id_user = u.id
             LEFT JOIN tb_detail_transaksi dt ON t.id = dt.id_transaksi
             LEFT JOIN tb_paket pk ON dt.id_paket = pk.id
             ORDER BY t.id DESC LIMIT 5"; // Hanya ambil 5 data transaksi terakhir[cite: 11]
    $list_transaksi = $pdo->query($query)->fetchAll();
    
    // - "catch" artinya perintah untuk menangkap atau mengamankan aplikasi jika terjadi kegagalan/error.
// - "PDOException $e" adalah jenis error khusus yang berkaitan dengan database (seperti gagal koneksi atau salah query).
// Jadi, kalau bagian atas gagal dieksekusi, error-nya ditangkap di sini agar website tidak crash, 
// dan variabel $list_transaksi dikosongkan dulu ([]) agar halaman tetap bisa terbuka dengan aman.
} catch (PDOException $e) {
    $list_transaksi = []; // Jika gagal, kosongkan datanya[cite: 11]
}

// Memanggil file tampilan bagian atas (Header website)[cite: 11]
include __DIR__ . '/partials/header.php';
?>

<!-- Tambahan Style Khusus untuk Memaksa Tampilan Tabel Sesuai Keinginan[cite: 11] -->
<style>
    .text-burgundy {
        color: #800020 !important;
    }
    /* Memaksa semua teks di baris tabel menjadi lebih tebal dan jelas[cite: 11] */
    .table-custom-style tbody td {
        font-weight: 600 !important;
        color: #212529 !important;
    }
    .table-custom-style thead th {
        background-color: #fcf1f3 !important;
        color: #800020 !important;
        font-weight: 700 !important;
    }
</style>

<div class="d-flex" style="width: 100%; overflow-x: hidden;">
    <!-- Memanggil menu samping (Sidebar)[cite: 11] -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Wrapper (Kotak Utama Halaman)[cite: 11] -->
    <div id="content" class="p-0" style="min-height: 100vh; background-color: #f8f9fa;">
        <!-- Memanggil menu bagian atas (Topbar)[cite: 11] -->
        <?php include __DIR__ . '/partials/topbar.php'; ?>

        <!-- Isi Utama Halaman (Page Content)[cite: 11] -->
        <div class="container-fluid px-4 pb-5">
            <div class="mb-4">
                <h3 class="fw-bold text-dark">Dashboard Operasional</h3>
                <p class="text-muted mb-0">Pantau status cucian, member, dan transaksi laundry di sini.</p>
            </div>

            <!-- Menampilkan Notifikasi Berhasil jika status sukses diperbarui[cite: 11] -->
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'status_sukses'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Status transaksi berhasil diperbarui dari Dashboard!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Kotak Statistik Ringkasan (Cards)[cite: 11] -->
            <div class="row g-4 mb-4">
                <!-- Kotak 1: Total Pelanggan[cite: 11] -->
                <div class="col-md-4">
                    <div class="card stat-card p-3 border-0 shadow-sm rounded-4 bg-white">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-burgundy-soft p-3 rounded-4 fs-4 text-burgundy">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <span class="text-muted small d-block fw-semibold">Total Pelanggan</span>
                                <h3 class="fw-bold mb-0 text-dark"><?= $total_pelanggan; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Kotak 2: Cucian yang Sedang Diproses[cite: 11] -->
                <div class="col-md-4">
                    <div class="card stat-card p-3 border-0 shadow-sm rounded-4 bg-white">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-burgundy-soft p-3 rounded-4 fs-4 text-burgundy">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                            <div>
                                <span class="text-muted small d-block fw-semibold">Cucian Diproses</span>
                                <h3 class="fw-bold mb-0 text-dark"><?= $cucian_diproses; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Kotak 3: Cucian Selesai/Siap Diambil[cite: 11] -->
                <div class="col-md-4">
                    <div class="card stat-card p-3 border-0 shadow-sm rounded-4 bg-white">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-burgundy-soft p-3 rounded-4 fs-4 text-burgundy">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div>
                                <span class="text-muted small d-block fw-semibold">Selesai / Siap Diambil</span>
                                <h3 class="fw-bold mb-0 text-dark"><?= $cucian_siap; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Riwayat Transaksi Terbaru[cite: 11] -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history me-2 text-secondary"></i>Riwayat Transaksi Terbaru</h5>
                    <a href="transaksi.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-custom-style">
                        <thead>
                            <tr>
                                <th class="py-3 ps-3">#</th>
                                <th class="py-3">Invoice</th>
                                <th class="py-3">Member</th>
                                <th class="py-3">Paket & Rincian Harga</th>
                                <th class="py-3">Tgl Masuk</th>
                                <th class="py-3">Status Cucian</th>
                                <th class="py-3">Pembayaran</th>
                                <th class="py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Mengecek apakah data transaksi ada isinya[cite: 11] -->
                            <?php if (count($list_transaksi) > 0): ?>
                                <?php $no = 1;
                                foreach ($list_transaksi as $t):
                                    // Menghitung total harga dikali jumlah/berat cucian, lalu ditambah biaya lain atau dikurangi diskon[cite: 11]
                                    $subtotal = ($t['harga_paket'] * $t['qty']);
                                    $grand_total = $subtotal + $t['biaya_tambahan'] - $t['diskon'] + $t['pajak'];
                                ?>
                                    <tr>
                                        <td class="ps-3"><?= $no++; ?></td>
                                        <td><code class="fw-bold text-burgundy"><?= htmlspecialchars($t['kode_invoice']); ?></code></td>
                                        <td><?= htmlspecialchars($t['nama_member']); ?></td>
                                        <td>
                                            <span class="d-block fw-bold"><?= htmlspecialchars($t['nama_paket']); ?></span>
                                            <span class="text-muted" style="font-size: 12px; font-weight: normal;">Qty/Berat: <?= $t['qty']; ?></span>

                                            <!-- Menampilkan rincian tambahan biaya, diskon, atau pajak jika ada[cite: 11] -->
                                            <?php if ($t['biaya_tambahan'] > 0 || $t['diskon'] > 0 || $t['pajak'] > 0): ?>
                                                <div class="mt-1" style="font-size: 11px; font-weight: normal;">
                                                    <?php if ($t['biaya_tambahan'] > 0): ?>
                                                        <span class="text-success">+ Tambahan: Rp <?= number_format($t['biaya_tambahan'], 0, ',', '.'); ?></span><br>
                                                    <?php endif; ?>
                                                    <?php if ($t['diskon'] > 0): ?>
                                                        <span class="text-danger">- Diskon: Rp <?= number_format($t['diskon'], 0, ',', '.'); ?></span><br>
                                                    <?php endif; ?>
                                                    <?php if ($t['pajak'] > 0): ?>
                                                        <span class="text-info">+ Pajak: Rp <?= number_format($t['pajak'], 0, ',', '.'); ?></span><br>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="text-dark mt-1" style="font-size: 12px;">
                                                Total: Rp <?= number_format($grand_total, 0, ',', '.'); ?>
                                            </div>
                                        </td>
                                        <td style="font-size: 13px;"><?= htmlspecialchars($t['tgl']); ?></td>
                                        <td>
                                            <!-- Menentukan warna badge berdasarkan status cucian[cite: 11] -->
                                            <?php
                                            $status = $t['status'];
                                            $badge_s = 'bg-secondary';
                                            if ($status === 'baru') $badge_s = 'bg-info text-dark';
                                            elseif ($status === 'proses') $badge_s = 'bg-warning text-dark';
                                            elseif ($status === 'selesai') $badge_s = 'bg-primary';
                                            elseif ($status === 'diambil') $badge_s = 'bg-success';
                                            ?>
                                            <span class="badge <?= $badge_s; ?> text-uppercase" style="font-size: 10px;">
                                                <?= htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Menampilkan status lunas atau belum lunas[cite: 11] -->
                                            <?php if ($t['dibayar'] === 'dibayar'): ?>
                                                <span class="badge bg-success text-uppercase" style="font-size: 10px;">LUNAS</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger text-uppercase" style="font-size: 10px;">BELUM LUNAS</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <!-- Tombol untuk melihat detail transaksi[cite: 11] -->
                                                <a href="transaksi_detail.php?id=<?= $t['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" title="Detail / Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <!-- Tombol dropdown untuk cepat mengubah status cucian[cite: 11] -->
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill px-3 py-1" type="button" data-bs-toggle="dropdown" style="font-size: 13px;">
                                                        Status
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="dashboard.php?id=<?= $t['id']; ?>&ubah_status=baru">Baru</a></li>
                                                        <li><a class="dropdown-item" href="dashboard.php?id=<?= $t['id']; ?>&ubah_status=proses">Proses</a></li>
                                                        <li><a class="dropdown-item" href="dashboard.php?id=<?= $t['id']; ?>&ubah_status=selesai">Selesai</a></li>
                                                        <li><a class="dropdown-item" href="dashboard.php?id=<?= $t['id']; ?>&ubah_status=diambil">Diambil (Lunas)</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Tampilan jika data transaksi masih kosong[cite: 11] -->
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-cart-x fs-2 d-block mb-2"></i>
                                        Belum ada data riwayat transaksi.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Memanggil Javascript Bootstrap agar komponen interaktif berfungsi[cite: 11] -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>