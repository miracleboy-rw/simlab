<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Manajemen Inventaris';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'tambah') {
        $kode = $_POST['kode_alat'];
        $nama = $_POST['nama_alat'];
        $merk = $_POST['merk'];
        $spesifikasi = $_POST['spesifikasi'];
        $lokasi = $_POST['lokasi_penyimpanan'];
        $stok = $_POST['stok_total'];

        $foto = 'default_alat.png';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = 'alat_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/foto_alat/' . $foto);
        }

        query("INSERT INTO alat (kode_alat, nama_alat, merk, spesifikasi, lokasi_penyimpanan, foto, stok_total, stok_tersedia) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
               [$kode, $nama, $merk, $spesifikasi, $lokasi, $foto, $stok, $stok]);
        alert('success', 'Alat berhasil ditambahkan!');
    } elseif ($action == 'edit') {
        $id = $_POST['id'];
        $kode = $_POST['kode_alat'];
        $nama = $_POST['nama_alat'];
        $merk = $_POST['merk'];
        $spesifikasi = $_POST['spesifikasi'];
        $lokasi = $_POST['lokasi_penyimpanan'];
        $stok_total = $_POST['stok_total'];
        $status = $_POST['status'];

        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = 'alat_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/foto_alat/' . $foto);
        }

        if ($foto) {
            query("UPDATE alat SET kode_alat=?, nama_alat=?, merk=?, spesifikasi=?, lokasi_penyimpanan=?, foto=?, stok_total=?, status=? WHERE id=?",
                   [$kode, $nama, $merk, $spesifikasi, $lokasi, $foto, $stok_total, $status, $id]);
        } else {
            query("UPDATE alat SET kode_alat=?, nama_alat=?, merk=?, spesifikasi=?, lokasi_penyimpanan=?, stok_total=?, status=? WHERE id=?",
                   [$kode, $nama, $merk, $spesifikasi, $lokasi, $stok_total, $status, $id]);
        }
        alert('success', 'Data alat berhasil diupdate!');
    } elseif ($action == 'hapus') {
        $id = $_POST['id'];
        query("DELETE FROM alat WHERE id = ?", [$id]);
        alert('success', 'Alat berhasil dihapus!');
    }
    redirect('manajemen_inventaris.php');
}

include '../includes/header.php';

$alat_list = fetchAll("SELECT * FROM alat ORDER BY nama_alat ASC");
?>
<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:24px;gap:16px">
    <div>
        <h1 class="page-title"><span class="material-symbols-outlined mr-3">inventory_2</span> Manajemen Inventaris Alat</h1>
        <p class="page-subtitle">Kelola data alat laboratorium</p>
    </div>
    <button class="btn btn-primary btn-md3" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><span class="material-symbols-outlined mr-2">add</span> Tambah Alat</button>
</div>

<div class="card p-5">
    <h5 style="font-weight:700; color:#111827" class="mb-4"><span class="material-symbols-outlined mr-2">list</span> Daftar Alat Laboratorium</h5>
    <div class="table-wrapper">
        <table class="table datatable">
            <thead>
                <tr>
                    <th>No</th><th>Kode</th><th>Nama Alat</th><th>Merk</th><th>Lokasi</th><th>Stok</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alat_list as $i => $a): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><span class="badge" style="background:#374151; color:#fff"><?= htmlspecialchars($a['kode_alat']) ?></span></td>
                    <td><?= htmlspecialchars($a['nama_alat']) ?></td>
                    <td><?= htmlspecialchars($a['merk'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($a['lokasi_penyimpanan'] ?: '-') ?></td>
                    <td><?= (int)$a['stok_tersedia'] ?>/<?= (int)$a['stok_total'] ?></td>
                    <td><?= statusBadge($a['status']) ?></td>
                    <td>
                        <div class="flex gap-1">
                            <button class="btn btn-warning btn-sm btn-md3" onclick="document.getElementById('editModal<?= $a['id'] ?>').classList.remove('hidden')"><span class="material-symbols-outlined">edit</span></button>
                            <button class="btn btn-danger btn-sm btn-md3" onclick="confirmHapus(<?= $a['id'] ?>, '<?= addslashes($a['nama_alat']) ?>')"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div id="tambahModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="card w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto p-5">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="tambah">
            <div class="card-header flex justify-between items-center"><h5 style="font-weight:700" class="text-lg"><span class="material-symbols-outlined mr-2">add_circle</span>Tambah Alat Baru</h5><button type="button" style="color:#9CA3AF; font-size:1.5rem; background:none; border:none; cursor:pointer" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <div class="grid-2">
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Kode Alat</label><input type="text" name="kode_alat" class="form-input" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Nama Alat</label><input type="text" name="nama_alat" class="form-input" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Merk</label><input type="text" name="merk" class="form-input"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Lokasi Penyimpanan</label><input type="text" name="lokasi_penyimpanan" class="form-input"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Stok Total</label><input type="number" name="stok_total" class="form-input" value="1" min="1" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Foto Alat</label><input type="file" name="foto" class="form-input" accept="image/*"></div>
                    <div class="col-span-2"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Spesifikasi</label><textarea name="spesifikasi" class="form-input" rows="3"></textarea></div>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary btn-md3"><span class="material-symbols-outlined mr-2">save</span> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($alat_list as $a): ?>
<div id="editModal<?= $a['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="card w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto p-5">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $a['id'] ?>">
            <div class="card-header flex justify-between items-center"><h5 style="font-weight:700" class="text-lg"><span class="material-symbols-outlined mr-2">edit</span>Edit Alat: <?= htmlspecialchars($a['nama_alat']) ?></h5><button type="button" style="color:#9CA3AF; font-size:1.5rem; background:none; border:none; cursor:pointer" onclick="document.getElementById('editModal<?= $a['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <div class="grid-2">
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Kode Alat</label><input type="text" name="kode_alat" class="form-input" value="<?= htmlspecialchars($a['kode_alat']) ?>" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Nama Alat</label><input type="text" name="nama_alat" class="form-input" value="<?= htmlspecialchars($a['nama_alat']) ?>" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Merk</label><input type="text" name="merk" class="form-input" value="<?= htmlspecialchars($a['merk']) ?>"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Lokasi</label><input type="text" name="lokasi_penyimpanan" class="form-input" value="<?= htmlspecialchars($a['lokasi_penyimpanan']) ?>"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Stok Total</label><input type="number" name="stok_total" class="form-input" value="<?= (int)$a['stok_total'] ?>" min="1" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Status</label>
                        <select name="status" class="form-input">
                            <option value="Tersedia" <?= $a['status'] == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="Dipinjam" <?= $a['status'] == 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                            <option value="Rusak" <?= $a['status'] == 'Rusak' ? 'selected' : '' ?>>Rusak</option>
                            <option value="Kalibrasi" <?= $a['status'] == 'Kalibrasi' ? 'selected' : '' ?>>Kalibrasi</option>
                            <option value="Tidak Tersedia" <?= $a['status'] == 'Tidak Tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
                        </select>
                    </div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Foto Baru</label><input type="file" name="foto" class="form-input" accept="image/*"></div>
                    <div class="col-span-2"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Spesifikasi</label><textarea name="spesifikasi" class="form-input" rows="3"><?= htmlspecialchars($a['spesifikasi']) ?></textarea></div>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editModal<?= $a['id'] ?>').classList.add('hidden')">Batal</button>
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
    if (confirm('Hapus alat "' + nama + '"?')) {
        document.getElementById('hapusId').value = id;
        document.getElementById('formHapus').submit();
    }
}
</script>
<?php include '../includes/footer.php'; ?>
