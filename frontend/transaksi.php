<?php
/**
 * --------------------------------------------------------------------------
 * HALAMAN MANAJEMEN TRANSAKSI (transaksi.php)
 * --------------------------------------------------------------------------
 * File ini berfungsi sebagai pusat pengelolaan transaksi laundry yang meliputi:
 * 1. Pengecekan sesi login dan hak akses (Role: admin & kasir).
 * 2. Pengambilan data outlet aktif, daftar member, dan paket cucian.
 * 3. Pemrosesan form untuk Tambah Transaksi, Edit Transaksi, dan Update Status.
 * 4. Sistem Filter data berdasarkan periode kuartal, tahun, dan status cucian.
 * 5. Sistem Pagination (Pembagian halaman tabel) agar data tidak menumpuk.
 */

// Memanggil file koneksi database PDO dan fungsi pengaman hak akses[cite: 5]
require_once __DIR__ . '/../Backend/components/koneksi.php';

// Membatasi akses halaman ini hanya untuk role 'admin' dan 'kasir'[cite: 5]
restrict_access(['admin', 'kasir']);

// Pengecekan keamanan ganda: Memastikan apakah variabel sesi 'user_id' benar-benar ada[cite: 5]
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, arahkan paksa ke halaman login dengan menyertakan pesan error[cite: 5]
    header("Location: login.php?error=Silakan login terlebih dahulu!");
    exit();
}

// Inisialisasi variabel data user dari sesi yang sedang aktif, gunakan nilai default jika kosong[cite: 5]
$nama_user = $_SESSION['nama'] ?? 'Administrator';
$role_user = $_SESSION['role'] ?? 'admin';
$id_outlet_user = $_SESSION['id_outlet'] ?? 1;

// Blok Try-Catch untuk mengambil nama outlet tempat user bertugas saat ini dari database[cite: 5]
try {
    $stmt_outlet = $pdo->prepare("SELECT nama FROM tb_outlet WHERE id = :id");
    $stmt_outlet->execute(['id' => $id_outlet_user]);
    $outlet_active = $stmt_outlet->fetch();
    $nama_outlet = $outlet_active['nama'] ?? 'Outlet Utama';
} catch (PDOException $e) {
    // Jika terjadi kendala database, fallback ke nama default 'Outlet Utama'[cite: 5]
    $nama_outlet = 'Outlet Utama';
}

// Inisialisasi variabel penampung pesan feedback (sukses/gagal) untuk notifikasi antarmuka[cite: 5]
$pesan_sukses = '';
$pesan_error = '';

// Menangkap parameter filter yang dikirimkan melalui metode GET di URL[cite: 5]
$periode = $_GET['periode'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');
$status_filter = $_GET['status'] ?? '';

// Konfigurasi sistem Pagination (Pembagian Halaman Tabel)[cite: 5]
$limit = 5; // Menetapkan jumlah maksimal baris data transaksi yang ditampilkan per halaman[cite: 5]
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Mengambil nomor halaman aktif dari URL, default halaman 1[cite: 5]
$page = max($page, 1); // Memastikan nomor halaman tidak bernilai negatif atau nol[cite: 5]
$offset = ($page - 1) * $limit; // Menghitung titik awal (offset) data yang akan diambil dari database[cite: 5]

/**
 * Fungsi Bantuan: catat_log
 * Digunakan untuk merekam setiap aktivitas penting pengguna ke dalam tabel 'activity_log'[cite: 5]
 */
function catat_log($pdo, $username, $aktivitas) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_log (username, activity, created_at) VALUES (:username, :activity, NOW())");
        $stmt->execute([
            'username' => $username,
            'activity' => $aktivitas
        ]);
    } catch (PDOException $e) {
        // Mengabaikan error secara aman agar proses utama tidak terganggu jika tabel log bermasalah[cite: 5]
    }
}

