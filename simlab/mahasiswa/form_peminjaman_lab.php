<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('mahasiswa')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Peminjaman Laboratorium';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pinjam'])) {
    $lab_id = (int)($_POST['lab_id'] ?? 0);
    $tujuan = $_POST['tujuan_peminjaman'] ?? '';
    $tgl_pinjam = $_POST['tgl_pinjam'] ?? '';
    $tgl_kembali = $_POST['tgl_kembali'] ?? '';
    $jam_mulai = $_POST['jam_mulai'] ?? '08:00';
    $jam_selesai = $_POST['jam_selesai'] ?? '17:00';
    $deskripsi = $_POST['deskripsi'] ?? '';

    $today = date('Y-m-d');
    if (!$lab_id) { alert('danger', 'Pilih laboratorium!'); }
    elseif ($tgl_pinjam < $today) { alert('danger', 'Tanggal pinjam tidak boleh sebelum hari ini!'); }
    elseif ($tgl_kembali < $tgl_pinjam) { alert('danger', 'Tanggal kembali tidak valid!'); }
    else {
        $kode = 'PJL-' . date('Ymd') . '-' . rand(100, 999);
        try {
            insertGetId(
                "INSERT INTO peminjaman_lab (user_id, lab_id, kode_peminjaman, tgl_pinjam, tgl_kembali, jam_mulai, jam_selesai, tujuan_peminjaman, deskripsi, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')",
                [$user_id, $lab_id, $kode, $tgl_pinjam, $tgl_kembali, $jam_mulai, $jam_selesai, $tujuan, $deskripsi]
            );
            alert('success', "Peminjaman lab berhasil diajukan! Kode: $kode");
            redirect('tracking_peminjaman_lab.php');
        } catch (Exception $e) {
            alert('danger', 'Gagal: ' . $e->getMessage());
        }
    }
}

include '../includes/header.php';

$lab_list = fetchAll("SELECT * FROM laboratorium ORDER BY nama_lab ASC");
?>
<div class="mb-6">
    <h1 class="page-title"><span class="material-symbols-outlined" style="color:#2a4dd7;margin-right:12px">meeting_room</span>Peminjaman Laboratorium</h1>
    <p class="text-muted">Ajukan peminjaman ruang laboratorium untuk kegiatan akademik</p>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
    <div class="card p-5">
        <h3 class="section-header"><span class="material-symbols-outlined" style="color:#2a4dd7;margin-right:8px">edit</span>Form Peminjaman Lab</h3>
        <form method="POST">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:16px">
                <div class="form-group">
                    <label class="form-label">Pilih Laboratorium <span style="color:#EF4444">*</span></label>
                    <select name="lab_id" class="form-input" required>
                        <option value="">— Pilih Lab —</option>
                        <?php foreach ($lab_list as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nama_lab']) ?> (<?= htmlspecialchars($l['kode_lab']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tujuan Peminjaman <span style="color:#EF4444">*</span></label>
                    <select name="tujuan_peminjaman" class="form-input" required>
                        <option value="">— Pilih Tujuan —</option>
                        <option value="Tugas Besar">Penelitian Tugas Besar Mata Kuliah</option>
                        <option value="Penelitian TA">Penelitian Tugas Akhir</option>
                        <option value="Seminar KP">Seminar Kerja Praktik</option>
                        <option value="Seminar Proposal">Seminar Proposal</option>
                        <option value="Sidang Akhir">Sidang Akhir</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Pinjam <span style="color:#EF4444">*</span></label>
                    <input type="date" name="tgl_pinjam" class="form-input" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Kembali <span style="color:#EF4444">*</span></label>
                    <input type="date" name="tgl_kembali" class="form-input" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Jam Mulai <span style="color:#EF4444">*</span></label>
                    <input type="time" name="jam_mulai" class="form-input" value="08:00" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Jam Selesai <span style="color:#EF4444">*</span></label>
                    <input type="time" name="jam_selesai" class="form-input" value="17:00" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi Kegiatan</label>
                <textarea name="deskripsi" class="form-textarea" rows="3" placeholder="Jelaskan kegiatan yang akan dilakukan di laboratorium..."></textarea>
            </div>
            <button type="submit" name="submit_pinjam" class="btn btn-primary"><span class="material-symbols-outlined" style="margin-right:8px">send</span> Ajukan Peminjaman</button>
        </form>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card p-5">
            <h3 class="section-header" style="margin-bottom:12px"><span class="material-symbols-outlined" style="color:#2a4dd7;margin-right:8px">info</span><?= count($semua_lab) ?> Laboratorium</h3>
            <?php $semua_lab = fetchAll("SELECT * FROM laboratorium ORDER BY nama_lab ASC"); ?>
            <div style="display:flex;flex-direction:column;gap:12px">
                <?php foreach ($semua_lab as $l): ?>
                <div style="padding:12px;border-radius:12px;background:#f7f9fc;border:1px solid rgba(229,231,235,0.5)">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;gap:4px;flex-wrap:wrap">
                        <span class="font-bold text-sm"><?= htmlspecialchars($l['nama_lab']) ?></span>
                        <?php
                        $badge_map = ['Tersedia' => ['bg'=>'#DCFCE7','color'=>'#16A34A'], 'Digunakan' => ['bg'=>'#FEF3C7','color'=>'#D97706']];
                        $bc = $badge_map[$l['status']] ?? ['bg'=>'#FEE2E2','color'=>'#DC2626'];
                        ?>
                        <span class="badge" style="background:<?= $bc['bg'] ?>;color:<?= $bc['color'] ?>;font-size:11px"><?= $l['status'] ?></span>
                    </div>
                    <div style="font-size:12px;color:#6B7280">
                        <div style="display:flex;align-items:center;gap:4px"><span class="material-symbols-outlined" style="font-size:16px">label</span> <?= htmlspecialchars($l['kode_lab']) ?></div>
                        <div style="display:flex;align-items:center;gap:4px"><span class="material-symbols-outlined" style="font-size:16px">location_on</span> <?= htmlspecialchars($l['lokasi'] ?: '-') ?></div>
                        <div style="display:flex;align-items:center;gap:4px"><span class="material-symbols-outlined" style="font-size:16px">group</span> Kapasitas <?= (int)$l['kapasitas'] ?> orang</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card p-5">
            <h3 class="section-header" style="margin-bottom:8px"><span class="material-symbols-outlined" style="color:#6B7280;margin-right:8px">schedule</span>Jam Operasional</h3>
            <div style="font-size:13px;color:#6B7280">
                <p style="display:flex;align-items:center;gap:4px"><span class="material-symbols-outlined" style="font-size:18px">calendar_today</span> Senin - Jumat: 08:00 - 17:00</p>
                <p style="display:flex;align-items:center;gap:4px"><span class="material-symbols-outlined" style="font-size:18px">calendar_today</span> Sabtu: 08:00 - 13:00</p>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
