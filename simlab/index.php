<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';
if (!isLoggedIn()) { redirect('auth/login.php'); }
$base_url = '';
$page_title = 'Dashboard';
$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];
include 'includes/header.php';

$total_alat = getCount('alat');
$total_bahan = getCount('bahan_habis_pakai');
$total_peminjaman = getCount('peminjaman');
$total_pending = getCount('peminjaman', "status = 'Pending'");
$total_kerusakan = getCount('laporan_kerusakan');
?>
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Selamat datang, <span class="text-primary font-semibold"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></span> &bull; <?= ucfirst($role) ?></p>
    </div>
    <div class="hidden sm:flex gap-2">
        <span class="glass-badge badge-primary"><i class="far fa-clock mr-1"></i> <?= date('d M Y') ?></span>
    </div>
</div>

<div class="grid-4 mb-8">
    <div class="stat-card flex items-center gap-4">
        <div class="stat-icon bg-gradient-to-br from-primary/10 to-secondary/10 text-primary"><i class="fas fa-microscope"></i></div>
        <div><div class="stat-value"><?= $total_alat ?></div><div class="stat-label">Total Alat</div></div>
    </div>
    <div class="stat-card flex items-center gap-4">
        <div class="stat-icon bg-gradient-to-br from-green-100 to-emerald-50 text-emerald-500"><i class="fas fa-vial"></i></div>
        <div><div class="stat-value"><?= $total_bahan ?></div><div class="stat-label">Bahan Habis Pakai</div></div>
    </div>
    <div class="stat-card flex items-center gap-4">
        <div class="stat-icon bg-gradient-to-br from-amber-100 to-yellow-50 text-amber-500"><i class="fas fa-exchange-alt"></i></div>
        <div><div class="stat-value"><?= $total_peminjaman ?></div><div class="stat-label">Total Peminjaman</div></div>
    </div>
    <div class="stat-card flex items-center gap-4">
        <div class="stat-icon bg-gradient-to-br from-rose-100 to-pink-50 text-accent"><i class="fas fa-exclamation-triangle"></i></div>
        <div><div class="stat-value"><?= $total_kerusakan ?></div><div class="stat-label">Laporan Kerusakan</div></div>
    </div>
</div>

<?php if ($role === 'mahasiswa'):
    $peminjaman_saya = getCount('peminjaman', "user_id = ?", [$user_id]);
    $notifikasi = fetchAll("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$user_id]);
?>
<div class="grid-2">
    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg text-navy"><i class="fas fa-history text-primary mr-2"></i>Peminjaman Saya</h3>
            <span class="text-3xl font-extrabold text-primary"><?= $peminjaman_saya ?></span>
        </div>
        <p class="text-muted text-sm mb-4">Total peminjaman yang pernah diajukan</p>
        <a href="mahasiswa/tracking_status.php" class="btn-glass btn-glass-primary btn-sm">Lihat Status</a>
    </div>
    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg text-navy"><i class="fas fa-bell text-secondary mr-2"></i>Notifikasi</h3>
            <?php if (!empty($notifikasi)): ?><span class="glass-badge badge-primary"><?= count($notifikasi) ?> baru</span><?php endif; ?>
        </div>
        <?php if (empty($notifikasi)): ?>
            <p class="text-muted text-sm">Tidak ada notifikasi</p>
        <?php else: ?>
            <div class="space-y-2"><?php foreach ($notifikasi as $n): ?>
                <div class="p-3 rounded-2xl bg-soft/50"><p class="font-semibold text-sm text-navy"><?= htmlspecialchars($n['judul']) ?></p><p class="text-xs text-muted"><?= htmlspecialchars($n['pesan']) ?></p></div>
            <?php endforeach; ?></div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($role === 'laboran'):
    $pending_count = getCount('peminjaman', "status = 'Pending'");
    $bahan_alert = fetchAll("SELECT * FROM bahan_habis_pakai WHERE stok <= stok_minimum");
?>
<div class="grid-2">
    <div class="glass-card p-6 border-l-4 border-l-amber-400">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg text-navy"><i class="fas fa-hourglass-half text-amber-500 mr-2"></i>Perlu Verifikasi</h3>
            <span class="text-3xl font-extrabold text-amber-500"><?= $pending_count ?></span>
        </div>
        <?php if ($pending_count > 0): ?><p class="text-muted text-sm mb-4">Terdapat <strong><?= $pending_count ?></strong> peminjaman yang perlu diverifikasi.</p>
        <a href="laboran/verifikasi_peminjaman.php" class="btn-glass btn-glass-warning btn-sm">Verifikasi Sekarang</a>
        <?php else: ?><p class="text-muted text-sm">Tidak ada peminjaman pending.</p><?php endif; ?>
    </div>
    <div class="glass-card p-6 border-l-4 border-l-accent">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg text-navy"><i class="fas fa-exclamation-diamond text-accent mr-2"></i>Alert Stok Bahan</h3>
            <?php if (!empty($bahan_alert)): ?><span class="glass-badge badge-danger"><?= count($bahan_alert) ?> kritis</span><?php endif; ?>
        </div>
        <?php if (empty($bahan_alert)): ?><p class="text-muted text-sm">Semua stok bahan dalam keadaan aman.</p>
        <?php else: ?><div class="space-y-2"><?php foreach ($bahan_alert as $b): ?>
            <div class="flex items-center justify-between p-3 rounded-2xl bg-red-50/50"><span class="font-medium text-sm text-navy"><?= htmlspecialchars($b['nama_bahan']) ?></span><span class="glass-badge badge-danger">Stok: <?= (int)$b['stok'] ?> <?= htmlspecialchars($b['satuan']) ?></span></div>
        <?php endforeach; ?></div><?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($role === 'dosen'):
    $pending_ta = getCount('verifikasi_ta', "status = 'Pending'");
    $mahasiswa_aktif = getCount('peminjaman', "status = 'Approved'");
?>
<div class="grid-3">
    <div class="glass-card p-6">
        <h3 class="font-bold text-lg text-navy mb-3"><i class="fas fa-graduation-cap text-info mr-2"></i>Verifikasi TA</h3>
        <div class="text-4xl font-extrabold text-secondary mb-2"><?= $pending_ta ?></div>
        <p class="text-muted text-sm mb-4">Permintaan verifikasi riset menunggu</p>
        <a href="dosen/verifikasi_riset.php" class="btn-glass btn-glass-outline btn-sm">Lihat</a>
    </div>
    <div class="glass-card p-6">
        <h3 class="font-bold text-lg text-navy mb-3"><i class="fas fa-users text-emerald-500 mr-2"></i>Mahasiswa Aktif</h3>
        <div class="text-4xl font-extrabold text-emerald-500 mb-2"><?= $mahasiswa_aktif ?></div>
        <p class="text-muted text-sm">Mahasiswa dengan peminjaman aktif</p>
    </div>
    <div class="glass-card p-6">
        <h3 class="font-bold text-lg text-navy mb-3"><i class="fas fa-file-export text-primary mr-2"></i>Export Data</h3>
        <p class="text-muted text-sm mb-4">Export data inventaris dan aktivitas lab</p>
        <a href="dosen/export.php" class="btn-glass btn-glass-primary btn-sm">Export Excel/PDF</a>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
