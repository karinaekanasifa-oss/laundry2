<?php
// Backend/outlet.php
require_once __DIR__ . '/pages/outlet_proses.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Outlet - Laundry App</title>
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

        /* Pengaturan Sidebar */
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

        /* Topbar Presisi agar garis menyambung rata dan tanpa bayangan ganda */
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
        <!-- Panggil Sidebar dari partials -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <!-- Main Wrapper -->
        <div id="content" class="p-0" style="background-color: #f8f9fa; min-height: 100vh;">
            
            <!-- Panggil Topbar dari partials -->
            <?php include __DIR__ . '/partials/topbar.php'; ?>

            <!-- Page Content -->
            <div class="container-fluid px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                    <div>
                        <h3 class="fw-bold text-dark">Manajemen Outlet</h3>
                        <p class="text-muted mb-0">Kelola daftar cabang outlet laundry (Khusus Admin).</p>
                    </div>
                    <button type="button" class="btn btn-burgundy rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahOutlet">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Outlet
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

                <!-- Tabel Outlet -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-custom">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Outlet</th>
                                    <th>Alamat</th>
                                    <th>No. Telepon</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($list_outlet) > 0): ?>
                                    <?php $no = 1;
                                    foreach ($list_outlet as $o): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($o['nama']); ?></td>
                                            <td><?= htmlspecialchars($o['alamat']); ?></td>
                                            <td><?= htmlspecialchars($o['tlp'] ?? '-'); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle btn-edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditOutlet"
                                                    data-id="<?= htmlspecialchars($o['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-nama="<?= htmlspecialchars($o['nama'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-alamat="<?= htmlspecialchars($o['alamat'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-tlp="<?= htmlspecialchars($o['tlp'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <a href="outlet.php?hapus=<?= htmlspecialchars($o['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    onclick="return confirm('Yakin ingin menghapus outlet ini?');"
                                                    class="btn btn-sm btn-outline-danger rounded-circle ms-1">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-shop fs-2 d-block mb-2"></i>
                                            Belum ada data outlet.
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

    <!-- Panggil File Modal dari folder pages -->
    <?php include __DIR__ . '/pages/modal_outlet.php'; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalEditOutlet = document.getElementById('modalEditOutlet');
        if (modalEditOutlet) {
            modalEditOutlet.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                document.getElementById('edit-id').value = button.getAttribute('data-id');
                document.getElementById('edit-nama').value = button.getAttribute('data-nama');
                document.getElementById('edit-alamat').value = button.getAttribute('data-alamat');
                document.getElementById('edit-tlp').value = button.getAttribute('data-tlp');
            });
        }
    </script>
</body>
</html>