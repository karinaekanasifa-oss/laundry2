<?php
// Backend/pages/transaksi_proses.php

// Memanggil file koneksi database agar halaman ini bisa terhubung ke database server[cite: 9]
require_once __DIR__ . '/../components/koneksi.php';

// Cek apakah pengguna sudah login atau belum[cite: 9]
// Jika belum ada sesi user_id, tendang keluar ke halaman login disertai pesan[cite: 9]
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Silakan login terlebih dahulu!");
    exit();
}

// Pencegahan keamanan: Jika yang login adalah role 'kasir', dilarang masuk ke backend ini dan langsung dipaksa pindah ke halaman frontend[cite: 9]
if (isset($_SESSION['role']) && $_SESSION['role'] === 'kasir') {
    header("Location: ../frontend/home.php");
    exit();
}

// Membatasi akses halaman hanya boleh diakses oleh role 'admin' atau 'owner'[cite: 9]
restrict_access(['admin', 'owner']);

// Mengambil data nama, role, dan ID outlet dari sesi pengguna yang sedang aktif (jika kosong pakai nilai default)[cite: 9]
$nama_user = $_SESSION['nama'] ?? 'Administrator';
$role_user = $_SESSION['role'] ?? 'admin';
$id_outlet_user = $_SESSION['id_outlet'] ?? 1;

// Mengambil informasi nama outlet aktif yang sedang dikelola dari database[cite: 9]
try {
    $stmt_outlet = $pdo->prepare("SELECT nama FROM tb_outlet WHERE id = :id");
    $stmt_outlet->execute(['id' => $id_outlet_user]);
    $outlet_active = $stmt_outlet->fetch();
    $nama_outlet = $outlet_active['nama'] ?? 'Outlet Utama';
} catch (PDOException $e) {
    // Jika terjadi error saat mengambil data outlet, gunakan nama default 'Outlet Utama'[cite: 9]
    $nama_outlet = 'Outlet Utama';
}

// Menyiapkan variabel penampung pesan sukses dan pesan error[cite: 9]
$pesan_sukses = '';
$pesan_error = '';

// Menangkap pesan sukses dari parameter URL (biasanya setelah redirect dari file aksi)[cite: 9]
if (isset($_GET['success'])) {
    $pesan_sukses = htmlspecialchars($_GET['success']);
}
// Menangkap pesan error dari parameter URL[cite: 9]
if (isset($_GET['error'])) {
    $pesan_error = htmlspecialchars($_GET['error']);
}

// Mengambil parameter filter data dari URL (seperti periode, tahun, atau status)[cite: 9]
$periode = $_GET['periode'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');
$status_filter = $_GET['status'] ?? '';

// Fungsi Bantuan (Helper) untuk mencatat aktivitas (activity log) ke dalam database[cite: 9]
function catat_log($pdo, $username, $aktivitas) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_log (username, activity, created_at) VALUES (:username, :activity, NOW())");
        $stmt->execute([
            'username' => $username,
            'activity' => $aktivitas
        ]);
    } catch (PDOException $e) {
        // Jika tabel log mengalami masalah/kendala, abaikan agar tidak mengganggu proses utama[cite: 9]
    }
}

// --------------------------------------------------------------------------
// 1. PROSES EDIT / UPDATE DATA TRANSAKSI
// --------------------------------------------------------------------------
// Dijalankan jika ada pengiriman form dengan tombol bernama 'edit_transaksi'[cite: 9]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_transaksi'])) {
    $id_transaksi   = $_POST['id_transaksi'];
    $id_member      = $_POST['id_member'];
    $id_paket       = $_POST['id_paket'];
    $qty            = floatval($_POST['qty']);
    $biaya_tambahan = floatval($_POST['biaya_tambahan'] ?? 0);
    $diskon         = abs(floatval($_POST['diskon'] ?? 0));
    $pajak          = floatval($_POST['pajak'] ?? 0);
    $dibayar        = $_POST['dibayar'];
    
    // Menentukan status tanggal bayar secara otomatis: jika dibayar, catat waktunya; jika belum, kosongkan (NULL)[cite: 9]
    $tgl_bayar_sql  = ($dibayar === 'dibayar') ? ", tgl_bayar = COALESCE(tgl_bayar, NOW())" : ", tgl_bayar = NULL";

    try {
        // Memperbarui data utama transaksi di tabel tb_transaksi[cite: 9]
        $stmt_up = $pdo->prepare("UPDATE tb_transaksi SET id_member = :id_member, biaya_tambahan = :biaya_tambahan, diskon = :diskon, pajak = :pajak, dibayar = :dibayar $tgl_bayar_sql WHERE id = :id");
        $stmt_up->execute([
            'id_member'      => $id_member,
            'biaya_tambahan' => $biaya_tambahan,
            'diskon'         => $diskon,
            'pajak'          => $pajak,
            'dibayar'        => $dibayar,
            'id'             => $id_transaksi
        ]);

        // Memperbarui data detail transaksi (paket dan jumlah/qty) di tabel tb_detail_transaksi[cite: 9]
        $stmt_dt_up = $pdo->prepare("UPDATE tb_detail_transaksi SET id_paket = :id_paket, qty = :qty WHERE id_transaksi = :id_transaksi");
        $stmt_dt_up->execute([
            'id_paket'     => $id_paket,
            'qty'          => $qty,
            'id_transaksi' => $id_transaksi
        ]);

        // Jika berhasil, buat pesan sukses dan catat ke activity log[cite: 9]
        $pesan_sukses = "Data transaksi berhasil diperbarui!";
        catat_log($pdo, $nama_user, "Memperbarui data transaksi ID: $id_transaksi");

    } catch (PDOException $e) {
        // Jika gagal, tangkap pesan errornya[cite: 9]
        $pesan_error = "Gagal memperbarui transaksi: " . $e->getMessage();
    }
}

