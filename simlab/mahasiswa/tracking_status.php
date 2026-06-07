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
<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:24px;gap:16px">
    <div>
        <h1 class="page-title"><span class="material-symbols-outlined" style="margin-right:12px">search</span> Tracking Status Peminjaman</h1>
        <p class="text-muted">Pantau status pengajuan peminjaman alat</p>
    </div>
    <div>
        <form method="GET">
            <select name="status" class="form-input" onchange="this.form.submit()">
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

<div class="card p-5 mb-6">
    <h5 class="section-header"><span class="material-symbols-outlined" style="margin-right:8px">list</span> Daftar Peminjaman</h5>
    <?php if (empty($peminjaman_list)): ?>
    <p class="text-muted">Belum ada data peminjaman.</p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Kode</th><th>Alat</th><th>Tanggal Pinjam</th><th>Tanggal Kembali</th><th>Status</th><th>Alasan (jika ditolak)</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($peminjaman_list as $p): ?>
                <tr>
                    <td><span class="badge" style="background:#E5E7EB;color:#374151"><?= htmlspecialchars($p['kode_peminjaman']) ?></span></td>
                    <td style="max-width:200px" class="truncate"><?= htmlspecialchars($p['alat_dipinjam']) ?></td>
                    <td><?= formatTanggalIndo($p['tgl_pinjam']) ?></td>
                    <td><?= formatTanggalIndo($p['tgl_kembali']) ?></td>
                    <td><?= statusBadge($p['status']) ?></td>
                    <td><?= htmlspecialchars($p['alasan_penolakan'] ?: '-') ?></td>
                    <td>
                        <?php if ($p['status'] == 'Pending'): ?>
                        <span style="color:#D97706;font-size:12px"><span class="material-symbols-outlined" style="margin-right:4px;font-size:14px">hourglass_empty</span> Menunggu verifikasi</span>
                        <?php elseif ($p['status'] == 'Approved'): ?>
                        <span style="color:#16A34A;font-size:12px"><span class="material-symbols-outlined" style="margin-right:4px;font-size:14px">check_circle</span> Disetujui</span>
                        <?php elseif ($p['status'] == 'Rejected'): ?>
                        <span style="color:#DC2626;font-size:12px"><span class="material-symbols-outlined" style="margin-right:4px;font-size:14px">cancel</span> Ditolak</span>
                        <?php elseif ($p['status'] == 'Returned'): ?>
                        <span style="color:#2563EB;font-size:12px"><span class="material-symbols-outlined" style="margin-right:4px;font-size:14px">undo</span> Dikembalikan</span>
                        <?php elseif ($p['status'] == 'Overdue'): ?>
                        <span style="color:#374151;font-size:12px"><span class="material-symbols-outlined" style="margin-right:4px;font-size:14px">warning</span> Terlambat</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="card p-5">
    <h5 class="section-header"><span class="material-symbols-outlined" style="margin-right:8px">picture_as_pdf</span> Dokumen Terupload</h5>
    <?php if (empty($dokumen_list)): ?>
    <p class="text-muted">Belum ada dokumen diupload.</p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Peminjaman</th><th>Nama File</th><th>Tipe</th><th>Tanggal Upload</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($dokumen_list as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['kode_peminjaman']) ?></td>
                    <td><?= htmlspecialchars($d['nama_file']) ?></td>
                    <td><span class="badge" style="background:#DBEAFE;color:#2563EB"><?= str_replace('_', ' ', ucfirst($d['tipe'])) ?></span></td>
                    <td><?= formatTanggalIndo($d['uploaded_at']) ?></td>
                    <td><a href="../uploads/dokumen/<?= $d['file_path'] ?>" class="btn btn-outline btn-sm" target="_blank"><span class="material-symbols-outlined">visibility</span></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
