<?php
// Backend/transaksi.php
require_once __DIR__ . '/pages/transaksi_proses.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Transaksi - Laundry App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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

        #content {
            margin-left: 260px !important;
            width: calc(100% - 260px) !important;
            min-height: 100vh;
            flex-grow: 1;
            box-sizing: border-box;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

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

        /* TAMBAHAN: Kelas bg-burgundy-soft agar warna logo maps muncul */
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
        <!-- Sidebar dipanggil dari folder partials -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <!-- Main Wrapper -->
        <div id="content" class="p-0" style="background-color: #f8f9fa; min-height: 100vh;">
            <!-- Topbar dipanggil dari folder partials -->
            <?php include __DIR__ . '/partials/topbar.php'; ?>

            <!-- Page Content -->
            <div class="container-fluid px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                    <div>
                        <h3 class="fw-bold text-dark">Manajemen Transaksi Kasir</h3>
                        <p class="text-muted mb-0">Input cucian masuk, edit rincian berat/diskon, atur progres pengerjaan.</p>
                    </div>
                    <button type="button" class="btn btn-burgundy rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahTransaksi">
                        <i class="bi bi-plus-lg me-1"></i> Transaksi Baru
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

                <!-- Form Filter -->
                <div class="card shadow-sm rounded-4 p-4 mb-4 border-0">
                    <form method="GET" action="transaksi.php" class="row g-3 align-items-end bg-light p-3 rounded-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Periode 3 Bulan</label>
                            <select name="periode" class="form-select rounded-pill border-0 shadow-sm">
                                <option value="">Semua Periode</option>
                                <option value="1" <?= $periode=='1'?'selected':''; ?>>Januari - Maret</option>
                                <option value="2" <?= $periode=='2'?'selected':''; ?>>April - Juni</option>
                                <option value="3" <?= $periode=='3'?'selected':''; ?>>Juli - September</option>
                                <option value="4" <?= $periode=='4'?'selected':''; ?>>Oktober - Desember</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">Tahun</label>
                            <input type="number" name="tahun" class="form-control rounded-pill border-0 shadow-sm" value="<?= htmlspecialchars($tahun); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">Status Cucian</label>
                            <select name="status" class="form-select rounded-pill border-0 shadow-sm">
                                <option value="">Semua Status</option>
                                <option value="baru" <?= $status_filter=='baru'?'selected':''; ?>>Baru</option>
                                <option value="proses" <?= $status_filter=='proses'?'selected':''; ?>>Proses</option>
                                <option value="selesai" <?= $status_filter=='selesai'?'selected':''; ?>>Selesai</option>
                                <option value="diambil" <?= $status_filter=='diambil'?'selected':''; ?>>Diambil</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-burgundy rounded-pill w-100 shadow-sm"><i class="bi bi-filter me-1"></i> Filter</button>
                        </div>
                    </form>
                </div>

                <!-- Tabel Transaksi -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-custom">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Invoice</th>
                                    <th>Member</th>
                                    <th>Paket & Rincian Harga</th>
                                    <th>Tgl Masuk</th>
                                    <th>Status Cucian</th>
                                    <th>Pembayaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($list_transaksi) > 0): ?>
                                    <?php $no = 1; foreach ($list_transaksi as $t): 
                                        $subtotal = ($t['harga_paket'] * $t['qty']);
                                        $diskon_bersih = abs($t['diskon']);
                                        $grand_total = $subtotal + $t['biaya_tambahan'] - $diskon_bersih + $t['pajak'];
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><code class="fw-bold text-dark"><?= htmlspecialchars($t['kode_invoice']); ?></code></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($t['nama_member']); ?></td>
                                            <td>
                                                <span class="small d-block fw-bold"><?= htmlspecialchars($t['nama_paket']); ?></span>
                                                <span class="text-muted" style="font-size: 12px;">Qty/Berat: <?= $t['qty']; ?></span>
                                                <div class="fw-bold text-dark mt-1" style="font-size: 12px;">
                                                    Total: Rp <?= number_format($grand_total, 0, ',', '.'); ?>
                                                </div>
                                            </td>
                                            <td class="small text-secondary"><?= htmlspecialchars($t['tgl']); ?></td>
                                            <td>
                                                <?php
                                                    $status = $t['status'];
                                                    $badge_s = 'bg-secondary';
                                                    if ($status === 'baru') $badge_s = 'bg-info text-dark';
                                                    elseif ($status === 'proses') $badge_s = 'bg-warning text-dark';
                                                    elseif ($status === 'selesai') $badge_s = 'bg-primary';
                                                    elseif ($status === 'diambil') $badge_s = 'bg-success';
                                                ?>
                                                <span class="badge <?= $badge_s; ?> text-uppercase" style="font-size: 10px;">
                                                    <?= htmlspecialchars($status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($t['dibayar'] === 'dibayar'): ?>
                                                    <span class="badge bg-success text-uppercase" style="font-size: 10px;">Lunas</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger text-uppercase" style="font-size: 10px;">Belum Lunas</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditTransaksi<?= $t['id']; ?>" 
                                                        title="Edit Transaksi">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>

                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                                                            Status
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="transaksi.php?id=<?= $t['id']; ?>&ubah_status=baru">Baru</a></li>
                                                            <li><a class="dropdown-item" href="transaksi.php?id=<?= $t['id']; ?>&ubah_status=proses">Proses</a></li>
                                                            <li><a class="dropdown-item" href="transaksi.php?id=<?= $t['id']; ?>&ubah_status=selesai">Selesai</a></li>
                                                            <li><a class="dropdown-item" href="transaksi.php?id=<?= $t['id']; ?>&ubah_status=diambil">Diambil (Lunas)</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Transaksi -->
                                        <div class="modal fade" id="modalEditTransaksi<?= $t['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content border-0 rounded-4 shadow">
                                                    <form action="" method="POST">
                                                        <div class="modal-header border-bottom-0 pb-0">
                                                            <h5 class="modal-title fw-bold text-dark">Edit Transaksi: <?= htmlspecialchars($t['kode_invoice']); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="id_transaksi" value="<?= $t['id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold small">Pelanggan (Member)</label>
                                                                <select name="id_member" class="form-select" required>
                                                                    <?php foreach ($list_member as $m): ?>
                                                                        <option value="<?= $m['id']; ?>" <?= ($m['id'] == $t['id_member_asli']) ? 'selected' : ''; ?>>
                                                                            <?= htmlspecialchars($m['nama']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold small">Paket Cucian</label>
                                                                <select name="id_paket" class="form-select" required>
                                                                    <?php foreach ($list_paket as $p): ?>
                                                                        <option value="<?= $p['id']; ?>" <?= ($p['id'] == $t['id_paket_asli']) ? 'selected' : ''; ?>>
                                                                            <?= htmlspecialchars($p['nama_paket']); ?> (Rp <?= number_format($p['harga'], 0, ',', '.'); ?>)
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold small">Jumlah / Berat (Kg / Pcs)</label>
                                                                <input type="number" step="0.01" min="0.01" class="form-control" name="qty" value="<?= $t['qty']; ?>" required>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label fw-semibold small">Biaya Tambahan (Rp)</label>
                                                                    <input type="number" min="0" class="form-control" name="biaya_tambahan" value="<?= $t['biaya_tambahan']; ?>">
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label fw-semibold small">Diskon (Rp)</label>
                                                                    <input type="number" min="0" class="form-control" name="diskon" value="<?= $t['diskon']; ?>">
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold small">Pajak (Rp)</label>
                                                                <input type="number" min="0" class="form-control" name="pajak" value="<?= $t['pajak']; ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold small">Status Pembayaran</label>
                                                                <select name="dibayar" class="form-select" required>
                                                                    <option value="belum_dibayar" <?= ($t['dibayar'] === 'belum_dibayar') ? 'selected' : ''; ?>>Belum Lunas</option>
                                                                    <option value="dibayar" <?= ($t['dibayar'] === 'dibayar') ? 'selected' : ''; ?>>Lunas</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top-0 pt-0">
                                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="edit_transaksi" class="btn btn-burgundy rounded-pill px-4">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bi bi-cart-x fs-2 d-block mb-2"></i>
                                            Tidak ada data transaksi untuk periode tersebut.
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

    <!-- Modal Tambah Transaksi Baru -->
    <div class="modal fade" id="modalTambahTransaksi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <form action="pages/transaksi_aksi.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="id_outlet" value="<?= $id_outlet_user; ?>">
                    <input type="hidden" name="kode_invoice" value="TRX-<?= date('YmdHis'); ?>">

                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark">Input Transaksi Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Pilih Pelanggan (Member)</label>
                            <select name="id_member" class="form-select" required>
                                <option value="">-- Pilih Member --</option>
                                <?php foreach ($list_member as $m): ?>
                                    <option value="<?= $m['id']; ?>"><?= htmlspecialchars($m['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Pilih Paket Cucian</label>
                            <select name="id_paket" class="form-select" required>
                                <option value="">-- Pilih Paket & Layanan --</option>
                                <?php foreach ($list_paket as $p): ?>
                                    <option value="<?= $p['id']; ?>"><?= htmlspecialchars($p['nama_paket']); ?> (Rp <?= number_format($p['harga'], 0, ',', '.'); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Jumlah / Berat (Kg atau Pcs)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="qty" required placeholder="Contoh: 3.5">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Keterangan / Catatan</label>
                            <textarea class="form-control" name="keterangan" rows="2" placeholder="Catatan cucian masuk..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-burgundy rounded-pill px-4">Proses Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>