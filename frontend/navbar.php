<?php
// Mengecek apakah sesi aplikasi sudah aktif atau belum. 
// Jika belum, nyalakan session_start() agar data login pengguna bisa terbaca[cite: 9].
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Membuat bar navigasi atas (navbar) dengan latar putih, ada bayangan halus, dan menempel di atas (sticky-top)[cite: 9] -->
<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top border-bottom py-3 px-4" style="border-color: #e2e8f0 !important;">
    <div class="container-fluid">
        <!-- Logo & Brand (Ikon keranjang laundry dan teks LaundryApp di sebelah kiri)[cite: 9] -->
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2 text-dark" href="home.php" style="font-family: 'Inter', sans-serif;">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 38px; height: 38px; background-color: #ff7700;">
                <i class="bi bi-basket3-fill fs-5"></i>
            </div>
            <span style="color: #1e293b; font-weight: 700;">LaundryApp</span>
        </a>

        <!-- Menu Navigasi di Bagian Tengah[cite: 9] -->
        <div class="collapse navbar-collapse justify-content-center">
            <ul class="navbar-nav gap-2">
                <!-- Menu Beranda: Dicek apakah halaman yang sedang dibuka home.php, jika ya warnanya oren, jika tidak abu-abu[cite: 9] -->
                <!-- basename($_SERVER['PHP_SELF']) artinya: Perintah untuk mengambil nama file -->
                <!-- yang sedang aktif dibuka (misal: 'home.php'). Ini dipakai buat ngecek menu -->
                <!--  mana yang lagi diklik, supaya tombol menunya otomatis berubah jadi warna oren.-->
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 rounded-pill fw-semibold <?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'text-white' : 'text-secondary'; ?>" href="home.php" style="<?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'background-color: #ff7700;' : ''; ?>">Beranda</a>
                </li>
                <!-- Menu Registrasi Member: Berubah jadi oren jika sedang dibuka[cite: 9] -->
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 rounded-pill fw-semibold <?= basename($_SERVER['PHP_SELF']) == 'member.php' ? 'text-white' : 'text-secondary'; ?>" href="member.php" style="<?= basename($_SERVER['PHP_SELF']) == 'member.php' ? 'background-color: #ff7700;' : ''; ?>">Registrasi Member</a>
                </li>
                <!-- Menu Entri Transaksi: Berubah jadi oren jika sedang dibuka[cite: 9] -->
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 rounded-pill fw-semibold <?= basename($_SERVER['PHP_SELF']) == 'transaksi.php' ? 'text-white' : 'text-secondary'; ?>" href="transaksi.php" style="<?= basename($_SERVER['PHP_SELF']) == 'transaksi.php' ? 'background-color: #ff7700;' : ''; ?>">Entri Transaksi</a>
                </li>
                <!-- Menu Generate Laporan: Berubah jadi oren jika sedang dibuka[cite: 9] -->
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 rounded-pill fw-semibold <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'text-white' : 'text-secondary'; ?>" href="laporan.php" style="<?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'background-color: #ff7700;' : ''; ?>">Generate Laporan</a>
                </li>
            </ul>
        </div>

        <!-- Bagian Kanan: Profil Pengguna & Tombol Logout[cite: 9] -->
        <div class="d-flex align-items-center gap-3">
            <!-- Kotak Informasi Nama & Role User yang sedang login[cite: 9] -->
            <div class="d-flex align-items-center gap-2 text-end">
                <!-- Menampilkan inisial huruf depan dari nama user di dalam lingkaran[cite: 9] -->
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" style="width: 38px; height: 38px; font-size: 14px; background-color: #3b82f6;">
                <!--Kode ini berfungsi untuk mengambil huruf depan dari nama user yang sedang login, 
                // mengubahnya jadi huruf kapital (strtoupper), lalu menampilkannya di dalam ikon profil. 
                // Jika namanya kosong, otomatis memakai huruf 'P' sebagai cadangan.  -->   
                <?= strtoupper(substr($_SESSION['nama'] ?? 'P', 0, 1)); ?>
                </div>
                <div class="text-start">
                    <!-- Menampilkan nama lengkap user[cite: 9] -->
                    <span class="d-block fw-bold text-dark small" style="line-height: 1.2;"><?= htmlspecialchars($_SESSION['nama'] ?? 'Tamu'); ?></span>
                    <!-- Menampilkan badge role / jabatan (misal: kasir/admin)[cite: 9] -->
                    <span class="badge bg-info text-dark text-uppercase" style="font-size: 9px; background-color: #e0f2fe !important; color: #0369a1 !important;"><?= htmlspecialchars($_SESSION['role'] ?? 'kasir'); ?></span>
                </div>
            </div>

            <!-- Tombol Logout: Jika diklik akan menjalankan fungsi konfirmasi SweetAlert2[cite: 9] -->
            <a href="#" onclick="konfirmasiLogout(event)" class="btn btn-sm rounded-pill px-4 py-2 fw-semibold text-white shadow-sm" style="background-color: #ff7700; border: none;" onmouseover="this.style.backgroundColor='#e66d00';" onmouseout="this.style.backgroundColor='#ff7700';">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

<!-- Memanggil Pustaka SweetAlert2 untuk memunculkan kotak dialog peringatan yang keren[cite: 9] -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Fungsi untuk memunculkan popup konfirmasi sebelum benar-benar keluar aplikasi
// Fungsi ini dipanggil saat tombol logout diklik. 
// "event.preventDefault();" berfungsi untuk menahan agar halaman tidak langsung 
// berpindah, melainkan memunculkan kotak peringatan (SweetAlert2) terlebih dahulu 
// untuk memastikan apakah user benar-benar ingin keluar atau batal.
function konfirmasiLogout(event) {
    event.preventDefault(); // Mencegah link langsung berpindah halaman sebelum diklik "Ya"
    
    Swal.fire({
        title: 'Keluar dari Sistem?',
        text: "Sesi Anda akan diakhiri dan Anda harus login kembali.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff7700',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Logout!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        // Jika user menekan tombol konfirmasi "Ya, Logout!"[cite: 9]
        if (result.isConfirmed) {
            // Mengarahkan sistem menuju file logout.php untuk menghapus sesi[cite: 9]
            window.location.href = 'logout.php';
        }
    });
}
</script>