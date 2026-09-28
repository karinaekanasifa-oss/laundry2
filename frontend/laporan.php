<?php
/**
 * --------------------------------------------------------------------------
 * HALAMAN GENERATE LAPORAN TRANSAKSI (laporan.php)
 * --------------------------------------------------------------------------
 * File ini berfungsi untuk:
 * 1. Pengecekan sesi login dan pembatasan hak akses (hanya admin dan kasir)[cite: 6].
 * 2. Menangkap parameter filter laporan (periode kuartal, tahun, dan status)[cite: 6].
 * 3. Mengambil data rekapitulasi transaksi dari database dengan teknik JOIN[cite: 6].
 * 4. Menghitung total akumulasi pendapatan khusus dari transaksi yang sudah lunas[cite: 6].
 * 5. Menyediakan fitur cetak laporan langsung via browser (Print Friendly)[cite: 6].
 */

// Memanggil file koneksi database PDO dan fungsi pembatas hak akses[cite: 6]
require_once __DIR__ . '/../Backend/components/koneksi.php';
restrict_access(['admin', 'kasir']);

// Pengecekan keamanan: Memastikan apakah variabel sesi 'user_id' sudah terdaftar[cite: 6]
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, arahkan paksa ke halaman login[cite: 6]
    header("Location: login.php?error=Silakan login terlebih dahulu!");
    exit();
}

// Inisialisasi variabel ID outlet aktif dari sesi pengguna, dengan nilai default 1[cite: 6]
$id_outlet_user = $_SESSION['id_outlet'] ?? 1;

