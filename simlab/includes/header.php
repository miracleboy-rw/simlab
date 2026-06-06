<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIFLAB-BM — <?= $page_title ?? 'Dashboard' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4318FF',secondary:'#6E38F7',accent:'#FF5B75',navy:'#2B3674',soft:'#F4F7FE',muted:'#A3AED0'},borderRadius:{'3xl':'24px'}}}}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">
    <link href="<?= $base_url ?? '../' ?>assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="flex">
<?php if (isLoggedIn()):

// Determine current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

function isActive($dir, $page) {
    global $current_dir, $current_page;
    if ($page && $current_page === $page) return true;
    return false;
}

$user_initial = strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1));
$role_class = $_SESSION['role'] === 'mahasiswa' ? 'from-amber-400 to-orange-400' : ($_SESSION['role'] === 'laboran' ? 'from-primary to-secondary' : 'from-secondary to-purple-500');
$role_icon = $_SESSION['role'] === 'mahasiswa' ? 'fa-user-graduate' : ($_SESSION['role'] === 'laboran' ? 'fa-tools' : 'fa-chalkboard-teacher');
?>
<!-- ===== SIDEBAR ===== -->
<aside id="sidebar" class="fixed lg:sticky top-0 left-0 z-40 h-screen w-64 bg-white/90 backdrop-blur-2xl border-r border-white/50 shadow-[4px_0_30px_rgba(153,153,153,0.08)] flex flex-col transition-all duration-300 -translate-x-full lg:translate-x-0">
    
    <!-- Brand -->
    <div class="px-6 pt-6 pb-4 flex items-center gap-3 border-b border-gray-100/50">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-md shrink-0">
            <i class="fas fa-flask text-white text-sm"></i>
        </div>
        <div>
            <a href="<?= $base_url ?? '../' ?>index.php" class="text-lg font-extrabold bg-gradient-to-br from-primary to-secondary bg-clip-text text-transparent">SIFLAB-BM</a>
            <div class="text-xs text-muted font-medium">SIM Lab Biomedis</div>
        </div>
    </div>

    <!-- User Info Mini -->
    <div class="px-5 py-4 border-b border-gray-100/50 flex items-center gap-3">
        <span class="w-9 h-9 rounded-xl bg-gradient-to-br <?= $role_class ?> text-white flex items-center justify-center text-sm font-bold shadow-sm"><?= $user_initial ?></span>
        <div class="min-w-0">
            <div class="text-sm font-bold text-navy truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></div>
            <div class="text-xs text-muted font-medium flex items-center gap-1"><i class="fas <?= $role_icon ?>"></i><?= ucfirst($_SESSION['role']) ?></div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

        <!-- UMUM -->
        <div class="text-xs font-bold text-muted uppercase tracking-wider px-3 pb-2 pt-2">Umum</div>
        <a href="<?= $base_url ?? '../' ?>public/katalog.php" class="sidebar-link <?= isActive('public', 'katalog.php') ? 'active' : '' ?>">
            <i class="fas fa-microscope w-5"></i> Katalog Alat
        </a>
        <a href="<?= $base_url ?? '../' ?>public/kalender.php" class="sidebar-link <?= isActive('public', 'kalender.php') ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt w-5"></i> Kalender Jadwal
        </a>
        <a href="<?= $base_url ?? '../' ?>public/e_library.php" class="sidebar-link <?= isActive('public', 'e_library.php') ? 'active' : '' ?>">
            <i class="fas fa-book w-5"></i> E-Library
        </a>

        <?php if (isRole('mahasiswa')): ?>
        <!-- MAHASISWA -->
        <div class="text-xs font-bold text-muted uppercase tracking-wider px-3 pb-2 pt-5 border-t border-gray-100/50 mt-3">Mahasiswa</div>
        <a href="<?= $base_url ?? '../' ?>mahasiswa/dashboard.php" class="sidebar-link <?= isActive('mahasiswa', 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-chart-pie w-5"></i> Dashboard
        </a>
        <a href="<?= $base_url ?? '../' ?>mahasiswa/laporan_kerusakan.php" class="sidebar-link <?= isActive('mahasiswa', 'laporan_kerusakan.php') ? 'active' : '' ?>">
            <i class="fas fa-exclamation-triangle w-5"></i> Lapor Kerusakan
        </a>
        <div class="text-xs font-bold text-muted uppercase tracking-wider px-3 pb-2 pt-4 border-t border-gray-100/50 mt-3">Peminjaman</div>
        <a href="<?= $base_url ?? '../' ?>mahasiswa/form_peminjaman.php" class="sidebar-link <?= isActive('mahasiswa', 'form_peminjaman.php') ? 'active' : '' ?>">
            <i class="fas fa-box w-5"></i> Pinjam Alat
        </a>
        <a href="<?= $base_url ?? '../' ?>mahasiswa/form_peminjaman_lab.php" class="sidebar-link <?= isActive('mahasiswa', 'form_peminjaman_lab.php') ? 'active' : '' ?>">
            <i class="fas fa-door-open w-5"></i> Pinjam Lab
        </a>
        <a href="<?= $base_url ?? '../' ?>mahasiswa/tracking_status.php?type=all" class="sidebar-link <?= isActive('mahasiswa', 'tracking_status.php') ? 'active' : '' ?>">
            <i class="fas fa-search w-5"></i> Tracking Peminjaman
        </a>
        <?php endif; ?>

        <?php if (isRole('laboran')): ?>
        <!-- LABORAN -->
        <div class="text-xs font-bold text-muted uppercase tracking-wider px-3 pb-2 pt-5 border-t border-gray-100/50 mt-3">Laboran</div>
        <a href="<?= $base_url ?? '../' ?>laboran/dashboard.php" class="sidebar-link <?= isActive('laboran', 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-chart-pie w-5"></i> Dashboard
        </a>
        <a href="<?= $base_url ?? '../' ?>laboran/manajemen_inventaris.php" class="sidebar-link <?= isActive('laboran', 'manajemen_inventaris.php') ? 'active' : '' ?>">
            <i class="fas fa-box w-5"></i> Manajemen Inventaris
        </a>
        <a href="<?= $base_url ?? '../' ?>laboran/manajemen_bahan.php" class="sidebar-link <?= isActive('laboran', 'manajemen_bahan.php') ? 'active' : '' ?>">
            <i class="fas fa-flask w-5"></i> Bahan Habis Pakai
        </a>
        <a href="<?= $base_url ?? '../' ?>laboran/kalibrasi_maintenance.php" class="sidebar-link <?= isActive('laboran', 'kalibrasi_maintenance.php') ? 'active' : '' ?>">
            <i class="fas fa-calendar-check w-5"></i> Kalibrasi & Maintenance
        </a>
        <a href="<?= $base_url ?? '../' ?>laboran/logbook_kerusakan.php" class="sidebar-link <?= isActive('laboran', 'logbook_kerusakan.php') ? 'active' : '' ?>">
            <i class="fas fa-exclamation-triangle w-5"></i> Logbook Kerusakan
        </a>
        <div class="text-xs font-bold text-muted uppercase tracking-wider px-3 pb-2 pt-4 border-t border-gray-100/50 mt-3">Verifikasi Peminjaman</div>
        <a href="<?= $base_url ?? '../' ?>laboran/verifikasi_peminjaman.php" class="sidebar-link <?= isActive('laboran', 'verifikasi_peminjaman.php') ? 'active' : '' ?>">
            <i class="fas fa-box w-5"></i> Verifikasi Alat
        </a>
        <a href="<?= $base_url ?? '../' ?>laboran/verifikasi_peminjaman_lab.php" class="sidebar-link <?= isActive('laboran', 'verifikasi_peminjaman_lab.php') ? 'active' : '' ?>">
            <i class="fas fa-door-open w-5"></i> Verifikasi Lab
        </a>
        <a href="<?= $base_url ?? '../' ?>laboran/manajemen_laboratorium.php" class="sidebar-link <?= isActive('laboran', 'manajemen_laboratorium.php') ? 'active' : '' ?>">
            <i class="fas fa-edit w-5"></i> Kelola Lab
        </a>
        <?php endif; ?>

        <?php if (isRole('dosen')): ?>
        <!-- DOSEN -->
        <div class="text-xs font-bold text-muted uppercase tracking-wider px-3 pb-2 pt-5 border-t border-gray-100/50 mt-3">Dosen</div>
        <a href="<?= $base_url ?? '../' ?>dosen/dashboard.php" class="sidebar-link <?= isActive('dosen', 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-chart-pie w-5"></i> Dashboard
        </a>
        <a href="<?= $base_url ?? '../' ?>dosen/verifikasi_riset.php" class="sidebar-link <?= isActive('dosen', 'verifikasi_riset.php') ? 'active' : '' ?>">
            <i class="fas fa-graduation-cap w-5"></i> Verifikasi Riset TA
        </a>
        <a href="<?= $base_url ?? '../' ?>dosen/statistik.php" class="sidebar-link <?= isActive('dosen', 'statistik.php') ? 'active' : '' ?>">
            <i class="fas fa-chart-bar w-5"></i> Statistik Penggunaan
        </a>
        <a href="<?= $base_url ?? '../' ?>dosen/export.php" class="sidebar-link <?= isActive('dosen', 'export.php') ? 'active' : '' ?>">
            <i class="fas fa-file-export w-5"></i> Export Akreditasi
        </a>
        <?php endif; ?>
    </nav>

    <!-- Logout -->
    <div class="px-3 py-3 border-t border-gray-100/50">
        <a href="<?= $base_url ?? '../' ?>auth/logout.php" class="sidebar-link text-accent hover:bg-red-50/50">
            <i class="fas fa-sign-out-alt w-5"></i> Logout
        </a>
    </div>
