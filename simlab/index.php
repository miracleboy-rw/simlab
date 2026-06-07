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
        <p class="text-muted text-sm mt-1">Selamat datang, <span class="text-primary font-semibold"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></span> &bull; <?= ucfirst($role) ?></p>
    </div>
    <div style="display:flex;align-items:center;gap:8px;padding:6px 12px;background:#f2f4f7;border-radius:8px;font-size:11px;color:#9CA3AF">
        <span class="material-symbols-outlined text-sm">calendar_today</span>
        <?= date('d M Y') ?>
    </div>
</div>

<div class="grid-4" style="margin-bottom:32px">
    <div class="card" style="display:flex;align-items:center;gap:16px;padding:20px">
        <div style="width:48px;height:48px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(42,77,215,0.1);color:#2a4dd7">
            <span class="material-symbols-outlined text-xl">biotech</span>
        </div>
        <div>
            <div class="stat-card-number"><?= $total_alat ?></div>
            <div class="stat-card-label">Total Alat</div>
        </div>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:16px;padding:20px">
        <div style="width:48px;height:48px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(34,197,94,0.1);color:#22C55E">
            <span class="material-symbols-outlined text-xl">science</span>
        </div>
        <div>
            <div class="stat-card-number"><?= $total_bahan ?></div>
            <div class="stat-card-label">Bahan Habis Pakai</div>
        </div>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:16px;padding:20px">
        <div style="width:48px;height:48px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(245,158,11,0.1);color:#F59E0B">
            <span class="material-symbols-outlined text-xl">swap_horiz</span>
        </div>
        <div>
            <div class="stat-card-number"><?= $total_peminjaman ?></div>
            <div class="stat-card-label">Total Peminjaman</div>
        </div>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:16px;padding:20px">
        <div style="width:48px;height:48px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(239,68,68,0.1);color:#EF4444">
            <span class="material-symbols-outlined text-xl">report</span>
        </div>
        <div>
            <div class="stat-card-number"><?= $total_kerusakan ?></div>
            <div class="stat-card-label">Laporan Kerusakan</div>
        </div>
    </div>
</div>

