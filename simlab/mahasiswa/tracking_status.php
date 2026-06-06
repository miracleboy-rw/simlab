<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('mahasiswa')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Tracking Status Peminjaman';
$user_id = $_SESSION['user_id'];
include '../includes/header.php';

$status_filter = $_GET['status'] ?? '';

$sql = "SELECT p.*, GROUP_CONCAT(a.nama_alat SEPARATOR ', ') as alat_dipinjam
        FROM peminjaman p
        LEFT JOIN peminjaman_items pi ON p.id = pi.peminjaman_id
        LEFT JOIN alat a ON pi.alat_id = a.id
        WHERE p.user_id = ?";
$params = [$user_id];
if ($status_filter) {
    $sql .= " AND p.status = ?";
    $params[] = $status_filter;
}
$sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
$peminjaman_list = fetchAll($sql, $params);

$dokumen_list = fetchAll("SELECT dp.*, p.kode_peminjaman FROM dokumen_pendukung dp
                          JOIN peminjaman p ON dp.peminjaman_id = p.id
                          WHERE dp.user_id = ? ORDER BY dp.uploaded_at DESC", [$user_id]);
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="page-title"><i class="fas fa-search mr-3"></i> Tracking Status Peminjaman</h1>
        <p class="page-subtitle">Pantau status pengajuan peminjaman alat</p>
    </div>
    <div class="sm:w-56">
        <form method="GET">
            <select name="status" class="glass-input" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Pending" <?= $status_filter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Approved" <?= $status_filter == 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Rejected" <?= $status_filter == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="Returned" <?= $status_filter == 'Returned' ? 'selected' : '' ?>>Returned</option>
                <option value="Overdue" <?= $status_filter == 'Overdue' ? 'selected' : '' ?>>Overdue</option>
            </select>
        </form>
    </div>
</div>

<div class="glass-card p-6 mb-6">
    <h5 class="font-bold text-navy mb-4"><i class="fas fa-list mr-2"></i> Daftar Peminjaman</h5>
    <?php if (empty($peminjaman_list)): ?>
    <p class="text-muted">Belum ada data peminjaman.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="glass-table">
            <thead>
                <tr>
                    <th>Kode</th><th>Alat</th><th>Tanggal Pinjam</th><th>Tanggal Kembali</th><th>Status</th><th>Alasan (jika ditolak)</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($peminjaman_list as $p): ?>
                <tr>
                    <td><span class="glass-badge badge-dark"><?= htmlspecialchars($p['kode_peminjaman']) ?></span></td>
                    <td class="max-w-[200px] truncate"><?= htmlspecialchars($p['alat_dipinjam']) ?></td>
                    <td><?= formatTanggalIndo($p['tgl_pinjam']) ?></td>
                    <td><?= formatTanggalIndo($p['tgl_kembali']) ?></td>
                    <td><?= statusBadge($p['status']) ?></td>
                    <td><?= htmlspecialchars($p['alasan_penolakan'] ?: '-') ?></td>
                    <td>
                        <?php if ($p['status'] == 'Pending'): ?>
                        <span class="text-yellow-600 text-sm"><i class="fas fa-hourglass-half mr-1"></i> Menunggu verifikasi</span>
                        <?php elseif ($p['status'] == 'Approved'): ?>
                        <span class="text-green-600 text-sm"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>
                        <?php elseif ($p['status'] == 'Rejected'): ?>
                        <span class="text-red-500 text-sm"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                        <?php elseif ($p['status'] == 'Returned'): ?>
                        <span class="text-blue-600 text-sm"><i class="fas fa-undo mr-1"></i> Dikembalikan</span>
                        <?php elseif ($p['status'] == 'Overdue'): ?>
                        <span class="text-gray-700 text-sm"><i class="fas fa-exclamation-triangle mr-1"></i> Terlambat</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="glass-card p-6">
    <h5 class="font-bold text-navy mb-4"><i class="fas fa-file-pdf mr-2"></i> Dokumen Terupload</h5>
    <?php if (empty($dokumen_list)): ?>
    <p class="text-muted">Belum ada dokumen diupload.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="glass-table">
            <thead><tr><th>Peminjaman</th><th>Nama File</th><th>Tipe</th><th>Tanggal Upload</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($dokumen_list as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['kode_peminjaman']) ?></td>
                    <td><?= htmlspecialchars($d['nama_file']) ?></td>
                    <td><span class="glass-badge badge-info"><?= str_replace('_', ' ', ucfirst($d['tipe'])) ?></span></td>
                    <td><?= formatTanggalIndo($d['uploaded_at']) ?></td>
                    <td><a href="../uploads/dokumen/<?= $d['file_path'] ?>" class="btn-glass btn-glass-outline btn-sm" target="_blank"><i class="fas fa-eye"></i></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
