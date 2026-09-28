<?php
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: home.php");
    exit();
}

$error_msg = '';
if (isset($_GET['error'])) {
    $err = $_GET['error'];
    if ($err === 'empty') {
        $error_msg = 'Username dan password wajib diisi!';
    } elseif ($err === 'wrong') {
        $error_msg = 'Password yang Anda masukkan salah!';
    } elseif ($err === 'notfound') {
        $error_msg = 'Username tidak terdaftar di sistem!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Kasir - LaundryApp</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="../Backend/img/loundryku.jpg">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f7ff 0%, #e2e8f0 100%);
        }
        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
        .btn-custom-oren {
            background-color: #ff7700;
            border: none;
            transition: all 0.2s ease-in-out;
        }
        .btn-custom-oren:hover {
            background-color: #e66d00;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 119, 0, 0.3);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100 px-3">

    <div class="card login-card p-4 p-md-5" style="width: 100%; max-width: 440px;">
        <!-- Header / Logo -->
        <div class="text-center mb-4">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3 shadow-sm" style="width: 64px; height: 64px; background-color: #ff7700;">
                <i class="bi bi-basket3-fill fs-3"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1" style="color: #1e293b;">LaundryApp</h3>
            <p class="text-muted small">Silakan masuk ke panel kasir</p>
        </div>

        <!-- Alert Error -->
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger border-0 text-center py-2 small rounded-3 mb-4" style="background-color: #fef2f2; color: #dc2626;">
                <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form action="proses_login.php" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" name="username" class="form-control bg-light border-start-0 rounded-end-pill py-2 px-2" placeholder="Masukkan username..." required autocomplete="off">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" name="password" class="form-control bg-light border-start-0 rounded-end-pill py-2 px-2" placeholder="Masukkan password..." required>
                </div>
            </div>

            <button type="submit" class="btn btn-custom-oren text-white w-100 rounded-pill py-3 fw-semibold shadow-sm">
                Masuk ke Beranda <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>

        <div class="text-center mt-4">
            <p class="text-muted small mb-0">Sistem Kasir Aman & Terproteksi</p>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>