<?php
// Backend/pages/user_proses.php
require_once __DIR__ . '/../components/koneksi.php';
restrict_access(['admin']);

// Cek apakah user sudah login dan memiliki hak akses (biasanya hanya admin)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Silakan login terlebih dahulu!");
    exit();
}

$nama_user = $_SESSION['nama'] ?? 'Administrator';
$role_user = $_SESSION['role'] ?? 'admin';
$id_outlet_user = $_SESSION['id_outlet'] ?? 1;

// Batasi akses jika bukan admin (opsional, sesuaikan kebutuhan)
if ($role_user !== 'admin') {
    header("Location: dashboard.php?error=Akses ditolak! Halaman khusus Admin.");
    exit();
}

// Ambil informasi nama outlet aktif berdasarkan user yang sedang login di database
try {
    $stmt_outlet = $pdo->prepare("SELECT tb_outlet.nama FROM tb_user JOIN tb_outlet ON tb_user.id_outlet = tb_outlet.id WHERE tb_user.id = :id_user");
    $stmt_outlet->execute(['id_user' => $_SESSION['user_id']]);
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

// 1. Proses Tambah User / Karyawan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
    $nama      = trim($_POST['nama']);
    $username  = trim($_POST['username']);
    $password  = $_POST['password'];
    $id_outlet = $_POST['id_outlet'];
    $role      = $_POST['role'];

    if (!empty($nama) && !empty($username) && !empty($password) && !empty($id_outlet) && !empty($role)) {
        try {
            // Cek apakah username sudah digunakan
            $stmt_check = $pdo->prepare("SELECT id FROM tb_user WHERE username = :username");
            $stmt_check->execute(['username' => $username]);
            if ($stmt_check->rowCount() > 0) {
                $pesan_error = "Username '$username' sudah terdaftar, gunakan username lain!";
            } else {
                // Hash password menggunakan password_hash agar aman
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO tb_user (nama, username, password, id_outlet, role) VALUES (:nama, :username, :password, :id_outlet, :role)");
                $stmt->execute([
                    'nama'      => $nama,
                    'username'  => $username,
                    'password'  => $hashed_password,
                    'id_outlet' => $id_outlet,
                    'role'      => $role
                ]);
                $pesan_sukses = "Pengguna baru berhasil ditambahkan!";
            }
        } catch (PDOException $e) {
            $pesan_error = "Gagal menyimpan data: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Semua field wajib diisi!";
    }
}

// 2. Proses Edit / Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id        = $_POST['id'];
    $nama      = trim($_POST['nama']);
    $username  = trim($_POST['username']);
    $password  = $_POST['password'];
    $id_outlet = $_POST['id_outlet'];
    $role      = $_POST['role'];

    if (!empty($id) && !empty($nama) && !empty($username) && !empty($id_outlet) && !empty($role)) {
        try {
            if (!empty($password)) {
                // Jika password diisi, update beserta password baru
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE tb_user SET nama = :nama, username = :username, password = :password, id_outlet = :id_outlet, role = :role WHERE id = :id");
                $stmt->execute([
                    'id'        => $id,
                    'nama'      => $nama,
                    'username'  => $username,
                    'password'  => $hashed_password,
                    'id_outlet' => $id_outlet,
                    'role'      => $role
                ]);
            } else {
                // Jika password kosong, jangan ubah password lama
                $stmt = $pdo->prepare("UPDATE tb_user SET nama = :nama, username = :username, id_outlet = :id_outlet, role = :role WHERE id = :id");
                $stmt->execute([
                    'id'        => $id,
                    'nama'      => $nama,
                    'username'  => $username,
                    'id_outlet' => $id_outlet,
                    'role'      => $role
                ]);
            }
            $pesan_sukses = "Data pengguna berhasil diperbarui!";
        } catch (PDOException $e) {
            $pesan_error = "Gagal memperbarui data: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Field utama wajib diisi!";
    }
}

// 3. Proses Hapus User
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Mencegah user menghapus akun sendiri yang sedang aktif login
    if ($id == $_SESSION['user_id']) {
        $pesan_error = "Anda tidak dapat menghapus akun yang sedang digunakan saat ini!";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM tb_user WHERE id = :id");
            $stmt->execute(['id' => $id]);
            header("Location: user.php?pesan=hapus_sukses");
            exit();
        } catch (PDOException $e) {
            $pesan_error = "Gagal menghapus pengguna.";
        }
    }
}

if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses') {
    $pesan_sukses = "Data pengguna berhasil dihapus!";
}

// Ambil semua data user beserta nama outletnya menggunakan JOIN
try {
    $query = "SELECT tb_user.*, tb_outlet.nama AS nama_outlet 
              FROM tb_user 
              LEFT JOIN tb_outlet ON tb_user.id_outlet = tb_outlet.id 
              ORDER BY tb_user.id DESC";
    $stmt_list = $pdo->query($query);
    $list_user = $stmt_list->fetchAll();
} catch (PDOException $e) {
    $list_user = [];
}