<?php if ($role === 'mahasiswa'):
    $peminjaman_saya = getCount('peminjaman', "user_id = ?", [$user_id]);
    $notifikasi = fetchAll("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$user_id]);
?>
<div class="grid-2">
    <div class="card" style="padding:20px">
        <div class="flex items-center justify-between mb-4">
            <h3 class="section-header flex items-center gap-2" style="margin-bottom:0">
                <span class="material-symbols-outlined text-primary text-xl">history</span>Peminjaman Saya
            </h3>
            <span class="text-3xl font-bold text-primary"><?= $peminjaman_saya ?></span>
        </div>
        <p class="text-muted2 text-sm mb-4">Total peminjaman yang pernah diajukan</p>
        <a href="mahasiswa/tracking_status.php" class="btn btn-primary text-sm">Lihat Status</a>
    </div>
    <div class="card" style="padding:20px">
        <div class="flex items-center justify-between mb-4">
            <h3 class="section-header flex items-center gap-2" style="margin-bottom:0">
                <span class="material-symbols-outlined text-secondary text-xl">notifications</span>Notifikasi
            </h3>
            <?php if (!empty($notifikasi)): ?><span class="badge" style="background:rgba(42,77,215,0.1);color:#2a4dd7"><?= count($notifikasi) ?> baru</span><?php endif; ?>
        </div>
        <?php if (empty($notifikasi)): ?>
            <p class="text-muted2 text-sm">Tidak ada notifikasi</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:8px"><?php foreach ($notifikasi as $n): ?>
                <div style="padding:12px;border-radius:8px;background:#f2f4f7"><p class="font-semibold text-sm text-navy"><?= htmlspecialchars($n['judul']) ?></p><p class="text-xs text-muted2"><?= htmlspecialchars($n['pesan']) ?></p></div>
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
    <div class="card" style="padding:20px;border-left:4px solid #F59E0B">
        <div class="flex items-center justify-between mb-4">
            <h3 class="section-header flex items-center gap-2" style="margin-bottom:0">
                <span class="material-symbols-outlined text-warning text-xl">hourglass_empty</span>Perlu Verifikasi
            </h3>
            <span class="text-3xl font-bold text-warning"><?= $pending_count ?></span>
        </div>
        <?php if ($pending_count > 0): ?><p class="text-muted2 text-sm mb-4">Terdapat <strong><?= $pending_count ?></strong> peminjaman yang perlu diverifikasi.</p>
        <a href="laboran/verifikasi_peminjaman.php" class="btn btn-warning text-sm">Verifikasi Sekarang</a>
        <?php else: ?><p class="text-muted2 text-sm">Tidak ada peminjaman pending.</p><?php endif; ?>
    </div>
    <div class="card" style="padding:20px;border-left:4px solid #EF4444">
        <div class="flex items-center justify-between mb-4">
            <h3 class="section-header flex items-center gap-2" style="margin-bottom:0">
                <span class="material-symbols-outlined text-danger text-xl">warning</span>Alert Stok Bahan
            </h3>
            <?php if (!empty($bahan_alert)): ?><span class="badge" style="background:rgba(239,68,68,0.1);color:#EF4444"><?= count($bahan_alert) ?> kritis</span><?php endif; ?>
        </div>
        <?php if (empty($bahan_alert)): ?><p class="text-muted2 text-sm">Semua stok bahan dalam keadaan aman.</p>
        <?php else: ?><div style="display:flex;flex-direction:column;gap:8px"><?php foreach ($bahan_alert as $b): ?>
            <div class="flex items-center justify-between" style="padding:12px;border-radius:8px;background:rgba(239,68,68,0.1)"><span style="font-weight:500;font-size:14px;color:#111827"><?= htmlspecialchars($b['nama_bahan']) ?></span><span class="badge" style="background:rgba(239,68,68,0.15);color:#EF4444">Stok: <?= (int)$b['stok'] ?> <?= htmlspecialchars($b['satuan']) ?></span></div>
        <?php endforeach; ?></div><?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($role === 'dosen'):
    $pending_ta = getCount('verifikasi_ta', "status = 'Pending'");
    $mahasiswa_aktif = getCount('peminjaman', "status = 'Approved'");
?>
<div class="grid-3">
    <div class="card" style="padding:20px">
        <h3 class="section-header flex items-center gap-2" style="margin-bottom:12px">
            <span class="material-symbols-outlined" style="color:#2563EB;font-size:20px">school</span>Verifikasi TA
        </h3>
        <div style="font-size:32px;font-weight:700;color:#6B7280;margin-bottom:8px"><?= $pending_ta ?></div>
        <p class="text-muted2 text-sm mb-4">Permintaan verifikasi riset menunggu</p>
        <a href="dosen/verifikasi_riset.php" class="btn btn-outline text-sm">Lihat</a>
    </div>
    <div class="card" style="padding:20px">
        <h3 class="section-header flex items-center gap-2" style="margin-bottom:12px">
            <span class="material-symbols-outlined text-success text-xl">group</span>Mahasiswa Aktif
        </h3>
        <div style="font-size:32px;font-weight:700;color:#22C55E;margin-bottom:8px"><?= $mahasiswa_aktif ?></div>
        <p class="text-muted2 text-sm">Mahasiswa dengan peminjaman aktif</p>
    </div>
    <div class="card" style="padding:20px">
        <h3 class="section-header flex items-center gap-2" style="margin-bottom:12px">
            <span class="material-symbols-outlined text-primary text-xl">file_upload</span>Export Data
        </h3>
        <p class="text-muted2 text-sm mb-4">Export data inventaris dan aktivitas lab</p>
        <a href="dosen/export.php" class="btn btn-primary text-sm">Export Excel/PDF</a>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