/**
 * --------------------------------------------------------------------------
 * 1. PROSES UTAMA: MENAMBAHKAN TRANSAKSI BARU
 * --------------------------------------------------------------------------
 * Dijalankan ketika form tambah transaksi disubmit via metode POST[cite: 5]
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_transaksi'])) {
    $id_outlet      = $id_outlet_user;
    $id_member      = $_POST['id_member'];
    $id_paket       = $_POST['id_paket'];
    $tgl            = date('Y-m-d H:i:s'); // Mencatat tanggal dan waktu transaksi dibuat saat ini[cite: 5]
    $batas_waktu    = date('Y-m-d H:i:s', strtotime('+3 days')); // Menetapkan estimasi selesai otomatis 3 hari ke depan[cite: 5]
    
    $dibayar        = $_POST['dibayar']; 
    // Jika status pembayaran 'dibayar' (lunas), catat waktu bayarnya, jika belum set NULL[cite: 5]
    $tgl_bayar      = ($dibayar === 'dibayar') ? date('Y-m-d H:i:s') : NULL;
    
    $qty            = floatval($_POST['qty']); // Mengubah input jumlah/berat menjadi tipe data pecahan (float)[cite: 5]
    $biaya_tambahan = floatval($_POST['biaya_tambahan'] ?? 0);
    $diskon         = abs(floatval($_POST['diskon'] ?? 0)); // Memastikan nilai diskon selalu positif dengan fungsi abs()[cite: 5]
    $pajak          = floatval($_POST['pajak'] ?? 0);
    $status         = 'baru'; // Status awal setiap cucian masuk selalu diset 'baru'[cite: 5]
    $id_user        = $_SESSION['user_id'];

    // Validasi form: Memastikan member, paket, dan jumlah kuantiti/berat sudah diisi dengan benar[cite: 5]
    if (!empty($id_member) && !empty($id_paket) && $qty > 0) {
        try {
            // Mengambil data harga satuan paket dari tabel tb_paket berdasarkan ID paket[cite: 5]
            $stmt_p = $pdo->prepare("SELECT harga FROM tb_paket WHERE id = :id");
            $stmt_p->execute(['id' => $id_paket]);
            $paket = $stmt_p->fetch();

            if ($paket) {
                // Membuat kode invoice unik secara otomatis berdasarkan format teks 'TRX-' dan timestamp waktu detik ini[cite: 5]
                $kode_invoice = 'TRX-' . date('YmdHis');

                // Menyiapkan query SQL untuk menyimpan data transaksi utama ke tabel 'tb_transaksi'[cite: 5]
                $stmt = $pdo->prepare("INSERT INTO tb_transaksi (id_outlet, kode_invoice, id_member, tgl, batas_waktu, tgl_bayar, biaya_tambahan, diskon, pajak, status, dibayar, id_user) VALUES (:id_outlet, :kode_invoice, :id_member, :tgl, :batas_waktu, :tgl_bayar, :biaya_tambahan, :diskon, :pajak, :status, :dibayar, :id_user)");
                
                // Mengeksekusi query dengan parameter yang terikat aman (Prepared Statements)[cite: 5]
                $stmt->execute([
                    'id_outlet'      => $id_outlet,
                    'kode_invoice'   => $kode_invoice,
                    'id_member'      => $id_member,
                    'tgl'            => $tgl,
                    'batas_waktu'    => $batas_waktu,
                    'tgl_bayar'      => $tgl_bayar,
                    'biaya_tambahan' => $biaya_tambahan,
                    'diskon'         => $diskon,
                    'pajak'          => $pajak,
                    'status'         => $status,
                    'dibayar'        => $dibayar,
                    'id_user'        => $id_user
                ]);

                // Mengambil ID unik dari data transaksi yang baru saja dimasukkan ke database[cite: 5]
                $id_transaksi_baru = $pdo->lastInsertId();

                // Menyimpan rincian detail transaksi (relasi ke paket dan jumlah berat/qty) ke tabel 'tb_detail_transaksi'[cite: 5]
                $stmt_detail = $pdo->prepare("INSERT INTO tb_detail_transaksi (id_transaksi, id_paket, qty, keterangan) VALUES (:id_transaksi, :id_paket, :qty, :keterangan)");
                $stmt_detail->execute([
                    'id_transaksi' => $id_transaksi_baru,
                    'id_paket'     => $id_paket,
                    'qty'          => $qty,
                    'keterangan'   => 'Cucian masuk'
                ]);

                $pesan_sukses = "Transaksi berhasil disimpan dengan Invoice: <b>$kode_invoice</b>";
                // Mencatat aktivitas penambahan transaksi ke log sistem[cite: 5]
                catat_log($pdo, $nama_user, "Menambahkan transaksi baru dengan invoice $kode_invoice");

            } else {
                $pesan_error = "Paket cucian tidak ditemukan!";
            }
        } catch (PDOException $e) {
            $pesan_error = "Gagal menyimpan transaksi: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Member, paket, dan jumlah/berat wajib diisi dengan benar!";
    }
}

/**
 * --------------------------------------------------------------------------
 * 2. PROSES UTAMA: EDIT / MEMPERBARUI DATA TRANSAKSI
 * --------------------------------------------------------------------------
 * Dijalankan ketika form edit transaksi disubmit via metode POST[cite: 5]
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_transaksi'])) {
    $id_transaksi   = $_POST['id_transaksi'];
    $id_member      = $_POST['id_member'];
    $id_paket       = $_POST['id_paket'];
    $qty            = floatval($_POST['qty']);
    $biaya_tambahan = floatval($_POST['biaya_tambahan'] ?? 0);
    $diskon         = abs(floatval($_POST['diskon'] ?? 0));
    $pajak          = floatval($_POST['pajak'] ?? 0);
    $dibayar        = $_POST['dibayar'];
    
    // Logika SQL dinamis: Jika status pembayaran diubah jadi 'dibayar', set tanggal bayar ke waktu sekarang (jika belum ada)[cite: 5]
    $tgl_bayar_sql  = ($dibayar === 'dibayar') ? ", tgl_bayar = COALESCE(tgl_bayar, NOW())" : ", tgl_bayar = NULL";

    try {
        // Melakukan update data utama transaksi di tabel 'tb_transaksi'[cite: 5]
        $stmt_up = $pdo->prepare("UPDATE tb_transaksi SET id_member = :id_member, biaya_tambahan = :biaya_tambahan, diskon = :diskon, pajak = :pajak, dibayar = :dibayar $tgl_bayar_sql WHERE id = :id");
        $stmt_up->execute([
            'id_member'      => $id_member,
            'biaya_tambahan' => $biaya_tambahan,
            'diskon'         => $diskon,
            'pajak'          => $pajak,
            'dibayar'        => $dibayar,
            'id'             => $id_transaksi
        ]);

        // Melakukan update rincian paket dan kuantitas pada tabel 'tb_detail_transaksi'[cite: 5]
        $stmt_dt_up = $pdo->prepare("UPDATE tb_detail_transaksi SET id_paket = :id_paket, qty = :qty WHERE id_transaksi = :id_transaksi");
        $stmt_dt_up->execute([
            'id_paket'     => $id_paket,
            'qty'          => $qty,
            'id_transaksi' => $id_transaksi
        ]);

        $pesan_sukses = "Data transaksi berhasil diperbarui!";
        catat_log($pdo, $nama_user, "Memperbarui data transaksi ID: $id_transaksi");

    } catch (PDOException $e) {
        $pesan_error = "Gagal memperbarui transaksi: " . $e->getMessage();
    }
}

/**
 * --------------------------------------------------------------------------
 * 3. PROSES UTAMA: MENGUBAH STATUS PENGERJAAN CUCIAN
 * --------------------------------------------------------------------------
 * Dijalankan ketika parameter 'ubah_status' dan 'id' dikirim melalui URL (GET)[cite: 5]
 */
