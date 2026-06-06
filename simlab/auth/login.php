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
    <title>SIFLAB-BM — Login</title>
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
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.5); border-radius: 24px; box-shadow: 0 20px 60px rgba(153,153,153,0.12); }
        .glass-input { background: rgba(255,255,255,0.8); border: 2px solid rgba(67,24,255,0.1); border-radius: 16px; padding: 0.75rem 1rem 0.75rem 2.75rem; color: #2B3674; font-size: 0.9375rem; width: 100%; outline: none; transition: all 0.3s; }
        .glass-input:focus { border-color: #4318FF; box-shadow: 0 0 0 4px rgba(67,24,255,0.1); background: white; }
        .btn-login { background: linear-gradient(135deg, #4318FF, #6E38F7); color: white; border-radius: 16px; padding: 0.875rem; font-weight: 700; font-size: 1rem; border: none; cursor: pointer; transition: all 0.3s; width: 100%; box-shadow: 0 4px 15px rgba(67,24,255,0.3); }
        .btn-login:hover { box-shadow: 0 8px 30px rgba(67,24,255,0.4); transform: translateY(-2px); }
        .demo-badge { background: rgba(67,24,255,0.08); border-radius: 12px; padding: 0.4rem 0.75rem; font-size: 0.8rem; color: #4318FF; font-weight: 500; }
    </style>
</head>
<body>
    <div class="login-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div></div>

    <div class="glass-card w-full max-w-[420px] relative z-10 p-8 sm:p-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                <i class="fas fa-flask text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-navy tracking-tight">SIFLAB-BM</h1>
            <p class="text-muted text-sm mt-1 font-medium">Sistem Informasi Manajemen Lab Biomedis</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-600 rounded-2xl px-4 py-3 text-sm font-medium mb-6 flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-muted"></i>
                <input type="text" name="username" class="glass-input" placeholder="Username" required autofocus>
            </div>
            <div class="relative">
                <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-muted"></i>
                <input type="password" name="password" class="glass-input" placeholder="Password" required>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-arrow-right-to-bracket mr-2"></i> Masuk
            </button>
        </form>

        <hr class="divider my-6">
        <div class="text-center">
            <p class="text-xs text-muted font-semibold uppercase tracking-wider mb-3">Akun Demo</p>
            <div class="flex flex-wrap justify-center gap-2">
                <span class="demo-badge"><i class="fas fa-tools mr-1"></i> laboran / admin123</span>
                <span class="demo-badge"><i class="fas fa-chalkboard-teacher mr-1"></i> dosen1 / admin123</span>
                <span class="demo-badge"><i class="fas fa-user-graduate mr-1"></i> mahasiswa1 / admin123</span>
            </div>
        </div>
    </div>
</body>
</html>
