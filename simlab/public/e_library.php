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
<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:24px;gap:16px">
    <div>
        <h1 class="page-title"><span class="material-symbols-outlined mr-3">auto_stories</span> E-Library K3 & Modul</h1>
        <p class="page-subtitle">Portal unduhan modul praktikum dan panduan alat</p>
    </div>
    <div class="flex items-center gap-3">
        <div>
            <form method="GET">
                <select name="tipe" class="form-input" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <option value="Modul Praktikum" <?= $filter_tipe == 'Modul Praktikum' ? 'selected' : '' ?>>Modul Praktikum</option>
                    <option value="Panduan Alat" <?= $filter_tipe == 'Panduan Alat' ? 'selected' : '' ?>>Panduan Alat</option>
                    <option value="K3" <?= $filter_tipe == 'K3' ? 'selected' : '' ?>>K3</option>
                    <option value="Lainnya" <?= $filter_tipe == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </form>
        </div>
        <?php if (isRole('laboran')): ?>
        <button class="btn btn-primary btn-md3" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><span class="material-symbols-outlined mr-2">upload</span> Upload</button>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($ebooks)): ?>
<div style="padding:12px 16px;border-radius:8px;font-size:13px;background:#DBEAFE;color:#1E40AF;border:1px solid #93C5FD;display:flex;align-items:center;gap:8px;margin-bottom:16px"><span class="material-symbols-outlined" style="font-size:18px">info</span> Belum ada dokumen tersedia.</div>
<?php endif; ?>

<div class="doc-grid">
    <?php foreach ($ebooks as $e): ?>
    <div class="card p-6 flex flex-col">
        <div class="flex items-start gap-4 mb-3">
            <span class="material-symbols-outlined text-4xl text-red-500">picture_as_pdf</span>
            <div class="flex-1 min-w-0">
                <h6 class="font-bold  mb-1 truncate"><?= htmlspecialchars($e['judul']) ?></h6>
                <span class="badge badge-info"><?= htmlspecialchars($e['tipe']) ?></span>
                <div class="text-xs text-muted mt-2 space-y-1">
                    <div><span class="material-symbols-outlined mr-1">person</span> <?= htmlspecialchars($e['nama_lengkap']) ?></div>
                    <div><span class="material-symbols-outlined mr-1">calendar_today</span> <?= formatTanggalIndo($e['created_at']) ?></div>
                </div>
            </div>
        </div>
        <?php if ($e['deskripsi']): ?>
        <p class="text-sm text-muted mb-3"><?= htmlspecialchars($e['deskripsi']) ?></p>
        <?php endif; ?>
        <div class="mt-auto flex gap-2">
            <a href="../<?= htmlspecialchars($e['file_path']) ?>" class="btn btn-outline btn-md3 btn-sm flex-1 text-center" target="_blank">
                <span class="material-symbols-outlined mr-1">download</span> Download
            </a>
            <?php if (isRole('laboran')): ?>
            <button class="btn btn-warning btn-sm btn-md3" onclick="document.getElementById('editModal<?= $e['id'] ?>').classList.remove('hidden')"><span class="material-symbols-outlined">edit</span></button>
            <button class="btn btn-danger btn-sm btn-md3" onclick="confirmHapus(<?= $e['id'] ?>, '<?= addslashes($e['judul']) ?>')"><span class="material-symbols-outlined">delete</span></button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (isRole('laboran')): ?>
<!-- Modal Tambah -->
<div id="tambahModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="card w-full max-w-xl mx-4">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="tambah">
            <div class="card-header flex justify-between items-center"><h5 class="font-bold text-lg"><span class="material-symbols-outlined mr-2">add_circle</span>Upload Dokumen Baru</h5><button type="button" class="text-muted2 hover:text-navy text-xl" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <div class="grid grid-cols-1 gap-4">
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Judul Dokumen</label><input type="text" name="judul" class="form-input" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Tipe</label>
                        <select name="tipe" class="form-input" required>
                            <option value="Modul Praktikum">Modul Praktikum</option>
                            <option value="Panduan Alat">Panduan Alat</option>
                            <option value="K3">K3</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Deskripsi</label><textarea name="deskripsi" class="form-input" rows="2"></textarea></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">File (PDF, DOC, XLS, PPT)</label><input type="file" name="file" class="form-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required></div>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline btn-md3" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary btn-md3"><span class="material-symbols-outlined mr-2">save</span> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($ebooks as $e): ?>
<div id="editModal<?= $e['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="card w-full max-w-xl mx-4">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $e['id'] ?>">
            <div class="card-header flex justify-between items-center"><h5 class="font-bold text-lg"><span class="material-symbols-outlined mr-2">edit</span>Edit Dokumen</h5><button type="button" class="text-muted2 hover:text-navy text-xl" onclick="document.getElementById('editModal<?= $e['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="card-body">
                <div class="grid grid-cols-1 gap-4">
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Judul Dokumen</label><input type="text" name="judul" class="form-input" value="<?= htmlspecialchars($e['judul']) ?>" required></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Tipe</label>
                        <select name="tipe" class="form-input" required>
                            <option value="Modul Praktikum" <?= $e['tipe']=='Modul Praktikum'?'selected':'' ?>>Modul Praktikum</option>
                            <option value="Panduan Alat" <?= $e['tipe']=='Panduan Alat'?'selected':'' ?>>Panduan Alat</option>
                            <option value="K3" <?= $e['tipe']=='K3'?'selected':'' ?>>K3</option>
                            <option value="Lainnya" <?= $e['tipe']=='Lainnya'?'selected':'' ?>>Lainnya</option>
                        </select>
                    </div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Deskripsi</label><textarea name="deskripsi" class="form-input" rows="2"><?= htmlspecialchars($e['deskripsi']) ?></textarea></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Ganti File (biarkan kosong jika tidak diganti)</label><input type="file" name="file" class="form-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"></div>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <button type="button" class="btn btn-outline btn-md3" onclick="document.getElementById('editModal<?= $e['id'] ?>').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary btn-md3"><span class="material-symbols-outlined mr-2">save</span> Simpan</button>
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
