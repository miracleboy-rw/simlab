<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Manajemen Inventaris';
include '../includes/header.php';

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

$alat_list = fetchAll("SELECT * FROM alat ORDER BY nama_alat ASC");
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="page-title"><i class="fas fa-box mr-3"></i> Manajemen Inventaris Alat</h1>
        <p class="page-subtitle">Kelola data alat laboratorium</p>
    </div>
    <button class="btn-glass btn-glass-primary" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><i class="fas fa-plus mr-2"></i> Tambah Alat</button>
</div>

<div class="glass-card p-6">
    <h5 class="font-bold text-navy mb-4"><i class="fas fa-list mr-2"></i> Daftar Alat Laboratorium</h5>
    <div class="overflow-x-auto">
        <table class="glass-table datatable">
            <thead>
                <tr>
                    <th>No</th><th>Kode</th><th>Nama Alat</th><th>Merk</th><th>Lokasi</th><th>Stok</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alat_list as $i => $a): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><span class="glass-badge badge-dark"><?= htmlspecialchars($a['kode_alat']) ?></span></td>
                    <td><?= htmlspecialchars($a['nama_alat']) ?></td>
                    <td><?= htmlspecialchars($a['merk'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($a['lokasi_penyimpanan'] ?: '-') ?></td>
                    <td><?= (int)$a['stok_tersedia'] ?>/<?= (int)$a['stok_total'] ?></td>
                    <td><?= statusBadge($a['status']) ?></td>
                    <td>
                        <div class="flex gap-1">
                            <button class="btn-glass btn-glass-warning btn-sm" onclick="document.getElementById('editModal<?= $a['id'] ?>').classList.remove('hidden')"><i class="fas fa-pen"></i></button>
                            <button class="btn-glass btn-glass-danger btn-sm" onclick="confirmHapus(<?= $a['id'] ?>, '<?= addslashes($a['nama_alat']) ?>')"><i class="fas fa-trash"></i></button>
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
    <div class="glass-modal-content w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="tambah">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-plus-circle mr-2"></i>Tambah Alat Baru</h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-semibold text-navy mb-1">Kode Alat</label><input type="text" name="kode_alat" class="glass-input" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Nama Alat</label><input type="text" name="nama_alat" class="glass-input" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Merk</label><input type="text" name="merk" class="glass-input"></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Lokasi Penyimpanan</label><input type="text" name="lokasi_penyimpanan" class="glass-input"></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Stok Total</label><input type="number" name="stok_total" class="glass-input" value="1" min="1" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Foto Alat</label><input type="file" name="foto" class="glass-input" accept="image/*"></div>
                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-navy mb-1">Spesifikasi</label><textarea name="spesifikasi" class="glass-input" rows="3"></textarea></div>
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-primary"><i class="fas fa-save mr-2"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($alat_list as $a): ?>
<div id="editModal<?= $a['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $a['id'] ?>">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-pen mr-2"></i>Edit Alat: <?= htmlspecialchars($a['nama_alat']) ?></h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('editModal<?= $a['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-semibold text-navy mb-1">Kode Alat</label><input type="text" name="kode_alat" class="glass-input" value="<?= htmlspecialchars($a['kode_alat']) ?>" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Nama Alat</label><input type="text" name="nama_alat" class="glass-input" value="<?= htmlspecialchars($a['nama_alat']) ?>" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Merk</label><input type="text" name="merk" class="glass-input" value="<?= htmlspecialchars($a['merk']) ?>"></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Lokasi</label><input type="text" name="lokasi_penyimpanan" class="glass-input" value="<?= htmlspecialchars($a['lokasi_penyimpanan']) ?>"></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Stok Total</label><input type="number" name="stok_total" class="glass-input" value="<?= (int)$a['stok_total'] ?>" min="1" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Status</label>
                        <select name="status" class="glass-input">
                            <option value="Tersedia" <?= $a['status'] == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="Dipinjam" <?= $a['status'] == 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                            <option value="Rusak" <?= $a['status'] == 'Rusak' ? 'selected' : '' ?>>Rusak</option>
                            <option value="Kalibrasi" <?= $a['status'] == 'Kalibrasi' ? 'selected' : '' ?>>Kalibrasi</option>
                            <option value="Tidak Tersedia" <?= $a['status'] == 'Tidak Tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Foto Baru</label><input type="file" name="foto" class="glass-input" accept="image/*"></div>
                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-navy mb-1">Spesifikasi</label><textarea name="spesifikasi" class="glass-input" rows="3"><?= htmlspecialchars($a['spesifikasi']) ?></textarea></div>
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('editModal<?= $a['id'] ?>').classList.add('hidden')">Batal</button>
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
    if (confirm('Hapus alat "' + nama + '"?')) {
        document.getElementById('hapusId').value = id;
        document.getElementById('formHapus').submit();
    }
}
</script>
<?php include '../includes/footer.php'; ?>
