<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Kalibrasi & Maintenance';
include '../includes/header.php';

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
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="page-title"><i class="fas fa-calendar-check mr-3"></i> Kalibrasi & Maintenance</h1>
        <p class="page-subtitle">Jadwalkan kalibrasi dan perawatan alat</p>
    </div>
    <button class="btn-glass btn-glass-primary" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><i class="fas fa-plus mr-2"></i> Tambah Jadwal</button>
</div>

<div class="glass-card p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <h5 class="font-bold text-navy"><i class="fas fa-list mr-2"></i> Riwayat & Jadwal Kalibrasi/Maintenance</h5>
        <form method="GET" class="sm:w-48">
            <select name="status" class="glass-input" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Terjadwal" <?= $filter_status == 'Terjadwal' ? 'selected' : '' ?>>Terjadwal</option>
                <option value="Sedang Berjalan" <?= $filter_status == 'Sedang Berjalan' ? 'selected' : '' ?>>Sedang Berjalan</option>
                <option value="Selesai" <?= $filter_status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
            </select>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="glass-table">
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
                    <td><span class="glass-badge <?= $km['tipe'] == 'Kalibrasi' ? 'badge-danger' : ($km['tipe'] == 'Maintenance' ? 'badge-warning' : 'badge-info') ?>"><?= htmlspecialchars($km['tipe']) ?></span></td>
                    <td><?= formatTanggalIndo($km['tgl_mulai']) ?></td>
                    <td><?= $km['tgl_selesai'] ? formatTanggalIndo($km['tgl_selesai']) : '-' ?></td>
                    <td><?= htmlspecialchars($km['keterangan'] ?: '-') ?></td>
                    <td><?= statusBadge($km['status']) ?></td>
                    <td>
                        <div class="flex gap-1">
                            <?php if ($km['status'] != 'Selesai'): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Tandai selesai?')">
                                <input type="hidden" name="action" value="selesai"><input type="hidden" name="id" value="<?= $km['id'] ?>">
                                <button class="btn-glass btn-glass-success btn-sm"><i class="fas fa-check"></i></button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus jadwal?')">
                                <input type="hidden" name="action" value="hapus"><input type="hidden" name="id" value="<?= $km['id'] ?>">
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

<div id="tambahModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-lg mx-4">
        <form method="POST"><input type="hidden" name="action" value="tambah">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-plus-circle mr-2"></i>Tambah Jadwal Baru</h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-navy mb-1">Alat</label>
                    <select name="alat_id" class="glass-input" required>
                        <option value="">Pilih alat</option>
                        <?php foreach ($alat_list as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama_alat']) ?> (<?= htmlspecialchars($a['kode_alat']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-navy mb-1">Tipe</label>
                    <select name="tipe" class="glass-input" required>
                        <option value="Kalibrasi">Kalibrasi</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Servis">Servis</option>
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Tanggal Mulai</label><input type="date" name="tgl_mulai" class="glass-input" required></div>
                    <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Tanggal Selesai</label><input type="date" name="tgl_selesai" class="glass-input"></div>
                </div>
                <div class="mb-4"><label class="block text-sm font-semibold text-navy mb-1">Keterangan</label><textarea name="keterangan" class="glass-input" rows="2"></textarea></div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-primary"><i class="fas fa-save mr-2"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
