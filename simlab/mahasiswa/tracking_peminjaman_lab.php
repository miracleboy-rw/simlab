<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('mahasiswa')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Tracking Peminjaman Lab';
$user_id = $_SESSION['user_id'];
include '../includes/header.php';

$status_filter = $_GET['status'] ?? '';
$sql = "SELECT pl.*, l.nama_lab, l.kode_lab, l.lokasi
        FROM peminjaman_lab pl
        JOIN laboratorium l ON pl.lab_id = l.id
        WHERE pl.user_id = ?";
$params = [$user_id];
if ($status_filter) { $sql .= " AND pl.status = ?"; $params[] = $status_filter; }
$sql .= " ORDER BY pl.created_at DESC";
$list = fetchAll($sql, $params);

$counts = [
    'total' => count($list),
    'pending' => count(array_filter($list, fn($v) => $v['status']=='Pending')),
    'approved' => count(array_filter($list, fn($v) => $v['status']=='Approved')),
    'rejected' => count(array_filter($list, fn($v) => $v['status']=='Rejected')),
];
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-search text-primary mr-3"></i>Tracking Peminjaman Lab</h1>
    <p class="page-subtitle">Pantau status pengajuan peminjaman laboratorium Anda</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="stat-card text-center"><div class="stat-value text-primary"><?= $counts['total'] ?></div><div class="stat-label">Total</div></div>
    <div class="stat-card text-center"><div class="stat-value text-amber-500"><?= $counts['pending'] ?></div><div class="stat-label">Pending</div></div>
    <div class="stat-card text-center"><div class="stat-value text-emerald-500"><?= $counts['approved'] ?></div><div class="stat-label">Disetujui</div></div>
    <div class="stat-card text-center"><div class="stat-value text-accent"><?= $counts['rejected'] ?></div><div class="stat-label">Ditolak</div></div>
</div>

<div class="glass-card p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <h3 class="font-bold text-navy"><i class="fas fa-list mr-2"></i>Riwayat Peminjaman Lab</h3>
        <div class="flex flex-wrap gap-2">
            <select class="glass-input !py-1.5 w-full sm:!w-auto text-sm" onchange="window.location='?status='+this.value">
                <option value="">Semua Status</option>
                <option value="Pending" <?= $status_filter=='Pending'?'selected':'' ?>>Pending</option>
                <option value="Approved" <?= $status_filter=='Approved'?'selected':'' ?>>Approved</option>
                <option value="Rejected" <?= $status_filter=='Rejected'?'selected':'' ?>>Rejected</option>
            </select>
            <a href="form_peminjaman_lab.php" class="btn-glass btn-glass-primary btn-sm"><i class="fas fa-plus mr-1"></i> Pinjam Lab</a>
        </div>
    </div>
    <?php if (empty($list)): ?>
    <div class="text-center py-8 text-muted"><i class="fas fa-inbox text-4xl mb-3 block"></i>Belum ada peminjaman lab.</div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="glass-table">
            <thead><tr>
                <th>Kode</th><th>Laboratorium</th><th>Tujuan</th><th>Tanggal</th><th>Jam</th><th>Status</th>
            </tr></thead>
            <tbody>
                <?php foreach ($list as $pl): ?>
                <tr>
                    <td><span class="glass-badge badge-dark"><?= htmlspecialchars($pl['kode_peminjaman']) ?></span></td>
                    <td><?= htmlspecialchars($pl['nama_lab']) ?></td>
                    <td><span class="glass-badge badge-info"><?= htmlspecialchars($pl['tujuan_peminjaman']) ?></span></td>
                    <td><?= formatTanggalIndo($pl['tgl_pinjam']) ?> - <?= formatTanggalIndo($pl['tgl_kembali']) ?></td>
                    <td><?= date('H:i', strtotime($pl['jam_mulai'])) ?> - <?= date('H:i', strtotime($pl['jam_selesai'])) ?></td>
                    <td><?= statusBadge($pl['status']) ?>
                        <?php if ($pl['status'] == 'Rejected' && $pl['alasan_penolakan']): ?>
                        <div class="text-xs text-accent mt-1"><?= htmlspecialchars($pl['alasan_penolakan']) ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
