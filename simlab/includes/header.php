<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIFLAB-BM — <?= $page_title ?? 'Dashboard' ?></title>
    <link rel="stylesheet" href="<?= $base_url ?? '../' ?>assets/css/style.css?v=5">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">
</head>
<body>
<?php if (isLoggedIn()):
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

function isActive($dir, $page) {
    global $current_dir, $current_page;
    if ($page && $current_page === $page) return true;
    return false;
}
$user_initial = strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1));
$role = $_SESSION['role'];
?>
<div class="app-layout">
    <aside class="sidebar" id="sidebarNav">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <span class="material-symbols-outlined">biotech</span>
            </div>
            <div>
                <div class="sidebar-brand-text">SIFLAB-BM</div>
                <div class="sidebar-brand-sub">SIM Lab Biomedis</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= $base_url ?? '../' ?>public/katalog.php" class="sidebar-nav-item <?= isActive('public', 'katalog.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">inventory_2</span><span>Katalog Alat</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>public/kalender.php" class="sidebar-nav-item <?= isActive('public', 'kalender.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">calendar_month</span><span>Kalender Jadwal</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>public/e_library.php" class="sidebar-nav-item <?= isActive('public', 'e_library.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">library_books</span><span>E-Library</span>
            </a>

            <?php if ($role === 'mahasiswa'): ?>
            <div style="padding:8px 12px 4px;font-size:11px;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;color:#9CA3AF;border-top:1px solid #E5E7EB;margin-top:8px">Mahasiswa</div>
            <a href="<?= $base_url ?? '../' ?>mahasiswa/dashboard.php" class="sidebar-nav-item <?= isActive('mahasiswa', 'dashboard.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">dashboard</span><span>Dashboard</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>mahasiswa/form_peminjaman.php" class="sidebar-nav-item <?= isActive('mahasiswa', 'form_peminjaman.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">add_box</span><span>Ajukan Peminjaman</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>mahasiswa/tracking_status.php?type=all" class="sidebar-nav-item <?= isActive('mahasiswa', 'tracking_status.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">track_changes</span><span>Tracking Peminjaman</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>mahasiswa/laporan_kerusakan.php" class="sidebar-nav-item <?= isActive('mahasiswa', 'laporan_kerusakan.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">report</span><span>Laporan Kerusakan</span>
            </a>
            <?php endif; ?>

            <?php if ($role === 'laboran'): ?>
            <div style="padding:8px 12px 4px;font-size:11px;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;color:#9CA3AF;border-top:1px solid #E5E7EB;margin-top:8px">Laboran</div>
            <a href="<?= $base_url ?? '../' ?>laboran/dashboard.php" class="sidebar-nav-item <?= isActive('laboran', 'dashboard.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">dashboard</span><span>Dashboard</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>laboran/manajemen_inventaris.php" class="sidebar-nav-item <?= isActive('laboran', 'manajemen_inventaris.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">inventory</span><span>Manajemen Inventaris</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>laboran/manajemen_bahan.php" class="sidebar-nav-item <?= isActive('laboran', 'manajemen_bahan.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">science</span><span>Bahan Habis Pakai</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>laboran/manajemen_laboratorium.php" class="sidebar-nav-item <?= isActive('laboran', 'manajemen_laboratorium.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">business</span><span>Manajemen Lab</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>laboran/verifikasi_peminjaman.php" class="sidebar-nav-item <?= isActive('laboran', 'verifikasi_peminjaman.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">verified</span><span>Verifikasi Peminjaman</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>laboran/verifikasi_peminjaman_lab.php" class="sidebar-nav-item <?= isActive('laboran', 'verifikasi_peminjaman_lab.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">meeting_room</span><span>Verifikasi Lab</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>laboran/kalibrasi_maintenance.php" class="sidebar-nav-item <?= isActive('laboran', 'kalibrasi_maintenance.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">build</span><span>Kalibrasi & Maintenance</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>laboran/logbook_kerusakan.php" class="sidebar-nav-item <?= isActive('laboran', 'logbook_kerusakan.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">report</span><span>Logbook Kerusakan</span>
            </a>
            <?php endif; ?>

            <?php if ($role === 'dosen'): ?>
            <div style="padding:8px 12px 4px;font-size:11px;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;color:#9CA3AF;border-top:1px solid #E5E7EB;margin-top:8px">Dosen</div>
            <a href="<?= $base_url ?? '../' ?>dosen/dashboard.php" class="sidebar-nav-item <?= isActive('dosen', 'dashboard.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">dashboard</span><span>Dashboard</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>dosen/verifikasi_riset.php" class="sidebar-nav-item <?= isActive('dosen', 'verifikasi_riset.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">verified</span><span>Verifikasi Riset TA</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>dosen/statistik.php" class="sidebar-nav-item <?= isActive('dosen', 'statistik.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">bar_chart</span><span>Statistik & Laporan</span>
            </a>
            <a href="<?= $base_url ?? '../' ?>dosen/export.php" class="sidebar-nav-item <?= isActive('dosen', 'export.php') ? 'active' : '' ?>">
                <span class="material-symbols-outlined">file_upload</span><span>Export Akreditasi</span>
            </a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <a href="../index.php" class="sidebar-nav-item">
                <span class="material-symbols-outlined">home</span><span>Beranda</span>
            </a>
            <a href="../auth/logout.php" class="sidebar-nav-item">
                <span class="material-symbols-outlined">logout</span><span>Logout</span>
            </a>
        </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-area">
        <header class="topbar">
            <div class="flex items-center gap-3">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="page-title"><?= $page_title ?? 'Dashboard' ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="notif-bell">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="notif-badge-dot"></span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="avatar"><?= $user_initial ?></div>
                    <div class="header-user-info">
                        <div class="header-user-name"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></div>
                        <div class="header-user-role"><?= ucfirst($role) ?></div>
                    </div>
                </div>
            </div>
        </header>
        <div class="content">
<?php else: ?>
<div class="auth-layout" style="min-height:100vh">
    <?php $auth_brand_side = true; ?>
    <div class="auth-form" style="width:100%;display:flex;align-items:center;justify-content:center">
        <div class="auth-form-inner">
<?php endif; ?>

<?php
$alert_html = showAlert();
if ($alert_html):
?>
<div class="mb-6"><?= $alert_html ?></div>
<?php endif; ?>

<style>
.animate-fadeIn { animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
