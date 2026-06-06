<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('mahasiswa')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Form Peminjaman Alat';
$user_id = $_SESSION['user_id'];

// Handle upload dokumen (harus SEBELUM output apa pun)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_dokumen'])) {
    $peminjaman_id = (int)$_POST['peminjaman_id'];
    $tipe_dokumen = $_POST['tipe_dokumen'];
    if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file_dokumen']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            alert('danger', 'Hanya file PDF yang diperbolehkan!');
        } else {
            $filename = 'dok_' . time() . '_' . str_replace(' ', '_', $_FILES['file_dokumen']['name']);
            $dest = '../uploads/dokumen/' . $filename;
            if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $dest)) {
                query("INSERT INTO dokumen_pendukung (peminjaman_id, user_id, nama_file, file_path, tipe) VALUES (?, ?, ?, ?, ?)",
                       [$peminjaman_id, $user_id, $_FILES['file_dokumen']['name'], $filename, $tipe_dokumen]);
                alert('success', 'Dokumen berhasil diupload!');
                redirect('tracking_status.php');
            } else {
                alert('danger', 'Gagal mengupload file.');
            }
        }
    } else {
        alert('danger', 'Pilih file PDF untuk diupload.');
    }
}

include '../includes/header.php';

$alat_tersedia = fetchAll("SELECT * FROM alat WHERE status = 'Tersedia' AND stok_tersedia > 0 ORDER BY nama_alat ASC");

// Handle submit peminjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tujuan'])) {
    $alat_id = $_POST['alat_id'] ?? [];
    $tgl_pinjam = $_POST['tgl_pinjam'];
    $tgl_kembali = $_POST['tgl_kembali'];
    $tujuan = $_POST['tujuan'];

    if (empty($alat_id)) {
        alert('danger', 'Pilih minimal satu alat!');
    } else {
        $today = date('Y-m-d');
        if ($tgl_pinjam < $today) {
            alert('danger', 'Tanggal pinjam tidak boleh sebelum hari ini!');
        } elseif ($tgl_kembali <= $tgl_pinjam) {
            alert('danger', 'Tanggal kembali harus setelah tanggal pinjam!');
        } else {
            $kode = 'PNJ-' . date('Ymd') . '-' . rand(100, 999);
            try {
                $pid = insertGetId(
                    "INSERT INTO peminjaman (user_id, kode_peminjaman, tgl_pinjam, tgl_kembali, tujuan, status) VALUES (?, ?, ?, ?, ?, 'Pending')",
                    [$user_id, $kode, $tgl_pinjam, $tgl_kembali, $tujuan]
                );
                foreach ($alat_id as $aid) {
                    query("INSERT INTO peminjaman_items (peminjaman_id, alat_id, jumlah) VALUES (?, ?, 1)", [$pid, (int)$aid]);
                }
                alert('success', "Peminjaman berhasil diajukan! Kode: $kode. Silakan upload dokumen pendukung.");
                redirect('form_peminjaman.php?upload=' . $pid);
            } catch (Exception $e) {
                alert('danger', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
            }
        }
    }
}

$upload_id = $_GET['upload'] ?? null;
$peminjaman_upload = null;
if ($upload_id) {
    $peminjaman_upload = fetchOne("SELECT * FROM peminjaman WHERE id = ? AND user_id = ?", [(int)$upload_id, $user_id]);
}
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-file-alt mr-3"></i> Form Peminjaman Alat</h1>
    <p class="page-subtitle">Ajukan peminjaman alat laboratorium</p>
</div>

<?php if ($peminjaman_upload): ?>
<div class="glass-card p-6 mb-6 border-2" style="border-color:rgba(5,205,153,0.3);">
    <h5 class="font-bold text-green-600 mb-4"><i class="fas fa-upload mr-2"></i> Upload Dokumen Pendukung</h5>
    <p class="mb-4">Peminjaman <strong><?= htmlspecialchars($peminjaman_upload['kode_peminjaman']) ?></strong> berhasil diajukan. Silakan upload dokumen pendukung (PDF):</p>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="peminjaman_id" value="<?= $peminjaman_upload['id'] ?>">
        <input type="hidden" name="upload_dokumen" value="1">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <select name="tipe_dokumen" class="glass-input" required>
                    <option value="">Pilih tipe dokumen</option>
                    <option value="izin_lab">Surat Izin Lab</option>
                    <option value="proposal">Proposal Penelitian</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <input type="file" name="file_dokumen" class="glass-input" accept=".pdf" required>
            </div>
            <div>
                <button type="submit" class="btn-glass btn-glass-success w-full"><i class="fas fa-upload mr-2"></i> Upload</button>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="glass-card p-6">
    <h5 class="font-bold text-navy mb-6"><i class="fas fa-list-check mr-2"></i> Data Peminjaman</h5>
    <form method="POST">
        <div class="mb-6">
            <label class="block font-bold text-navy mb-3">Pilih Alat <span class="text-red-500">*</span></label>
            <?php if (empty($alat_tersedia)): ?>
            <div class="glass-alert alert-warning"><i class="fas fa-exclamation-triangle mr-2"></i> Tidak ada alat tersedia saat ini.</div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <?php foreach ($alat_tersedia as $a): ?>
                <label class="glass-card p-4 flex items-start gap-3 cursor-pointer hover:shadow-lg transition">
                    <input type="checkbox" name="alat_id[]" value="<?= $a['id'] ?>" class="mt-1 accent-primary">
                    <div>
                        <span class="font-semibold text-navy"><?= htmlspecialchars($a['nama_alat']) ?></span>
                        <small class="block text-muted">Stok: <?= (int)$a['stok_tersedia'] ?> | <?= htmlspecialchars($a['kode_alat']) ?></small>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block font-bold text-navy mb-2">Tanggal Pinjam <span class="text-red-500">*</span></label>
                <input type="date" name="tgl_pinjam" class="glass-input" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
                <label class="block font-bold text-navy mb-2">Tanggal Kembali <span class="text-red-500">*</span></label>
                <input type="date" name="tgl_kembali" class="glass-input" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
            </div>
        </div>
        <div class="mb-6">
            <label class="block font-bold text-navy mb-2">Tujuan Penggunaan <span class="text-red-500">*</span></label>
            <textarea name="tujuan" class="glass-input" rows="3" placeholder="Contoh: Praktikum Fisiologi, Penelitian TA, dll." required></textarea>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" class="btn-glass btn-glass-primary whitespace-nowrap w-full sm:w-auto justify-center"><i class="fas fa-paper-plane mr-2"></i> Ajukan Peminjaman</button>
            <a href="dashboard.php" class="btn-glass btn-glass-outline text-center w-full sm:w-auto">Batal</a>
        </div>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
