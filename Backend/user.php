<?php
// Backend/user.php
require_once __DIR__ . '/pages/user_proses.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Laundry App</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="icon" type="image/jpeg" href="img/loundryku.jpg">

    <style>
        :root {
            --burgundy-primary: #800020;
            --burgundy-hover: #600018;
            --burgundy-light: #fcf1f3;
            --burgundy-bg: #f7ebee;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
            margin: 0;
        }

        /* Pengaturan Sidebar agar presisi */
        #sidebar { 
            width: 260px;
            min-width: 260px;
            max-width: 260px;
            background: #fff; 
            border-right: 1px solid #ebd3d7; 
            min-height: 100vh; 
            position: fixed; 
            top: 0; 
            left: 0; 
            z-index: 100;
            box-sizing: border-box;
        }
        
        #sidebar .nav-link { 
            color: #495057; 
            border-radius: 0; 
            padding: 11px 24px;
            font-size: 15px;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        #sidebar .nav-link:hover, 
        #sidebar .nav-link.active { 
            background-color: var(--burgundy-light); 
            color: var(--burgundy-primary); 
            font-weight: 600; 
            border-left: 4px solid var(--burgundy-primary); 
        }

        /* Pengaturan Area Konten Utama */
        #content {
            margin-left: 260px !important;
            width: calc(100% - 260px) !important;
            min-height: 100vh;
            flex-grow: 1;
            box-sizing: border-box;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* Topbar Presisi */
        .custom-topbar {
            height: 70px !important;
            min-height: 70px !important;
            max-height: 70px !important;
            border-bottom: 1px solid #ebd3d7 !important;
            box-shadow: none !important;
            margin-bottom: 0 !important;
            box-sizing: border-box !important;
            display: flex !important;
            align-items: center !important;
        }

        .custom-topbar .container-fluid {
            height: 70px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        .bg-burgundy-soft {
            background-color: var(--burgundy-light);
            color: var(--burgundy-primary);
        }

        .btn-burgundy {
            background-color: var(--burgundy-primary);
            color: #fff;
        }

        .btn-burgundy:hover {
            background-color: var(--burgundy-hover);
            color: #fff;
        }

        .table-custom th {
            background-color: #fcf1f3;
            color: var(--burgundy-primary);
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="d-flex">
        <!-- Sidebar dipanggil dari folder partials (TIDAK DIUBAH) -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <!-- Main Wrapper -->
        <div id="content" class="p-0" style="background-color: #f8f9fa; min-height: 100vh;">
            <!-- Topbar dipanggil dari folder partials (TIDAK DIUBAH) -->
            <?php include __DIR__ . '/partials/topbar.php'; ?>

            <!-- Page Content -->
            <div class="container-fluid px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                    <div>
                        <h3 class="fw-bold text-dark">Manajemen Pengguna</h3>
                        <p class="text-muted mb-0">Kelola akun administrator, kasir, dan owner yang memiliki akses ke aplikasi.</p>
                    </div>
                    <button type="button" class="btn btn-burgundy rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                        <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna
                    </button>
                </div>

                <!-- Alert Notifikasi -->
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

                <!-- Tabel Pengguna -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-custom">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Lengkap</th>
                                    <th>Username</th>
                                    <th>Outlet</th>
                                    <th>Role / Hak Akses</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($list_user) > 0): ?>
                                    <?php $no = 1; foreach ($list_user as $u): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($u['nama']); ?></td>
                                            <td><code class="text-secondary"><?= htmlspecialchars($u['username']); ?></code></td>
                                            <td>
                                                <i class="bi bi-shop me-1 text-danger"></i> <?= htmlspecialchars($u['nama_outlet'] ?? 'Outlet Tidak Ditemukan'); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $badge_color = 'bg-secondary';
                                                    if ($u['role'] === 'admin') $badge_color = 'bg-danger';
                                                    elseif ($u['role'] === 'kasir') $badge_color = 'bg-primary';
                                                    elseif ($u['role'] === 'owner') $badge_color = 'bg-success';
                                                ?>
                                                <span class="badge <?= $badge_color; ?> text-uppercase" style="font-size: 11px;">
                                                    <?= htmlspecialchars($u['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle btn-edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditUser"
                                                    data-id="<?= htmlspecialchars($u['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-nama="<?= htmlspecialchars($u['nama'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-id_outlet="<?= htmlspecialchars($u['id_outlet'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-role="<?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <a href="user.php?hapus=<?= htmlspecialchars($u['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    onclick="return confirm('Yakin ingin menghapus pengguna ini?');"
                                                    class="btn btn-sm btn-outline-danger rounded-circle ms-1">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-person-badge fs-2 d-block mb-2"></i>
                                            Belum ada data pengguna.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Tambah Pengguna -->
    <div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <form action="" method="POST">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark">Tambah Pengguna Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" required placeholder="Contoh: Budi Santoso">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Username</label>
                            <input type="text" class="form-control" name="username" required placeholder="Contoh: kasir1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Password</label>
                            <input type="password" class="form-control" name="password" required placeholder="Masukkan password akun">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Penempatan Outlet</label>
                            <select name="id_outlet" class="form-select" required>
                                <option value="">-- Pilih Outlet Cabang --</option>
                                <?php foreach ($semua_outlet as $ot): ?>
                                    <option value="<?= $ot['id']; ?>"><?= htmlspecialchars($ot['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Role / Hak Akses</label>
                            <select name="role" class="form-select" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="admin">Admin</option>
                                <option value="kasir">Kasir</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_user" class="btn btn-burgundy rounded-pill px-4">Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Pengguna -->
    <div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <form action="" method="POST">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark">Edit Data Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit-nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Username</label>
                            <input type="text" class="form-control" id="edit-username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Password Baru <span class="text-muted fw-normal">(Kosongkan jika tidak ingin diubah)</span></label>
                            <input type="password" class="form-control" name="password" placeholder="Biarkan kosong jika tetap">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Penempatan Outlet</label>
                            <select name="id_outlet" id="edit-id_outlet" class="form-select" required>
                                <option value="">-- Pilih Outlet Cabang --</option>
                                <?php foreach ($semua_outlet as $ot): ?>
                                    <option value="<?= $ot['id']; ?>"><?= htmlspecialchars($ot['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Role / Hak Akses</label>
                            <select name="role" id="edit-role" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="kasir">Kasir</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_user" class="btn btn-burgundy rounded-pill px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalEditUser = document.getElementById('modalEditUser');
        if (modalEditUser) {
            modalEditUser.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                document.getElementById('edit-id').value = button.getAttribute('data-id');
                document.getElementById('edit-nama').value = button.getAttribute('data-nama');
                document.getElementById('edit-username').value = button.getAttribute('data-username');
                document.getElementById('edit-id_outlet').value = button.getAttribute('data-id_outlet');
                document.getElementById('edit-role').value = button.getAttribute('data-role');
            });
        }
    </script>
</body>

</html>