<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
$base_url = '../';
$page_title = 'Katalog Alat & Bahan';

if (isRole('laboran') && $_SERVER['REQUEST_METHOD'] === 'POST') {
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

        $existing = fetchOne("SELECT * FROM alat WHERE id = ?", [$id]);
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
        query("DELETE FROM alat WHERE id = ?", [$_POST['id']]);
        alert('success', 'Alat berhasil dihapus!');
    }
    redirect('katalog.php');
}

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

$sql = "SELECT * FROM alat WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (nama_alat LIKE ? OR merk LIKE ? OR spesifikasi LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
if ($filter) { $sql .= " AND status = ?"; $params[] = $filter; }
$sql .= " ORDER BY nama_alat ASC";
$alat_list = fetchAll($sql, $params);
include '../includes/header.php';
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="page-title"><i class="fas fa-microscope text-primary mr-3"></i>Katalog Alat & Bahan</h1>
        <p class="page-subtitle">Temukan alat laboratorium yang tersedia</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <form method="GET" class="flex flex-wrap gap-2 w-full sm:w-auto">
            <input type="text" name="search" class="glass-input !py-2 !pr-3 !pl-10 w-full sm:min-w-[200px]" placeholder="Cari alat..." value="<?= htmlspecialchars($search) ?>" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2716%27 height=%2716%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23A3AED0%27 stroke-width=%272%27%3E%3Ccircle cx=%2711%27 cy=%2711%27 r=%278%27/%3E%3Cline x1=%2721%27 y1=%2721%27 x2=%2716.65%27 y2=%2716.65%27/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:12px center;">
            <select name="filter" class="glass-input !py-2 w-full sm:!w-auto" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Tersedia" <?= $filter == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                <option value="Dipinjam" <?= $filter == 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                <option value="Rusak" <?= $filter == 'Rusak' ? 'selected' : '' ?>>Rusak</option>
                <option value="Kalibrasi" <?= $filter == 'Kalibrasi' ? 'selected' : '' ?>>Kalibrasi</option>
            </select>
        </form>
        <?php if (isRole('laboran')): ?>
        <button class="btn-glass btn-glass-primary whitespace-nowrap" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><i class="fas fa-plus mr-2"></i> Tambah Alat</button>
        <?php endif; ?>
    </div>
</div>

<?= showAlert() ?>

<?php if (empty($alat_list)): ?>
<div class="glass-card p-8 text-center">
    <i class="fas fa-box-open text-5xl text-muted mb-4"></i>
    <p class="text-muted font-medium">Tidak ada alat ditemukan.</p>
</div>
<?php endif; ?>

