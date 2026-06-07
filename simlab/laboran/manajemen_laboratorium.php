<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Kelola Laboratorium';

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

include '../includes/header.php';

$lab_list = fetchAll("SELECT * FROM laboratorium ORDER BY kode_lab ASC");
?>
<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:24px;gap:16px">
    <div>
        <h1 class="page-title"><span class="material-symbols-outlined mr-3">meeting_room</span> Kelola Ruangan Laboratorium</h1>
        <p class="page-subtitle">Tambah, edit, atau hapus data laboratorium</p>
    </div>
    <button class="btn btn-primary btn-md3" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><span class="material-symbols-outlined mr-2">add</span> Tambah Lab</button>
</div>

<div class="card p-5">
    <h5 style="font-weight:700; color:#111827" class="mb-4"><span class="material-symbols-outlined mr-2">list</span> Daftar Laboratorium</h5>
    <div class="table-wrapper">
        <table class="table datatable">
            <thead>
                <tr><th>No</th><th>Kode</th><th>Nama Lab</th><th>Lokasi</th><th>Kapasitas</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($lab_list as $i => $l): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><span class="badge" style="background:#374151; color:#fff"><?= htmlspecialchars($l['kode_lab']) ?></span></td>
                    <td class="font-medium"><?= htmlspecialchars($l['nama_lab']) ?></td>
                    <td><?= htmlspecialchars($l['lokasi'] ?: '-') ?></td>
                    <td><?= (int)$l['kapasitas'] ?> orang</td>
                    <td><?= statusBadge($l['status']) ?></td>
                    <td>
                        <div class="flex gap-1">
                            <button class="btn btn-warning btn-sm btn-md3" onclick="document.getElementById('editModal<?= $l['id'] ?>').classList.remove('hidden')"><span class="material-symbols-outlined">edit</span></button>
                            <button class="btn btn-danger btn-sm btn-md3" onclick="confirmHapus(<?= $l['id'] ?>, '<?= addslashes($l['nama_lab']) ?>')"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="tambahModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="card w-full max-w-xl mx-4 p-5">
        <form method="POST">
            <input type="hidden" name="action" value="tambah">
            <div class="card-header flex justify-between items-center"><h5 style="font-weight:700" class="text-lg"><span class="material-symbols-outlined mr-2">add_circle</span>Tambah Laboratorium Baru</h5><button type="button" style="color:#9CA3AF; font-size:1.5rem; background:none; border:none; cursor:pointer" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <div class="grid-2">
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Kode Lab</label><input type="text" name="kode_lab" class="form-input" placeholder="Contoh: LAB-SJ" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Nama Lab</label><input type="text" name="nama_lab" class="form-input" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Lokasi</label><input type="text" name="lokasi" class="form-input" placeholder="Gedung / Lantai"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Kapasitas (orang)</label><input type="number" name="kapasitas" class="form-input" value="15" min="1" required></div>
                    <div class="col-span-2"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Deskripsi</label><textarea name="deskripsi" class="form-input" rows="2"></textarea></div>
                    <div class="col-span-2"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Fasilitas</label><textarea name="fasilitas" class="form-input" rows="2" placeholder="Microscope, Laminar Air Flow, dll"></textarea></div>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary btn-md3"><span class="material-symbols-outlined mr-2">save</span> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($lab_list as $l): ?>
<div id="editModal<?= $l['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="card w-full max-w-xl mx-4 p-5">
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $l['id'] ?>">
            <div class="card-header flex justify-between items-center"><h5 style="font-weight:700" class="text-lg"><span class="material-symbols-outlined mr-2">edit</span>Edit: <?= htmlspecialchars($l['nama_lab']) ?></h5><button type="button" style="color:#9CA3AF; font-size:1.5rem; background:none; border:none; cursor:pointer" onclick="document.getElementById('editModal<?= $l['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <div class="grid-2">
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Kode Lab</label><input type="text" name="kode_lab" class="form-input" value="<?= htmlspecialchars($l['kode_lab']) ?>" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Nama Lab</label><input type="text" name="nama_lab" class="form-input" value="<?= htmlspecialchars($l['nama_lab']) ?>" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Lokasi</label><input type="text" name="lokasi" class="form-input" value="<?= htmlspecialchars($l['lokasi'] ?: '') ?>"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Kapasitas (orang)</label><input type="number" name="kapasitas" class="form-input" value="<?= (int)$l['kapasitas'] ?>" min="1" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Status</label>
                        <select name="status" class="form-input">
                            <option value="Tersedia" <?= $l['status']=='Tersedia'?'selected':'' ?>>Tersedia</option>
                            <option value="Digunakan" <?= $l['status']=='Digunakan'?'selected':'' ?>>Digunakan</option>
                            <option value="Perbaikan" <?= $l['status']=='Perbaikan'?'selected':'' ?>>Perbaikan</option>
                        </select>
                    </div>
                    <div style="grid-column:span 2"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Deskripsi</label><textarea name="deskripsi" class="form-input" rows="2"><?= htmlspecialchars($l['deskripsi'] ?: '') ?></textarea></div>
                    <div style="grid-column:span 2"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Fasilitas</label><textarea name="fasilitas" class="form-input" rows="2"><?= htmlspecialchars($l['fasilitas'] ?: '') ?></textarea></div>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editModal<?= $l['id'] ?>').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary btn-md3"><span class="material-symbols-outlined mr-2">save</span> Simpan</button>
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
