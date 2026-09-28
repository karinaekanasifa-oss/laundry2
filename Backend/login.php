<?php
// Backend/login.php
require_once __DIR__ . '/components/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Laundry App</title>
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
            --burgundy-bg: #f7ebee; /* Latar belakang warna burgundy sangat soft */
            --burgundy-light: #fcf1f3;
            --burgundy-focus: rgba(128, 0, 32, 0.15);
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--burgundy-bg); /* Latar belakang layar kini berwarna burgundy lembut */
            height: 100vh;
        }
        .card {
            border: 1px solid #ebd3d7;
            border-radius: 1rem;
            background-color: #ffffff;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border-color: #d8c8cc;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px var(--burgundy-focus);
            border-color: var(--burgundy-primary);
        }
        .btn-burgundy {
            background-color: var(--burgundy-primary);
            color: #ffffff;
            border: none;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-burgundy:hover {
            background-color: var(--burgundy-hover);
            color: #ffffff;
        }
        .icon-box {
            background-color: var(--burgundy-light);
            color: var(--burgundy-primary);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

    <div class="container" style="max-width: 410px;">
        <div class="card shadow-sm p-4">
            <div class="card-body">
                <!-- Logo / Judul -->
                <div class="text-center mb-4">
                    <div class="icon-box rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 55px; height: 55px;">
                        <i class="bi bi-shield-lock-fill fs-4"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Laundry Management</h4>
                    <p class="text-muted small">Silakan login untuk masuk ke panel backend</p>
                </div>

                <!-- Notifikasi Error -->
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="login_process.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label fw-medium small text-secondary">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" placeholder="Masukkan username" required autocomplete="off">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-medium small text-secondary">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Masukkan password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-burgundy w-100 shadow-sm">
                        Masuk ke Sistem
                    </button>
                </form>
            </div>
        </div>
        <div class="text-center text-secondary mt-3 small">
            &copy; 2026 Laundry App Backend Security
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>