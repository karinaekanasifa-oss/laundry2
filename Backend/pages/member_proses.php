<?php
// Backend/pages/member_proses.php
require_once __DIR__ . '/../components/koneksi.php';
restrict_access(['admin', 'kasir']);

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Silakan login terlebih dahulu!");
    exit();
}

$nama_user = $_SESSION['nama'] ?? 'Administrator';
$role_user = $_SESSION['role'] ?? 'admin';
$id_outlet = $_SESSION['id_outlet'] ?? 1;

// Ambil informasi nama outlet
try {
    $stmt_outlet = $pdo->prepare("SELECT nama FROM tb_outlet WHERE id = :id");
    $stmt_outlet->execute(['id' => $id_outlet]);
    $outlet = $stmt_outlet->fetch();
    $nama_outlet = $outlet['nama'] ?? 'Outlet Utama';
} catch (PDOException $e) {
    $nama_outlet = 'Outlet Utama';
}

$pesan_sukses = '';
$pesan_error = '';

// 1. Proses Tambah Member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_member'])) {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tlp = trim($_POST['tlp']);

    if (!empty($nama) && !empty($tlp)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tb_member (nama, alamat, jenis_kelamin, tlp) VALUES (:nama, :alamat, :jenis_kelamin, :tlp)");
            $stmt->execute([
                'nama' => $nama,
                'alamat' => $alamat,
                'jenis_kelamin' => $jenis_kelamin,
                'tlp' => $tlp
            ]);
            $pesan_sukses = "Member baru berhasil ditambahkan!";
        } catch (PDOException $e) {
            $pesan_error = "Gagal menyimpan data: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Nama dan Nomor Telepon wajib diisi!";
    }
}

// 2. Proses Edit / Update Member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_member'])) {
    $id = $_POST['id'];
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tlp = trim($_POST['tlp']);

    if (!empty($nama) && !empty($tlp)) {
        try {
            $stmt = $pdo->prepare("UPDATE tb_member SET nama = :nama, alamat = :alamat, jenis_kelamin = :jenis_kelamin, tlp = :tlp WHERE id = :id");
            $stmt->execute([
                'id' => $id,
                'nama' => $nama,
                'alamat' => $alamat,
                'jenis_kelamin' => $jenis_kelamin,
                'tlp' => $tlp
            ]);
            $pesan_sukses = "Data member berhasil diperbarui!";
        } catch (PDOException $e) {
            $pesan_error = "Gagal memperbarui data: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Nama dan Nomor Telepon wajib diisi!";
    }
}

// 3. Proses Hapus Member
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_member WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: member.php?pesan=hapus_sukses");
        exit();
    } catch (PDOException $e) {
        $pesan_error = "Gagal menghapus data (kemungkinan data sedang digunakan di transaksi).";
    }
}

if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses') {
    $pesan_sukses = "Data member berhasil dihapus!";
}

// Ambil data member dari database
try {
    $stmt_member = $pdo->query("SELECT * FROM tb_member ORDER BY id DESC");
    $list_member = $stmt_member->fetchAll();
} catch (PDOException $e) {
    $list_member = [];
}