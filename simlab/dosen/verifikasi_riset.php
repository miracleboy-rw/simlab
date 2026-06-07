<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('dosen')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Verifikasi Riset TA';
$dosen_id = $_SESSION['user_id'];

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

include '../includes/header.php';

$verifikasi_list = fetchAll("SELECT v.*, u.nama_lengkap as mahasiswa, u.nim_nidn
                             FROM verifikasi_ta v
                             JOIN users u ON v.mahasiswa_id = u.id
                             WHERE v.dosen_id = ?
                             ORDER BY v.created_at DESC", [$dosen_id]);

$mahasiswa_bimbingan = fetchAll("SELECT * FROM users WHERE role = 'mahasiswa' ORDER BY nama_lengkap ASC");
?>
<div class="flex mb-6" style="gap:16px;align-items:center;justify-content:space-between;flex-wrap:wrap">
    <div>
        <h1 class="page-title"><span class="material-symbols-outlined" style="margin-right:12px">school</span> Verifikasi Riset TA</h1>
        <p class="text-muted" style="margin-top:4px;font-size:13px">Berikan persetujuan riset mahasiswa bimbingan</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><span class="material-symbols-outlined" style="margin-right:8px">add</span> Ajukan Verifikasi</button>
</div>

<div class="card p-5">
    <div class="section-header" style="margin-bottom:16px"><span class="material-symbols-outlined" style="margin-right:8px">list</span> Daftar Verifikasi Riset TA</div>
    <div class="table-wrapper">
        <table>
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
                    <td class="text-muted"><?= htmlspecialchars($v['nim_nidn'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($v['judul_penelitian']) ?></td>
                    <td><?= statusBadge($v['status']) ?></td>
                    <td><?= htmlspecialchars($v['catatan'] ?: '-') ?></td>
                    <td><?= formatTanggalIndo($v['created_at']) ?></td>
                    <td>
                        <?php if ($v['status'] == 'Pending'): ?>
                        <button class="btn btn-success btn-sm" onclick="document.getElementById('verifModal<?= $v['id'] ?>').classList.remove('hidden')"><span class="material-symbols-outlined">check</span></button>
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
<div id="tambahModal" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal">
        <form method="POST" action="verifikasi_riset_add.php" enctype="multipart/form-data">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px"><h5 style="font-size:18px;font-weight:700"><span class="material-symbols-outlined" style="margin-right:8px">add_circle</span>Pengajuan Verifikasi Riset TA</h5><button type="button" style="border:none;background:none;color:#9CA3AF;cursor:pointer;font-size:24px" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div>
                <div class="form-group">
                    <label class="form-label">Mahasiswa</label>
                    <select name="mahasiswa_id" class="form-select" required>
                        <option value="">Pilih mahasiswa</option>
                        <?php foreach ($mahasiswa_bimbingan as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_lengkap']) ?> (<?= htmlspecialchars($m['nim_nidn']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Judul Penelitian</label>
                    <input type="text" name="judul_penelitian" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">File Proposal (PDF)</label>
                    <input type="file" name="file_proposal" class="form-input" accept=".pdf">
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px;padding-top:16px;border-top:1px solid #E5E7EB">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary"><span class="material-symbols-outlined" style="margin-right:8px">send</span> Ajukan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($verifikasi_list as $v): ?>
<?php if ($v['status'] == 'Pending'): ?>
<div id="verifModal<?= $v['id'] ?>" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $v['id'] ?>">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px"><h5 style="font-size:18px;font-weight:700"><span class="material-symbols-outlined" style="margin-right:8px">check_circle</span>Verifikasi: <?= htmlspecialchars($v['judul_penelitian']) ?></h5><button type="button" style="border:none;background:none;color:#9CA3AF;cursor:pointer;font-size:24px" onclick="document.getElementById('verifModal<?= $v['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div>
                <p style="margin-bottom:8px"><strong>Mahasiswa:</strong> <?= htmlspecialchars($v['mahasiswa']) ?> (<?= htmlspecialchars($v['nim_nidn']) ?>)</p>
                <p style="margin-bottom:8px"><strong>Judul:</strong> <?= htmlspecialchars($v['judul_penelitian']) ?></p>
                <?php if ($v['file_proposal']): ?>
                <p style="margin-bottom:16px"><a href="../uploads/dokumen/<?= $v['file_proposal'] ?>" target="_blank" class="btn btn-outline btn-sm"><span class="material-symbols-outlined" style="margin-right:4px">picture_as_pdf</span> Lihat Proposal</a></p>
                <?php endif; ?>
                <hr style="border:none;border-top:1px solid #E5E7EB;margin-bottom:16px">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Catatan / Komentar</label>
                    <textarea name="catatan" class="form-textarea" rows="3"></textarea>
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;gap:8px;margin-top:20px;padding-top:16px;border-top:1px solid #E5E7EB">
                <button type="submit" name="action" value="tolak" class="btn btn-danger" onclick="return confirm('Tolak riset ini?')"><span class="material-symbols-outlined" style="margin-right:8px">close</span> Tolak</button>
                <button type="submit" name="action" value="setuju" class="btn btn-success" onclick="return confirm('Setujui riset ini?')"><span class="material-symbols-outlined" style="margin-right:8px">check</span> Setujui</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>
