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
<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;margin-bottom:32px">
    <div>
        <h1 class="page-title"><span class="material-symbols-outlined" style="color:#2a4dd7;margin-right:12px">biotech</span>Katalog Alat & Bahan</h1>
        <p class="page-subtitle">Temukan alat laboratorium yang tersedia</p>
    </div>
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:8px">
            <div class="search-box">
                <span class="material-symbols-outlined">search</span>
                <input type="text" name="search" class="form-input" placeholder="Cari alat..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="filter" class="form-input" style="width:auto" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Tersedia" <?= $filter == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                <option value="Dipinjam" <?= $filter == 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                <option value="Rusak" <?= $filter == 'Rusak' ? 'selected' : '' ?>>Rusak</option>
                <option value="Kalibrasi" <?= $filter == 'Kalibrasi' ? 'selected' : '' ?>>Kalibrasi</option>
            </select>
        </form>
        <?php if (isRole('laboran')): ?>
        <button class="btn btn-primary" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><span class="material-symbols-outlined">add</span> Tambah Alat</button>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($alat_list)): ?>
<div class="card p-8 text-center">
    <span class="material-symbols-outlined text-5xl text-muted mb-4">inventory_2</span>
    <p class="text-muted font-medium">Tidak ada alat ditemukan.</p>
</div>
<?php endif; ?>

<div class="equipment-grid">
    <?php foreach ($alat_list as $alat): ?>
    <div class="card" style="overflow:hidden">
        <img src="<?= $base_url ?>uploads/foto_alat/<?= $alat['foto'] ?: 'default_alat.png' ?>" style="width:100%;height:180px;object-fit:cover" alt="<?= htmlspecialchars($alat['nama_alat']) ?>"
             onerror="this.src='https://placehold.co/400x200/F4F7FE/A3AED0?text=<?= urlencode($alat['nama_alat']) ?>'">
        <div style="padding:20px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
                <h3 style="font-weight:700;font-size:15px"><?= htmlspecialchars($alat['nama_alat']) ?></h3>
                <?= statusBadge($alat['status']) ?>
            </div>
            <p style="color:#6B7280;font-size:12px;margin-bottom:12px"><?= htmlspecialchars($alat['merk'] ?: 'Tanpa merk') ?></p>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                <span class="badge" style="background:#DBEAFE;color:#2a4dd7"><span class="material-symbols-outlined" style="font-size:14px;margin-right:4px">inventory_2</span> Stok: <?= (int)$alat['stok_tersedia'] ?>/<?= (int)$alat['stok_total'] ?></span>
                <span class="badge" style="background:#f2f4f7;color:#6B7280"><span class="material-symbols-outlined" style="font-size:14px;margin-right:4px">label</span> <?= htmlspecialchars($alat['kode_alat']) ?></span>
            </div>
            <p style="font-size:12px;color:#6B7280;display:flex;align-items:center;gap:4px;margin-bottom:16px"><span class="material-symbols-outlined" style="font-size:16px">location_on</span> <?= htmlspecialchars($alat['lokasi_penyimpanan'] ?: 'Tidak tercatat') ?></p>
            <div style="display:flex;gap:8px">
                <button onclick="document.getElementById('detailModal<?= $alat['id'] ?>').classList.remove('hidden')" class="btn btn-outline" style="flex:1;justify-content:center">
                    <span class="material-symbols-outlined" style="margin-right:4px">visibility</span> Lihat Detail
                </button>
                <?php if (isRole('laboran')): ?>
                <button class="btn btn-warning btn-sm btn-md3" onclick="document.getElementById('editModal<?= $alat['id'] ?>').classList.remove('hidden')"><span class="material-symbols-outlined">edit</span></button>
                <button class="btn btn-danger btn-sm btn-md3" onclick="confirmHapus(<?= $alat['id'] ?>, '<?= addslashes($alat['nama_alat']) ?>')"><span class="material-symbols-outlined">delete</span></button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div id="detailModal<?= $alat['id'] ?>" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/20 backdrop-blur-sm" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="card w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="card-header flex items-center justify-between">
                <h3 class="text-xl font-bold "><?= htmlspecialchars($alat['nama_alat']) ?></h3>
                <button onclick="this.closest('.fixed').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-soft flex items-center justify-center text-muted hover:text-navy transition"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="card-body">
                    <div style="display:flex;flex-direction:row;gap:24px">
                        <div style="width:40%">
                            <img src="<?= $base_url ?>uploads/foto_alat/<?= $alat['foto'] ?: 'default_alat.png' ?>" class="w-full rounded-2xl"
                                 onerror="this.src='https://placehold.co/400x300/F4F7FE/A3AED0?text=<?= urlencode($alat['nama_alat']) ?>'">
                        </div>
                        <div style="width:60%" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div><span class="text-muted">Kode Alat</span><p class="font-semibold"><?= htmlspecialchars($alat['kode_alat']) ?></p></div>
                            <div><span class="text-muted">Merk</span><p class="font-semibold"><?= htmlspecialchars($alat['merk'] ?: '-') ?></p></div>
                            <div><span class="text-muted">Lokasi</span><p class="font-semibold"><?= htmlspecialchars($alat['lokasi_penyimpanan'] ?: '-') ?></p></div>
                            <div><span class="text-muted">Status</span><p><?= statusBadge($alat['status']) ?></p></div>
                            <div class="col-span-2"><span class="text-muted">Stok</span><p class="font-semibold"><?= (int)$alat['stok_tersedia'] ?> tersedia dari <?= (int)$alat['stok_total'] ?></p></div>
                        </div>
                        <hr class="divider">
                        <div><h4 class="font-bold  mb-2">Spesifikasi Teknis</h4><p class="text-sm text-muted leading-relaxed"><?= nl2br(htmlspecialchars($alat['spesifikasi'] ?: 'Tidak ada spesifikasi')) ?></p></div>
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
    <div class="card w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="tambah">
            <div class="card-header flex justify-between items-center"><h5 class="font-bold text-lg"><span class="material-symbols-outlined mr-2">add_circle</span>Tambah Alat Baru</h5><button type="button" class="text-muted2 hover:text-navy text-xl" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <div class="grid-2">
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Kode Alat</label><input type="text" name="kode_alat" class="form-input" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Nama Alat</label><input type="text" name="nama_alat" class="form-input" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Merk</label><input type="text" name="merk" class="form-input"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Lokasi Penyimpanan</label><input type="text" name="lokasi_penyimpanan" class="form-input"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Stok Total</label><input type="number" name="stok_total" class="form-input" value="1" min="1" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Foto Alat</label><input type="file" name="foto" class="form-input" accept="image/*"></div>
                    <div class="col-span-2"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Spesifikasi</label><textarea name="spesifikasi" class="form-input" rows="3"></textarea></div>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline btn-md3" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary btn-md3"><span class="material-symbols-outlined mr-2">save</span> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($alat_list as $alat): ?>