// --------------------------------------------------------------------------
// 2. PROSES UPDATE STATUS TRANSAKSI (CEPAT DARI DROPDOWN)
// --------------------------------------------------------------------------
// Dijalankan jika ada parameter 'ubah_status' dan 'id' di URL[cite: 9]
if (isset($_GET['ubah_status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status_baru = $_GET['ubah_status'];
    try {
        // Jika status diubah menjadi 'diambil', otomatis status pembayaran diset 'dibayar' dan dicatat tanggal bayarnya[cite: 9]
        if ($status_baru === 'diambil') {
            $stmt = $pdo->prepare("UPDATE tb_transaksi SET status = :status, dibayar = 'dibayar', tgl_bayar = NOW() WHERE id = :id");
        } else {
            // Jika status lain, cukup update statusnya saja[cite: 9]
            $stmt = $pdo->prepare("UPDATE tb_transaksi SET status = :status WHERE id = :id");
        }
        $stmt->execute(['status' => $status_baru, 'id' => $id]);
        
        // Catat aktivitas perubahan status ke log, lalu arahkan kembali halaman dengan pesan sukses[cite: 9]
        catat_log($pdo, $nama_user, "Mengubah status transaksi ID $id menjadi $status_baru");
        header("Location: transaksi.php?success=Status transaksi berhasil diperbarui");
        exit();
    } catch (PDOException $e) {
        $pesan_error = "Gagal memperbarui status transaksi.";
    }
}

// --------------------------------------------------------------------------
// 3. MENGAMBIL DATA MEMBER, PAKET, DAN DAFTAR TRANSAKSI DENGAN FILTER
// --------------------------------------------------------------------------
try {
    // Mengambil daftar seluruh member diurutkan berdasarkan nama[cite: 9]
    $list_member = $pdo->query("SELECT * FROM tb_member ORDER BY nama ASC")->fetchAll();
    
    // Mengambil daftar paket laundry beserta nama outletnya[cite: 9]
    $list_paket  = $pdo->query("SELECT tb_paket.*, tb_outlet.nama AS nama_outlet FROM tb_paket JOIN tb_outlet ON tb_paket.id_outlet = tb_outlet.id ORDER BY tb_paket.nama_paket ASC")->fetchAll();
    
    // Menyusun query SQL utama untuk menggabungkan data transaksi, member, user, detail transaksi, dan paket[cite: 9]
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
              LEFT JOIN tb_paket pk ON dt.id_paket = pk.id WHERE 1=1";
    
    $params = [];

    // Jika filter status cucian dipilih, tambahkan kondisi ke query[cite: 9]
    if (!empty($status_filter)) {
        $query .= " AND t.status = :status";
        $params['status'] = $status_filter;
    }

    // Jika filter periode 3 bulanan (kuartal) dipilih, tentukan rentang tanggal awal dan akhirnya[cite: 9]
    if (!empty($periode)) {
        $map_bulan = [
            1 => ['-01-01', '-03-31'], // Januari - Maret
            2 => ['-04-01', '-06-30'], // April - Juni
            3 => ['-07-01', '-09-30'], // Juli - September
            4 => ['-10-01', '-12-31']  // Oktober - Desember
        ];
        
        if (isset($map_bulan[$periode])) {
            $query .= " AND t.tgl BETWEEN :start_date AND :end_date";
            $params['start_date'] = $tahun . $map_bulan[$periode][0] . ' 00:00:00';
            $params['end_date'] = $tahun . $map_bulan[$periode][1] . ' 23:59:59';
        }
    }

    // Urutkan data transaksi dari yang paling baru (ID terbesar)[cite: 9]
    $query .= " ORDER BY t.id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $list_transaksi = $stmt->fetchAll();

} catch (PDOException $e) {
    // Jika terjadi kendala pada database saat mengambil data, kosongkan variabel array[cite: 9]
    $list_member = [];
    $list_paket  = [];
    $list_transaksi = [];
}