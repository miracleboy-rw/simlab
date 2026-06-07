<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Kalibrasi & Maintenance';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'tambah') {
        query("INSERT INTO kalibrasi_maintenance (alat_id, tipe, tgl_mulai, tgl_selesai, keterangan, status) VALUES (?, ?, ?, ?, ?, 'Terjadwal')",
               [$_POST['alat_id'], $_POST['tipe'], $_POST['tgl_mulai'], $_POST['tgl_selesai'] ?: null, $_POST['keterangan']]);
        query("INSERT INTO kalender_events (alat_id, judul, tgl_mulai, tgl_selesai, tipe, warna) VALUES (?, ?, ?, ?, ?, ?)",
               [$_POST['alat_id'], $_POST['tipe'] . ': ' . fetchOne("SELECT nama_alat FROM alat WHERE id=?", [$_POST['alat_id']])['nama_alat'],
                $_POST['tgl_mulai'], $_POST['tgl_selesai'] ?: $_POST['tgl_mulai'],
                $_POST['tipe'], $_POST['tipe'] == 'Kalibrasi' ? '#dc3545' : '#ffc107']);
        alert('success', 'Jadwal ' . $_POST['tipe'] . ' berhasil ditambahkan!');
    } elseif ($action == 'selesai') {
        query("UPDATE kalibrasi_maintenance SET status = 'Selesai' WHERE id = ?", [$_POST['id']]);
        alert('success', 'Status diubah menjadi Selesai.');
    } elseif ($action == 'hapus') {
        query("DELETE FROM kalibrasi_maintenance WHERE id = ?", [$_POST['id']]);
        alert('success', 'Jadwal dihapus.');
    }
    redirect('kalibrasi_maintenance.php');
}

include '../includes/header.php';

$alat_list = fetchAll("SELECT * FROM alat ORDER BY nama_alat ASC");
$filter_status = $_GET['status'] ?? '';
$sql = "SELECT km.*, a.nama_alat, a.kode_alat FROM kalibrasi_maintenance km JOIN alat a ON km.alat_id = a.id";
$params = [];
if ($filter_status) {
    $sql .= " WHERE km.status = ?";
    $params[] = $filter_status;
}
$sql .= " ORDER BY km.tgl_mulai DESC";
$km_list = fetchAll($sql, $params);
?>
<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:24px;gap:16px">
    <div>
        <h1 class="page-title"><span class="material-symbols-outlined mr-3">calendar_month</span> Kalibrasi & Maintenance</h1>
        <p class="page-subtitle">Jadwalkan kalibrasi dan perawatan alat</p>
    </div>
    <button class="btn btn-primary btn-md3" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><span class="material-symbols-outlined mr-2">add</span> Tambah Jadwal</button>
</div>

<div class="card p-5">
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px">
        <h5 style="font-weight:700; color:#111827"><span class="material-symbols-outlined mr-2">list</span> Riwayat & Jadwal Kalibrasi/Maintenance</h5>
        <form method="GET">
            <select name="status" class="form-input" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Terjadwal" <?= $filter_status == 'Terjadwal' ? 'selected' : '' ?>>Terjadwal</option>
                <option value="Sedang Berjalan" <?= $filter_status == 'Sedang Berjalan' ? 'selected' : '' ?>>Sedang Berjalan</option>
                <option value="Selesai" <?= $filter_status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
            </select>
        </form>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr><th>Alat</th><th>Tipe</th><th>Tanggal Mulai</th><th>Tanggal Selesai</th><th>Keterangan</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($km_list)): ?>
                <tr><td colspan="7" class="text-center text-muted">Belum ada data</td></tr>
                <?php endif; ?>
                <?php foreach ($km_list as $km): ?>
                <tr>
                    <td><?= htmlspecialchars($km['nama_alat']) ?> <small class="text-muted">(<?= htmlspecialchars($km['kode_alat']) ?>)</small></td>
                    <td><span class="badge" style="<?= $km['tipe'] == 'Kalibrasi' ? 'background:#FEE2E2; color:#DC2626' : ($km['tipe'] == 'Maintenance' ? 'background:#FEF3C7; color:#D97706' : 'background:#E0F2FE; color:#0284C7') ?>"><?= htmlspecialchars($km['tipe']) ?></span></td>
                    <td><?= formatTanggalIndo($km['tgl_mulai']) ?></td>
                    <td><?= $km['tgl_selesai'] ? formatTanggalIndo($km['tgl_selesai']) : '-' ?></td>
                    <td><?= htmlspecialchars($km['keterangan'] ?: '-') ?></td>
                    <td><?= statusBadge($km['status']) ?></td>
                    <td>
                        <div class="flex gap-1">
                            <?php if ($km['status'] != 'Selesai'): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Tandai selesai?')">
                                <input type="hidden" name="action" value="selesai"><input type="hidden" name="id" value="<?= $km['id'] ?>">
                                <button class="btn btn-success btn-sm btn-md3"><span class="material-symbols-outlined">check</span></button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus jadwal?')">
                                <input type="hidden" name="action" value="hapus"><input type="hidden" name="id" value="<?= $km['id'] ?>">
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

<div id="tambahModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="card w-full max-w-lg mx-4 p-5">
        <form method="POST"><input type="hidden" name="action" value="tambah">
            <div class="card-header flex justify-between items-center"><h5 style="font-weight:700" class="text-lg"><span class="material-symbols-outlined mr-2">add_circle</span>Tambah Jadwal Baru</h5><button type="button" style="color:#9CA3AF; font-size:1.5rem; background:none; border:none; cursor:pointer" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <div class="mb-4">
                    <label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Alat</label>
                    <select name="alat_id" class="form-input" required>
                        <option value="">Pilih alat</option>
                        <?php foreach ($alat_list as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama_alat']) ?> (<?= htmlspecialchars($a['kode_alat']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Tipe</label>
                    <select name="tipe" class="form-input" required>
                        <option value="Kalibrasi">Kalibrasi</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Servis">Servis</option>
                    </select>
                </div>
                <div class="grid-2">
                    <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Tanggal Mulai</label><input type="date" name="tgl_mulai" class="form-input" required></div>
                    <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Tanggal Selesai</label><input type="date" name="tgl_selesai" class="form-input"></div>
                </div>
                <div class="mb-4"><label style="display:block;font-size:12px;font-weight:600;color:#111827;margin-bottom:4px">Keterangan</label><textarea name="keterangan" class="form-input" rows="2"></textarea></div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary btn-md3"><span class="material-symbols-outlined mr-2">save</span> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