<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($alat_list as $alat): ?>
    <div class="glass-card overflow-hidden">
        <img src="<?= $base_url ?>uploads/foto_alat/<?= $alat['foto'] ?: 'default_alat.png' ?>" class="equipment-img w-full" alt="<?= htmlspecialchars($alat['nama_alat']) ?>"
             onerror="this.src='https://placehold.co/400x200/F4F7FE/A3AED0?text=<?= urlencode($alat['nama_alat']) ?>'">
        <div class="p-5">
            <div class="flex items-start justify-between mb-2">
                <h3 class="font-bold text-lg text-navy"><?= htmlspecialchars($alat['nama_alat']) ?></h3>
                <?= statusBadge($alat['status']) ?>
            </div>
            <p class="text-muted text-sm mb-3"><?= htmlspecialchars($alat['merk'] ?: 'Tanpa merk') ?></p>
            <div class="flex items-center gap-2 mb-3">
                <span class="glass-badge badge-primary"><i class="fas fa-boxes mr-1"></i> Stok: <?= (int)$alat['stok_tersedia'] ?>/<?= (int)$alat['stok_total'] ?></span>
                <span class="glass-badge badge-info"><i class="fas fa-tag mr-1"></i> <?= htmlspecialchars($alat['kode_alat']) ?></span>
            </div>
            <p class="text-sm text-muted flex items-center gap-1 mb-4"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($alat['lokasi_penyimpanan'] ?: 'Tidak tercatat') ?></p>
            <div class="flex gap-2">
                <button onclick="document.getElementById('detailModal<?= $alat['id'] ?>').classList.remove('hidden')" class="btn-glass btn-glass-outline flex-1 text-center justify-center">
                    <i class="fas fa-eye mr-1"></i> Lihat Detail
                </button>
                <?php if (isRole('laboran')): ?>
                <button class="btn-glass btn-glass-warning btn-sm" onclick="document.getElementById('editModal<?= $alat['id'] ?>').classList.remove('hidden')"><i class="fas fa-pen"></i></button>
                <button class="btn-glass btn-glass-danger btn-sm" onclick="confirmHapus(<?= $alat['id'] ?>, '<?= addslashes($alat['nama_alat']) ?>')"><i class="fas fa-trash"></i></button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div id="detailModal<?= $alat['id'] ?>" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/20 backdrop-blur-sm" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="glass-modal-content w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="modal-header flex items-center justify-between">
                <h3 class="text-xl font-bold text-navy"><?= htmlspecialchars($alat['nama_alat']) ?></h3>
                <button onclick="this.closest('.fixed').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-soft flex items-center justify-center text-muted hover:text-navy transition"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="md:w-2/5">
                        <img src="<?= $base_url ?>uploads/foto_alat/<?= $alat['foto'] ?: 'default_alat.png' ?>" class="w-full rounded-2xl"
                             onerror="this.src='https://placehold.co/400x300/F4F7FE/A3AED0?text=<?= urlencode($alat['nama_alat']) ?>'">
                    </div>
                    <div class="md:w-3/5 space-y-3">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div><span class="text-muted">Kode Alat</span><p class="font-semibold"><?= htmlspecialchars($alat['kode_alat']) ?></p></div>
                            <div><span class="text-muted">Merk</span><p class="font-semibold"><?= htmlspecialchars($alat['merk'] ?: '-') ?></p></div>
                            <div><span class="text-muted">Lokasi</span><p class="font-semibold"><?= htmlspecialchars($alat['lokasi_penyimpanan'] ?: '-') ?></p></div>
                            <div><span class="text-muted">Status</span><p><?= statusBadge($alat['status']) ?></p></div>
                            <div class="col-span-2"><span class="text-muted">Stok</span><p class="font-semibold"><?= (int)$alat['stok_tersedia'] ?> tersedia dari <?= (int)$alat['stok_total'] ?></p></div>
                        </div>
                        <hr class="divider">
                        <div><h4 class="font-bold text-navy mb-2">Spesifikasi Teknis</h4><p class="text-sm text-muted leading-relaxed"><?= nl2br(htmlspecialchars($alat['spesifikasi'] ?: 'Tidak ada spesifikasi')) ?></p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (isRole('laboran')): ?>
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

<?php foreach ($alat_list as $alat): ?>
<div id="editModal<?= $alat['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $alat['id'] ?>">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-pen mr-2"></i>Edit Alat: <?= htmlspecialchars($alat['nama_alat']) ?></h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('editModal<?= $alat['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-semibold text-navy mb-1">Kode Alat</label><input type="text" name="kode_alat" class="glass-input" value="<?= htmlspecialchars($alat['kode_alat']) ?>" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Nama Alat</label><input type="text" name="nama_alat" class="glass-input" value="<?= htmlspecialchars($alat['nama_alat']) ?>" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Merk</label><input type="text" name="merk" class="glass-input" value="<?= htmlspecialchars($alat['merk']) ?>"></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Lokasi</label><input type="text" name="lokasi_penyimpanan" class="glass-input" value="<?= htmlspecialchars($alat['lokasi_penyimpanan']) ?>"></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Stok Total</label><input type="number" name="stok_total" class="glass-input" value="<?= (int)$alat['stok_total'] ?>" min="1" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Status</label>
                        <select name="status" class="glass-input">
                            <option value="Tersedia" <?= $alat['status'] == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="Dipinjam" <?= $alat['status'] == 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                            <option value="Rusak" <?= $alat['status'] == 'Rusak' ? 'selected' : '' ?>>Rusak</option>
                            <option value="Kalibrasi" <?= $alat['status'] == 'Kalibrasi' ? 'selected' : '' ?>>Kalibrasi</option>
                            <option value="Tidak Tersedia" <?= $alat['status'] == 'Tidak Tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Foto Baru</label><input type="file" name="foto" class="glass-input" accept="image/*"></div>
                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-navy mb-1">Spesifikasi</label><textarea name="spesifikasi" class="glass-input" rows="3"><?= htmlspecialchars($alat['spesifikasi']) ?></textarea></div>
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('editModal<?= $alat['id'] ?>').classList.add('hidden')">Batal</button>
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
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
