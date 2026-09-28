<?php
// pages/laporan_proses.php
require_once __DIR__ . '/../components/koneksi.php';
restrict_access(['admin', 'kasir', 'owner']);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Silakan login terlebih dahulu!");
    exit();
}

$nama_outlet_aktif = get_nama_outlet_aktif($pdo);
$status = $_GET['status'] ?? '';
$outlet = $_GET['id_outlet'] ?? '';
$periode = $_GET['periode'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');
$tab = $_GET['tab'] ?? 'transaksi';

try {
    $sql = "SELECT t.*, COALESCE(m.nama, 'Member Umum') AS nama_member, COALESCE(o.nama, 'Utama') AS nama_outlet_transaksi,
            COALESCE(pk.nama_paket, 'Paket Manual') AS nama_paket, COALESCE(pk.harga, 0) AS harga_paket, COALESCE(dt.qty, 1) AS qty
            FROM tb_transaksi t
            LEFT JOIN tb_member m ON t.id_member = m.id
            LEFT JOIN tb_outlet o ON t.id_outlet = o.id
            LEFT JOIN tb_detail_transaksi dt ON t.id = dt.id_transaksi
            LEFT JOIN tb_paket pk ON dt.id_paket = pk.id WHERE 1=1";
    $params = [];

    if (!empty($outlet)) {
        $sql .= " AND t.id_outlet = :outlet"; 
        $params['outlet'] = $outlet; 
    }
    if (!empty($status)) {
        $sql .= " AND t.status = :status"; 
        $params['status'] = $status; 
    }
    
    if (!empty($periode)) {
        $map_bulan = [
            1 => ['-01-01', '-03-31'],
            2 => ['-04-01', '-06-30'],
            3 => ['-07-01', '-09-30'],
            4 => ['-10-01', '-12-31']
        ];
        
        if (isset($map_bulan[$periode])) {
            $sql .= " AND t.tgl BETWEEN :start_date AND :end_date";
            $params['start_date'] = $tahun . $map_bulan[$periode][0] . ' 00:00:00';
            $params['end_date'] = $tahun . $map_bulan[$periode][1] . ' 23:59:59';
        }
    }
    
    $stmt = $pdo->prepare($sql . " ORDER BY t.id DESC");
    $stmt->execute($params);
    $laporan_list = $stmt->fetchAll();

    $total_omset = 0;
    foreach ($laporan_list as $r) {
        if ($r['dibayar'] === 'dibayar') {
            $total_omset += (($r['harga_paket'] * $r['qty']) + $r['biaya_tambahan'] - $r['diskon'] + $r['pajak']);
        }
    }

    $list_log = $pdo->query("SELECT * FROM activity_log ORDER BY id DESC LIMIT 50")->fetchAll();
    $semua_outlet = $pdo->query("SELECT * FROM tb_outlet ORDER BY nama ASC")->fetchAll();
} catch (PDOException $e) {
    $laporan_list = $list_log = $semua_outlet = [];
    $total_omset = 0;
}