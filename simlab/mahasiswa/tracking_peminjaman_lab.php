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
    <h1 class="page-title"><span class="material-symbols-outlined" style="color:#2a4dd7;margin-right:12px">search</span>Tracking Peminjaman Lab</h1>
    <p class="text-muted">Pantau status pengajuan peminjaman laboratorium Anda</p>
</div>

<div class="grid-4" style="margin-bottom:24px">
    <div class="card stat-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#2a4dd7"><?= $counts['total'] ?></div><div class="stat-card-title">Total</div></div>
    <div class="card stat-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#D97706"><?= $counts['pending'] ?></div><div class="stat-card-title">Pending</div></div>
    <div class="card stat-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#16A34A"><?= $counts['approved'] ?></div><div class="stat-card-title">Disetujui</div></div>
    <div class="card stat-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#DC2626"><?= $counts['rejected'] ?></div><div class="stat-card-title">Ditolak</div></div>
</div>

<div class="card p-5">
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px">
        <h3 class="section-header" style="margin-bottom:0"><span class="material-symbols-outlined" style="margin-right:8px">list</span>Riwayat Peminjaman Lab</h3>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
            <select class="form-input" style="width:auto;padding:4px 12px;font-size:12px;height:auto" onchange="window.location='?status='+this.value">
                <option value="">Semua Status</option>
                <option value="Pending" <?= $status_filter=='Pending'?'selected':'' ?>>Pending</option>
                <option value="Approved" <?= $status_filter=='Approved'?'selected':'' ?>>Approved</option>
                <option value="Rejected" <?= $status_filter=='Rejected'?'selected':'' ?>>Rejected</option>
            </select>
            <a href="form_peminjaman_lab.php" class="btn btn-primary btn-sm"><span class="material-symbols-outlined" style="font-size:16px;margin-right:4px">add</span> Pinjam Lab</a>
        </div>
    </div>
    <?php if (empty($list)): ?>
    <div class="text-center" style="padding:32px 0;color:#6B7280"><span class="material-symbols-outlined" style="font-size:40px;display:block;margin-bottom:12px">inbox</span>Belum ada peminjaman lab.</div>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>Kode</th><th>Laboratorium</th><th>Tujuan</th><th>Tanggal</th><th>Jam</th><th>Status</th>
            </tr></thead>
            <tbody>
                <?php foreach ($list as $pl): ?>
                <tr>
                    <td><span class="badge" style="background:#E5E7EB;color:#374151"><?= htmlspecialchars($pl['kode_peminjaman']) ?></span></td>
                    <td><?= htmlspecialchars($pl['nama_lab']) ?></td>
                    <td><span class="badge" style="background:#DBEAFE;color:#2563EB"><?= htmlspecialchars($pl['tujuan_peminjaman']) ?></span></td>
                    <td><?= formatTanggalIndo($pl['tgl_pinjam']) ?> - <?= formatTanggalIndo($pl['tgl_kembali']) ?></td>
                    <td><?= date('H:i', strtotime($pl['jam_mulai'])) ?> - <?= date('H:i', strtotime($pl['jam_selesai'])) ?></td>
                    <td><?= statusBadge($pl['status']) ?>
                        <?php if ($pl['status'] == 'Rejected' && $pl['alasan_penolakan']): ?>
                        <div style="font-size:11px;color:#DC2626;margin-top:4px"><?= htmlspecialchars($pl['alasan_penolakan']) ?></div>
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
