<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Manajemen Bahan Habis Pakai';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'tambah') {
        query("INSERT INTO bahan_habis_pakai (nama_bahan, satuan, stok, stok_minimum) VALUES (?, ?, ?, ?)",
               [$_POST['nama_bahan'], $_POST['satuan'], $_POST['stok'], $_POST['stok_minimum']]);
        alert('success', 'Bahan berhasil ditambahkan!');
    } elseif ($action == 'edit') {
        query("UPDATE bahan_habis_pakai SET nama_bahan=?, satuan=?, stok=?, stok_minimum=? WHERE id=?",
               [$_POST['nama_bahan'], $_POST['satuan'], $_POST['stok'], $_POST['stok_minimum'], $_POST['id']]);
        alert('success', 'Data bahan diupdate!');
    } elseif ($action == 'hapus') {
        query("DELETE FROM bahan_habis_pakai WHERE id = ?", [$_POST['id']]);
        alert('success', 'Bahan dihapus!');
    } elseif ($action == 'tambah_stok') {
        $id = $_POST['id'];
        $tambah = (int)$_POST['tambah_stok'];
        query("UPDATE bahan_habis_pakai SET stok = stok + ? WHERE id = ?", [$tambah, $id]);
        alert('success', "Stok bertambah $tambah!");
    }
    redirect('manajemen_bahan.php');
}

$bahan_list = fetchAll("SELECT * FROM bahan_habis_pakai ORDER BY nama_bahan ASC");
$alert_count = getCount('bahan_habis_pakai', 'stok <= stok_minimum');
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="page-title"><i class="fas fa-flask mr-3"></i> Manajemen Bahan Habis Pakai</h1>
        <p class="page-subtitle">Kelola stok bahan laboratorium</p>
    </div>
    <button class="btn-glass btn-glass-primary" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><i class="fas fa-plus mr-2"></i> Tambah Bahan</button>
</div>

<?php if ($alert_count > 0): ?>
<div class="glass-alert alert-danger mb-6 font-semibold">
    <i class="fas fa-exclamation-triangle mr-2"></i> Terdapat <strong><?= $alert_count ?></strong> bahan dengan stok di bawah batas minimum!
</div>
<?php endif; ?>

<div class="glass-card p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
        <h5 class="font-bold text-navy"><i class="fas fa-list mr-2"></i> Daftar Bahan Habis Pakai</h5>
        <span class="glass-badge badge-danger self-start sm:self-auto">Alert jika stok &le; minimum</span>
    </div>
    <div class="overflow-x-auto">
        <table class="glass-table">
            <thead>
                <tr><th>No</th><th>Nama Bahan</th><th>Satuan</th><th>Stok Saat Ini</th><th>Stok Minimum</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($bahan_list as $i => $b): 
                    $is_kritis = $b['stok'] <= $b['stok_minimum'];
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($b['nama_bahan']) ?></td>
                    <td><?= htmlspecialchars($b['satuan']) ?></td>
                    <td><strong><?= (int)$b['stok'] ?></strong></td>
                    <td><?= (int)$b['stok_minimum'] ?></td>
                    <td>
                        <?php if ($is_kritis): ?>
                            <span class="glass-badge badge-danger"><i class="fas fa-exclamation-circle mr-1"></i> Kritis</span>
                        <?php else: ?>
                            <span class="glass-badge badge-success">Aman</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex gap-1">
                            <button class="btn-glass btn-glass-success btn-sm" onclick="document.getElementById('tambahStokModal<?= $b['id'] ?>').classList.remove('hidden')"><i class="fas fa-plus-circle"></i></button>
                            <button class="btn-glass btn-glass-warning btn-sm" onclick="document.getElementById('editModal<?= $b['id'] ?>').classList.remove('hidden')"><i class="fas fa-pen"></i></button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus <?= htmlspecialchars($b['nama_bahan']) ?>?')">
                                <input type="hidden" name="action" value="hapus"><input type="hidden" name="id" value="<?= $b['id'] ?>">
                                <button class="btn-glass btn-glass-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
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
    <div class="glass-modal-content w-full max-w-lg mx-4">
        <form method="POST"><input type="hidden" name="action" value="tambah">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-plus-circle mr-2"></i>Tambah Bahan Baru</h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Nama Bahan</label><input type="text" name="nama_bahan" class="glass-input" required></div>
                <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Satuan</label><input type="text" name="satuan" class="glass-input" placeholder="contoh: buah, tube, box, liter" required></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Stok Awal</label><input type="number" name="stok" class="glass-input" min="0" required></div>
                    <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Stok Minimum</label><input type="number" name="stok_minimum" class="glass-input" min="0" required></div>
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-primary"><i class="fas fa-save mr-2"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($bahan_list as $b): ?>
<!-- Modal Edit -->
<div id="editModal<?= $b['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-lg mx-4">
        <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= $b['id'] ?>">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-pen mr-2"></i>Edit Bahan</h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('editModal<?= $b['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Nama Bahan</label><input type="text" name="nama_bahan" class="glass-input" value="<?= htmlspecialchars($b['nama_bahan']) ?>" required></div>
                <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Satuan</label><input type="text" name="satuan" class="glass-input" value="<?= htmlspecialchars($b['satuan']) ?>" required></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Stok</label><input type="number" name="stok" class="glass-input" value="<?= (int)$b['stok'] ?>" min="0" required></div>
                    <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Stok Minimum</label><input type="number" name="stok_minimum" class="glass-input" value="<?= (int)$b['stok_minimum'] ?>" min="0" required></div>
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('editModal<?= $b['id'] ?>').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-primary"><i class="fas fa-save mr-2"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Stok -->
<div id="tambahStokModal<?= $b['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-lg mx-4">
        <form method="POST"><input type="hidden" name="action" value="tambah_stok"><input type="hidden" name="id" value="<?= $b['id'] ?>">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-plus-circle mr-2"></i>Tambah Stok: <?= htmlspecialchars($b['nama_bahan']) ?></h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('tambahStokModal<?= $b['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <p class="mb-4">Stok saat ini: <strong><?= (int)$b['stok'] ?> <?= htmlspecialchars($b['satuan']) ?></strong></p>
                <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Jumlah Tambahan</label><input type="number" name="tambah_stok" class="glass-input" min="1" required></div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('tambahStokModal<?= $b['id'] ?>').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-success"><i class="fas fa-plus-circle mr-2"></i> Tambah Stok</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>
