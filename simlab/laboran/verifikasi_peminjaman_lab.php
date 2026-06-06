<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Verifikasi Peminjaman Lab';
include '../includes/header.php';

// Proses approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];
    $alasan = $_POST['alasan_penolakan'] ?? '';

    $p = fetchOne("SELECT pl.*, l.nama_lab FROM peminjaman_lab pl JOIN laboratorium l ON pl.lab_id = l.id WHERE pl.id = ?", [$id]);
    if (!$p) { alert('danger', 'Data tidak ditemukan!'); redirect('verifikasi_peminjaman_lab.php'); }

    if ($action == 'approve') {
        query("UPDATE peminjaman_lab SET status = 'Approved' WHERE id = ?", [$id]);
        query("UPDATE laboratorium SET status = 'Digunakan' WHERE id = ?", [$p['lab_id']]);
        query("INSERT INTO notifikasi (user_id, judul, pesan) VALUES (?, 'Peminjaman Lab Disetujui', 'Peminjaman {$p['kode_peminjaman']} untuk {$p['nama_lab']} telah disetujui.')",
               [$p['user_id']]);
        alert('success', 'Peminjaman lab disetujui!');
    } elseif ($action == 'reject') {
        if (!$alasan) { alert('danger', 'Alasan penolakan wajib diisi!'); redirect('verifikasi_peminjaman_lab.php'); }
        query("UPDATE peminjaman_lab SET status = 'Rejected', alasan_penolakan = ? WHERE id = ?", [$alasan, $id]);
        query("INSERT INTO notifikasi (user_id, judul, pesan) VALUES (?, 'Peminjaman Lab Ditolak', 'Peminjaman {$p['kode_peminjaman']} ditolak. Alasan: $alasan')",
               [$p['user_id']]);
        alert('success', 'Peminjaman lab ditolak.');
    } elseif ($action == 'return') {
        query("UPDATE peminjaman_lab SET status = 'Returned' WHERE id = ?", [$id]);
        query("UPDATE laboratorium SET status = 'Tersedia' WHERE id = ?", [$p['lab_id']]);
        alert('success', 'Status dikembalikan, lab tersedia kembali.');
    }
    redirect('verifikasi_peminjaman_lab.php');
}

$filter = $_GET['status'] ?? 'Pending';
$sql = "SELECT pl.*, u.nama_lengkap, u.nim_nidn, l.nama_lab, l.kode_lab, l.lokasi
        FROM peminjaman_lab pl
        JOIN users u ON pl.user_id = u.id
        JOIN laboratorium l ON pl.lab_id = l.id";
$params = [];
if ($filter) { $sql .= " WHERE pl.status = ?"; $params[] = $filter; }
$sql .= " ORDER BY pl.created_at DESC";
$list = fetchAll($sql, $params);
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-door-open text-primary mr-3"></i>Verifikasi Peminjaman Lab</h1>
    <p class="page-subtitle">Setujui atau tolak peminjaman laboratorium oleh mahasiswa</p>
</div>

<div class="flex gap-2 mb-6 flex-wrap">
    <?php foreach (['','Pending','Approved','Rejected','Returned'] as $s): ?>
    <a href="?status=<?= $s ?>" class="btn-glass <?= $filter===$s ? 'btn-glass-primary' : 'btn-glass-outline' ?> btn-sm"><?= $s ?: 'Semua' ?></a>
    <?php endforeach; ?>
</div>

<div class="glass-card p-6">
    <?php if (empty($list)): ?>
    <div class="text-center py-8 text-muted"><i class="fas fa-check-circle text-4xl mb-3 block"></i>Tidak ada data peminjaman lab.</div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="glass-table">
            <thead><tr>
                <th>Kode</th><th>Peminjam</th><th>Lab</th><th>Tujuan</th><th>Tanggal</th><th>Jam</th><th>Status</th><th>Aksi</th>
            </tr></thead>
            <tbody>
                <?php foreach ($list as $pl): ?>
                <tr class="<?= $pl['status']=='Pending' ? 'bg-amber-50/50' : '' ?>">
                    <td><span class="glass-badge badge-dark"><?= htmlspecialchars($pl['kode_peminjaman']) ?></span></td>
                    <td><?= htmlspecialchars($pl['nama_lengkap']) ?><br><small class="text-muted"><?= htmlspecialchars($pl['nim_nidn']) ?></small></td>
                    <td><?= htmlspecialchars($pl['nama_lab']) ?><br><small class="text-muted"><?= htmlspecialchars($pl['kode_lab']) ?></small></td>
                    <td><span class="glass-badge badge-info"><?= htmlspecialchars($pl['tujuan_peminjaman']) ?></span></td>
                    <td><?= formatTanggalIndo($pl['tgl_pinjam']) ?><br><small class="text-muted">s/d <?= formatTanggalIndo($pl['tgl_kembali']) ?></small></td>
                    <td><?= date('H:i', strtotime($pl['jam_mulai'])) ?> - <?= date('H:i', strtotime($pl['jam_selesai'])) ?></td>
                    <td><?= statusBadge($pl['status']) ?></td>
                    <td>
                        <?php if ($pl['status'] == 'Pending'): ?>
                        <div class="flex gap-1 flex-col">
                            <form method="POST" onsubmit="return confirm('Setujui peminjaman ini?')">
                                <input type="hidden" name="id" value="<?= $pl['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button class="btn-glass btn-glass-success btn-sm w-full"><i class="fas fa-check mr-1"></i> Approve</button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Tolak peminjaman ini?')">
                                <input type="hidden" name="id" value="<?= $pl['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <input type="text" name="alasan_penolakan" class="glass-input !py-1 text-xs mb-1" placeholder="Alasan tolak..." required>
                                <button class="btn-glass btn-glass-danger btn-sm w-full"><i class="fas fa-times mr-1"></i> Reject</button>
                            </form>
                        </div>
                        <?php elseif ($pl['status'] == 'Approved'): ?>
                        <form method="POST" onsubmit="return confirm('Tandai sudah dikembalikan?')">
                            <input type="hidden" name="id" value="<?= $pl['id'] ?>">
                            <input type="hidden" name="action" value="return">
                            <button class="btn-glass btn-glass-outline btn-sm"><i class="fas fa-undo-alt mr-1"></i> Kembali</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($pl['status'] == 'Rejected' && $pl['alasan_penolakan']): ?>
                <tr><td colspan="8" class="!pt-0 !pb-3"><div class="text-xs text-accent ml-2"><i class="fas fa-info-circle mr-1"></i> Alasan: <?= htmlspecialchars($pl['alasan_penolakan']) ?></div></td></tr>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
