<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
if (isLoggedIn()) { redirect('../index.php'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $user = fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        alert('success', 'Selamat datang, ' . $user['nama_lengkap'] . '!');
        redirect('../index.php');
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM-LAB — Login</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body>
<div class="auth-layout">
    <div class="auth-brand">
        <div class="auth-brand-content">
            <div class="auth-brand-icon">
                <span class="material-symbols-outlined" style="font-size:32px">biotech</span>
            </div>
            <h1>SIM-LAB</h1>
            <p>Sistem Informasi Manajemen Laboratorium Biomedis terintegrasi untuk mendukung kegiatan praktikum, penelitian, dan akreditasi. Kelola inventaris, peminjaman alat, dan jadwal laboratorium dengan mudah dan efisien.</p>
        </div>
    </div>
    <div class="auth-form">
        <div class="auth-form-inner">
            <div style="margin-bottom:32px">
                <h2 style="font-size:24px;font-weight:700;color:#111827;margin-bottom:4px">Selamat Datang</h2>
                <p style="color:#6B7280;font-size:14px">Silakan masuk ke akun Anda</p>
            </div>
            <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label class="form-label">NIM / NIP / Email</label>
                    <div style="position:relative">
                        <span class="material-symbols-outlined" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:18px;color:#9CA3AF">person</span>
                        <input type="text" name="username" class="form-input" style="padding-left:36px" placeholder="Masukkan NIM, NIP, atau Email" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div style="position:relative">
                        <span class="material-symbols-outlined" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:18px;color:#9CA3AF">lock</span>
                        <input type="password" name="password" class="form-input" style="padding-left:36px" placeholder="Masukkan password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-full" style="justify-content:center;height:44px;font-size:15px">
                    <span class="material-symbols-outlined">login</span> Masuk
                </button>
            </form>
            <p style="text-align:center;margin-top:24px;font-size:13px;color:#6B7280">
                Belum punya akun? <a href="register.php" style="color:#2a4dd7;font-weight:600">Daftar sekarang</a>
            </p>
            <p style="text-align:center;margin-top:8px;font-size:11px;color:#9CA3AF">&copy; 2026 SIM-LAB. All rights reserved.</p>
        </div>
    </div>
</div>
</body>
</html>
