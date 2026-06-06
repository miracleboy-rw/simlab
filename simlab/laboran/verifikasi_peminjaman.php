<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Verifikasi Peminjaman';
$laboran_id = $_SESSION['user_id'];
include '../includes/header.php';

if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    $alasan = $_POST['alasan_penolakan'] ?? '';

    if ($action == 'approve') {
        $peminjaman = fetchOne("SELECT * FROM peminjaman WHERE id = ?", [$id]);
        if ($peminjaman && $peminjaman['status'] == 'Pending') {
            query("UPDATE peminjaman SET status = 'Approved' WHERE id = ?", [$id]);
            $items = fetchAll("SELECT * FROM peminjaman_items WHERE peminjaman_id = ?", [$id]);
            foreach ($items as $item) {
                query("UPDATE alat SET stok_tersedia = stok_tersedia - ?, status = CASE WHEN stok_tersedia - ? <= 0 THEN 'Dipinjam' ELSE status END WHERE id = ?",
                       [$item['jumlah'], $item['jumlah'], $item['alat_id']]);
            }
            query("INSERT INTO kalender_events (peminjaman_id, judul, tgl_mulai, tgl_selesai, tipe, warna) VALUES (?, ?, ?, ?, 'Praktikum', '#28a745')",
                   [$id, 'Peminjaman: ' . $peminjaman['kode_peminjaman'], $peminjaman['tgl_pinjam'], $peminjaman['tgl_kembali']]);
            query("INSERT INTO notifikasi (user_id, judul, pesan) VALUES (?, 'Peminjaman Disetujui', 'Peminjaman {$peminjaman['kode_peminjaman']} telah disetujui.')",
                   [$peminjaman['user_id']]);
            alert('success', 'Peminjaman berhasil disetujui!');
        }
    } elseif ($action == 'reject' && $alasan) {
        $peminjaman = fetchOne("SELECT * FROM peminjaman WHERE id = ?", [$id]);
        if ($peminjaman && $peminjaman['status'] == 'Pending') {
            query("UPDATE peminjaman SET status = 'Rejected', alasan_penolakan = ? WHERE id = ?", [$alasan, $id]);
            query("INSERT INTO notifikasi (user_id, judul, pesan) VALUES (?, 'Peminjaman Ditolak', 'Peminjaman {$peminjaman['kode_peminjaman']} ditolak. Alasan: $alasan')",
                   [$peminjaman['user_id']]);
            alert('success', 'Peminjaman ditolak.');
        }
    } elseif ($action == 'return') {
        $peminjaman = fetchOne("SELECT * FROM peminjaman WHERE id = ?", [$id]);
        if ($peminjaman && ($peminjaman['status'] == 'Approved' || $peminjaman['status'] == 'Overdue')) {
            query("UPDATE peminjaman SET status = 'Returned' WHERE id = ?", [$id]);
            $items = fetchAll("SELECT * FROM peminjaman_items WHERE peminjaman_id = ?", [$id]);
            foreach ($items as $item) {
                query("UPDATE alat SET stok_tersedia = stok_tersedia + ?, status = 'Tersedia' WHERE id = ?",
                       [$item['jumlah'], $item['alat_id']]);
            }
            alert('success', 'Alat berhasil dikembalikan!');
        }
    } elseif ($action == 'overdue') {
        query("UPDATE peminjaman SET status = 'Overdue' WHERE id = ? AND status = 'Approved'", [$id]);
        alert('warning', 'Status diubah menjadi Overdue.');
    }
    redirect('verifikasi_peminjaman.php');
}

$filter_status = $_GET['status'] ?? '';
$sql = "SELECT p.*, u.nama_lengkap, u.nim_nidn, GROUP_CONCAT(a.nama_alat SEPARATOR ', ') as alat_dipinjam
        FROM peminjaman p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN peminjaman_items pi ON p.id = pi.peminjaman_id
        LEFT JOIN alat a ON pi.alat_id = a.id";
$params = [];
if ($filter_status) {
    $sql .= " WHERE p.status = ?";
    $params[] = $filter_status;
}
$sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
$peminjaman_list = fetchAll($sql, $params);

$detail_id = $_GET['id'] ?? null;
$detail = null;
$detail_items = [];
$detail_dokumen = [];
if ($detail_id) {
    $detail = fetchOne("SELECT p.*, u.nama_lengkap, u.nim_nidn FROM peminjaman p JOIN users u ON p.user_id = u.id WHERE p.id = ?", [$detail_id]);
    if ($detail) {
        $detail_items = fetchAll("SELECT pi.*, a.nama_alat, a.kode_alat FROM peminjaman_items pi JOIN alat a ON pi.alat_id = a.id WHERE pi.peminjaman_id = ?", [$detail_id]);
        $detail_dokumen = fetchAll("SELECT * FROM dokumen_pendukung WHERE peminjaman_id = ?", [$detail_id]);
    }
}
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-check-double mr-3"></i> Verifikasi & Approval Peminjaman</h1>
    <p class="page-subtitle">Tinjau, setujui, atau tolak pengajuan peminjaman</p>
</div>