</aside>

<!-- ===== OVERLAY MOBILE ===== -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

<!-- ===== TOP BAR (mobile) ===== -->
<div class="lg:hidden fixed top-0 left-0 right-0 z-20 bg-white/90 backdrop-blur-2xl border-b border-white/50 px-4 h-14 flex items-center justify-between shadow-sm">
    <button onclick="toggleSidebar()" class="w-9 h-9 rounded-xl bg-soft flex items-center justify-center text-navy hover:bg-primary/10 transition">
        <i class="fas fa-bars"></i>
    </button>
    <div class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-xl bg-gradient-to-br <?= $role_class ?> text-white flex items-center justify-center text-xs font-bold"><?= $user_initial ?></span>
        <span class="text-sm font-bold text-navy"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></span>
    </div>
    <div class="w-9"></div>
</div>

<!-- ===== MAIN CONTENT ===== -->
<main class="flex-1 min-h-screen bg-soft pt-14 lg:pt-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

<?php else: ?>
<main class="flex-1 min-h-screen bg-soft">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
<?php endif; ?>

<?php
$alert_html = showAlert();
if ($alert_html):
?>
<div class="mb-6 animate-fadeIn"><?= $alert_html ?></div>
<?php endif; ?>

<style>
.animate-fadeIn { animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.sidebar-link {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.7rem 0.875rem; border-radius: 14px;
    font-size: 0.9rem; font-weight: 600; color: #2B3674;
    transition: all 0.2s ease; text-decoration: none;
}
.sidebar-link:hover { background: rgba(67,24,255,0.06); color: #4318FF; }
.sidebar-link.active { background: linear-gradient(135deg, rgba(67,24,255,0.1), rgba(110,56,247,0.06)); color: #4318FF; }
.sidebar-link.active i { color: #4318FF; }
.sidebar-link i { width: 1.25rem; color: #A3AED0; transition: color 0.2s; }
.sidebar-link:hover i { color: #4318FF; }
</style>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('sidebarOverlay').classList.toggle('hidden');
}
</script>
