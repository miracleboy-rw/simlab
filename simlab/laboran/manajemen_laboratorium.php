<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Kelola Laboratorium';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'tambah') {
        $kode_lab = $_POST['kode_lab'];
        $nama_lab = $_POST['nama_lab'];
        $lokasi = $_POST['lokasi'];
        $kapasitas = $_POST['kapasitas'];
        $deskripsi = $_POST['deskripsi'] ?? '';
        $fasilitas = $_POST['fasilitas'] ?? '';

        query("INSERT INTO laboratorium (kode_lab, nama_lab, lokasi, kapasitas, deskripsi, fasilitas) VALUES (?, ?, ?, ?, ?, ?)",
               [$kode_lab, $nama_lab, $lokasi, $kapasitas, $deskripsi, $fasilitas]);
        alert('success', 'Laboratorium berhasil ditambahkan!');
    } elseif ($action == 'edit') {
        $id = $_POST['id'];
        $kode_lab = $_POST['kode_lab'];
        $nama_lab = $_POST['nama_lab'];
        $lokasi = $_POST['lokasi'];
        $kapasitas = $_POST['kapasitas'];
        $deskripsi = $_POST['deskripsi'] ?? '';
        $fasilitas = $_POST['fasilitas'] ?? '';
        $status = $_POST['status'];

        query("UPDATE laboratorium SET kode_lab=?, nama_lab=?, lokasi=?, kapasitas=?, deskripsi=?, fasilitas=?, status=? WHERE id=?",
               [$kode_lab, $nama_lab, $lokasi, $kapasitas, $deskripsi, $fasilitas, $status, $id]);
        alert('success', 'Laboratorium berhasil diupdate!');
    } elseif ($action == 'hapus') {
        $id = $_POST['id'];
        query("DELETE FROM laboratorium WHERE id = ?", [$id]);
        alert('success', 'Laboratorium berhasil dihapus!');
    }
    redirect('manajemen_laboratorium.php');
}

$lab_list = fetchAll("SELECT * FROM laboratorium ORDER BY kode_lab ASC");
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="page-title"><i class="fas fa-door-open mr-3"></i> Kelola Ruangan Laboratorium</h1>
        <p class="page-subtitle">Tambah, edit, atau hapus data laboratorium</p>
    </div>
    <button class="btn-glass btn-glass-primary" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><i class="fas fa-plus mr-2"></i> Tambah Lab</button>
</div>

<?= showAlert() ?>

<div class="glass-card p-6">
    <h5 class="font-bold text-navy mb-4"><i class="fas fa-list mr-2"></i> Daftar Laboratorium</h5>
    <div class="overflow-x-auto">
        <table class="glass-table datatable">
            <thead>
                <tr><th>No</th><th>Kode</th><th>Nama Lab</th><th>Lokasi</th><th>Kapasitas</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($lab_list as $i => $l): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><span class="glass-badge badge-dark"><?= htmlspecialchars($l['kode_lab']) ?></span></td>
                    <td class="font-medium"><?= htmlspecialchars($l['nama_lab']) ?></td>
                    <td><?= htmlspecialchars($l['lokasi'] ?: '-') ?></td>
                    <td><?= (int)$l['kapasitas'] ?> orang</td>
                    <td><?= statusBadge($l['status']) ?></td>
                    <td>
                        <div class="flex gap-1">
                            <button class="btn-glass btn-glass-warning btn-sm" onclick="document.getElementById('editModal<?= $l['id'] ?>').classList.remove('hidden')"><i class="fas fa-pen"></i></button>
                            <button class="btn-glass btn-glass-danger btn-sm" onclick="confirmHapus(<?= $l['id'] ?>, '<?= addslashes($l['nama_lab']) ?>')"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="tambahModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-xl mx-4">
        <form method="POST">
            <input type="hidden" name="action" value="tambah">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-plus-circle mr-2"></i>Tambah Laboratorium Baru</h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-semibold text-navy mb-1">Kode Lab</label><input type="text" name="kode_lab" class="glass-input" placeholder="Contoh: LAB-SJ" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Nama Lab</label><input type="text" name="nama_lab" class="glass-input" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Lokasi</label><input type="text" name="lokasi" class="glass-input" placeholder="Gedung / Lantai"></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Kapasitas (orang)</label><input type="number" name="kapasitas" class="glass-input" value="15" min="1" required></div>
                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-navy mb-1">Deskripsi</label><textarea name="deskripsi" class="glass-input" rows="2"></textarea></div>
                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-navy mb-1">Fasilitas</label><textarea name="fasilitas" class="glass-input" rows="2" placeholder="Microscope, Laminar Air Flow, dll"></textarea></div>
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-primary"><i class="fas fa-save mr-2"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($lab_list as $l): ?>
<div id="editModal<?= $l['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-xl mx-4">
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $l['id'] ?>">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-pen mr-2"></i>Edit: <?= htmlspecialchars($l['nama_lab']) ?></h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('editModal<?= $l['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-semibold text-navy mb-1">Kode Lab</label><input type="text" name="kode_lab" class="glass-input" value="<?= htmlspecialchars($l['kode_lab']) ?>" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Nama Lab</label><input type="text" name="nama_lab" class="glass-input" value="<?= htmlspecialchars($l['nama_lab']) ?>" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Lokasi</label><input type="text" name="lokasi" class="glass-input" value="<?= htmlspecialchars($l['lokasi'] ?: '') ?>"></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Kapasitas (orang)</label><input type="number" name="kapasitas" class="glass-input" value="<?= (int)$l['kapasitas'] ?>" min="1" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Status</label>
                        <select name="status" class="glass-input">
                            <option value="Tersedia" <?= $l['status']=='Tersedia'?'selected':'' ?>>Tersedia</option>
                            <option value="Digunakan" <?= $l['status']=='Digunakan'?'selected':'' ?>>Digunakan</option>
                            <option value="Perbaikan" <?= $l['status']=='Perbaikan'?'selected':'' ?>>Perbaikan</option>
                        </select>
                    </div>
                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-navy mb-1">Deskripsi</label><textarea name="deskripsi" class="glass-input" rows="2"><?= htmlspecialchars($l['deskripsi'] ?: '') ?></textarea></div>
                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-navy mb-1">Fasilitas</label><textarea name="fasilitas" class="glass-input" rows="2"><?= htmlspecialchars($l['fasilitas'] ?: '') ?></textarea></div>
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('editModal<?= $l['id'] ?>').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-primary"><i class="fas fa-save mr-2"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<form id="formHapus" method="POST" style="display:none">
    <input type="hidden" name="action" value="hapus">
    <input type="hidden" name="id" id="hapusId">
</form>
<script>
function confirmHapus(id, nama) {
    if (confirm('Hapus laboratorium "' + nama + '"?')) {
        document.getElementById('hapusId').value = id;
        document.getElementById('formHapus').submit();
    }
}
</script>
<?php include '../includes/footer.php'; ?>