<?php if ($detail && $detail['status'] == 'Pending'): ?>
<div class="glass-card p-6 mb-6 border-2" style="border-color:rgba(255,184,0,0.3);">
    <h5 class="font-bold text-yellow-700 mb-4"><i class="fas fa-pen mr-2"></i> Verifikasi Peminjaman: <?= htmlspecialchars($detail['kode_peminjaman']) ?></h5>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <table class="w-full text-sm">
                <tr><td class="font-semibold text-navy py-1 pr-4">Peminjam</td><td>: <?= htmlspecialchars($detail['nama_lengkap']) ?> (<?= htmlspecialchars($detail['nim_nidn']) ?>)</td></tr>
                <tr><td class="font-semibold text-navy py-1 pr-4">Kode</td><td>: <?= htmlspecialchars($detail['kode_peminjaman']) ?></td></tr>
                <tr><td class="font-semibold text-navy py-1 pr-4">Tanggal Pinjam</td><td>: <?= formatTanggalIndo($detail['tgl_pinjam']) ?></td></tr>
                <tr><td class="font-semibold text-navy py-1 pr-4">Tanggal Kembali</td><td>: <?= formatTanggalIndo($detail['tgl_kembali']) ?></td></tr>
                <tr><td class="font-semibold text-navy py-1 pr-4">Tujuan</td><td>: <?= htmlspecialchars($detail['tujuan']) ?></td></tr>
            </table>
        </div>
        <div>
            <h6 class="font-bold text-navy mb-2">Alat yang dipinjam:</h6>
            <ul class="list-disc list-inside text-sm space-y-1 mb-3">
                <?php foreach ($detail_items as $di): ?>
                <li><?= htmlspecialchars($di['nama_alat']) ?> (<?= htmlspecialchars($di['kode_alat']) ?>) - <?= (int)$di['jumlah'] ?> unit</li>
                <?php endforeach; ?>
            </ul>
            <?php if (!empty($detail_dokumen)): ?>
            <h6 class="font-bold text-navy mb-2">Dokumen Pendukung:</h6>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($detail_dokumen as $dd): ?>
                <a href="../uploads/dokumen/<?= $dd['file_path'] ?>" class="btn-glass btn-glass-outline btn-sm" target="_blank">
                    <i class="fas fa-file-pdf mr-1"></i> <?= htmlspecialchars($dd['nama_file']) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <hr class="divider my-4">
    <div class="flex flex-wrap gap-3">
        <a href="?action=approve&id=<?= $detail['id'] ?>" class="btn-glass btn-glass-success" onclick="return confirm('Setujui peminjaman ini?')">
            <i class="fas fa-check mr-2"></i> Approve
        </a>
        <form method="POST" action="?action=reject&id=<?= $detail['id'] ?>" class="flex flex-wrap gap-2" onsubmit="return confirm('Tolak peminjaman ini?')">
            <input type="text" name="alasan_penolakan" class="glass-input" placeholder="Alasan penolakan..." required style="min-width:250px">
            <button type="submit" class="btn-glass btn-glass-danger"><i class="fas fa-times mr-2"></i> Reject</button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="glass-card p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <h5 class="font-bold text-navy"><i class="fas fa-list mr-2"></i> Semua Peminjaman</h5>
        <form method="GET" class="sm:w-48">
            <select name="status" class="glass-input" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Approved" <?= $filter_status == 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Rejected" <?= $filter_status == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="Returned" <?= $filter_status == 'Returned' ? 'selected' : '' ?>>Returned</option>
                <option value="Overdue" <?= $filter_status == 'Overdue' ? 'selected' : '' ?>>Overdue</option>
            </select>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="glass-table">
            <thead><tr>
                <th>Kode</th><th>Peminjam</th><th>Alat</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Aksi</th>
            </tr></thead>
            <tbody>
                <?php if (empty($peminjaman_list)): ?>
                <tr><td colspan="7" class="text-center text-muted">Belum ada data</td></tr>
                <?php endif; ?>
                <?php foreach ($peminjaman_list as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['kode_peminjaman']) ?></td>
                    <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                    <td class="max-w-[150px] truncate"><?= htmlspecialchars($p['alat_dipinjam']) ?></td>
                    <td><?= formatTanggalIndo($p['tgl_pinjam']) ?></td>
                    <td><?= formatTanggalIndo($p['tgl_kembali']) ?></td>
                    <td><?= statusBadge($p['status']) ?></td>
                    <td>
                        <div class="flex gap-1">
                            <a href="?id=<?= $p['id'] ?>" class="btn-glass btn-glass-outline btn-sm"><i class="fas fa-eye"></i></a>
                            <?php if ($p['status'] == 'Pending'): ?>
                            <a href="?action=approve&id=<?= $p['id'] ?>" class="btn-glass btn-glass-success btn-sm" onclick="return confirm('Setujui?')"><i class="fas fa-check"></i></a>
                            <?php elseif ($p['status'] == 'Approved'): ?>
                            <a href="?action=return&id=<?= $p['id'] ?>" class="btn-glass btn-glass-primary btn-sm" onclick="return confirm('Konfirmasi pengembalian?')"><i class="fas fa-undo"></i></a>
                            <a href="?action=overdue&id=<?= $p['id'] ?>" class="btn-glass btn-glass-warning btn-sm" onclick="return confirm('Tandai terlambat?')"><i class="fas fa-exclamation-triangle"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