// Menangkap parameter filter laporan yang dikirimkan melalui metode GET di URL[cite: 6]
$periode = $_GET['periode'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');
$status_filter = $_GET['status'] ?? '';

// Blok Try-Catch untuk mengambil dan memfilter data laporan transaksi dari database[cite: 6]
try {
    // Menyusun query SQL utama dengan teknik LEFT JOIN untuk relasi tabel[cite: 6]
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
              LEFT JOIN tb_paket pk ON dt.id_paket = pk.id WHERE 1=1";
    
    $params = [];

    // Kondisi Filter 1: Berdasarkan status cucian (baru, proses, selesai, diambil)[cite: 6]
    if (!empty($status_filter)) {
        $query .= " AND t.status = :status";
        $params['status'] = $status_filter;
    }

    // Kondisi Filter 2: Berdasarkan periode kuartal / rentang waktu 3 bulanan dalam setahun[cite: 6]
    if (!empty($periode)) {
        $map_bulan = [
            1 => ['-01-01', '-03-31'], // Kuartal 1: Januari - Maret[cite: 6]
            2 => ['-04-01', '-06-30'], // Kuartal 2: April - Juni[cite: 6]
            3 => ['-07-01', '-09-30'], // Kuartal 3: Juli - September[cite: 6]
            4 => ['-10-01', '-12-31']  // Kuartal 4: Oktober - Desember[cite: 6]
        ];
        
        if (isset($map_bulan[$periode])) {
            $query .= " AND t.tgl BETWEEN :start_date AND :end_date";
            $params['start_date'] = $tahun . $map_bulan[$periode][0] . ' 00:00:00';
            $params['end_date'] = $tahun . $map_bulan[$periode][1] . ' 23:59:59';
        }
    }

    // Mengurutkan data laporan berdasarkan ID terbesar (transaksi terbaru berada di atas)[cite: 6]
    $query .= " ORDER BY t.id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $list_laporan = $stmt->fetchAll();

    // Kalkulasi Finansial: Menghitung total pendapatan keseluruhan dari data transaksi yang berstatus lunas[cite: 6]
    $total_pendapatan = 0;
    foreach ($list_laporan as $l) {
        if ($l['dibayar'] === 'dibayar') {
            $subtotal = ($l['harga_paket'] * $l['qty']);
            $diskon_bersih = abs($l['diskon']);
            $grand_total = $subtotal + $l['biaya_tambahan'] - $diskon_bersih + $l['pajak'];
            $total_pendapatan += $grand_total;
        }
    }

} catch (PDOException $e) {
    // Nilai fallback cadangan jika terjadi kesalahan pada query database[cite: 6]
    $list_laporan = [];
    $total_pendapatan = 0;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Laporan - Lumiere Laundry</title>
    
    <!-- Memuat file pustaka CSS eksternal (Glide, AOS, Bootstrap, Custom Style)[cite: 6] -->
    <link rel="stylesheet" href="./assets/libraries/glide/css/glide.core.min.css">
    <link rel="stylesheet" href="./assets/libraries/aos/aos.css">
    <link rel="stylesheet" href="./assets/css/main.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <!-- Memuat ikon FontAwesome dan Bootstrap Icons[cite: 6] -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/jpeg" href="../Backend/img/loundryku.jpg">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .table-custom th {
            background-color: #fcf1f3;
            color: #800020;
            font-weight: 600;
        }
        /* Pengaturan Gaya Khusus Saat Dokumen Dicetak (Print Mode) */
        @media print {
            .no-print {
                display: none !important; /* Menyembunyikan tombol dan navigasi ketika cetak laporan */
            }
            body {
                background-color: white !important;
            }
        }
    </style>
</head>

<body>
     <?php include 'navbar.php'; // Menyisipkan komponen navigasi utama[cite: 6] ?>

    <!-- Bagian Konten Utama Halaman Laporan[cite: 6] -->
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h2 class="fw-bold text-dark">Generate Laporan Transaksi</h2>
                <p class="text-muted mb-0">Saring dan cetak rekapitulasi laporan pendapatan laundry.</p>
            </div>
            <!-- Tombol interaktif untuk memicu fungsi cetak browser (window.print)[cite: 6] -->
            <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow-sm text-white">
                <i class="bi bi-printer-fill me-1"></i> Cetak Laporan
            </button>
        </div>

        <!-- Kartu Formulir Filter Laporan (Disembunyikan saat dicetak)[cite: 6] -->
        <div class="card shadow-sm border-0 rounded-4 p-4 mb-4 bg-white no-print">
            <form method="GET" action="laporan.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary">Periode 3 Bulan</label>
                    <select name="periode" class="form-select rounded-pill shadow-sm">
                        <option value="">Semua Periode</option>
                        <option value="1" <?= $periode=='1'?'selected':''; ?>>Januari - Maret</option>
                        <option value="2" <?= $periode=='2'?'selected':''; ?>>April - Juni</option>
                        <option value="3" <?= $periode=='3'?'selected':''; ?>>Juli - September</option>
                        <option value="4" <?= $periode=='4'?'selected':''; ?>>Oktober - Desember</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Tahun</label>
                    <input type="number" name="tahun" class="form-control rounded-pill shadow-sm" value="<?= htmlspecialchars($tahun); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Status Pembayaran / Filter</label>
                    <select name="status" class="form-select rounded-pill shadow-sm">
                        <option value="">Semua Status Cucian</option>
                        <option value="baru" <?= $status_filter=='baru'?'selected':''; ?>>Baru</option>
                        <option value="proses" <?= $status_filter=='proses'?'selected':''; ?>>Proses</option>
                        <option value="selesai" <?= $status_filter=='selesai'?'selected':''; ?>>Selesai</option>
                        <option value="diambil" <?= $status_filter=='diambil'?'selected':''; ?>>Diambil</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 shadow-sm text-white"><i class="bi bi-filter me-1"></i> Tampilkan</button>
                </div>
            </form>
        </div>

        <!-- Kartu Ringkasan Total Pendapatan[cite: 6] -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase small fw-bold mb-1">Total Pendapatan (Transaksi Lunas)</h6>
                            <h3 class="fw-bold mb-0">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Tabel Rekapitulasi Data Laporan[cite: 6] -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <!-- Kop Judul Khusus yang Hanya Muncul Saat Dicetak (Print Only)[cite: 6] -->
            <div class="text-center mb-4 d-none d-print-block">
                <h3 class="fw-bold">LUMIERE LAUNDRY - LAPORAN TRANSAKSI</h3>
                <p class="text-muted small">Dicetak pada: <?= date('d-m-Y H:i:s'); ?></p>
                <hr>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle table-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice</th>
                            <th>Member</th>
                            <th>Paket & Rincian</th>
                            <th>Tgl Masuk</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($list_laporan) > 0): ?>
                            <?php 
                            $no = 1; 
                            foreach ($list_laporan as $l): 
                                // Kalkulasi ulang grand total untuk tiap baris transaksi[cite: 6]
                                $subtotal = ($l['harga_paket'] * $l['qty']);
                                $diskon_bersih = abs($l['diskon']);
                                $grand_total = $subtotal + $l['biaya_tambahan'] - $diskon_bersih + $l['pajak'];
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><code class="fw-bold text-dark"><?= htmlspecialchars($l['kode_invoice']); ?></code></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($l['nama_member']); ?></td>
                                    <td>
                                        <span class="small d-block fw-bold"><?= htmlspecialchars($l['nama_paket']); ?></span>
                                        <span class="text-muted" style="font-size: 11px;">Berat/Qty: <?= $l['qty']; ?></span>
                                    </td>
                                    <td class="small text-secondary"><?= htmlspecialchars($l['tgl']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary text-uppercase" style="font-size: 9px;"><?= htmlspecialchars($l['status']); ?></span>
                                    </td>
                                    <td>
                                        <!-- Indikator Badge Status Pembayaran[cite: 6] -->
                                        <?php if ($l['dibayar'] === 'dibayar'): ?>
                                            <span class="badge bg-success text-uppercase" style="font-size: 9px;">Lunas</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-uppercase" style="font-size: 9px;">Belum Lunas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-dark">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Baris alternatif apabila data laporan kosong[cite: 6] -->
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-file-earmark-text fs-2 d-block mb-2"></i>
                                    Tidak ada data laporan untuk filter yang dipilih.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bagian Footer Website[cite: 6] -->
    <footer class="py-4 bg-body border-top text-center text-body-secondary text-sm">
        <div class="container">
            <p class="mb-0">© <?= date('Y'); ?> Lumiere Laundry.</p>
        </div>
    </footer>

    <!-- Memuat Pustaka Skrip JavaScript Pendukung[cite: 6] -->
    <script src="./assets/libraries/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/libraries/glide/glide.min.js"></script>
    <script src="./assets/libraries/aos/aos.js"></script>
    <script src="./assets/js/scripts.js"></script>
</body>
</html>