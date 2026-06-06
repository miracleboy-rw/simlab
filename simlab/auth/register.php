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

        if ((int)$tahun_kode < 123 || (int)$tahun_kode > $kode_tahun_sekarang) {
            $tahun_min = 2023;
            $errors[] = "Kode tahun <strong>$tahun_kode</strong> tidak valid. (Kode tahun $tahun_kode = angkatan $tahun_ajaran, kode tahun $kode_tahun_sekarang = angkatan $tahun_sekarang)";
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4318FF',secondary:'#6E38F7',accent:'#FF5B75',navy:'#2B3674',soft:'#F4F7FE',muted:'#A3AED0'},borderRadius:{'3xl':'24px'}}}}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #F4F7FE; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .login-bg { position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
        .login-bg .orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; }
        .orb-1 { width: 400px; height: 400px; background: #4318FF; top: -100px; right: -100px; }
        .orb-2 { width: 350px; height: 350px; background: #FF5B75; bottom: -80px; left: -80px; }
        .orb-3 { width: 200px; height: 200px; background: #6E38F7; top: 50%; left: 50%; transform: translate(-50%, -50%); }
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.5); border-radius: 24px; box-shadow: 0 20px 60px rgba(153,153,153,0.12); }
        .glass-input { background: rgba(255,255,255,0.8); border: 2px solid rgba(67,24,255,0.1); border-radius: 16px; padding: 0.75rem 1rem 0.75rem 2.75rem; color: #2B3674; font-size: 0.9375rem; width: 100%; outline: none; transition: all 0.3s; }
        .glass-input:focus { border-color: #4318FF; box-shadow: 0 0 0 4px rgba(67,24,255,0.1); background: white; }
        .btn-login { background: linear-gradient(135deg, #4318FF, #6E38F7); color: white; border-radius: 16px; padding: 0.875rem; font-weight: 700; font-size: 1rem; border: none; cursor: pointer; transition: all 0.3s; width: 100%; box-shadow: 0 4px 15px rgba(67,24,255,0.3); }
        .btn-login:hover { box-shadow: 0 8px 30px rgba(67,24,255,0.4); transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="login-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div></div>

    <div class="glass-card w-full max-w-[420px] relative z-10 p-8 sm:p-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                <i class="fas fa-flask text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-navy tracking-tight">Daftar Akun</h1>
            <p class="text-muted text-sm mt-1 font-medium">Mahasiswa Teknik Biomedis</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-600 rounded-2xl px-4 py-3 text-sm font-medium mb-6 flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-2xl px-4 py-3 text-sm font-medium mb-6 flex items-center gap-2">
            <i class="fas fa-check-circle"></i> <?= $success ?>
        </div>
        <div class="text-center mt-4">
            <a href="login.php" class="text-primary font-semibold text-sm hover:underline"><i class="fas fa-arrow-left mr-1"></i> Login Sekarang</a>
        </div>
        <?php else: ?>

        <form method="POST" class="space-y-4">
            <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-muted"></i>
                <input type="text" name="nama_lengkap" class="glass-input" placeholder="Nama Lengkap" value="<?= htmlspecialchars($nama_lengkap ?? '') ?>" required autofocus>
            </div>
            <div class="relative">
                <i class="fas fa-id-card absolute left-3 top-1/2 -translate-y-1/2 text-muted"></i>
                <input type="text" name="nim" class="glass-input" placeholder="NIM (contoh: 123430119)" value="<?= htmlspecialchars($nim ?? '') ?>" required maxlength="9">
            </div>
            <div class="relative">
                <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-muted"></i>
                <input type="password" name="password" class="glass-input" placeholder="Password (min. 6 karakter)" required>
            </div>
            <div class="relative">
                <i class="fas fa-check-circle absolute left-3 top-1/2 -translate-y-1/2 text-muted"></i>
                <input type="password" name="confirm_password" class="glass-input" placeholder="Konfirmasi Password" required>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-user-plus mr-2"></i> Daftar
            </button>
        </form>

        <div class="text-center mt-6">
            <p class="text-xs text-muted">Sudah punya akun? <a href="login.php" class="text-primary font-semibold hover:underline">Login</a></p>
        </div>

        <hr class="divider my-5">
        <div class="bg-soft/70 rounded-2xl p-4 text-xs text-muted leading-relaxed">
            <p class="font-semibold text-navy mb-1"><i class="fas fa-info-circle mr-1"></i> Format NIM:</p>
            3 digit kode tahun + 3 digit kode prodi + 3 digit nomor urut<br>
            <strong>Contoh:</strong> 123430119<br>
            <span class="text-primary">123</span> = angkatan 2023 &middot; <span class="text-primary">430</span> = Teknik Biomedis &middot; <span class="text-primary">119</span> = urutan ke-119
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
