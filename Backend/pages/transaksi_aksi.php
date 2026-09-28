<?php
// Backend/pages/transaksi_aksi.php

// Memanggil file koneksi database agar aplikasi bisa terhubung ke database server[cite: 8]
require_once __DIR__ . '/../components/koneksi.php';

// Mengecek apakah user sudah login atau belum. 
// Jika belum ada sesi user_id, maka user akan diusir paksa kembali ke halaman login.php[cite: 8]
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Mengecek apakah data dikirimkan melalui metode HTTP POST (artinya form sedang disubmit)[cite: 8]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Memverifikasi token CSRF untuk memastikan data dikirim dari halaman resmi, bukan bajakan/serangan luar[cite: 8]
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    // Mengambil data dari form dan membersihkannya (sanitasi) agar aman dari serangan berbahaya[cite: 8]
    $id_outlet   = filter_var($_POST['id_outlet'], FILTER_SANITIZE_NUMBER_INT);
    $kode_invoice= htmlspecialchars(trim($_POST['kode_invoice']), ENT_QUOTES, 'UTF-8');
    $id_member   = filter_var($_POST['id_member'], FILTER_SANITIZE_NUMBER_INT);
    $id_user     = $_SESSION['user_id']; // Mengambil ID user yang sedang login saat ini
    
    // Mengambil data detail transaksi (paket yang dipilih, jumlah/berat, dan catatan)[cite: 8]
    $id_paket    = filter_var($_POST['id_paket'], FILTER_SANITIZE_NUMBER_INT);
    $qty         = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $keterangan  = htmlspecialchars(trim($_POST['keterangan']), ENT_QUOTES, 'UTF-8');

    // Menggunakan try-catch untuk menangani proses database. 
    // Jika ada error di tengah jalan, proses bisa dibatalkan (rollback) agar data tidak korup/setengah-setengah.[cite: 8]
    try {
        // Memulai transaksi database (Database Transaction)[cite: 8]
        $pdo->beginTransaction();

        // 1. Menyimpan data utama ke tabel tb_transaksi menggunakan teknik Prepared Statement agar aman dari SQL Injection[cite: 8]
        // Tanggal masuk otomatis (NOW()), batas waktu diset 3 hari ke depan, status awal 'baru', dan pembayaran 'belum_dibayar'[cite: 8]
        $sqlTransaksi = "INSERT INTO tb_transaksi (id_outlet, kode_invoice, id_member, tgl, batas_waktu, tgl_bayar, biaya_tambahan, diskon, pajak, status, dibayar, id_user) 
                         VALUES (:id_outlet, :kode_invoice, :id_member, NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY), NULL, 0, 0, 0, 'baru', 'belum_dibayar', :id_user)";
        
        $stmt = $pdo->prepare($sqlTransaksi);
        $stmt->execute([
            'id_outlet'    => $id_outlet,
            'kode_invoice' => $kode_invoice,
            'id_member'    => $id_member,
            'id_user'      => $id_user
        ]);

        // Mengambil ID unik dari transaksi yang baru saja berhasil dimasukkan ke database[cite: 8]
        $id_transaksi = $pdo->lastInsertId();

        // 2. Memanggil Stored Procedure bernama 'sp_detail_transaksi' yang ada di database[cite: 8]
        // Berguna untuk menyimpan detail paket dan kuantitas cucian yang dihubungkan ke ID transaksi tersebut[cite: 8]
        $stmtProc = $pdo->prepare("CALL sp_detail_transaksi(:id_transaksi, :id_paket, :qty, :keterangan)");
        $stmtProc->execute([
            'id_transaksi' => $id_transaksi,
            'id_paket'     => $id_paket,
            'qty'          => $qty,
            'keterangan'   => $keterangan
        ]);

        // Jika semua proses di atas sukses tanpa error, simpan permanen (commit) ke database[cite: 8]
        // Lalu arahkan kembali halaman ke transaksi.php dengan membawa pesan sukses[cite: 8]
        $pdo->commit();
        header("Location: ../transaksi.php?success=Transaksi berhasil disimpan");
        exit();

    } catch (Exception $e) {
        // Jika di tengah proses ada error/gagal, batalkan semua perubahan yang sudah sempat masuk (rollBack)[cite: 8]
        $pdo->rollBack();
        // Mencatat pesan error ke dalam log sistem server untuk diperiksa jika ada kendala[cite: 8]
        error_log($e->getMessage());
        // Arahkan kembali ke halaman transaksi dengan membawa pesan error[cite: 8]
        header("Location: ../transaksi.php?error=Gagal memproses transaksi");
        exit();
    }
}
?>