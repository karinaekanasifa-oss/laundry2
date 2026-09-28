<?php
// Backend/login_process.php
require_once __DIR__ . '/components/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header("Location: login.php?error=Username dan password wajib diisi!");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM tb_user WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Cek login: Mendukung password plaintext (teks biasa) ATAU password hash
        $is_password_valid = false;
        if ($user) {
            // Cek apakah password cocok dengan teks biasa ATAU cocok dengan hash
            if ($password === $user['password'] || password_verify($password, $user['password'])) {
                $is_password_valid = true;
            }
        }

        if ($user && $is_password_valid) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8');
            $_SESSION['role'] = $user['role'];
            $_SESSION['id_outlet'] = $user['id_outlet'];

            // --- SISIPKAN PENCATATAN LOG LOGIN DI SINI ---
            try {
                $log_stmt = $pdo->prepare("INSERT INTO activity_log (username, activity) VALUES (:username, :activity)");
                $log_stmt->execute([
                    'username' => $username,
                    'activity' => 'Berhasil melakukan login ke sistem'
                ]);
            } catch (Exception $e) {
                // Biarkan kosong atau abaikan jika log gagal agar login tidak terganggu
            }
            // ---------------------------------------------

            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: login.php?error=Username atau password salah!");
            exit();
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header("Location: login.php?error=Terjadi kesalahan pada sistem.");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>