<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
$base_url = '../';
$page_title = 'Kalender Jadwal Laboratorium';

// --- CRUD Manual Events (hanya laboran) ---
if (isRole('laboran') && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action == 'tambah') {
        query("INSERT INTO kalender_events (judul, tgl_mulai, tgl_selesai, tipe, warna, lab_id) VALUES (?, ?, ?, ?, ?, ?)",
               [$_POST['judul'], $_POST['tgl_mulai'], $_POST['tgl_selesai'] ?: null, $_POST['tipe'], $_POST['warna'], $_POST['lab_id'] ?: null]);
        alert('success', 'Event berhasil ditambahkan!');
    } elseif ($action == 'edit') {
        query("UPDATE kalender_events SET judul=?, tgl_mulai=?, tgl_selesai=?, tipe=?, warna=?, lab_id=? WHERE id=?",
               [$_POST['judul'], $_POST['tgl_mulai'], $_POST['tgl_selesai'] ?: null, $_POST['tipe'], $_POST['warna'], $_POST['lab_id'] ?: null, $_POST['id']]);
        alert('success', 'Event berhasil diupdate!');
    } elseif ($action == 'hapus') {
        query("DELETE FROM kalender_events WHERE id = ?", [$_POST['id']]);
        alert('success', 'Event berhasil dihapus!');
    }
    redirect('kalender.php' . (isset($_GET['lab_id']) ? '?lab_id=' . (int)$_GET['lab_id'] : ''));
}
include '../includes/header.php';

// --- Lab Filter ---
$lab_filter = isset($_GET['lab_id']) ? (int)$_GET['lab_id'] : 0;
$all_labs = fetchAll("SELECT * FROM laboratorium ORDER BY kode_lab ASC");

// --- Manual events (Praktikum, Riset, Maintenance, Kalibrasi) ---
$manual_sql = "SELECT ke.*, l.nama_lab FROM kalender_events ke LEFT JOIN laboratorium l ON ke.lab_id = l.id WHERE 1=1";
$manual_params = [];
if ($lab_filter > 0) {
    $manual_sql .= " AND (ke.lab_id = ? OR ke.lab_id IS NULL)";
    $manual_params[] = $lab_filter;
}
$manual_sql .= " ORDER BY ke.tgl_mulai ASC";
$manual_events = fetchAll($manual_sql, $manual_params);

// --- Lab bookings (dinamis dari peminjaman_lab) ---
$lab_sql = "SELECT pl.*, l.nama_lab, l.kode_lab, l.kapasitas, u.nama_lengkap as peminjam
            FROM peminjaman_lab pl
            JOIN laboratorium l ON pl.lab_id = l.id
            JOIN users u ON pl.user_id = u.id
            WHERE pl.status IN ('Approved','Pending')";
$lab_params = [];
if ($lab_filter > 0) {
    $lab_sql .= " AND pl.lab_id = ?";
    $lab_params[] = $lab_filter;
}
$lab_sql .= " ORDER BY pl.tgl_pinjam ASC";
$lab_bookings = fetchAll($lab_sql, $lab_params);
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-4">
    <div>
        <h1 class="page-title"><i class="fas fa-calendar-alt mr-3"></i> Kalender Jadwal Laboratorium</h1>
        <p class="page-subtitle">Jadwal peminjaman lab, praktikum, riset, dan perawatan alat</p>
    </div>
    <?php if (isRole('laboran')): ?>
    <button class="btn-glass btn-glass-primary" onclick="document.getElementById('tambahModal').classList.remove('hidden')"><i class="fas fa-plus mr-2"></i> Tambah Event</button>
    <?php endif; ?>
</div>

<?= showAlert() ?>

