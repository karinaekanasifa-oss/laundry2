<?php
// frontend/member.php
require_once __DIR__ . '/../Backend/components/koneksi.php';
restrict_access(['kasir'] , 'kasir');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Silakan login terlebih dahulu!");
    exit();
}

$nama_user = $_SESSION['nama'] ?? 'Administrator';
$pesan_sukses = '';
$pesan_error = '';

// Fungsi Bantuan untuk Log Aktivitas
function catat_log($pdo, $username, $aktivitas) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_log (username, activity, created_at) VALUES (:username, :activity, NOW())");
        $stmt->execute(['username' => $username, 'activity' => $aktivitas]);
    } catch (PDOException $e) {
        // Abaikan jika tabel log belum ada
    }
}

// 1. Proses Tambah Member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_member'])) {
    $nama     = trim($_POST['nama']);
    $alamat   = trim($_POST['alamat']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tlp      = trim($_POST['tlp']);

    if (!empty($nama) && !empty($tlp)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tb_member (nama, alamat, jenis_kelamin, tlp) VALUES (:nama, :alamat, :jenis_kelamin, :tlp)");
            $stmt->execute([
                'nama' => $nama,
                'alamat' => $alamat,
                'jenis_kelamin' => $jenis_kelamin,
                'tlp' => $tlp
            ]);
            $pesan_sukses = "Member baru atas nama <b>" . htmlspecialchars($nama) . "</b> berhasil ditambahkan!";
            catat_log($pdo, $nama_user, "Menambahkan member baru: $nama");
        } catch (PDOException $e) {
            $pesan_error = "Gagal menambah member: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Nama dan nomor telepon wajib diisi!";
    }
}

// 2. Proses Edit Member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_member'])) {
    $id       = $_POST['id_member'];
    $nama     = trim($_POST['nama']);
    $alamat   = trim($_POST['alamat']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tlp      = trim($_POST['tlp']);

    if (!empty($nama) && !empty($tlp)) {
        try {
            $stmt = $pdo->prepare("UPDATE tb_member SET nama = :nama, alamat = :alamat, jenis_kelamin = :jenis_kelamin, tlp = :tlp WHERE id = :id");
            $stmt->execute([
                'nama' => $nama,
                'alamat' => $alamat,
                'jenis_kelamin' => $jenis_kelamin,
                'tlp' => $tlp,
                'id' => $id
            ]);
            $pesan_sukses = "Data member berhasil diperbarui!";
            catat_log($pdo, $nama_user, "Memperbarui data member ID: $id");
        } catch (PDOException $e) {
            $pesan_error = "Gagal memperbarui member: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Nama dan nomor telepon wajib diisi!";
    }
}

// 3. Proses Hapus Member
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_member WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $pesan_sukses = "Member berhasil dihapus!";
        catat_log($pdo, $nama_user, "Menghapus member ID: $id");
    } catch (PDOException $e) {
        $pesan_error = "Gagal menghapus member (kemungkinan masih memiliki riwayat transaksi aktif).";
    }
}

