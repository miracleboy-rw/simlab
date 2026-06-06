<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('dosen')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Verifikasi Riset TA';
$dosen_id = $_SESSION['user_id'];
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $action = $_POST['action'] ?? '';

    if ($action == 'setuju') {
        query("UPDATE verifikasi_ta SET status = 'Disetujui', catatan = ? WHERE id = ? AND dosen_id = ?",
               [$_POST['catatan'], $id, $dosen_id]);
        $v = fetchOne("SELECT * FROM verifikasi_ta WHERE id = ?", [$id]);
        query("INSERT INTO notifikasi (user_id, judul, pesan) VALUES (?, 'Riset TA Disetujui', 'Penelitian TA Anda telah disetujui oleh dosen pembimbing.')",
               [$v['mahasiswa_id']]);
        alert('success', 'Verifikasi riset disetujui!');
    } elseif ($action == 'tolak') {
        query("UPDATE verifikasi_ta SET status = 'Ditolak', catatan = ? WHERE id = ? AND dosen_id = ?",
               [$_POST['catatan'], $id, $dosen_id]);
        $v = fetchOne("SELECT * FROM verifikasi_ta WHERE id = ?", [$id]);
        query("INSERT INTO notifikasi (user_id, judul, pesan) VALUES (?, 'Riset TA Ditolak', 'Penelitian TA Anda ditolak. Catatan: " . $_POST['catatan'] . "')",
               [$v['mahasiswa_id']]);
        alert('success', 'Verifikasi riset ditolak.');
    }
    redirect('verifikasi_riset.php');
}

$verifikasi_list = fetchAll("SELECT v.*, u.nama_lengkap as mahasiswa, u.nim_nidn
                             FROM verifikasi_ta v
                             JOIN users u ON v.mahasiswa_id = u.id
                             WHERE v.dosen_id = ?
                             ORDER BY v.created_at DESC", [$dosen_id]);

$mahasiswa_bimbingan = fetchAll("SELECT * FROM users WHERE role = 'mahasiswa' ORDER BY nama_lengkap ASC");
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="page-title"><i class="fas fa-graduation-cap mr-3"></i> Verifikasi Riset TA</h1>
        <p class="page-subtitle">Berikan persetujuan riset mahasiswa bimbingan</p>
    </div>
    <button class="btn-glass btn-glass-primary" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><i class="fas fa-plus mr-2"></i> Ajukan Verifikasi</button>
</div>

<div class="glass-card p-6">
    <h5 class="font-bold text-navy mb-4"><i class="fas fa-list mr-2"></i> Daftar Verifikasi Riset TA</h5>
    <div class="overflow-x-auto">
        <table class="glass-table">
            <thead>
                <tr><th>Mahasiswa</th><th>NIM</th><th>Judul Penelitian</th><th>Status</th><th>Catatan</th><th>Tanggal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($verifikasi_list)): ?>
                <tr><td colspan="7" class="text-center text-muted">Belum ada data verifikasi.</td></tr>
                <?php endif; ?>
                <?php foreach ($verifikasi_list as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['mahasiswa']) ?></td>
                    <td><?= htmlspecialchars($v['nim_nidn'] ?: '-') ?></td>
                    <td class="max-w-[200px] truncate"><?= htmlspecialchars($v['judul_penelitian']) ?></td>
                    <td><?= statusBadge($v['status']) ?></td>
                    <td class="max-w-[150px] truncate"><?= htmlspecialchars($v['catatan'] ?: '-') ?></td>
                    <td><?= formatTanggalIndo($v['created_at']) ?></td>
                    <td>
                        <?php if ($v['status'] == 'Pending'): ?>
                        <button class="btn-glass btn-glass-success btn-sm" onclick="document.getElementById('verifModal<?= $v['id'] ?>').classList.remove('hidden')"><i class="fas fa-check"></i></button>
                        <?php else: ?>
                        <span class="text-muted text-sm">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Verifikasi -->
<div id="tambahModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-lg mx-4">
        <form method="POST" action="verifikasi_riset_add.php" enctype="multipart/form-data">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-plus-circle mr-2"></i>Pengajuan Verifikasi Riset TA</h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-navy mb-1">Mahasiswa</label>
                    <select name="mahasiswa_id" class="glass-input" required>
                        <option value="">Pilih mahasiswa</option>
                        <?php foreach ($mahasiswa_bimbingan as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_lengkap']) ?> (<?= htmlspecialchars($m['nim_nidn']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-navy mb-1">Judul Penelitian</label>
                    <input type="text" name="judul_penelitian" class="glass-input" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-navy mb-1">File Proposal (PDF)</label>
                    <input type="file" name="file_proposal" class="glass-input" accept=".pdf">
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-primary"><i class="fas fa-paper-plane mr-2"></i> Ajukan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($verifikasi_list as $v): ?>
<?php if ($v['status'] == 'Pending'): ?>
<div id="verifModal<?= $v['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-lg mx-4">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $v['id'] ?>">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-check-circle mr-2"></i>Verifikasi: <?= htmlspecialchars($v['judul_penelitian']) ?></h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('verifModal<?= $v['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <p class="mb-2"><strong>Mahasiswa:</strong> <?= htmlspecialchars($v['mahasiswa']) ?> (<?= htmlspecialchars($v['nim_nidn']) ?>)</p>
                <p class="mb-2"><strong>Judul:</strong> <?= htmlspecialchars($v['judul_penelitian']) ?></p>
                <?php if ($v['file_proposal']): ?>
                <p class="mb-4"><a href="../uploads/dokumen/<?= $v['file_proposal'] ?>" target="_blank" class="btn-glass btn-glass-outline btn-sm"><i class="fas fa-file-pdf mr-1"></i> Lihat Proposal</a></p>
                <?php endif; ?>
                <hr class="divider mb-4">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-navy mb-1">Catatan / Komentar</label>
                    <textarea name="catatan" class="glass-input" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer flex justify-between">
                <button type="submit" name="action" value="tolak" class="btn-glass btn-glass-danger" onclick="return confirm('Tolak riset ini?')"><i class="fas fa-times mr-2"></i> Tolak</button>
                <button type="submit" name="action" value="setuju" class="btn-glass btn-glass-success" onclick="return confirm('Setujui riset ini?')"><i class="fas fa-check mr-2"></i> Setujui</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>
