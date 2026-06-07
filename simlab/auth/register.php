<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
if (isLoggedIn()) { redirect('../index.php'); }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$nama_lengkap || !$nim || !$password) {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (!preg_match('/^\d{9}$/', $nim)) {
        $error = 'NIM harus 9 digit angka.';
    } else {
        $tahun_kode = substr($nim, 0, 3);
        $kode_prodi = substr($nim, 3, 3);
        $no_urut = (int)substr($nim, 6, 3);
        $tahun_ajaran = 1900 + (int)$tahun_kode;
        $tahun_sekarang = (int)date('Y');
        $kode_tahun_sekarang = $tahun_sekarang - 1900;

        $errors = [];

        $tahun_min = max(2020, $tahun_sekarang - 7);
        $kode_tahun_min = $tahun_min - 1900;
        if ((int)$tahun_kode < $kode_tahun_min || (int)$tahun_kode > $kode_tahun_sekarang) {
            $errors[] = "Kode tahun <strong>$tahun_kode</strong> tidak valid. (Minimal angkatan $tahun_min, maksimal angkatan $tahun_sekarang)";
        }
        if ($kode_prodi !== '430') {
            $errors[] = "Kode prodi <strong>$kode_prodi</strong> tidak valid. Kode prodi Teknik Biomedis adalah <strong>430</strong>.";
        }
        if ($no_urut < 1 || $no_urut > 150) {
            $errors[] = "Nomor urut <strong>$no_urut</strong> tidak valid. (001-150)";
        }

        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        } else {
            $cek = fetchOne("SELECT id FROM users WHERE nim_nidn = ?", [$nim]);
            if ($cek) {
                $error = "NIM <strong>$nim</strong> sudah terdaftar.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $username = $nim;
                query("INSERT INTO users (username, password, role, nama_lengkap, nim_nidn) VALUES (?, ?, 'mahasiswa', ?, ?)",
                    [$username, $hash, $nama_lengkap, $nim]);
                $success = 'Akun berhasil dibuat! Silakan login.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIFLAB-BM — Daftar Akun</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body>
<div class="auth-layout">
    <div class="auth-brand">
        <div class="auth-brand-content">
            <div class="auth-brand-icon">
                <span class="material-symbols-outlined" style="font-size:32px">biotech</span>
            </div>
            <h1>SIFLAB-BM</h1>
            <p>Platform manajemen laboratorium biomedis terpadu. Daftar sekarang untuk mengakses berbagai layanan peminjaman alat, jadwal laboratorium, dan sumber daya akademik.</p>
            <div style="margin-top:32px;background:rgba(255,255,255,0.1);border-radius:12px;padding:20px">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                    <span class="material-symbols-outlined">science</span>
                    <span style="font-size:14px;font-weight:500">Akses ke 150+ alat laboratorium</span>
                </div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                    <span class="material-symbols-outlined">calendar_month</span>
                    <span style="font-size:14px;font-weight:500">Jadwal laboratorium terintegrasi</span>
                </div>
                <div style="display:flex;align-items:center;gap:12px">
                    <span class="material-symbols-outlined">library_books</span>
                    <span style="font-size:14px;font-weight:500">Pustaka modul praktikum digital</span>
                </div>
            </div>
        </div>
    </div>
    <div class="auth-form">
        <div class="auth-form-inner">
            <div style="margin-bottom:24px">
                <h2 style="font-size:24px;font-weight:700;color:#111827;margin-bottom:4px">Daftar Akun Baru</h2>
                <p style="color:#6B7280;font-size:14px">Isi data diri untuk mendaftar</p>
            </div>
            <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
            <p style="text-align:center;margin-top:16px">
                <a href="login.php" class="btn btn-primary" style="justify-content:center;text-decoration:none">
                    <span class="material-symbols-outlined">login</span> Login Sekarang
                </a>
            </p>
            <?php else: ?>
            <form method="post">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-input" placeholder="Masukkan nama lengkap" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">NIM</label>
                    <input type="text" name="nim" class="form-input" placeholder="Masukkan NIM" required maxlength="9">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-input" placeholder="Ulangi password" required>
                </div>
                <button type="submit" class="btn btn-primary w-full" style="justify-content:center;height:44px;font-size:15px">
                    <span class="material-symbols-outlined">person_add</span> Daftar
                </button>
            </form>
            <p style="text-align:center;margin-top:16px;font-size:13px;color:#6B7280">
                Sudah punya akun? <a href="login.php" style="color:#2a4dd7;font-weight:600">Masuk sekarang</a>
            </p>
            <?php endif; ?>
            <p style="text-align:center;margin-top:8px;font-size:11px;color:#9CA3AF">&copy; 2026 SIFLAB-BM. All rights reserved.</p>
        </div>
    </div>
</div>
</body>
</html>
