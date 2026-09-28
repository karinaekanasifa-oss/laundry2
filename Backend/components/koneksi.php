<?php
// Backend/components/koneksi.php
// Mengatur informasi dasar untuk menyambungkan aplikasi ke database MySQL[cite: 5]
$host = 'localhost'; // Alamat server database (komputer lokal)[cite: 5]
$db   = 'laundry_app'; // Nama database yang digunakan[cite: 5]
$user = 'root'; // Username bawaan database XAMPP[cite: 5]
$pass = ''; // Password database (biasanya kosong untuk XAMPP)[cite: 5]
$charset = 'utf8mb4'; // Format karakter teks agar support berbagai jenis Huruf/Emoji[cite: 5]

// Menggabungkan pengaturan di atas menjadi satu kalimat koneksi (DSN)[cite: 5]
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
// Pengaturan tambahan (options) untuk keamanan dan cara kerja PDO[cite: 5]
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mengaktifkan mode deteksi error berupa exception[cite: 5]
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Mengatur hasil data database agar tampil dalam bentuk array asosiatif (nama kolom)[cite: 5]
    PDO::ATTR_EMULATE_PREPARES   => false, // Mematikan emulasi dan memaksimalkan Prepared Statements asli agar aman dari SQL Injection[cite: 5]
];

// Mencoba menyambungkan aplikasi ke database menggunakan PDO[cite: 5]
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Penyelamat (catch) jika koneksi gagal: error asli disembunyikan dari publik demi keamanan (mencegah Information Disclosure)[cite: 5]
    error_log($e->getMessage()); // Catat error di file log sistem[cite: 5]
    exit("Terjadi kesalahan pada koneksi database sistem."); // Tampilkan pesan ramah ke layar[cite: 5]
}

// Memulai Sesi (Session) yang aman jika belum aktif[cite: 5]
if (session_status() === PHP_SESSION_NONE) {
    // Mengatur parameter keamanan cookie sesi[cite: 5]
    session_set_cookie_params([
        'lifetime' => 0, // Sesi akan berakhir otomatis jika browser ditutup[cite: 5]
        'path' => '/', // Berlaku untuk seluruh folder dalam website[cite: 5]
        'domain' => '', // Mengikuti domain saat ini[cite: 5]
        'secure' => isset($_SERVER['HTTPS']), // Aktif otomatis jika website menggunakan HTTPS[cite: 5]
        'httponly' => true, // Mencegah cookie dicuri oleh script jahat JavaScript (Perlindungan XSS)[cite: 5]
        'samesite' => 'Strict' // Mencegah pembajakan sesi dari situs luar[cite: 5]
    ]);
    session_start(); // Menyalakan sesi[cite: 5]
}

// Fungsi untuk membuat (Generate) Kode Rahasia CSRF Token guna mencegah pemalsuan permintaan web[cite: 5]
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Membuat token acak yang aman[cite: 5]
    }
    return $_SESSION['csrf_token'];
}

// Fungsi untuk memeriksa (Verifikasi) apakah token CSRF yang dikirimkan sudah benar[cite: 5]
function verifyCsrfToken($token)
{
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        exit("Akses ditolak: CSRF Token tidak valid!"); // Tolak akses jika token tidak cocok[cite: 5]
    }
}

// ==========================================
// Fungsi Helper untuk Mengambil Nama Outlet Aktif[cite: 5]
// ==========================================
function get_nama_outlet_aktif($pdo)
{
    // Jika sesi outlet belum ada, kembalikan teks default 'Outlet Utama'[cite: 5]
    if (!isset($_SESSION['id_outlet'])) {
        return 'Outlet Utama';
    }
    try {
        // Mengambil nama outlet dari database berdasarkan ID outlet yang sedang aktif[cite: 5]
        $stmt = $pdo->prepare("SELECT nama FROM tb_outlet WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['id_outlet']]);
        $result = $stmt->fetch();
        return $result['nama'] ?? 'Outlet Utama'; // Kembalikan namanya, atau default 'Outlet Utama' jika kosong[cite: 5]
    } catch (PDOException $e) {
        return 'Outlet Utama'; // Cadangan jika terjadi error pada database[cite: 5]
    }
}

// ==========================================
// Fungsi Satpam Hak Akses Role (Pembatas Halaman)[cite: 5]
// ==========================================
function restrict_access($allowed_roles = [])
{
    // 1. Cek apakah user sudah login atau belum (punya user_id dan role atau tidak)[cite: 5]
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: login.php"); // Jika belum, lempar balik ke halaman login[cite: 5]
        exit();
    }

    // 2. CEGAH KASIR: Pemeriksaan tambahan jika role user tidak sesuai dengan daftar yang diizinkan[cite: 5]
    if(!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: login.php");
        exit();
    }

        

    // 3. Cek apakah role user diizinkan masuk ke halaman khusus tersebut (biasanya untuk Admin/Owner)[cite: 5]
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        // Jika tidak diizinkan, munculkan jendela peringatan (alert) lalu arahkan kembali ke dashboard[cite: 5]
        echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke halaman ini.'); window.location='dashboard.php';</script>";
        exit();
    }
}