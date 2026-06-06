<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
$base_url = '../';
$page_title = 'E-Library K3 & Modul';

// Handle CRUD untuk laboran
if (isRole('laboran') && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'tambah') {
        $judul = $_POST['judul'];
        $tipe = $_POST['tipe'];
        $deskripsi = $_POST['deskripsi'] ?? '';
        $user_id = $_SESSION['user_id'];

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            alert('danger', 'File harus diupload!');
            redirect('e_library.php');
        }
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            alert('danger', 'Tipe file tidak diizinkan!');
            redirect('e_library.php');
        }
        $filename = 'dokumen_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['file']['tmp_name'], '../uploads/e_library/' . $filename);
        $file_path = 'uploads/e_library/' . $filename;

        query("INSERT INTO e_library (judul, tipe, file_path, deskripsi, uploaded_by) VALUES (?, ?, ?, ?, ?)",
               [$judul, $tipe, $file_path, $deskripsi, $user_id]);
        alert('success', 'Dokumen berhasil ditambahkan!');
    } elseif ($action == 'edit') {
        $id = $_POST['id'];
        $judul = $_POST['judul'];
        $tipe = $_POST['tipe'];
        $deskripsi = $_POST['deskripsi'] ?? '';

        $existing = fetchOne("SELECT * FROM e_library WHERE id = ?", [$id]);
        $file_path = $existing['file_path'];

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                alert('danger', 'Tipe file tidak diizinkan!');
                redirect('e_library.php');
            }
            if ($existing['file_path'] && file_exists('../' . $existing['file_path'])) {
                unlink('../' . $existing['file_path']);
            }
            $filename = 'dokumen_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], '../uploads/e_library/' . $filename);
            $file_path = 'uploads/e_library/' . $filename;
        }

        query("UPDATE e_library SET judul=?, tipe=?, file_path=?, deskripsi=? WHERE id=?",
               [$judul, $tipe, $file_path, $deskripsi, $id]);
        alert('success', 'Dokumen berhasil diupdate!');
    } elseif ($action == 'hapus') {
        $id = $_POST['id'];
        $existing = fetchOne("SELECT * FROM e_library WHERE id = ?", [$id]);
        if ($existing && $existing['file_path'] && file_exists('../' . $existing['file_path'])) {
            unlink('../' . $existing['file_path']);
        }
        query("DELETE FROM e_library WHERE id = ?", [$id]);
        alert('success', 'Dokumen berhasil dihapus!');
    }
    redirect('e_library.php');
}
include '../includes/header.php';

$filter_tipe = $_GET['tipe'] ?? '';

$sql = "SELECT e.*, u.nama_lengkap FROM e_library e JOIN users u ON e.uploaded_by = u.id WHERE 1=1";
$params = [];
if ($filter_tipe) {
    $sql .= " AND e.tipe = ?";
    $params[] = $filter_tipe;
}
$sql .= " ORDER BY e.created_at DESC";
$ebooks = fetchAll($sql, $params);
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="page-title"><i class="fas fa-book mr-3"></i> E-Library K3 & Modul</h1>
        <p class="page-subtitle">Portal unduhan modul praktikum dan panduan alat</p>
    </div>
    <div class="flex items-center gap-3">
        <div class="sm:w-56">
            <form method="GET">
                <select name="tipe" class="glass-input" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <option value="Modul Praktikum" <?= $filter_tipe == 'Modul Praktikum' ? 'selected' : '' ?>>Modul Praktikum</option>
                    <option value="Panduan Alat" <?= $filter_tipe == 'Panduan Alat' ? 'selected' : '' ?>>Panduan Alat</option>
                    <option value="K3" <?= $filter_tipe == 'K3' ? 'selected' : '' ?>>K3</option>
                    <option value="Lainnya" <?= $filter_tipe == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </form>
        </div>
        <?php if (isRole('laboran')): ?>
        <button class="btn-glass btn-glass-primary" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><i class="fas fa-plus mr-2"></i> Upload</button>
        <?php endif; ?>
    </div>
</div>

<?= showAlert() ?>