if (isset($_GET['ubah_status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status_baru = $_GET['ubah_status'];
    try {
        // Khusus jika status diubah menjadi 'diambil', sistem otomatis melunasi pembayaran dan mencatat tanggal bayar[cite: 5]
        if ($status_baru === 'diambil') {
            $stmt = $pdo->prepare("UPDATE tb_transaksi SET status = :status, dibayar = 'dibayar', tgl_bayar = NOW() WHERE id = :id");
        } else {
            $stmt = $pdo->prepare("UPDATE tb_transaksi SET status = :status WHERE id = :id");
        }
        $stmt->execute(['status' => $status_baru, 'id' => $id]);
        
        catat_log($pdo, $nama_user, "Mengubah status transaksi ID $id menjadi $status_baru");
        // Melakukan redirect halaman agar parameter GET bersih dan terhindar dari resiko resubmit form[cite: 5]
        header("Location: transaksi.php?pesan=status_sukses");
        exit();
    } catch (PDOException $e) {
        $pesan_error = "Gagal memperbarui status transaksi.";
    }
}

// Menangkap pesan sukses khusus setelah proses perubahan status berhasil[cite: 5]
if (isset($_GET['pesan']) && $_GET['pesan'] == 'status_sukses') {
    $pesan_sukses = "Status transaksi berhasil diperbarui!";
}

/**
 * --------------------------------------------------------------------------
 * PENGAMBILAN DATA DAN PENERAPAN FILTER + PAGINATION
 * --------------------------------------------------------------------------
 * Mengambil data referensi member, paket, serta data transaksi dengan kondisi filter[cite: 5]
 */
try {
    // Mengambil seluruh daftar member diurutkan berdasarkan nama secara Ascending[cite: 5]
    $list_member = $pdo->query("SELECT * FROM tb_member ORDER BY nama ASC")->fetchAll();
    
    // Mengambil daftar paket cucian beserta nama outlet asalnya menggunakan fungsi JOIN SQL[cite: 5]
    $list_paket  = $pdo->query("SELECT tb_paket.*, tb_outlet.nama AS nama_outlet FROM tb_paket JOIN tb_outlet ON tb_paket.id_outlet = tb_outlet.id ORDER BY tb_paket.nama_paket ASC")->fetchAll();
    
    // Menyiapkan kerangka dasar klausa WHERE untuk sistem pencarian/filter data[cite: 5]
    $where_sql = " WHERE 1=1";
    $params = [];

    // Kondisi Filter 1: Berdasarkan status cucian (baru, proses, selesai, diambil)[cite: 5]
    if (!empty($status_filter)) {
        $where_sql .= " AND t.status = :status";
        $params['status'] = $status_filter;
    }

    // Kondisi Filter 2: Berdasarkan periode kuartal / rentang 3 bulan dalam setahun[cite: 5]
    if (!empty($periode)) {
        $map_bulan = [
            1 => ['-01-01', '-03-31'], // Kuartal 1: Januari - Maret[cite: 5]
            2 => ['-04-01', '-06-30'], // Kuartal 2: April - Juni[cite: 5]
            3 => ['-07-01', '-09-30'], // Kuartal 3: Juli - September[cite: 5]
            4 => ['-10-01', '-12-31']  // Kuartal 4: Oktober - Desember[cite: 5]
        ];
        
        if (isset($map_bulan[$periode])) {
            $where_sql .= " AND t.tgl BETWEEN :start_date AND :end_date";
            $params['start_date'] = $tahun . $map_bulan[$periode][0] . ' 00:00:00';
            $params['end_date'] = $tahun . $map_bulan[$periode][1] . ' 23:59:59';
        }
    }

    // Menghitung total keseluruhan baris data transaksi yang sesuai dengan filter (untuk perhitungan jumlah halaman pagination)[cite: 5]
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM tb_transaksi t" . $where_sql);
    $stmt_count->execute($params);
    $total_data = $stmt_count->fetchColumn();
    $total_pages = ceil($total_data / $limit); // Membulatkan ke atas total halaman yang tersedia[cite: 5]

    // Menyusun Query SQL utama dengan teknik LEFT JOIN untuk menggabungkan tabel Transaksi, Member, User, Detail, dan Paket[cite: 5]
    $query = "SELECT t.*, 
              m.id AS id_member_asli,
              COALESCE(m.nama, 'Member Umum / Terhapus') AS nama_member, 
              COALESCE(u.nama, 'Administrator') AS nama_user, 
              dt.id_paket AS id_paket_asli,
              COALESCE(pk.nama_paket, 'Paket Manual') AS nama_paket, 
              COALESCE(pk.harga, 0) AS harga_paket,
              COALESCE(dt.qty, 1) AS qty
              FROM tb_transaksi t
              LEFT JOIN tb_member m ON t.id_member = m.id
              LEFT JOIN tb_user u ON t.id_user = u.id
              LEFT JOIN tb_detail_transaksi dt ON t.id = dt.id_transaksi
              LEFT JOIN tb_paket pk ON dt.id_paket = pk.id" 
              . $where_sql . " ORDER BY t.id DESC LIMIT :limit OFFSET :offset";
    
    // Menyiapkan dan mengikat parameter query secara aman ke PDO Statement[cite: 5]
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue(':' . $key, $val);
    }
    // Mengikat nilai LIMIT dan OFFSET sebagai tipe data Integer demi keamanan performa database[cite: 5]
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $list_transaksi = $stmt->fetchAll(); // Mengambil hasil data dalam bentuk array asosiatif[cite: 5]

} catch (PDOException $e) {
    // Penanganan error cadangan jika terjadi kegagalan query database[cite: 5]
    $list_member = [];
    $list_paket  = [];
    $list_transaksi = [];
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Transaksi - Lumiere Laundry</title>
    
    <!-- Memuat file pustaka CSS eksternal (Glide slider, AOS animation, Bootstrap, Custom Style)[cite: 5] -->
    <link rel="stylesheet" href="./assets/libraries/glide/css/glide.core.min.css">
    <link rel="stylesheet" href="./assets/libraries/aos/aos.css">
    <link rel="stylesheet" href="./assets/css/main.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <!-- Memuat FontAwesome & Bootstrap Icons untuk ikon visual antarmuka[cite: 5] -->
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
    </style>
</head>

<body>
     <?php include 'navbar.php'; // Menyisipkan komponen navigasi utama situs[cite: 5] ?>

    <!-- Bagian Konten Utama Halaman Manajemen Transaksi[cite: 5] -->
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark">Manajemen Transaksi Kasir</h2>
                <p class="text-muted mb-0">Input cucian masuk, edit rincian berat/diskon, atur progres pengerjaan.</p>
            </div>
            <!-- Tombol interaktif untuk memicu pemunculan Modal Tambah Transaksi Baru[cite: 5] -->
            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm text-white" data-bs-toggle="modal" data-bs-target="#modalTambahTransaksi">
                <i class="bi bi-plus-lg me-1"></i> Transaksi Baru
            </button>
        </div>

        <!-- Blok Kondisional: Menampilkan pesan sukses berwarna hijau jika ada feedback positif[cite: 5] -->
        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $pesan_sukses; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Blok Kondisional: Menampilkan pesan error berwarna merah jika terjadi kendala[cite: 5] -->
        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $pesan_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Kartu Formulir Filter Data Transaksi (Periode, Tahun, Status)[cite: 5] -->
        <div class="card shadow-sm border-0 rounded-4 p-4 mb-4 bg-white">
            <form method="GET" action="transaksi.php" class="row g-3 align-items-end">
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
                    <label class="form-label small fw-bold text-secondary">Status Cucian</label>
                    <select name="status" class="form-select rounded-pill shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="baru" <?= $status_filter=='baru'?'selected':''; ?>>Baru</option>
                        <option value="proses" <?= $status_filter=='proses'?'selected':''; ?>>Proses</option>
                        <option value="selesai" <?= $status_filter=='selesai'?'selected':''; ?>>Selesai</option>
                        <option value="diambil" <?= $status_filter=='diambil'?'selected':''; ?>>Diambil</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 shadow-sm text-white"><i class="bi bi-filter me-1"></i> Filter</button>
                </div>
            </form>
        </div>

        <!-- Kartu Tabel Penampung Daftar Data Transaksi[cite: 5] -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice</th>
                            <th>Member</th>
                            <th>Paket & Rincian Harga</th>
                            <th>Tgl Masuk</th>
                            <th>Status Cucian</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($list_transaksi) > 0): ?>
                            <?php 
                            $no = $offset + 1; // Penomoran baris tabel agar berlanjut secara akurat sesuai halaman pagination[cite: 5]
                            foreach ($list_transaksi as $t): 
                                // Kalkulasi matematika finansial: Menghitung subtotal, diskon bersih, dan grand total keseluruhan[cite: 5]
                                $subtotal = ($t['harga_paket'] * $t['qty']);
                                $diskon_bersih = abs($t['diskon']);
                                $grand_total = $subtotal + $t['biaya_tambahan'] - $diskon_bersih + $t['pajak'];
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><code class="fw-bold text-dark"><?= htmlspecialchars($t['kode_invoice']); ?></code></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($t['nama_member']); ?></td>
                                    <td>
                                        <span class="small d-block fw-bold"><?= htmlspecialchars($t['nama_paket']); ?></span>
                                        <span class="text-muted" style="font-size: 12px;">Qty/Berat: <?= $t['qty']; ?></span>
                                        
                                        <!-- Blok Opsional: Menampilkan rincian biaya tambahan, diskon, atau pajak jika nilainya lebih dari 0[cite: 5] -->
                                        <?php if ($t['biaya_tambahan'] > 0 || $diskon_bersih > 0 || $t['pajak'] > 0): ?>
                                            <div class="text-secondary mt-1" style="font-size: 11px;">
                                                <?php if ($t['biaya_tambahan'] > 0): ?>
                                                    <span class="text-success">+ Tambahan: Rp <?= number_format($t['biaya_tambahan'], 0, ',', '.'); ?></span><br>
                                                <?php endif; ?>
                                                <?php if ($diskon_bersih > 0): ?>
                                                    <span class="text-danger">- Diskon: Rp <?= number_format($diskon_bersih, 0, ',', '.'); ?></span><br>
                                                <?php endif; ?>
                                                <?php if ($t['pajak'] > 0): ?>
                                                    <span class="text-info">+ Pajak: Rp <?= number_format($t['pajak'], 0, ',', '.'); ?></span><br>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="fw-bold text-dark mt-1" style="font-size: 12px;">
                                            Total: Rp <?= number_format($grand_total, 0, ',', '.'); ?>
                                        </div>
                                    </td>
                                    <td class="small text-secondary"><?= htmlspecialchars($t['tgl']); ?></td>
                                    <td>
                                        <?php
                                            // Menentukan warna variasi Badge Bootstrap secara dinamis berdasarkan status cucian[cite: 5]
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
                                        <!-- Menampilkan badge indikator status pembayaran (Lunas / Belum Lunas)[cite: 5] -->
                                        <?php if ($t['dibayar'] === 'dibayar'): ?>
                                            <span class="badge bg-success text-uppercase" style="font-size: 10px;">Lunas</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-uppercase" style="font-size: 10px;">Belum Lunas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <!-- Tombol untuk memicu Modal Edit Transaksi spesifik berdasarkan ID data[cite: 5] -->
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditTransaksi<?= $t['id']; ?>" 
                                                title="Edit Berat, Diskon, Biaya Tambahan">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <!-- Menu Dropdown interaktif untuk mempercepat perubahan status pengerjaan cucian[cite: 5] -->
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                                                    Status
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="transaksi.php?id=<?= $t['id']; ?>&ubah_status=baru">Baru</a></li>
                                                    <li><a class="dropdown-item" href="transaksi.php?id=<?= $t['id']; ?>&ubah_status=proses">Proses</a></li>
                                                    <li><a class="dropdown-item" href="transaksi.php?id=<?= $t['id']; ?>&ubah_status=selesai">Selesai</a></li>
                                                    <li><a class="dropdown-item" href="transaksi.php?id=<?= $t['id']; ?>&ubah_status=diambil">Diambil (Lunas)</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Transaksi Unik untuk Setiap Baris Data[cite: 5] -->
                                <div class="modal fade" id="modalEditTransaksi<?= $t['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0 rounded-4 shadow">
                                            <form action="" method="POST">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark">Edit Transaksi: <?= htmlspecialchars($t['kode_invoice']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <!-- Menyimpan ID transaksi tersembunyi sebagai referensi update[cite: 5] -->
                                                    <input type="hidden" name="id_transaksi" value="<?= $t['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Pelanggan (Member)</label>
                                                        <select name="id_member" class="form-select" required>
                                                            <?php foreach ($list_member as $m): ?>
                                                                <option value="<?= $m['id']; ?>" <?= ($m['id'] == $t['id_member_asli']) ? 'selected' : ''; ?>>
                                                                    <?= htmlspecialchars($m['nama']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Paket Cucian</label>
                                                        <select name="id_paket" class="form-select" required>
                                                            <?php foreach ($list_paket as $p): ?>
                                                                <option value="<?= $p['id']; ?>" <?= ($p['id'] == $t['id_paket_asli']) ? 'selected' : ''; ?>>
                                                                    <?= htmlspecialchars($p['nama_paket']); ?> (Rp <?= number_format($p['harga'], 0, ',', '.'); ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Jumlah / Berat (Kg atau Pcs)</label>
                                                        <input type="number" step="0.01" min="0.01" class="form-control" name="qty" value="<?= $t['qty']; ?>" required>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold small">Biaya Tambahan (Rp)</label>
                                                            <input type="number" min="0" class="form-control" name="biaya_tambahan" value="<?= $t['biaya_tambahan']; ?>">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold small">Diskon (Rp)</label>
                                                            <input type="number" min="0" class="form-control" name="diskon" value="<?= $t['diskon']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Pajak (Rp)</label>
                                                        <input type="number" min="0" class="form-control" name="pajak" value="<?= $t['pajak']; ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Status Pembayaran</label>
                                                        <select name="dibayar" class="form-select" required>
                                                            <option value="belum_dibayar" <?= ($t['dibayar'] === 'belum_dibayar') ? 'selected' : ''; ?>>Belum Lunas</option>
                                                            <option value="dibayar" <?= ($t['dibayar'] === 'dibayar') ? 'selected' : ''; ?>>Lunas</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0">
                                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="edit_transaksi" class="btn btn-primary text-white rounded-pill px-4">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Tampilan baris alternatif apabila data transaksi kosong / tidak ditemukan[cite: 5] -->
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-cart-x fs-2 d-block mb-2"></i>
                                    Tidak ada data transaksi untuk periode tersebut.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sistem Navigasi Halaman / Pagination[cite: 5] -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center mb-0">
                        <?php 
                        // Mempertahankan parameter filter di URL agar tetap aktif ketika berpindah halaman pagination[cite: 5]
                        $query_params = $_GET;
                        unset($query_params['page']);
                        $query_string = http_build_query($query_params);
                        $query_string = $query_string ? '&' . $query_string : '';
                        ?>
                        
                        <!-- Tombol Navigasi 'Sebelumnya'[cite: 5] -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page - 1; ?><?= $query_string; ?>">Sebelumnya</a>
                        </li>

                        <!-- Perulangan Angka Nomor Halaman[cite: 5] -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?><?= $query_string; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Tombol Navigasi 'Selanjutnya'[cite: 5] -->
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page + 1; ?><?= $query_string; ?>">Selanjutnya</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>

    <!-- Modal Utama: Form Tambah Transaksi Baru[cite: 5] -->
    <div class="modal fade" id="modalTambahTransaksi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <form action="" method="POST">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark">Input Transaksi Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Pilih Pelanggan (Member)</label>
                            <select name="id_member" class="form-select" required>
                                <option value="">-- Pilih Member --</option>
                                <?php foreach ($list_member as $m): ?>
                                    <option value="<?= $m['id']; ?>"><?= htmlspecialchars($m['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Pilih Paket Cucian</label>
                            <select name="id_paket" class="form-select" required>
                                <option value="">-- Pilih Paket & Layanan --</option>
                                <?php foreach ($list_paket as $p): ?>
                                    <option value="<?= $p['id']; ?>"><?= htmlspecialchars($p['nama_paket']); ?> (Rp <?= number_format($p['harga'], 0, ',', '.'); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Jumlah / Berat (Kg atau Pcs)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="qty" required placeholder="Contoh: 3.5">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Biaya Tambahan (Rp)</label>
                                <input type="number" min="0" class="form-control" name="biaya_tambahan" value="0" placeholder="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Diskon (Rp)</label>
                                <input type="number" min="0" class="form-control" name="diskon" value="0" placeholder="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Pajak (Rp)</label>
                            <input type="number" min="0" class="form-control" name="pajak" value="0" placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Status Pembayaran Awal</label>
                            <select name="dibayar" class="form-select" required>
                                <option value="belum_dibayar">Belum Lunas (Bayar nanti saat diambil)</option>
                                <option value="dibayar">Lunas (Bayar sekarang)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_transaksi" class="btn btn-primary text-white rounded-pill px-4">Proses Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bagian Footer Website[cite: 5] -->
    <footer class="py-4 bg-body border-top text-center text-body-secondary text-sm">
        <div class="container">
            <p class="mb-0">© <?= date('Y'); ?> Lumiere Laundry.</p>
        </div>
    </footer>

    <!-- Memuat Pustaka Skrip JavaScript (Bootstrap Bundle, Glide, AOS, dan Script Utama)[cite: 5] -->
    <script src="./assets/libraries/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/libraries/glide/glide.min.js"></script>
    <script src="./assets/libraries/aos/aos.js"></script>
    <script src="./assets/js/scripts.js"></script>
</body>
</html>