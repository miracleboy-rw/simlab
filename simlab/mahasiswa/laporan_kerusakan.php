<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('mahasiswa')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Laporan Kerusakan Alat';
$user_id = $_SESSION['user_id'];

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

include '../includes/header.php';

$alat_list = fetchAll("SELECT * FROM alat ORDER BY nama_alat ASC");

$laporan_saya = fetchAll("SELECT l.*, a.nama_alat, a.kode_alat
                          FROM laporan_kerusakan l
                          JOIN alat a ON l.alat_id = a.id
                          WHERE l.user_id = ?
                          ORDER BY l.created_at DESC", [$user_id]);
?>
<div class="mb-6">
    <h1 class="page-title"><span class="material-symbols-outlined" style="margin-right:12px">warning</span> Laporan Kerusakan Alat</h1>
    <p class="text-muted">Laporkan kerusakan alat saat praktikum</p>
</div>

<div class="grid-2fr-3fr" style="gap:24px">
    <div class="card p-5">
        <h5 class="section-header"><span class="material-symbols-outlined" style="margin-right:8px">edit</span> Form Laporan</h5>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Alat <span style="color:#EF4444">*</span></label>
                <select name="alat_id" class="form-input" required>
                    <option value="">Pilih alat</option>
                    <?php foreach ($alat_list as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama_alat']) ?> (<?= htmlspecialchars($a['kode_alat']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Kejadian <span style="color:#EF4444">*</span></label>
                <input type="date" name="tgl_kejadian" class="form-input" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Kronologi Kejadian <span style="color:#EF4444">*</span></label>
                <textarea name="kronologi" class="form-textarea" rows="3" placeholder="Jelaskan kronologi kejadian..." required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Gejala Kerusakan <span style="color:#EF4444">*</span></label>
                <textarea name="gejala" class="form-textarea" rows="3" placeholder="Jelaskan gejala kerusakan yang terlihat..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-full"><span class="material-symbols-outlined" style="margin-right:8px">send</span> Kirim Laporan</button>
        </form>
    </div>
    <div class="card p-5">
        <h5 class="section-header"><span class="material-symbols-outlined" style="margin-right:8px">list</span> Riwayat Laporan Saya</h5>
        <?php if (empty($laporan_saya)): ?>
        <p class="text-muted">Belum ada laporan kerusakan.</p>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Alat</th><th>Tanggal</th><th>Gejala</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($laporan_saya as $l): ?>
                    <tr>
                        <td><?= htmlspecialchars($l['nama_alat']) ?></td>
                        <td><?= formatTanggalIndo($l['tgl_kejadian']) ?></td>
                        <td style="max-width:200px" class="truncate"><?= htmlspecialchars($l['gejala_kerusakan']) ?></td>
                        <td><?= statusBadge($l['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