<!-- Lab Filter Tabs -->
<div class="glass-card p-2 mb-6 overflow-x-auto">
    <div class="flex gap-2 min-w-max">
        <a href="kalender.php" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all <?= $lab_filter == 0 ? 'bg-primary text-white shadow-md' : 'text-muted hover:text-navy hover:bg-soft' ?>">
            <i class="fas fa-layer-group mr-1"></i> Semua Lab
        </a>
        <?php foreach ($all_labs as $lb): ?>
        <a href="kalender.php?lab_id=<?= $lb['id'] ?>" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all <?= $lab_filter == $lb['id'] ? 'bg-primary text-white shadow-md' : 'text-muted hover:text-navy hover:bg-soft' ?>">
            <i class="fas fa-door-open mr-1"></i> <?= htmlspecialchars($lb['kode_lab']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3">
        <div class="glass-card p-6">
            <div id="calendar"></div>
        </div>
    </div>
    <div class="lg:col-span-1">
        <div class="glass-card p-6">
            <h5 class="font-bold text-navy mb-4"><i class="fas fa-info-circle mr-2"></i>Keterangan</h5>
            <p class="mb-2 flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:#6E38F7;display:inline-block;"></span> Lab Disetujui</p>
            <p class="mb-2 flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:#B794F4;display:inline-block;opacity:0.6;"></span> Lab Pending</p>
            <p class="mb-2 flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:#28a745;display:inline-block;"></span> Praktikum</p>
            <p class="mb-2 flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:#0d6efd;display:inline-block;"></span> Riset</p>
            <p class="mb-2 flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:#ffc107;display:inline-block;"></span> Maintenance</p>
            <p class="mb-2 flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:#dc3545;display:inline-block;"></span> Kalibrasi</p>
            <hr class="divider my-4">
            <h6 class="font-bold text-navy mb-3"><i class="fas fa-list mr-2"></i>Jadwal Mendatang</h6>
            <ul class="space-y-3" id="jadwalList">
                <?php
                $all_upcoming = [];
                foreach ($manual_events as $e) {
                    $all_upcoming[] = ['tgl' => $e['tgl_mulai'], 'judul' => $e['judul'], 'tipe' => $e['tipe'], 'warna' => $e['warna'], 'id' => 'e'.$e['id'], 'is_lab' => false, 'lab_nama' => $e['nama_lab']];
                }
                foreach ($lab_bookings as $b) {
                    $all_upcoming[] = ['tgl' => $b['tgl_pinjam'], 'judul' => $b['nama_lab'] . ' - ' . $b['tujuan_peminjaman'], 'tipe' => 'Peminjaman Lab', 'warna' => $b['status']=='Approved'?'#6E38F7':'#B794F4', 'id' => 'b'.$b['id'], 'is_lab' => true, 'booking' => $b];
                }
                usort($all_upcoming, fn($a, $b) => strtotime($a['tgl']) - strtotime($b['tgl']));
                $shown = array_slice($all_upcoming, 0, 8);
                ?>
                <?php if (empty($shown)): ?>
                <li class="text-sm text-muted">Tidak ada jadwal</li>
                <?php endif; ?>
                <?php foreach ($shown as $s): ?>
                    <li class="pb-2 text-sm" style="border-bottom:1px solid rgba(0,0,0,0.05);cursor:pointer"
                        onclick="showDetail(<?= htmlspecialchars(json_encode($s, JSON_HEX_APOS|JSON_HEX_QUOT)) ?>)">
                        <span class="font-bold text-navy"><?= formatTanggalIndo($s['tgl']) ?></span><br>
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full" style="background:<?= $s['warna'] ?>;display:inline-block;"></span>
                            <?= htmlspecialchars($s['judul']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Modal Detail Event -->
<div id="detailEventModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-lg mx-4" id="detailModalContent"></div>
</div>

<script>
function showDetail(data) {
    let html = '';
    if (data.is_lab && data.booking) {
        const b = data.booking;
        const tglKembali = new Date(b.tgl_kembali + 'T' + (b.jam_selesai || '17:00'));
        const tersedia = tglKembali.toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) + ' pukul ' + (b.jam_selesai || '17:00');
        const tglPinjam = new Date(b.tgl_pinjam + 'T' + (b.jam_mulai || '08:00')).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
        const tglKembaliStr = new Date(b.tgl_kembali + 'T' + (b.jam_selesai || '17:00')).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});

        html = `<div class="modal-header flex justify-between items-center">
                    <h5 class="font-bold text-lg"><i class="fas fa-door-open mr-2" style="color:${data.warna}"></i>Detail Peminjaman Lab</h5>
                    <button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('detailEventModal').classList.add('hidden')">&times;</button>
                </div>
                <div class="modal-body space-y-4">
                    <div class="col-span-2 p-4 rounded-2xl" style="background:${data.warna}15;border:1px solid ${data.warna}30">
                        <h6 class="font-bold text-navy text-lg">${b.nama_lab}</h6>
                        <p class="text-sm text-muted">${b.kode_lab} &middot; Kapasitas ${b.kapasitas} orang</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-muted block mb-1">Keperluan</span>
                            <span class="font-semibold text-navy">${b.tujuan_peminjaman}</span>
                        </div>
                        <div>
                            <span class="text-xs text-muted block mb-1">Peminjam</span>
                            <span class="font-semibold text-navy">${b.peminjam}</span>
                        </div>
                        <div>
                            <span class="text-xs text-muted block mb-1">Tanggal Pinjam</span>
                            <span class="font-semibold text-navy">${tglPinjam}</span>
                        </div>
                        <div>
                            <span class="text-xs text-muted block mb-1">Tanggal Kembali</span>
                            <span class="font-semibold text-navy">${tglKembaliStr}</span>
                        </div>
                        <div>
                            <span class="text-xs text-muted block mb-1">Jam Mulai</span>
                            <span class="font-semibold text-navy">${b.jam_mulai}</span>
                        </div>
                        <div>
                            <span class="text-xs text-muted block mb-1">Jam Selesai</span>
                            <span class="font-semibold text-navy">${b.jam_selesai}</span>
                        </div>
                        <div>
                            <span class="text-xs text-muted block mb-1">Status</span>
                            <span class="glass-badge ${b.status==='Approved'?'badge-success':'badge-warning'}">${b.status}</span>
                        </div>
                        <div class="col-span-2 p-3 rounded-2xl bg-emerald-50 border border-emerald-200">
                            <span class="text-xs text-emerald-700 block mb-1"><i class="fas fa-clock mr-1"></i>Tersedia Kembali</span>
                            <span class="font-bold text-emerald-800">${tersedia}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex justify-end">
                    <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('detailEventModal').classList.add('hidden')">Tutup</button>
                </div>`;
    } else {
        const tgl = new Date(data.tgl + 'T00:00:00').toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
        html = `<div class="modal-header flex justify-between items-center">
                    <h5 class="font-bold text-lg"><i class="fas fa-calendar-alt mr-2" style="color:${data.warna}"></i>${data.judul}</h5>
                    <button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('detailEventModal').classList.add('hidden')">&times;</button>
                </div>
                <div class="modal-body space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background:${data.warna};display:inline-block"></span>
                        <span class="glass-badge badge-info">${data.tipe}</span>
                        ${data.lab_nama ? `<span class="glass-badge badge-primary"><i class="fas fa-door-open mr-1"></i>${data.lab_nama}</span>` : ''}
                    </div>
                    <p class="text-sm text-muted">Tanggal: <span class="font-semibold text-navy">${tgl}</span></p>
                </div>
                <div class="modal-footer flex justify-end">
                    <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('detailEventModal').classList.add('hidden')">Tutup</button>
                </div>`;
    }
    document.getElementById('detailModalContent').innerHTML = html;
    document.getElementById('detailEventModal').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var events = [];

    // Manual events
    <?php foreach ($manual_events as $e): ?>
    events.push({
        id: 'e<?= $e['id'] ?>',
        title: <?= json_encode($e['judul']) ?>,
        start: '<?= $e['tgl_mulai'] ?>',
        end: '<?= $e['tgl_selesai'] ? date('Y-m-d', strtotime($e['tgl_selesai'].' +1 day')) : date('Y-m-d', strtotime($e['tgl_mulai'].' +1 day')) ?>',
        color: '<?= $e['warna'] ?>',
        display: 'block',
        extendedProps: { type: 'manual', tipe: '<?= $e['tipe'] ?>', tgl: '<?= $e['tgl_mulai'] ?>', judul: <?= json_encode($e['judul']) ?>, warna: '<?= $e['warna'] ?>', is_lab: false, lab_nama: <?= json_encode($e['nama_lab'] ?: '') ?> }
    });
    <?php endforeach; ?>

    // Lab bookings
    <?php foreach ($lab_bookings as $b): ?>
    events.push({
        id: 'b<?= $b['id'] ?>',
        title: <?= json_encode($b['nama_lab'] . ' - ' . $b['tujuan_peminjaman']) ?>,
        start: '<?= $b['tgl_pinjam'] ?>T<?= $b['jam_mulai'] ?>',
        end: '<?= $b['tgl_kembali'] ?>T<?= $b['jam_selesai'] ?>',
        color: '<?= $b['status'] == 'Approved' ? '#6E38F7' : '#B794F4' ?>',
        display: 'block',
        <?php if ($b['status'] == 'Pending'): ?>className: 'fc-event-pending',<?php endif; ?>
        extendedProps: {
            type: 'lab',
            is_lab: true,
            warna: '<?= $b['status'] == 'Approved' ? '#6E38F7' : '#B794F4' ?>',
            booking: <?= json_encode([
                'nama_lab' => $b['nama_lab'],
                'kode_lab' => $b['kode_lab'],
                'kapasitas' => $b['kapasitas'],
                'tujuan_peminjaman' => $b['tujuan_peminjaman'],
                'peminjam' => $b['peminjam'],
                'tgl_pinjam' => $b['tgl_pinjam'],
                'tgl_kembali' => $b['tgl_kembali'],
                'jam_mulai' => $b['jam_mulai'],
                'jam_selesai' => $b['jam_selesai'],
                'status' => $b['status'],
            ]) ?>
        }
    });
    <?php endforeach; ?>

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: window.innerWidth < 640 ? {
            left: 'prev,next',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        } : {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: events,
        eventClick: function(info) {
            var props = info.event.extendedProps;
            if (props.is_lab) {
                showDetail({ is_lab: true, booking: props.booking, warna: props.warna });
            } else {
                showDetail({ is_lab: false, judul: props.judul, tipe: props.tipe, tgl: props.tgl, warna: props.warna, lab_nama: props.lab_nama });
            }
        }
    });
    calendar.render();
});
</script>

