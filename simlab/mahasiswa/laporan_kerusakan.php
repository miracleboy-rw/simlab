<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('mahasiswa')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Laporan Kerusakan Alat';
$user_id = $_SESSION['user_id'];
include '../includes/header.php';

$alat_list = fetchAll("SELECT * FROM alat ORDER BY nama_alat ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alat_id = (int)($_POST['alat_id'] ?? 0);
    $kronologi = $_POST['kronologi'] ?? '';
    $gejala = $_POST['gejala'] ?? '';
    $tgl_kejadian = $_POST['tgl_kejadian'] ?? '';

    if ($alat_id && $kronologi && $gejala && $tgl_kejadian) {
        query("INSERT INTO laporan_kerusakan (user_id, alat_id, kronologi, gejala_kerusakan, tgl_kejadian, status) VALUES (?, ?, ?, ?, ?, 'Dilaporkan')",
               [$user_id, $alat_id, $kronologi, $gejala, $tgl_kejadian]);
        alert('success', 'Laporan kerusakan berhasil dikirim!');
    } else {
        alert('danger', 'Semua field harus diisi!');
    }
    redirect('laporan_kerusakan.php');
}

$laporan_saya = fetchAll("SELECT l.*, a.nama_alat, a.kode_alat
                          FROM laporan_kerusakan l
                          JOIN alat a ON l.alat_id = a.id
                          WHERE l.user_id = ?
                          ORDER BY l.created_at DESC", [$user_id]);
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-exclamation-triangle mr-3"></i> Laporan Kerusakan Alat</h1>
    <p class="page-subtitle">Laporkan kerusakan alat saat praktikum</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    <div class="lg:col-span-2">
        <div class="glass-card p-6">
            <h5 class="font-bold text-navy mb-4"><i class="fas fa-pen mr-2"></i> Form Laporan</h5>
            <form method="POST">
                <div class="mb-4">
                    <label class="block font-bold text-navy mb-2">Alat <span class="text-red-500">*</span></label>
                    <select name="alat_id" class="glass-input" required>
                        <option value="">Pilih alat</option>
                        <?php foreach ($alat_list as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama_alat']) ?> (<?= htmlspecialchars($a['kode_alat']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block font-bold text-navy mb-2">Tanggal Kejadian <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_kejadian" class="glass-input" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="mb-4">
                    <label class="block font-bold text-navy mb-2">Kronologi Kejadian <span class="text-red-500">*</span></label>
                    <textarea name="kronologi" class="glass-input" rows="3" placeholder="Jelaskan kronologi kejadian..." required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block font-bold text-navy mb-2">Gejala Kerusakan <span class="text-red-500">*</span></label>
                    <textarea name="gejala" class="glass-input" rows="3" placeholder="Jelaskan gejala kerusakan yang terlihat..." required></textarea>
                </div>
                <button type="submit" class="btn-glass btn-glass-accent w-full"><i class="fas fa-paper-plane mr-2"></i> Kirim Laporan</button>
            </form>
        </div>
    </div>
    <div class="lg:col-span-3">
        <div class="glass-card p-6">
            <h5 class="font-bold text-navy mb-4"><i class="fas fa-list mr-2"></i> Riwayat Laporan Saya</h5>
            <?php if (empty($laporan_saya)): ?>
            <p class="text-muted">Belum ada laporan kerusakan.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="glass-table">
                    <thead>
                        <tr><th>Alat</th><th>Tanggal</th><th>Gejala</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan_saya as $l): ?>
                        <tr>
                            <td><?= htmlspecialchars($l['nama_alat']) ?></td>
                            <td><?= formatTanggalIndo($l['tgl_kejadian']) ?></td>
                            <td class="max-w-[200px] truncate"><?= htmlspecialchars($l['gejala_kerusakan']) ?></td>
                            <td><?= statusBadge($l['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