<?php if (empty($ebooks)): ?>
<div class="glass-alert alert-info"><i class="fas fa-info-circle mr-2"></i> Belum ada dokumen tersedia.</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($ebooks as $e): ?>
    <div class="glass-card p-6 flex flex-col">
        <div class="flex items-start gap-4 mb-3">
            <i class="fas fa-file-pdf text-4xl text-red-500"></i>
            <div class="flex-1 min-w-0">
                <h6 class="font-bold text-navy mb-1 truncate"><?= htmlspecialchars($e['judul']) ?></h6>
                <span class="glass-badge badge-info"><?= htmlspecialchars($e['tipe']) ?></span>
                <div class="text-xs text-muted mt-2 space-y-1">
                    <div><i class="fas fa-user mr-1"></i> <?= htmlspecialchars($e['nama_lengkap']) ?></div>
                    <div><i class="fas fa-calendar mr-1"></i> <?= formatTanggalIndo($e['created_at']) ?></div>
                </div>
            </div>
        </div>
        <?php if ($e['deskripsi']): ?>
        <p class="text-sm text-muted mb-3"><?= htmlspecialchars($e['deskripsi']) ?></p>
        <?php endif; ?>
        <div class="mt-auto flex gap-2">
            <a href="../<?= htmlspecialchars($e['file_path']) ?>" class="btn-glass btn-glass-outline btn-sm flex-1 text-center" target="_blank">
                <i class="fas fa-download mr-1"></i> Download
            </a>
            <?php if (isRole('laboran')): ?>
            <button class="btn-glass btn-glass-warning btn-sm" onclick="document.getElementById('editModal<?= $e['id'] ?>').classList.remove('hidden')"><i class="fas fa-pen"></i></button>
            <button class="btn-glass btn-glass-danger btn-sm" onclick="confirmHapus(<?= $e['id'] ?>, '<?= addslashes($e['judul']) ?>')"><i class="fas fa-trash"></i></button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (isRole('laboran')): ?>
<!-- Modal Tambah -->
<div id="tambahModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-xl mx-4">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="tambah">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-plus-circle mr-2"></i>Upload Dokumen Baru</h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="grid grid-cols-1 gap-4">
                    <div><label class="block text-sm font-semibold text-navy mb-1">Judul Dokumen</label><input type="text" name="judul" class="glass-input" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Tipe</label>
                        <select name="tipe" class="glass-input" required>
                            <option value="Modul Praktikum">Modul Praktikum</option>
                            <option value="Panduan Alat">Panduan Alat</option>
                            <option value="K3">K3</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Deskripsi</label><textarea name="deskripsi" class="glass-input" rows="2"></textarea></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">File (PDF, DOC, XLS, PPT)</label><input type="file" name="file" class="glass-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required></div>
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-primary"><i class="fas fa-save mr-2"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($ebooks as $e): ?>
<div id="editModal<?= $e['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-xl mx-4">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $e['id'] ?>">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-pen mr-2"></i>Edit Dokumen</h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('editModal<?= $e['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="grid grid-cols-1 gap-4">
                    <div><label class="block text-sm font-semibold text-navy mb-1">Judul Dokumen</label><input type="text" name="judul" class="glass-input" value="<?= htmlspecialchars($e['judul']) ?>" required></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Tipe</label>
                        <select name="tipe" class="glass-input" required>
                            <option value="Modul Praktikum" <?= $e['tipe']=='Modul Praktikum'?'selected':'' ?>>Modul Praktikum</option>
                            <option value="Panduan Alat" <?= $e['tipe']=='Panduan Alat'?'selected':'' ?>>Panduan Alat</option>
                            <option value="K3" <?= $e['tipe']=='K3'?'selected':'' ?>>K3</option>
                            <option value="Lainnya" <?= $e['tipe']=='Lainnya'?'selected':'' ?>>Lainnya</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Deskripsi</label><textarea name="deskripsi" class="glass-input" rows="2"><?= htmlspecialchars($e['deskripsi']) ?></textarea></div>
                    <div><label class="block text-sm font-semibold text-navy mb-1">Ganti File (biarkan kosong jika tidak diganti)</label><input type="file" name="file" class="glass-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"></div>
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('editModal<?= $e['id'] ?>').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-primary"><i class="fas fa-save mr-2"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<form id="formHapus" method="POST" style="display:none">
    <input type="hidden" name="action" value="hapus">
    <input type="hidden" name="id" id="hapusId">
</form>
<script>
function confirmHapus(id, nama) {
    if (confirm('Hapus dokumen "' + nama + '"?')) {
        document.getElementById('hapusId').value = id;
        document.getElementById('formHapus').submit();
    }
}
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
