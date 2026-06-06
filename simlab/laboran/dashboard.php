<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Dashboard Laboran';
include '../includes/header.php';

$total_alat = getCount('alat');
$total_bahan = getCount('bahan_habis_pakai');
$pending_count = getCount('peminjaman', "status = 'Pending'");
$approved_count = getCount('peminjaman', "status = 'Approved'");
$rusak_count = getCount('alat', "status = 'Rusak'");
$bahan_alert = fetchAll("SELECT * FROM bahan_habis_pakai WHERE stok <= stok_minimum");

$recent_peminjaman = fetchAll("SELECT p.*, u.nama_lengkap FROM peminjaman p
                               JOIN users u ON p.user_id = u.id
                               WHERE p.status = 'Pending'
                               ORDER BY p.created_at DESC LIMIT 5");
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-tools mr-3"></i> Dashboard Laboran</h1>
    <p class="page-subtitle">Panel manajemen laboratorium</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="stat-card text-center">
        <div class="stat-value text-primary"><?= $total_alat ?></div>
        <div class="stat-label">Total Alat</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value" style="color:#05CD99"><?= $total_bahan ?></div>
        <div class="stat-label">Bahan Habis</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value" style="color:#B87A00"><?= $pending_count ?></div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value" style="color:#6E38F7"><?= $approved_count ?></div>
        <div class="stat-label">Disetujui</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value text-red-500"><?= $rusak_count ?></div>
        <div class="stat-label">Alat Rusak</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value text-gray-600"><?= count($bahan_alert) ?></div>
        <div class="stat-label">Alert Bahan</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-7 gap-6">
    <div class="lg:col-span-4">
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-navy"><i class="fas fa-hourglass-half mr-2"></i> Peminjaman Perlu Verifikasi</h5>
                <a href="verifikasi_peminjaman.php" class="btn-glass btn-glass-warning btn-sm">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="glass-table">
                    <thead><tr><th>Kode</th><th>Peminjam</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php if (empty($recent_peminjaman)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Tidak ada peminjaman pending</td></tr>
                        <?php else: ?>
                        <?php foreach ($recent_peminjaman as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['kode_peminjaman']) ?></td>
                            <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                            <td><?= formatTanggalIndo($p['created_at']) ?></td>
                            <td><?= statusBadge($p['status']) ?></td>
                            <td><a href="verifikasi_peminjaman.php?id=<?= $p['id'] ?>" class="btn-glass btn-glass-primary btn-sm">Verifikasi</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="lg:col-span-3">
        <div class="glass-card p-6 border-2" style="border-color:rgba(255,91,117,0.3);">
            <h5 class="font-bold text-red-500 mb-4"><i class="fas fa-exclamation-diamond mr-2"></i> Alert Stok Minimal</h5>
            <?php if (empty($bahan_alert)): ?>
            <p class="text-muted">Semua stok aman.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="glass-table">
                    <thead><tr><th>Bahan</th><th>Stok</th><th>Minimal</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($bahan_alert as $b): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['nama_bahan']) ?></td>
                            <td><?= (int)$b['stok'] ?> <?= htmlspecialchars($b['satuan']) ?></td>
                            <td><?= (int)$b['stok_minimum'] ?> <?= htmlspecialchars($b['satuan']) ?></td>
                            <td><span class="glass-badge badge-danger">Kritis</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <div class="mt-4">
                <a href="manajemen_bahan.php" class="btn-glass btn-glass-danger btn-sm w-full text-center">Kelola Stok Bahan</a>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