<style>
.fc-event-pending .fc-event-main { opacity: 0.6; }
.fc-event-pending { border-style: dashed !important; }
</style>

<?php if (isRole('laboran')): ?>
<!-- Modal Tambah -->
<div id="tambahModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-xl mx-4">
        <form method="POST">
            <input type="hidden" name="action" value="tambah">
            <input type="hidden" name="lab_id" value="<?= $lab_filter ?>">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-plus-circle mr-2"></i>Tambah Event Baru</h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('tambahModal').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="grid grid-cols-1 gap-4">
                    <div><label class="block text-sm font-semibold text-navy mb-1">Judul Event</label><input type="text" name="judul" class="glass-input" required></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-sm font-semibold text-navy mb-1">Tanggal Mulai</label><input type="date" name="tgl_mulai" class="glass-input" required></div>
                        <div><label class="block text-sm font-semibold text-navy mb-1">Tanggal Selesai</label><input type="date" name="tgl_selesai" class="glass-input"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-sm font-semibold text-navy mb-1">Tipe</label>
                            <select name="tipe" class="glass-input" required>
                                <option value="Praktikum">Praktikum</option>
                                <option value="Riset">Riset</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Kalibrasi">Kalibrasi</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div><label class="block text-sm font-semibold text-navy mb-1">Warna</label>
                            <div class="flex gap-2 items-center">
                                <input type="color" name="warna" class="w-10 h-10 rounded-xl border-0 cursor-pointer" value="#3788d8" style="background:transparent">
                                <input type="text" name="warna_text" class="glass-input flex-1" value="#3788d8" maxlength="7" oninput="this.previousElementSibling.value=this.value" onchange="this.previousElementSibling.value=this.value">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex justify-end gap-2">
                <button type="button" class="btn-glass btn-glass-outline" onclick="document.getElementById('tambahModal').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn-glass btn-glass-primary"><i class="fas fa-save mr-2"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($manual_events as $e): ?>
