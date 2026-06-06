<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('mahasiswa')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Peminjaman Laboratorium';
$user_id = $_SESSION['user_id'];
include '../includes/header.php';

$lab_list = fetchAll("SELECT * FROM laboratorium ORDER BY nama_lab ASC");

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
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-door-open text-primary mr-3"></i>Peminjaman Laboratorium</h1>
    <p class="page-subtitle">Ajukan peminjaman ruang laboratorium untuk kegiatan akademik</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form -->
    <div class="lg:col-span-2">
        <div class="glass-card p-6">
            <h3 class="font-bold text-lg text-navy mb-4"><i class="fas fa-edit text-primary mr-2"></i>Form Peminjaman Lab</h3>
            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-navy mb-1.5">Pilih Laboratorium <span class="text-accent">*</span></label>
                        <select name="lab_id" class="glass-input" required>
                            <option value="">— Pilih Lab —</option>
                            <?php foreach ($lab_list as $l): ?>
                            <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nama_lab']) ?> (<?= htmlspecialchars($l['kode_lab']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-navy mb-1.5">Tujuan Peminjaman <span class="text-accent">*</span></label>
                        <select name="tujuan_peminjaman" class="glass-input" required>
                            <option value="">— Pilih Tujuan —</option>
                            <option value="Tugas Besar">Penelitian Tugas Besar Mata Kuliah</option>
                            <option value="Penelitian TA">Penelitian Tugas Akhir</option>
                            <option value="Seminar KP">Seminar Kerja Praktik</option>
                            <option value="Seminar Proposal">Seminar Proposal</option>
                            <option value="Sidang Akhir">Sidang Akhir</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-navy mb-1.5">Tanggal Pinjam <span class="text-accent">*</span></label>
                        <input type="date" name="tgl_pinjam" class="glass-input" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-navy mb-1.5">Tanggal Kembali <span class="text-accent">*</span></label>
                        <input type="date" name="tgl_kembali" class="glass-input" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-navy mb-1.5">Jam Mulai <span class="text-accent">*</span></label>
                        <input type="time" name="jam_mulai" class="glass-input" value="08:00" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-navy mb-1.5">Jam Selesai <span class="text-accent">*</span></label>
                        <input type="time" name="jam_selesai" class="glass-input" value="17:00" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-navy mb-1.5">Deskripsi Kegiatan</label>
                    <textarea name="deskripsi" class="glass-input" rows="3" placeholder="Jelaskan kegiatan yang akan dilakukan di laboratorium..."></textarea>
                </div>
                <button type="submit" name="submit_pinjam" class="btn-glass btn-glass-primary"><i class="fas fa-paper-plane mr-2"></i> Ajukan Peminjaman</button>
            </form>
        </div>
    </div>

    <!-- Info Lab -->
    <div class="space-y-4">
        <div class="glass-card p-5">
            <h3 class="font-bold text-navy mb-3"><i class="fas fa-info-circle text-primary mr-2"></i><?= count($semua_lab) ?> Laboratorium</h3>
            <?php $semua_lab = fetchAll("SELECT * FROM laboratorium ORDER BY nama_lab ASC"); ?>
            <div class="space-y-3">
                <?php foreach ($semua_lab as $l): ?>
                <div class="p-3 rounded-2xl bg-soft/70 border border-gray-100/50">
                    <div class="flex items-center justify-between mb-1 gap-1 flex-wrap">
                        <span class="font-bold text-sm text-navy"><?= htmlspecialchars($l['nama_lab']) ?></span>
                        <span class="glass-badge badge-<?= $l['status'] == 'Tersedia' ? 'success' : ($l['status'] == 'Digunakan' ? 'warning' : 'danger') ?> text-xs"><?= $l['status'] ?></span>
                    </div>
                    <div class="text-xs text-muted space-y-0.5">
                        <div><i class="fas fa-tag w-4"></i> <?= htmlspecialchars($l['kode_lab']) ?></div>
                        <div><i class="fas fa-map-marker-alt w-4"></i> <?= htmlspecialchars($l['lokasi'] ?: '-') ?></div>
                        <div><i class="fas fa-users w-4"></i> Kapasitas <?= (int)$l['kapasitas'] ?> orang</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="glass-card p-5">
            <h3 class="font-bold text-navy mb-2"><i class="fas fa-clock text-secondary mr-2"></i>Jam Operasional</h3>
            <div class="text-sm text-muted space-y-1">
                <p><i class="fas fa-calendar-day w-5"></i> Senin - Jumat: 08:00 - 17:00</p>
                <p><i class="fas fa-calendar-day w-5"></i> Sabtu: 08:00 - 13:00</p>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
