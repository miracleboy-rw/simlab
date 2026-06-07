<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('mahasiswa')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Dashboard Mahasiswa';
$user_id = $_SESSION['user_id'];
$user = fetchRow("SELECT * FROM users WHERE id = ?", [$user_id]);

$total_pinjam = getCount('peminjaman', "user_id = ?", [$user_id]);
$total_approved = getCount('peminjaman', "user_id = ? AND status IN ('Approved','Returned')", [$user_id]);
$total_pending = getCount('peminjaman', "user_id = ? AND status = 'Pending'", [$user_id]);
$total_returned = getCount('peminjaman', "user_id = ? AND status = 'Returned'", [$user_id]);

$riwayat = fetchAll("SELECT p.*, 
    (SELECT a.nama_alat FROM peminjaman_items pi JOIN alat a ON pi.alat_id = a.id WHERE pi.peminjaman_id = p.id LIMIT 1) as nama_alat,
    (SELECT pi2.jumlah FROM peminjaman_items pi2 WHERE pi2.peminjaman_id = p.id LIMIT 1) as jumlah
    FROM peminjaman p 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC LIMIT 5", [$user_id]);

function iconAlat($name) {
    $map = [
        'ecg' => 'monitor_heart', 'mikroskop' => 'biotech', 'oscillos' => 'waves',
        'set' => 'content_cut', 'sp02' => 'sensors', 'sensor' => 'sensors',
    ];
    foreach ($map as $k => $v) {
        if (stripos($name, $k) !== false) return $v;
    }
    return 'biotech';
}

function statusBadgeNew($status) {
    $map = [
        'Approved' => ['color' => '#16A34A', 'bg' => '#DCFCE7'],
        'Returned' => ['color' => '#6B7280', 'bg' => '#E5E7EB'],
        'Rejected' => ['color' => '#DC2626', 'bg' => '#FEE2E2'],
        'Pending' => ['color' => '#D97706', 'bg' => '#FEF3C7'],
        'Overdue' => ['color' => '#DC2626', 'bg' => '#FEE2E2'],
    ];
    $c = $map[$status] ?? ['color' => '#6B7280', 'bg' => '#E5E7EB'];
    return "<span class=\"badge\" style=\"background:{$c['bg']};color:{$c['color']}\">{$status}</span>";
}

include '../includes/header.php';
?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card" style="position:relative;overflow:hidden">
        <div class="stat-card-top">
            <span class="stat-card-title">Total Peminjaman</span>
            <div class="stat-card-icon" style="background:#DBEAFE;color:#2a4dd7">
                <span class="material-symbols-outlined">inventory_2</span>
            </div>
        </div>
        <div class="stat-card-number"><?= $total_pinjam ?></div>
        <div style="position:absolute;right:-16px;bottom:-12px;opacity:0.05;pointer-events:none">
            <span class="material-symbols-outlined" style="font-size:60px">inventory_2</span>
        </div>
    </div>
    <div class="card stat-card" style="position:relative;overflow:hidden">
        <div class="stat-card-top">
            <span class="stat-card-title">Disetujui</span>
            <div class="stat-card-icon" style="background:#DCFCE7;color:#22C55E">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
        </div>
        <div class="stat-card-number"><?= $total_approved ?></div>
        <div style="position:absolute;right:-16px;bottom:-12px;opacity:0.05;pointer-events:none">
            <span class="material-symbols-outlined" style="font-size:60px">check_circle</span>
        </div>
    </div>
    <div class="card stat-card" style="position:relative;overflow:hidden">
        <div class="stat-card-top">
            <span class="stat-card-title">Pending</span>
            <div class="stat-card-icon" style="background:#FEF3C7;color:#F59E0B">
                <span class="material-symbols-outlined">pending_actions</span>
            </div>
        </div>
        <div class="stat-card-number"><?= $total_pending ?></div>
        <div style="position:absolute;right:-16px;bottom:-12px;opacity:0.05;pointer-events:none">
            <span class="material-symbols-outlined" style="font-size:60px">pending_actions</span>
        </div>
    </div>
    <div class="card stat-card" style="position:relative;overflow:hidden">
        <div class="stat-card-top">
            <span class="stat-card-title">Returned</span>
            <div class="stat-card-icon" style="background:#EDE9FE;color:#2a4dd7">
                <span class="material-symbols-outlined">assignment_return</span>
            </div>
        </div>
        <div class="stat-card-number"><?= $total_returned ?></div>
        <div style="position:absolute;right:-16px;bottom:-12px;opacity:0.05;pointer-events:none">
            <span class="material-symbols-outlined" style="font-size:60px">assignment_return</span>
        </div>
    </div>
</div>

<section class="card" style="overflow:hidden">
    <div class="card-header" style="padding-bottom:8px">
        <h3 class="section-header">Riwayat Peminjaman Terakhir</h3>
        <div class="tabs" style="margin-bottom:0">
            <button class="tab active">Alat</button>
            <a href="form_peminjaman_lab.php" class="tab">Lab</a>
        </div>
    </div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
        <?php if (empty($riwayat)): ?>
        <div class="text-center" style="padding:32px 0;color:#9CA3AF">Belum ada peminjaman.</div>
        <?php else: ?>
        <?php foreach ($riwayat as $p): ?>
        <div class="card p-4" style="box-shadow:none;display:flex;flex-direction:column;gap:16px">
            <div style="display:flex;align-items:center;gap:16px">
                <div style="width:48px;height:48px;border-radius:10px;background:#f2f4f7;display:flex;align-items:center;justify-content:center;color:#6B7280;flex-shrink:0">
                    <span class="material-symbols-outlined"><?= iconAlat($p['nama_alat'] ?? '') ?></span>
                </div>
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        <span style="font-size:11px;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;color:#6B7280;padding:2px 8px;background:#f2f4f7;border-radius:4px"><?= htmlspecialchars($p['kode_peminjaman']) ?></span>
                        <span style="font-size:14px;font-weight:600"><?= htmlspecialchars($p['nama_alat'] ?? 'Alat') ?></span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;color:#6B7280;font-size:11px">
                        <span class="material-symbols-outlined" style="font-size:14px">event</span>
                        <span><?= formatTanggalIndo($p['tgl_pinjam']) ?> - <?= formatTanggalIndo($p['tgl_kembali']) ?></span>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:12px;flex-shrink:0">
                    <?php if ($p['status'] === 'Rejected' && !empty($p['alasan_penolakan'])): ?>
                    <div style="display:flex;flex-direction:column;gap:4px">
                        <?= statusBadgeNew($p['status']) ?>
                        <div style="display:flex;align-items:center;gap:4px;color:#DC2626;font-size:11px;background:rgba(254,226,226,0.5);padding:4px 8px;border-radius:6px">
                            <span class="material-symbols-outlined" style="font-size:14px">info</span>
                            <span>Alasan: <?= htmlspecialchars($p['alasan_penolakan']) ?></span>
                        </div>
                    </div>
                    <?php else: ?>
                    <?= statusBadgeNew($p['status']) ?>
                    <?php endif; ?>
                    <a href="tracking_status.php?type=all" style="color:#6B7280;text-decoration:none;padding:8px;border-radius:50%;display:flex;align-items:center;justify-content:center">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="card-footer flex justify-center">
        <a href="tracking_status.php?type=all" style="display:flex;align-items:center;gap:8px;color:#2a4dd7;font-size:14px;font-weight:600;text-decoration:none">
            Lihat Semua Tracking Status
            <span class="material-symbols-outlined" style="font-size:18px">arrow_forward</span>
        </a>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
