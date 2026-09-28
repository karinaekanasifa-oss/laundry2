<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LaundryApp - Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="img/loundryku.jpg">
    
    <style>
        :root { 
            --burgundy-primary: #800020; 
            --burgundy-light: #fcf1f3; 
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
        
        #sidebar .border-bottom {
            height: 70px; 
            display: flex;
            align-items: center;
            border-bottom: 1px solid #ebd3d7 !important;
            box-sizing: border-box;
        }

        #sidebar .nav-item {
            margin: 0;
            padding: 0;
        }
        
        #sidebar .nav-link { 
            color: #495057; 
            border-radius: 0; 
            padding: 11px 24px; /* Memberikan jarak kiri-kanan agar ikon dan teks tidak terlalu mepet */
            font-size: 15px;
            font-family: 'Inter', sans-serif;
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
        }

        /* PENGUNCI UTAMA TOPBAR: Memaksa ukuran dan posisi rata tengah mutlak */
        .custom-topbar {
            height: 70px !important;
            min-height: 70px !important;
            max-height: 70px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-bottom: 1px solid #ebd3d7 !important;
            margin-top: 0 !important;
            margin-left: 0 !important;
            box-sizing: border-box !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Memastikan container di dalam topbar mengisi tinggi penuh dan posisinya center */
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

        /* Kelas Pendukung Warna */
        .bg-burgundy-soft { 
            background-color: var(--burgundy-light); 
            color: var(--burgundy-primary); 
        }
        .btn-burgundy {
            background-color: var(--burgundy-primary);
            color: #fff;
        }
        .btn-burgundy:hover {
            background-color: #600018;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="d-flex">