<div id="editModal<?= $e['id'] ?>" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="glass-modal-content w-full max-w-xl mx-4">
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $e['id'] ?>">
            <div class="modal-header flex justify-between items-center"><h5 class="font-bold text-lg"><i class="fas fa-pen mr-2"></i>Edit Event</h5><button type="button" class="text-gray-400 hover:text-navy text-xl" onclick="document.getElementById('editModal<?= $e['id'] ?>').classList.add('hidden')">&times;</button></div>
            <div class="modal-body">
                <div class="grid grid-cols-1 gap-4">
                    <div><label class="block text-sm font-semibold text-navy mb-1">Judul Event</label><input type="text" name="judul" class="glass-input" value="<?= htmlspecialchars($e['judul']) ?>" required></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-sm font-semibold text-navy mb-1">Tanggal Mulai</label><input type="date" name="tgl_mulai" class="glass-input" value="<?= $e['tgl_mulai'] ?>" required></div>
                        <div><label class="block text-sm font-semibold text-navy mb-1">Tanggal Selesai</label><input type="date" name="tgl_selesai" class="glass-input" value="<?= $e['tgl_selesai'] ?>"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-sm font-semibold text-navy mb-1">Tipe</label>
                            <select name="tipe" class="glass-input" required>
                                <option value="Praktikum" <?= $e['tipe']=='Praktikum'?'selected':'' ?>>Praktikum</option>
                                <option value="Riset" <?= $e['tipe']=='Riset'?'selected':'' ?>>Riset</option>
                                <option value="Maintenance" <?= $e['tipe']=='Maintenance'?'selected':'' ?>>Maintenance</option>
                                <option value="Kalibrasi" <?= $e['tipe']=='Kalibrasi'?'selected':'' ?>>Kalibrasi</option>
                                <option value="Lainnya" <?= $e['tipe']=='Lainnya'?'selected':'' ?>>Lainnya</option>
                            </select>
                        </div>
                        <div><label class="block text-sm font-semibold text-navy mb-1">Warna</label>
                            <div class="flex gap-2 items-center">
                                <input type="color" name="warna" class="w-10 h-10 rounded-xl border-0 cursor-pointer" value="<?= htmlspecialchars($e['warna']) ?>" style="background:transparent">
                                <input type="text" name="warna_text" class="glass-input flex-1" value="<?= htmlspecialchars($e['warna']) ?>" maxlength="7" oninput="this.previousElementSibling.value=this.value" onchange="this.previousElementSibling.value=this.value">
                            </div>
                        </div>
                    </div>
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
    if (confirm('Hapus event "' + nama + '"?')) {
        document.getElementById('hapusId').value = id;
        document.getElementById('formHapus').submit();
    }
}
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
