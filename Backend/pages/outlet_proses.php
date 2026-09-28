<?php
// Backend/pages/outlet_proses.php
require_once __DIR__ . '/../components/koneksi.php';
restrict_access(['admin']);

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Silakan login terlebih dahulu!");
    exit();
}

// Batasi akses: Hanya ADMIN yang boleh masuk ke CRUD Outlet
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php?error=Akses ditolak! Halaman khusus Administrator.");
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

$pesan_sukses = '';
$pesan_error = '';

// 1. Proses Tambah Outlet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_outlet'])) {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $tlp = trim($_POST['tlp']);

    if (!empty($nama) && !empty($alamat)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tb_outlet (nama, alamat, tlp) VALUES (:nama, :alamat, :tlp)");
            $stmt->execute([
                'nama' => $nama,
                'alamat' => $alamat,
                'tlp' => $tlp
            ]);
            $pesan_sukses = "Outlet baru berhasil ditambahkan!";
        } catch (PDOException $e) {
            $pesan_error = "Gagal menyimpan data: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Nama dan alamat outlet wajib diisi!";
    }
}

// 2. Proses Edit / Update Outlet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_outlet'])) {
    $id = $_POST['id'];
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $tlp = trim($_POST['tlp']);

    if (!empty($nama) && !empty($alamat)) {
        try {
            $stmt = $pdo->prepare("UPDATE tb_outlet SET nama = :nama, alamat = :alamat, tlp = :tlp WHERE id = :id");
            $stmt->execute([
                'id' => $id,
                'nama' => $nama,
                'alamat' => $alamat,
                'tlp' => $tlp
            ]);
            $pesan_sukses = "Data outlet berhasil diperbarui!";
        } catch (PDOException $e) {
            $pesan_error = "Gagal memperbarui data: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Nama dan alamat outlet wajib diisi!";
    }
}

// 3. Proses Hapus Outlet
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_outlet WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: outlet.php?pesan=hapus_sukses");
        exit();
    } catch (PDOException $e) {
        $pesan_error = "Gagal menghapus outlet (kemungkinan data sedang digunakan relasinya).";
    }
}

if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses') {
    $pesan_sukses = "Data outlet berhasil dihapus!";
}

// Ambil semua data outlet dari database
try {
    $stmt_list = $pdo->query("SELECT * FROM tb_outlet ORDER BY id DESC");
    $list_outlet = $stmt_list->fetchAll();
} catch (PDOException $e) {
    $list_outlet = [];
}