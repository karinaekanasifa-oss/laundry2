<?php
require_once '../Backend/components/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header("Location: login.php?error=empty");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id, nama, username, password, role, id_outlet FROM tb_user WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();

        if ($row) {
            if (password_verify($password, $row['password'])) {
                session_regenerate_id(true);

                // Gunakan session standar agar terdeteksi di seluruh aplikasi
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['nama'] = $row['nama'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['id_outlet'] = $row['id_outlet'];
                $_SESSION['logged_in'] = true;

                // Jika yang login Admin/Owner, arahkan ke Backend. Jika Kasir, ke Frontend.
                if ($row['role'] === 'kasir') {
                    header("Location: home.php");
                    exit();
                } else {
                    header("Location: ../Backend/dashboard.php");
                    exit();
                }
            } else {
                header("Location: login.php?error=wrong");
                exit();
            }
        } else {
            header("Location: login.php?error=notfound");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: login.php?error=wrong");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}