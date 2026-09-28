<?php
// Backend/pages/paket_proses.php
require_once __DIR__ . '/../components/koneksi.php';
restrict_access(['admin']);

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Silakan login terlebih dahulu!");
    exit();
}

$nama_user = $_SESSION['nama'] ?? 'Administrator';
$role_user = $_SESSION['role'] ?? 'admin';
$id_outlet_user = $_SESSION['id_outlet'] ?? 1;

// Ambil informasi nama outlet aktif user
try {
    $stmt_outlet = $pdo->prepare("SELECT nama FROM tb_outlet WHERE id = :id");
    $stmt_outlet->execute(['id' => $id_outlet_user]);
    $outlet_active = $stmt_outlet->fetch();
    $nama_outlet = $outlet_active['nama'] ?? 'Outlet Utama';
} catch (PDOException $e) {
    $nama_outlet = 'Outlet Utama';
}

// Ambil daftar seluruh outlet untuk pilihan dropdown
try {
    $stmt_list_outlet = $pdo->query("SELECT * FROM tb_outlet ORDER BY nama ASC");
    $semua_outlet = $stmt_list_outlet->fetchAll();
} catch (PDOException $e) {
    $semua_outlet = [];
}

$pesan_sukses = '';
$pesan_error = '';

// 1. Proses Tambah Paket Cucian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_paket'])) {
    $id_outlet  = $_POST['id_outlet'];
    $jenis      = trim($_POST['jenis']);
    $nama_paket = trim($_POST['nama_paket']);
    $harga      = trim($_POST['harga']);

    if (!empty($id_outlet) && !empty($jenis) && !empty($nama_paket) && !empty($harga)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tb_paket (id_outlet, jenis, nama_paket, harga) VALUES (:id_outlet, :jenis, :nama_paket, :harga)");
            $stmt->execute([
                'id_outlet'  => $id_outlet,
                'jenis'      => $jenis,
                'nama_paket' => $nama_paket,
                'harga'      => $harga
            ]);
            $pesan_sukses = "Paket cucian baru berhasil ditambahkan!";
        } catch (PDOException $e) {
            $pesan_error = "Gagal menyimpan data: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Semua field wajib diisi!";
    }
}

// 2. Proses Edit / Update Paket Cucian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_paket'])) {
    $id         = $_POST['id'];
    $id_outlet  = $_POST['id_outlet'];
    $jenis      = trim($_POST['jenis']);
    $nama_paket = trim($_POST['nama_paket']);
    $harga      = trim($_POST['harga']);

    if (!empty($id) && !empty($id_outlet) && !empty($jenis) && !empty($nama_paket) && !empty($harga)) {
        try {
            $stmt = $pdo->prepare("UPDATE tb_paket SET id_outlet = :id_outlet, jenis = :jenis, nama_paket = :nama_paket, harga = :harga WHERE id = :id");
            $stmt->execute([
                'id'         => $id,
                'id_outlet'  => $id_outlet,
                'jenis'      => $jenis,
                'nama_paket' => $nama_paket,
                'harga'      => $harga
            ]);
            $pesan_sukses = "Data paket cucian berhasil diperbarui!";
        } catch (PDOException $e) {
            $pesan_error = "Gagal memperbarui data: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Semua field wajib diisi!";
    }
}

// 3. Proses Hapus Paket Cucian
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_paket WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: paket.php?pesan=hapus_sukses");
        exit();
    } catch (PDOException $e) {
        $pesan_error = "Gagal menghapus paket cucian.";
    }
}

if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses') {
    $pesan_sukses = "Data paket cucian berhasil dihapus!";
}

// Ambil semua data paket cucian beserta nama outletnya menggunakan JOIN
try {
    $query = "SELECT tb_paket.*, tb_outlet.nama AS nama_outlet 
              FROM tb_paket 
              LEFT JOIN tb_outlet ON tb_paket.id_outlet = tb_outlet.id 
              ORDER BY tb_paket.id DESC";
    $stmt_list = $pdo->query($query);
    $list_paket = $stmt_list->fetchAll();
} catch (PDOException $e) {
    $list_paket = [];
}