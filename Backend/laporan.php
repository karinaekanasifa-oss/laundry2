<?php
// Backend/laporan.php
require_once __DIR__ . '/pages/laporan_proses.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Laporan & Aktivitas - LaundryApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="img/loundryku.jpg">
    
    <style>
        :root {
            --burgundy-primary: #800020;
            --burgundy-hover: #600018;
            --burgundy-light: #fcf1f3;
            --burgundy-border: #ebd3d7;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            color: #333;
            overflow-x: hidden;
            margin: 0;
        }

        /* Pengaturan Sidebar */
        #sidebar { 
            width: 260px;
            min-width: 260px;
            max-width: 260px;
            background: #fff; 
            border-right: 1px solid var(--burgundy-border); 
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
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
            border-left: 4px solid transparent;
        }
        
        #sidebar .nav-link:hover, 
        #sidebar .nav-link.active { 
            background-color: var(--burgundy-light); 
            color: var(--burgundy-primary); 
            font-weight: 600; 
            border-left: 4px solid var(--burgundy-primary); 
        }

        #sidebar .nav-link:hover i,
        #sidebar .nav-link.active i {
            color: var(--burgundy-primary) !important;
        }

        /* Pengaturan Area Konten Utama */
        #content {
            margin-left: 260px !important;
            width: calc(100% - 260px) !important;
            min-height: 100vh;
            flex-grow: 1;
            box-sizing: border-box;
        }

        /* PENGUNCI UTAMA TOPBAR: Supaya garis bawahnya menyambung lurus rata dengan sidebar */
        .custom-topbar {
            height: 70px !important;
            min-height: 70px !important;
            max-height: 70px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-bottom: 1px solid var(--burgundy-border) !important;
            margin-top: 0 !important;
            margin-left: 0 !important;
            margin-bottom: 0 !important;
            box-sizing: border-box !important;
            display: flex !important;
            align-items: center !important;
        }

        .custom-topbar .container-fluid {
            height: 70px !important;
            min-height: 70px !important;
            max-height: 70px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            margin: 0 !important;
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.03);
        }

        .bg-burgundy-soft {
            background-color: var(--burgundy-light);
            color: var(--burgundy-primary);
        }

        .btn-burgundy {
            background-color: var(--burgundy-primary);
            color: #fff;
            border: none;
        }

        .btn-burgundy:hover {
            background-color: var(--burgundy-hover);
            color: #fff;
        }

        .nav-pills .nav-link {
            color: #555;
            font-weight: 600;
            border-radius: 50rem;
            padding: 0.6rem 1.5rem;
            background: #fff;
            border: 1px solid #dee2e6;
        }

        .nav-pills .nav-link.active {
            background-color: var(--burgundy-primary) !important;
            color: #fff !important;
            border-color: var(--burgundy-primary);
            box-shadow: 0 4px 12px rgba(128,0,32,0.2);
        }

        .table-custom th {
            background-color: var(--burgundy-light) !important;
            color: var(--burgundy-primary);
            font-weight: 600;
            border-bottom: none;
        }

        @media print {
            #sidebar, 
            .navbar-top, 
            .no-print {
                display: none !important;
            }
            #content {
                margin: 0 !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Memanggil Sidebar dari folder partials -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div id="content" class="p-0" style="background-color: #f8f9fa; min-height: 100vh;">
        <!-- Memanggil Topbar dari folder partials -->
        <?php include __DIR__ . '/partials/topbar.php'; ?>

        <div class="container-fluid px-4 py-3">
            <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                <div>
                    <h3 class="fw-bold text-dark mb-1">Pusat Laporan & Aktivitas</h3>
                    <p class="text-muted small mb-0">Pantau rekapitulasi transaksi dan riwayat aktivitas sistem per rentang 3 bulan.</p>
                </div>
                <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow-sm no-print"><i class="bi bi-printer-fill me-2"></i> Cetak Laporan</button>
            </div>
            <ul class="nav nav-pills mb-4 gap-2 no-print" role="tablist">
                <li class="nav-item"><a class="nav-link <?= $tab=='transaksi'?'active':''; ?>" href="laporan.php?tab=transaksi"><i class="bi bi-receipt-cutoff me-2"></i> Laporan Transaksi</a></li>
                <li class="nav-item"><a class="nav-link <?= $tab=='activity'?'active':''; ?>" href="laporan.php?tab=activity"><i class="bi bi-clock-history me-2"></i> Riwayat Aktivitas</a></li>
            </ul>
            <?php if ($tab == 'transaksi'): ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm rounded-4 p-3 border-start border-4 border-danger">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-burgundy-soft p-3 rounded-4 fs-4"><i class="bi bi-wallet2"></i></div>
                                <div><span class="text-muted small d-block">Total Pendapatan (Lunas)</span><h4 class="fw-bold text-dark mb-0">Rp <?= number_format($total_omset, 0, ',', '.'); ?></h4></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm rounded-4 p-3 border-start border-4 border-secondary">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light p-3 rounded-4 fs-4 text-secondary"><i class="bi bi-receipt"></i></div>
                                <div><span class="text-muted small d-block">Total Transaksi Tercatat</span><h4 class="fw-bold text-dark mb-0"><?= count($laporan_list); ?> Data</h4></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm rounded-4 p-4 mb-4">
                    <form method="GET" action="laporan.php" class="row g-3 align-items-end mb-4 no-print bg-light p-3 rounded-4">
                        <input type="hidden" name="tab" value="transaksi">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">Filter Outlet</label>
                            <select name="id_outlet" class="form-select rounded-pill border-0 shadow-sm">
                                <option value="">Semua Outlet</option>
                                <?php foreach ($semua_outlet as $o): ?>
                                    <option value="<?= $o['id']; ?>" <?= $outlet == $o['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($o['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">Periode 3 Bulan</label>
                            <select name="periode" class="form-select rounded-pill border-0 shadow-sm">
                                <option value="">Semua Periode</option>
                                <option value="1" <?= $periode=='1'?'selected':''; ?>>Januari - Maret</option>
                                <option value="2" <?= $periode=='2'?'selected':''; ?>>April - Juni</option>
                                <option value="3" <?= $periode=='3'?'selected':''; ?>>Juli - September</option>
                                <option value="4" <?= $periode=='4'?'selected':''; ?>>Oktober - Desember</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-secondary">Tahun</label>
                            <input type="number" name="tahun" class="form-control rounded-pill border-0 shadow-sm" value="<?= htmlspecialchars($tahun); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-secondary">Status</label>
                            <select name="status" class="form-select rounded-pill border-0 shadow-sm">
                                <option value="">Semua Status</option>
                                <option value="baru" <?= $status=='baru'?'selected':''; ?>>Baru</option>
                                <option value="proses" <?= $status=='proses'?'selected':''; ?>>Proses</option>
                                <option value="selesai" <?= $status=='selesai'?'selected':''; ?>>Selesai</option>
                                <option value="diambil" <?= $status=='diambil'?'selected':''; ?>>Diambil</option>
                            </select>
                        </div>
                        <div class="col-md-2"><button type="submit" class="btn btn-burgundy rounded-pill w-100 shadow-sm"><i class="bi bi-filter me-1"></i> Filter</button></div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-custom rounded-3 overflow-hidden">
                            <thead>
                                <tr>
                                    <th class="py-3">#</th>
                                    <th class="py-3">Invoice</th>
                                    <th class="py-3">Outlet</th>
                                    <th class="py-3">Member</th>
                                    <th class="py-3">Paket</th>
                                    <th class="py-3">Total Harga</th>
                                    <th class="py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($laporan_list) > 0): ?>
                                    <?php $no = 1; foreach ($laporan_list as $row): 
                                        $gt = (($row['harga_paket'] * $row['qty']) + $row['biaya_tambahan'] - $row['diskon'] + $row['pajak']); ?>
                                        <tr>
                                            <td class="text-muted"><?= $no++; ?></td>
                                            <td><code class="fw-bold text-dark"><?= htmlspecialchars($row['kode_invoice']); ?></code></td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['nama_outlet_transaksi']); ?></span></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($row['nama_member']); ?></td>
                                            <td>
                                                <span class="small d-block fw-bold"><?= htmlspecialchars($row['nama_paket']); ?></span>
                                                <span class="text-muted" style="font-size: 11px;">Qty: <?= $row['qty']; ?></span>
                                            </td>
                                            <td class="fw-bold text-dark">Rp <?= number_format($gt, 0, ',', '.'); ?></td>
                                            <td><span class="badge bg-secondary text-uppercase" style="font-size: 10px;"><?= htmlspecialchars($row['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data laporan untuk periode 3 bulan tersebut.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm rounded-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-secondary me-2"></i>Log Aktivitas Pengguna</h5>
                        <span class="badge bg-burgundy-soft px-3 py-2 rounded-pill small">50 Aktivitas Terakhir</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-custom rounded-3 overflow-hidden">
                            <thead>
                                <tr>
                                    <th class="py-3">#ID</th>
                                    <th class="py-3">Pengguna</th>
                                    <th class="py-3">Aktivitas Sistem</th>
                                    <th class="py-3">Waktu Kejadian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($list_log) > 0): ?>
                                    <?php foreach ($list_log as $log): ?>
                                        <tr>
                                            <td class="text-muted">#<?= $log['id']; ?></td>
                                            <td><span class="badge bg-burgundy-soft px-3 py-2"><i class="bi bi-person-fill me-1"></i> <?= htmlspecialchars($log['username']); ?></span></td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($log['activity']); ?></td>
                                            <td class="small text-secondary"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($log['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada riwayat aktivitas.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>