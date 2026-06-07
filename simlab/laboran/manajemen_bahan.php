<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Manajemen Bahan Habis Pakai';

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

include '../includes/header.php';

$bahan_list = fetchAll("SELECT * FROM bahan_habis_pakai ORDER BY nama_bahan ASC");
$alert_count = getCount('bahan_habis_pakai', 'stok <= stok_minimum');
?>
<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:24px;gap:16px">
    <div>
        <h1 class="page-title"><span class="material-symbols-outlined mr-3">science</span> Manajemen Bahan Habis Pakai</h1>
        <p class="page-subtitle">Kelola stok bahan laboratorium</p>
    </div>
    <button class="btn btn-primary btn-md3" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><span class="material-symbols-outlined mr-2">add</span> Tambah Bahan</button>
</div>

<?php if ($alert_count > 0): ?>
<div class="glass-alert alert-danger mb-6" style="font-weight:600">
    <span class="material-symbols-outlined mr-2">warning</span> Terdapat <strong><?= $alert_count ?></strong> bahan dengan stok di bawah batas minimum!
</div>
<?php endif; ?>

<div class="card p-5">
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:16px;gap:8px">
        <h5 style="font-weight:700; color:#111827"><span class="material-symbols-outlined mr-2">list</span> Daftar Bahan Habis Pakai</h5>
        <span class="badge" style="background:#FEE2E2; color:#DC2626">Alert jika stok &le; minimum</span>
    </div>
    <div class="table-wrapper">
        <table class="table">
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
                            <span class="badge" style="background:#FEE2E2; color:#DC2626"><span class="material-symbols-outlined mr-1">error</span> Kritis</span>
                        <?php else: ?>
                            <span class="badge" style="background:#DCFCE7; color:#16A34A">Aman</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex gap-1">
                            <button class="btn btn-success btn-sm btn-md3" onclick="document.getElementById('tambahStokModal<?= $b['id'] ?>').classList.remove('hidden')"><span class="material-symbols-outlined">add_circle</span></button>
                            <button class="btn btn-warning btn-sm btn-md3" onclick="document.getElementById('editModal<?= $b['id'] ?>').classList.remove('hidden')"><span class="material-symbols-outlined">edit</span></button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus <?= htmlspecialchars($b['nama_bahan']) ?>?')">
                                <input type="hidden" name="action" value="hapus"><input type="hidden" name="id" value="<?= $b['id'] ?>">
                                <button class="btn btn-danger btn-sm btn-md3"><span class="material-symbols-outlined">delete</span></button>
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
    <div class="card w-full max-w-lg mx-4 p-5">
        <form method="POST"><input type="hidden" name="action" value="tambah">
            <div class="card-header flex justify-between items-center"><h5 style="font-weight:700" class="text-lg"><span class="material-symbols-outlined mr-2">add_circle</span>Tambah Bahan Baru</h5><button type="button" style="color:#9CA3AF; font-size:1.5rem; background:none; border:none; cursor:pointer" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Nama Bahan</label><input type="text" name="nama_bahan" class="form-input" required></div>
                <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Satuan</label><input type="text" name="satuan" class="form-input" placeholder="contoh: buah, tube, box, liter" required></div>
                <div class="grid-2">
                    <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Stok Awal</label><input type="number" name="stok" class="form-input" min="0" required></div>
                    <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Stok Minimum</label><input type="number" name="stok_minimum" class="form-input" min="0" required></div>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary btn-md3"><span class="material-symbols-outlined mr-2">save</span> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($bahan_list as $b): ?>
<!-- Modal Edit -->
<div id="editModal<?= $b['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="card w-full max-w-lg mx-4 p-5">
        <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= $b['id'] ?>">
            <div class="card-header flex justify-between items-center"><h5 style="font-weight:700" class="text-lg"><span class="material-symbols-outlined mr-2">edit</span>Edit Bahan</h5><button type="button" style="color:#9CA3AF; font-size:1.5rem; background:none; border:none; cursor:pointer" onclick="document.getElementById('editModal<?= $b['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Nama Bahan</label><input type="text" name="nama_bahan" class="form-input" value="<?= htmlspecialchars($b['nama_bahan']) ?>" required></div>
                <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Satuan</label><input type="text" name="satuan" class="form-input" value="<?= htmlspecialchars($b['satuan']) ?>" required></div>
                <div class="grid-2">
                    <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Stok</label><input type="number" name="stok" class="form-input" value="<?= (int)$b['stok'] ?>" min="0" required></div>
                    <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Stok Minimum</label><input type="number" name="stok_minimum" class="form-input" value="<?= (int)$b['stok_minimum'] ?>" min="0" required></div>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editModal<?= $b['id'] ?>').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary btn-md3"><span class="material-symbols-outlined mr-2">save</span> Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Stok -->
<div id="tambahStokModal<?= $b['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="card w-full max-w-lg mx-4 p-5">
        <form method="POST"><input type="hidden" name="action" value="tambah_stok"><input type="hidden" name="id" value="<?= $b['id'] ?>">
            <div class="card-header flex justify-between items-center"><h5 style="font-weight:700" class="text-lg"><span class="material-symbols-outlined mr-2">add_circle</span>Tambah Stok: <?= htmlspecialchars($b['nama_bahan']) ?></h5><button type="button" style="color:#9CA3AF; font-size:1.5rem; background:none; border:none; cursor:pointer" onclick="document.getElementById('tambahStokModal<?= $b['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <p class="mb-4">Stok saat ini: <strong><?= (int)$b['stok'] ?> <?= htmlspecialchars($b['satuan']) ?></strong></p>
                <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Jumlah Tambahan</label><input type="number" name="tambah_stok" class="form-input" min="1" required></div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('tambahStokModal<?= $b['id'] ?>').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-success btn-md3"><span class="material-symbols-outlined mr-2">add_circle</span> Tambah Stok</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>
