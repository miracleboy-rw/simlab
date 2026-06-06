<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Logbook Tindakan Kerusakan';
$laboran_id = $_SESSION['user_id'];
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'tindak') {
        $laporan_id = $_POST['laporan_id'];
        $tindakan = $_POST['tindakan'];
        $status_operasional = $_POST['status_operasional'];

        query("INSERT INTO logbook_kerusakan (laporan_id, laboran_id, tindakan, status_operasional) VALUES (?, ?, ?, ?)",
               [$laporan_id, $laboran_id, $tindakan, $status_operasional]);
        query("UPDATE laporan_kerusakan SET status = 'Ditangani' WHERE id = ?", [$laporan_id]);

        $laporan = fetchOne("SELECT * FROM laporan_kerusakan WHERE id = ?", [$laporan_id]);
        query("UPDATE alat SET status = ? WHERE id = ?",
               [$status_operasional == 'Operasional' ? 'Tersedia' : 'Rusak', $laporan['alat_id']]);

        alert('success', 'Tindakan berhasil dicatat!');
    } elseif ($_POST['action'] == 'selesai') {
        $laporan_id = $_POST['laporan_id'];
        query("UPDATE laporan_kerusakan SET status = 'Selesai' WHERE id = ?", [$laporan_id]);
        alert('success', 'Laporan ditandai selesai.');
    }
    redirect('logbook_kerusakan.php');
}

$laporan_list = fetchAll("SELECT l.*, a.nama_alat, a.kode_alat, u.nama_lengkap as pelapor
                          FROM laporan_kerusakan l
                          JOIN alat a ON l.alat_id = a.id
                          JOIN users u ON l.user_id = u.id
                          ORDER BY l.created_at DESC");

$logbook_list = fetchAll("SELECT lb.*, l.kronologi, l.gejala_kerusakan, a.nama_alat, u.nama_lengkap as pelapor, lu.nama_lengkap as laboran
                          FROM logbook_kerusakan lb
                          JOIN laporan_kerusakan l ON lb.laporan_id = l.id
                          JOIN alat a ON l.alat_id = a.id
                          JOIN users u ON l.user_id = u.id
                          JOIN users lu ON lb.laboran_id = lu.id
                          ORDER BY lb.created_at DESC LIMIT 20");
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-book mr-3"></i> Logbook Tindakan Kerusakan</h1>
    <p class="page-subtitle">Catat tindakan perbaikan alat laboratorium</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-7 gap-6">
    <div class="lg:col-span-4">
        <div class="glass-card p-6">
            <h5 class="font-bold text-navy mb-4"><i class="fas fa-exclamation-triangle mr-2"></i> Laporan Kerusakan Masuk</h5>
            <div class="overflow-x-auto">
                <table class="glass-table">
                    <thead><tr>
                        <th>Alat</th><th>Pelapor</th><th>Gejala</th><th>Tanggal</th><th>Status</th><th>Aksi</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($laporan_list)): ?>
                        <tr><td colspan="6" class="text-center text-muted">Belum ada laporan</td></tr>
                        <?php endif; ?>
                        <?php foreach ($laporan_list as $l): ?>
                        <tr>
                            <td><?= htmlspecialchars($l['nama_alat']) ?> <small>(<?= htmlspecialchars($l['kode_alat']) ?>)</small></td>
                            <td><?= htmlspecialchars($l['pelapor']) ?></td>
                            <td class="max-w-[150px] truncate"><?= htmlspecialchars($l['gejala_kerusakan']) ?></td>
                            <td><?= formatTanggalIndo($l['tgl_kejadian']) ?></td>
                            <td><?= statusBadge($l['status']) ?></td>
                            <td>
                                <button class="btn-glass btn-glass-primary btn-sm" onclick="document.getElementById('tindakModal<?= $l['id'] ?>').classList.remove('hidden')">
                                    <i class="fas fa-tools mr-1"></i> Tindak
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="lg:col-span-3">
        <div class="glass-card p-6">
            <h5 class="font-bold text-navy mb-4"><i class="fas fa-clock mr-2"></i> Logbook Tindakan Terbaru</h5>
            <?php if (empty($logbook_list)): ?>
            <p class="text-muted">Belum ada tindakan tercatat.</p>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($logbook_list as $lb): ?>
                <div class="pb-3 border-b border-gray-100">
                    <div class="flex justify-between items-start">
                        <strong class="text-navy text-sm"><?= htmlspecialchars($lb['nama_alat']) ?></strong>
                        <small class="text-muted text-xs"><?= formatTanggalIndo($lb['created_at']) ?></small>
                    </div>
                    <p class="text-sm text-gray-600 my-1"><?= htmlspecialchars($lb['tindakan']) ?></p>
                    <div class="flex items-center gap-2">
                        <?= statusBadge($lb['status_operasional']) ?>
                        <small class="text-muted">oleh <?= htmlspecialchars($lb['laboran']) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php foreach ($laporan_list as $l): ?>
<div id="tindakModal<?= $l['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-lg mx-4">
        <form method="POST">
            <input type="hidden" name="action" value="tindak">
            <input type="hidden" name="laporan_id" value="<?= $l['id'] ?>">
            <div class="modal-header flex justify-between items-center">
                <h5 class="font-bold text-lg"><i class="fas fa-tools mr-2"></i>Tindakan: <?= htmlspecialchars($l['nama_alat']) ?></h5>
                <button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('tindakModal<?= $l['id'] ?>').classList.add('hidden')">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Kronologi:</strong> <?= htmlspecialchars($l['kronologi']) ?></p>
                <p class="mb-4"><strong>Gejala:</strong> <?= htmlspecialchars($l['gejala_kerusakan']) ?></p>
                <hr class="divider mb-4">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-navy mb-1">Tindakan yang dilakukan</label>
                    <textarea name="tindakan" class="glass-input" rows="3" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-navy mb-1">Status Operasional Alat</label>
                    <select name="status_operasional" class="glass-input" required>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                        <option value="Servis">Servis</option>
                        <option value="Operasional">Operasional (Normal)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('tindakModal<?= $l['id'] ?>').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-primary"><i class="fas fa-save mr-2"></i> Catat Tindakan</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>