<div id="editModal<?= $alat['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="card w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $alat['id'] ?>">
            <div class="card-header flex justify-between items-center"><h5 class="font-bold text-lg"><span class="material-symbols-outlined mr-2">edit</span>Edit Alat: <?= htmlspecialchars($alat['nama_alat']) ?></h5><button type="button" class="text-muted2 hover:text-navy text-xl" onclick="document.getElementById('editModal<?= $alat['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <div class="grid-2">
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Kode Alat</label><input type="text" name="kode_alat" class="form-input" value="<?= htmlspecialchars($alat['kode_alat']) ?>" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Nama Alat</label><input type="text" name="nama_alat" class="form-input" value="<?= htmlspecialchars($alat['nama_alat']) ?>" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Merk</label><input type="text" name="merk" class="form-input" value="<?= htmlspecialchars($alat['merk']) ?>"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Lokasi</label><input type="text" name="lokasi_penyimpanan" class="form-input" value="<?= htmlspecialchars($alat['lokasi_penyimpanan']) ?>"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Stok Total</label><input type="number" name="stok_total" class="form-input" value="<?= (int)$alat['stok_total'] ?>" min="1" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Status</label>
                        <select name="status" class="form-input">
                            <option value="Tersedia" <?= $alat['status'] == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="Dipinjam" <?= $alat['status'] == 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                            <option value="Rusak" <?= $alat['status'] == 'Rusak' ? 'selected' : '' ?>>Rusak</option>
                            <option value="Kalibrasi" <?= $alat['status'] == 'Kalibrasi' ? 'selected' : '' ?>>Kalibrasi</option>
                            <option value="Tidak Tersedia" <?= $alat['status'] == 'Tidak Tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
                        </select>
                    </div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Foto Baru</label><input type="file" name="foto" class="form-input" accept="image/*"></div>
                    <div class="col-span-2"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Spesifikasi</label><textarea name="spesifikasi" class="form-input" rows="3"><?= htmlspecialchars($alat['spesifikasi']) ?></textarea></div>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline btn-md3" onclick="document.getElementById('editModal<?= $alat['id'] ?>').classList.add('hidden')">Batal</button>
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
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
