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

// Handle submit peminjaman (sebelum header output)
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

include '../includes/header.php';

$alat_tersedia = fetchAll("SELECT * FROM alat WHERE status = 'Tersedia' AND stok_tersedia > 0 ORDER BY nama_alat ASC");

$upload_id = $_GET['upload'] ?? null;
$peminjaman_upload = null;
if ($upload_id) {
    $peminjaman_upload = fetchOne("SELECT * FROM peminjaman WHERE id = ? AND user_id = ?", [(int)$upload_id, $user_id]);
}
?>
<div class="mb-6">
    <h1 class="page-title"><span class="material-symbols-outlined" style="margin-right:12px">description</span> Form Peminjaman Alat</h1>
    <p class="text-muted">Ajukan peminjaman alat laboratorium</p>
</div>

<?php if ($peminjaman_upload): ?>
<div class="card p-5 mb-6" style="border-color:rgba(5,205,153,0.3);border-width:2px">
    <h5 class="font-bold mb-4" style="color:#16A34A"><span class="material-symbols-outlined" style="margin-right:8px">upload</span> Upload Dokumen Pendukung</h5>
    <p class="mb-4">Peminjaman <strong><?= htmlspecialchars($peminjaman_upload['kode_peminjaman']) ?></strong> berhasil diajukan. Silakan upload dokumen pendukung (PDF):</p>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="peminjaman_id" value="<?= $peminjaman_upload['id'] ?>">
        <input type="hidden" name="upload_dokumen" value="1">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px">
            <div>
                <select name="tipe_dokumen" class="form-input" required>
                    <option value="">Pilih tipe dokumen</option>
                    <option value="izin_lab">Surat Izin Lab</option>
                    <option value="proposal">Proposal Penelitian</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div>
                <input type="file" name="file_dokumen" class="form-input" accept=".pdf" required>
            </div>
            <div>
                <button type="submit" class="btn btn-success w-full"><span class="material-symbols-outlined" style="margin-right:8px">upload</span> Upload</button>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card p-5">
    <h5 class="section-header"><span class="material-symbols-outlined" style="margin-right:8px">checklist</span> Data Peminjaman</h5>
    <form method="POST">
        <div class="mb-6">
            <label class="form-label" style="font-size:13px;font-weight:600;text-transform:none;letter-spacing:0;color:#111827;margin-bottom:12px">Pilih Alat <span style="color:#EF4444">*</span></label>
            <?php if (empty($alat_tersedia)): ?>
            <div style="padding:12px 16px;border-radius:8px;font-size:13px;background:#FEF3C7;color:#92400E;border:1px solid #FDE68A;display:flex;align-items:center;gap:8px"><span class="material-symbols-outlined">warning</span> Tidak ada alat tersedia saat ini.</div>
            <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
                <?php foreach ($alat_tersedia as $a): ?>
                <label class="card p-4" style="display:flex;align-items:flex-start;gap:12px;cursor:pointer;box-shadow:none">
                    <input type="checkbox" name="alat_id[]" value="<?= $a['id'] ?>" style="margin-top:3px;accent-color:#2a4dd7">
                    <div>
                        <span class="font-semibold"><?= htmlspecialchars($a['nama_alat']) ?></span>
                        <small class="text-muted" style="display:block">Stok: <?= (int)$a['stok_tersedia'] ?> | <?= htmlspecialchars($a['kode_alat']) ?></small>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:16px">
            <div class="form-group">
                <label class="form-label">Tanggal Pinjam <span style="color:#EF4444">*</span></label>
                <input type="date" name="tgl_pinjam" class="form-input" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Kembali <span style="color:#EF4444">*</span></label>
                <input type="date" name="tgl_kembali" class="form-input" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Tujuan Penggunaan <span style="color:#EF4444">*</span></label>
            <textarea name="tujuan" class="form-textarea" rows="3" placeholder="Contoh: Praktikum Fisiologi, Penelitian TA, dll." required></textarea>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <button type="submit" class="btn btn-primary"><span class="material-symbols-outlined" style="margin-right:8px">send</span> Ajukan Peminjaman</button>
            <a href="dashboard.php" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
