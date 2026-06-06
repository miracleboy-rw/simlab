<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('mahasiswa')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Dashboard Mahasiswa';
$user_id = $_SESSION['user_id'];
include '../includes/header.php';

$peminjaman = fetchAll("SELECT p.* FROM peminjaman p WHERE p.user_id = ? ORDER BY p.created_at DESC LIMIT 5", [$user_id]);
$total_pinjam = getCount('peminjaman', "user_id = ?", [$user_id]);
$total_pending = getCount('peminjaman', "user_id = ? AND status = 'Pending'", [$user_id]);
$total_approved = getCount('peminjaman', "user_id = ? AND status = 'Approved'", [$user_id]);
$total_ditolak = getCount('peminjaman', "user_id = ? AND status = 'Rejected'", [$user_id]);
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-user-graduate mr-3"></i> Dashboard Mahasiswa</h1>
    <p class="page-subtitle">Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?></p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="stat-icon" style="background:rgba(67,24,255,0.1);color:#4318FF;"><i class="fas fa-box"></i></div>
            <div>
                <div class="stat-value"><?= $total_pinjam ?></div>
                <div class="stat-label">Total Peminjaman</div>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="stat-icon" style="background:rgba(255,184,0,0.1);color:#B87A00;"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <div class="stat-value"><?= $total_pending ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="stat-icon" style="background:rgba(5,205,153,0.1);color:#05CD99;"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-value"><?= $total_approved ?></div>
                <div class="stat-label">Disetujui</div>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="stat-icon" style="background:rgba(255,91,117,0.1);color:#FF5B75;"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="stat-value"><?= $total_ditolak ?></div>
                <div class="stat-label">Ditolak</div>
            </div>
        </div>
    </div>
</div>

<div class="glass-card p-6">
    <div class="flex items-center justify-between mb-4">
        <h5 class="font-bold text-navy"><i class="fas fa-clock mr-2"></i> Riwayat Peminjaman</h5>
        <a href="form_peminjaman.php" class="btn-glass btn-glass-primary btn-sm"><i class="fas fa-plus mr-1"></i> Pinjam Alat</a>
    </div>
    <?php if (empty($peminjaman)): ?>
    <p class="text-muted">Belum ada peminjaman.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="glass-table">
            <thead><tr>
                <th>Kode</th><th>Tanggal Pinjam</th><th>Tanggal Kembali</th><th>Tujuan</th><th>Status</th><th>Aksi</th>
            </tr></thead>
            <tbody>
                <?php foreach ($peminjaman as $p): ?>
                <tr>
                    <td><span class="glass-badge badge-dark"><?= htmlspecialchars($p['kode_peminjaman']) ?></span></td>
                    <td><?= formatTanggalIndo($p['tgl_pinjam']) ?></td>
                    <td><?= formatTanggalIndo($p['tgl_kembali']) ?></td>
                    <td class="max-w-[150px] truncate"><?= htmlspecialchars($p['tujuan']) ?></td>
                    <td><?= statusBadge($p['status']) ?></td>
                    <td><a href="tracking_status.php" class="btn-glass btn-glass-outline btn-sm">Detail</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
