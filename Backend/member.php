<?php
// Backend/member.php
require_once __DIR__ . '/pages/member_proses.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Member - Laundry App</title>
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
        <!-- Panggil Sidebar dari folder partials (TIDAK DIUBAH) -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
       
        <!-- Main Wrapper -->
        <div id="content" class="p-0" style="background-color: #f8f9fa; min-height: 100vh;">
            <!-- Panggil Topbar dari folder partials (TIDAK DIUBAH) -->
            <?php include __DIR__ . '/partials/topbar.php'; ?>

            <!-- Page Content / Body -->
            <div class="container-fluid px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                    <div>
                        <h3 class="fw-bold text-dark">Manajemen Member</h3>
                        <p class="text-muted mb-0">Kelola data pelanggan laundry dengan mudah.</p>
                    </div>
                    <button type="button" class="btn btn-burgundy rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMember">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Member
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

                <!-- Tabel Member -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-custom">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Alamat</th>
                                    <th>Jenis Kelamin</th>
                                    <th>No. Telepon</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($list_member) > 0): ?>
                                    <?php $no = 1; foreach ($list_member as $m): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($m['nama']); ?></td>
                                            <td><?= htmlspecialchars($m['alamat']); ?></td>
                                            <td><?= ($m['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                                            <td><?= htmlspecialchars($m['tlp']); ?></td>
                                            <td>
                                                <!-- Tombol Edit memicu modal dengan membawa data -->
                                                <button class="btn btn-sm btn-outline-primary rounded-circle btn-edit" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditMember"
                                                        data-id="<?= $m['id']; ?>"
                                                        data-nama="<?= htmlspecialchars($m['nama']); ?>"
                                                        data-alamat="<?= htmlspecialchars($m['alamat']); ?>"
                                                        data-jk="<?= $m['jenis_kelamin']; ?>"
                                                        data-tlp="<?= htmlspecialchars($m['tlp']); ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <!-- Tombol Hapus dengan konfirmasi -->
                                                <a href="member.php?hapus=<?= $m['id']; ?>" onclick="return confirm('Yakin ingin menghapus member ini?');" class="btn btn-sm btn-outline-danger rounded-circle ms-1">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
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
        </div>
    </div>

    <!-- Modal Tambah Member -->
    <div class="modal fade" id="modalTambahMember" tabindex="-1" aria-labelledby="modalTambahMemberLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <form action="" method="POST">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark" id="modalTambahMemberLabel">Tambah Member Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama" class="form-label fw-semibold small">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama pelanggan">
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label fw-semibold small">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="2" placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label fw-semibold small">Jenis Kelamin</label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tlp" class="form-label fw-semibold small">No. Telepon / WhatsApp</label>
                            <input type="text" class="form-control" id="tlp" name="tlp" required placeholder="Contoh: 081234567890">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_member" class="btn btn-burgundy rounded-pill px-4">Simpan Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Member -->
    <div class="modal fade" id="modalEditMember" tabindex="-1" aria-labelledby="modalEditMemberLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <form action="" method="POST">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark" id="modalEditMemberLabel">Edit Data Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit-nama" class="form-label fw-semibold small">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit-nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-alamat" class="form-label fw-semibold small">Alamat</label>
                            <textarea class="form-control" id="edit-alamat" name="alamat" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit-jk" class="form-label fw-semibold small">Jenis Kelamin</label>
                            <select class="form-select" id="edit-jk" name="jenis_kelamin">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-tlp" class="form-label fw-semibold small">No. Telepon / WhatsApp</label>
                            <input type="text" class="form-control" id="edit-tlp" name="tlp" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_member" class="btn btn-burgundy rounded-pill px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script untuk melempar data dari tombol edit ke dalam modal edit
        const modalEditMember = document.getElementById('modalEditMember');
        if (modalEditMember) {
            modalEditMember.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                
                const id = button.getAttribute('data-id');
                const nama = button.getAttribute('data-nama');
                const alamat = button.getAttribute('data-alamat');
                const jk = button.getAttribute('data-jk');
                const tlp = button.getAttribute('data-tlp');

                document.getElementById('edit-id').value = id;
                document.getElementById('edit-nama').value = nama;
                document.getElementById('edit-alamat').value = alamat;
                document.getElementById('edit-jk').value = jk;
                document.getElementById('edit-tlp').value = tlp;
            });
        }
    </script>
</body>
</html>