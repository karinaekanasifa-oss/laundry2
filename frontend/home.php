<?php
// Mengecek apakah sesi aplikasi sudah aktif atau belum. Jika belum, nyalakan sesi-nya[cite: 9]
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menghubungkan file ini ke file koneksi database (PDO) agar bisa tersambung ke database[cite: 9]
require_once __DIR__ . '/../Backend/components/koneksi.php'; // atau sesuaikan path-nya

// 1. Bagian untuk mengambil data statistik ringkasan yang muncul di kotak atas dashboard[cite: 9]
try {
    // Menghitung jumlah total member/pelanggan yang terdaftar di database[cite: 9]
    $total_pelanggan = $pdo->query("SELECT COUNT(*) FROM tb_member")->fetchColumn();
    // Menghitung jumlah cucian yang statusnya masih 'proses' atau 'baru'[cite: 9]
    $cucian_diproses = $pdo->query("SELECT COUNT(*) FROM tb_transaksi WHERE status = 'proses' OR status = 'baru'")->fetchColumn();
    // Menghitung jumlah cucian yang sudah 'selesai' dan siap diambil[cite: 9]
    $cucian_siap     = $pdo->query("SELECT COUNT(*) FROM tb_transaksi WHERE status = 'selesai'")->fetchColumn();
   
       // - "catch" artinya perintah untuk menangkap atau mengamankan aplikasi jika terjadi kegagalan/error.
// - "PDOException $e" adalah jenis error khusus yang berkaitan dengan database (seperti gagal koneksi atau salah query).
// Jadi, kalau bagian atas gagal dieksekusi, error-nya ditangkap di sini agar website tidak crash, 
// dan variabel $list_transaksi dikosongkan dulu ([]) agar halaman tetap bisa terbuka dengan aman.
    } catch (PDOException $e) {
    // Penyelamat jika gagal mengambil data statistik, nilainya di-set nol (0) supaya halaman tidak error[cite: 9]
    $total_pelanggan = 0;
    $cucian_diproses = 0;
    $cucian_siap = 0;
}

// 2. Bagian untuk mengambil data riwayat transaksi terbaru[cite: 9]
try {
    // Menggunakan query SQL dengan JOIN untuk menggabungkan data transaksi dengan tabel member, user/kasir, dan paket[cite: 9]
    $query = "SELECT t.*, 
             -- COALESCE adalah penyelamat: kalau data aslinya ada tampilkan, tapi kalau sudah terhapus tampilkan teks cadangan[cite: 9]
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
             ORDER BY t.id DESC LIMIT 5"; // Urutkan dari data transaksi paling baru (DESC) dan ambil 5 data saja[cite: 9]
    
    // Menjalankan query dan mengambil semua hasilnya (fetchAll)[cite: 9]
    $list_transaksi = $pdo->query($query)->fetchAll();
} catch (PDOException $e) {
    // Penyelamat (catch) jika query gagal/error, variabel list transaksi dikosongkan agar website tetap aman[cite: 9]
    $list_transaksi = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir - LaundryApp</title>
    <!-- Memasukkan link CSS Bootstrap dan Icon agar tampilan halaman menjadi rapi dan indah[cite: 9] -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <link rel="icon" type="image/jpeg" href="../Backend/img/loundryku.jpg">
</head>
<body class="bg-light">

   <!-- Memanggil file menu navigasi atas (navbar)[cite: 9] -->
   <?php include 'navbar.php'; ?>


    <!-- Navbar Atas (Opsional, kalau navbar tetap ingin dipakai) -->
    <!-- (Sesuaikan navbar yang sudah ada di project kamu di sini) -->

    <!-- Konten Utama Dashboard Saja -->
    <div class="container py-4">
        
        <!-- Header Judul Halaman -->
        <div class="mb-4">
            <h3 class="fw-bold text-dark">Dashboard Operasional</h3>
            <p class="text-muted mb-0">Pantau status cucian, member, dan transaksi laundry di sini.</p>
        </div>

        <!-- Statistik Cards (Kotak Informasi Angka Ringkasan) -->
        <div class="row g-4 mb-4">
            <!-- Kotak 1: Total Pelanggan[cite: 9] -->
            <div class="col-md-4">
                <div class="card stat-card p-3 border-0 shadow-sm rounded-4 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light p-3 rounded-4 fs-4 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block fw-semibold">Total Pelanggan</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= $total_pelanggan; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Kotak 2: Cucian Diproses[cite: 9] -->
            <div class="col-md-4">
                <div class="card stat-card p-3 border-0 shadow-sm rounded-4 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light p-3 rounded-4 fs-4 text-warning">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block fw-semibold">Cucian Diproses</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= $cucian_diproses; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Kotak 3: Selesai / Siap Diambil[cite: 9] -->
            <div class="col-md-4">
                <div class="card stat-card p-3 border-0 shadow-sm rounded-4 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light p-3 rounded-4 fs-4 text-success">
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

        <!-- Tabel Riwayat Transaksi Terbaru -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history me-2 text-secondary"></i>Riwayat Transaksi Terbaru</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="py-3 ps-3">#</th>
                            <th class="py-3">Invoice</th>
                            <th class="py-3">Member</th>
                            <th class="py-3">Paket & Rincian Harga</th>
                            <th class="py-3">Tgl Masuk</th>
                            <th class="py-3">Status Cucian</th>
                            <th class="py-3">Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Mengecek apakah data transaksi ada isinya atau tidak[cite: 9] -->
                        <?php if (count($list_transaksi) > 0): ?>
                            <?php $no = 1; foreach ($list_transaksi as $t): 
                                // Menghitung subtotal harga paket dikali jumlah berat, lalu dihitung grand total dengan biaya tambahan/diskon/pajak[cite: 9]
                                $subtotal = ($t['harga_paket'] * $t['qty']);
                                $grand_total = $subtotal + $t['biaya_tambahan'] - $t['diskon'] + $t['pajak'];
                            ?>
                                <tr>
                                    <td class="ps-3"><?= $no++; ?></td>
                                    <td><code class="fw-bold text-danger"><?= htmlspecialchars($t['kode_invoice']); ?></code></td>
                                    <td><?= htmlspecialchars($t['nama_member']); ?></td>
                                    <td>
                                        <span class="d-block fw-bold"><?= htmlspecialchars($t['nama_paket']); ?></span>
                                        <span class="text-muted" style="font-size: 12px;">Qty/Berat: <?= $t['qty']; ?></span>
                                        <div class="text-dark mt-1" style="font-size: 12px;">
                                            Total: Rp <?= number_format($grand_total, 0, ',', '.'); ?>
                                        </div>
                                    </td>
                                    <td style="font-size: 13px;"><?= htmlspecialchars($t['tgl']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary text-uppercase" style="font-size: 10px;">
                                            <?= htmlspecialchars($t['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Menampilkan label status pembayaran lunas atau belum lunas[cite: 9] -->
                                        <?php if ($t['dibayar'] === 'dibayar'): ?>
                                            <span class="badge bg-success text-uppercase" style="font-size: 10px;">LUNAS</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-uppercase" style="font-size: 10px;">BELUM LUNAS</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Pesan yang muncul jika data transaksi di database masih kosong[cite: 9] -->
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada data riwayat transaksi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <!-- Footer sederhana di bagian paling bawah web[cite: 9] -->
    <footer class="py-4 bg-body border-top text-center text-body-secondary text-sm">
        <div class="container">
            <p class="mb-0">© <?= date('Y'); ?> Lumiere Laundry.</p>
        </div>
    </footer>

</body>
</html>