// Ambil data seluruh member
try {
    $list_member = $pdo->query("SELECT * FROM tb_member ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    $list_member = [];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Member - Lumiere Laundry</title>
    
    <!-- Stylesheets Bawaan Template Frontend -->
    <link rel="stylesheet" href="./assets/libraries/glide/css/glide.core.min.css">
    <link rel="stylesheet" href="./assets/libraries/aos/aos.css">
    <link rel="stylesheet" href="./assets/css/main.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <!-- FontAwesome & Bootstrap Icons -->
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
     <?php include 'navbar.php'; ?>

    <!-- Navbar Atas Ala Template Frontend -->
    <!-- <header class="navigation position-sticky top-0 w-100 bg-body-tertiary shadow-sm border-bottom z-3">
        <nav class="navbar navbar-expand-xl" aria-label="Offcanvas navbar large">
            <div class="container py-1">
                <a href="home.php" class="navbar-brand">
                    <img src="./assets/logo/logo.png" height="40" alt="logo">
                </a>

                <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar2" aria-controls="offcanvasNavbar2" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="offcanvas offcanvas-end border-0 rounded-start-0" tabindex="-1" id="offcanvasNavbar2" aria-labelledby="offcanvasNavbar2Label">
                    <div class="offcanvas-header" style="padding: 2rem 2rem 1.5rem 2rem;">
                        <h5 class="offcanvas-title m-0" id="offcanvasNavbar2Label">
                            <a class="navbar-brand" href="home.php">
                                <img src="./assets/logo/logo.png" height="32" alt="logo">
                            </a>
                        </h5>
                        <button type="button" class="btn-close text-body-emphasis" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>

                    <div class="offcanvas-body">
                        <ul class="navbar-nav align-items-xl-center flex-grow-1 column-gap-4 row-gap-4 row-gap-xl-2 ms-auto">
                            <li class="nav-item">
                                <a href="home.php" class="px-3 text-body-emphasis bg-body-secondary-hover nav-link rounded-3 text-base leading-6 fw-semibold">
                                    Beranda
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="member.php" class="px-3 text-body-emphasis bg-body-secondary-hover nav-link rounded-3 text-base leading-6 fw-semibold active" aria-current="page">
                                    Registrasi Member
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="transaksi.php" class="px-3 text-body-emphasis bg-body-secondary-hover nav-link rounded-3 text-base leading-6 fw-semibold">
                                    Entri Transaksi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="laporan.php" class="px-3 text-body-emphasis bg-body-secondary-hover nav-link rounded-3 text-base leading-6 fw-semibold">
                                    Generate Laporan
                                </a>
                            </li>
                            <li class="nav-item ms-xl-3">
                                <a href="../backend/logout.php" class="btn btn-danger text-white btn-sm px-3 rounded-pill">
                                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header> -->

    <!-- Konten Utama Halaman Member -->
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark">Registrasi Pelanggan (Member)</h2>
                <p class="text-muted mb-0">Kelola data pelanggan laundry yang terdaftar dalam sistem.</p>
            </div>
            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm text-white" data-bs-toggle="modal" data-bs-target="#modalTambahMember">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah Member
            </button>
        </div>

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $pesan_sukses; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $pesan_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Tabel Data Member -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Lengkap</th>
                            <th>Alamat</th>
                            <th>Jenis Kelamin</th>
                            <th>No. Telepon / WhatsApp</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($list_member) > 0): ?>
                            <?php $no = 1; foreach ($list_member as $m): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($m['nama']); ?></td>
                                    <td><?= htmlspecialchars($m['alamat']); ?></td>
                                    <td>
                                        <?php if ($m['jenis_kelamin'] === 'L'): ?>
                                            <span class="badge bg-info text-dark">Laki-laki</span>
                                        <?php else: ?>
                                            <span class="badge bg-pink text-dark" style="background-color: #ffd1dc;">Perempuan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><i class="bi bi-whatsapp text-success me-1"></i> <?= htmlspecialchars($m['tlp']); ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditMember<?= $m['id']; ?>">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <a href="member.php?hapus=<?= $m['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin ingin menghapus member ini?');">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Member -->
                                <div class="modal fade" id="modalEditMember<?= $m['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0 rounded-4 shadow">
                                            <form action="" method="POST">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark">Edit Data Member</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <input type="hidden" name="id_member" value="<?= $m['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Nama Lengkap</label>
                                                        <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($m['nama']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Alamat</label>
                                                        <textarea class="form-control" name="alamat" rows="2" required><?= htmlspecialchars($m['alamat']); ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Jenis Kelamin</label>
                                                        <select name="jenis_kelamin" class="form-select" required>
                                                            <option value="L" <?= ($m['jenis_kelamin'] === 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                                            <option value="P" <?= ($m['jenis_kelamin'] === 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">No. Telepon / WhatsApp</label>
                                                        <input type="text" class="form-control" name="tlp" value="<?= htmlspecialchars($m['tlp']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0">
                                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="edit_member" class="btn btn-primary text-white rounded-pill px-4">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-people fs-2 d-block mb-2"></i>
                                    Belum ada data member yang terdaftar.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Member Baru -->
    <div class="modal fade" id="modalTambahMember" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <form action="" method="POST">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark">Tambah Member Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" required placeholder="Nama pelanggan">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Alamat</label>
                            <textarea class="form-control" name="alamat" rows="2" required placeholder="Alamat lengkap"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">No. Telepon / WhatsApp</label>
                            <input type="text" class="form-control" name="tlp" required placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_member" class="btn btn-primary text-white rounded-pill px-4">Simpan Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer sederhana -->
    <footer class="py-4 bg-body border-top text-center text-body-secondary text-sm">
        <div class="container">
            <p class="mb-0">© <?= date('Y'); ?> Lumiere Laundry.</p>
        </div>
    </footer>

    <!-- Scripts Bootstrap & Template -->
    <script src="./assets/libraries/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/libraries/glide/glide.min.js"></script>
    <script src="./assets/libraries/aos/aos.js"></script>
    <script src="./assets/js/scripts.js"></script>
</body>
</html>