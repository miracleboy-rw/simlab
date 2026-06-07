<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('laboran')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Dashboard';
$user = fetchRow("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

$total_alat = getCount('alat');
$total_bahan = getCount('bahan_habis_pakai');
$pending_count = getCount('peminjaman', "status = 'Pending'");
$approved_count = getCount('peminjaman', "status = 'Approved'");
$rusak_count = getCount('alat', "status = 'Rusak'");
$bahan_alert = fetchAll("SELECT * FROM bahan_habis_pakai WHERE stok <= stok_minimum ORDER BY (stok * 1.0 / stok_minimum) ASC LIMIT 5");

$recent_peminjaman = fetchAll("SELECT p.*, u.nama_lengkap FROM peminjaman p
                               JOIN users u ON p.user_id = u.id
                               WHERE p.status = 'Pending'
                               ORDER BY p.created_at DESC LIMIT 5");
include '../includes/header.php';
?>
<div class="grid-6">
    <div class="card stat-card">
        <div class="stat-card-top">
            <span class="stat-card-title">Total Alat</span>
            <span class="stat-card-icon" style="background:#DBEAFE;color:#2a4dd7">
                <span class="material-symbols-outlined">inventory_2</span>
            </span>
        </div>
        <p class="stat-card-number"><?= $total_alat ?></p>
    </div>
    <div class="card stat-card">
        <div class="stat-card-top">
            <span class="stat-card-title">Total Bahan</span>
            <span class="stat-card-icon" style="background:#EDE9FE;color:#9333EA">
                <span class="material-symbols-outlined">science</span>
            </span>
        </div>
        <p class="stat-card-number"><?= $total_bahan ?></p>
    </div>
    <div class="card stat-card">
        <div class="stat-card-top">
            <span class="stat-card-title">Pending</span>
            <span class="stat-card-icon" style="background:#FEF3C7;color:#F59E0B">
                <span class="material-symbols-outlined">hourglass_empty</span>
            </span>
        </div>
        <p class="stat-card-number" style="color:#F59E0B"><?= $pending_count ?></p>
    </div>
    <div class="card stat-card">
        <div class="stat-card-top">
            <span class="stat-card-title">Approved</span>
            <span class="stat-card-icon" style="background:#DCFCE7;color:#22C55E">
                <span class="material-symbols-outlined">check_circle</span>
            </span>
        </div>
        <p class="stat-card-number" style="color:#22C55E"><?= $approved_count ?></p>
    </div>
    <div class="card stat-card">
        <div class="stat-card-top">
            <span class="stat-card-title">Alat Rusak</span>
            <span class="stat-card-icon" style="background:#FEE2E2;color:#EF4444">
                <span class="material-symbols-outlined">report</span>
            </span>
        </div>
        <p class="stat-card-number" style="color:#EF4444"><?= $rusak_count ?></p>
    </div>
    <div class="card stat-card">
        <div class="stat-card-top">
            <span class="stat-card-title">Alert Bahan</span>
            <span class="stat-card-icon" style="background:#FEF3C7;color:#F59E0B">
                <span class="material-symbols-outlined">warning</span>
            </span>
        </div>
        <p class="stat-card-number" style="color:#F59E0B"><?= count($bahan_alert) ?></p>
    </div>
</div>

<div class="grid-2" style="margin-top:24px">
    <div class="card" style="grid-column:1 / -1">
        <div style="padding:16px 20px;border-bottom:1px solid #E5E7EB;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:16px;font-weight:600;margin:0">Peminjaman Perlu Verifikasi</h3>
            <a href="verifikasi_peminjaman.php" style="font-size:11px;font-weight:600;color:#2a4dd7">Lihat Semua</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_peminjaman)): ?>
                    <tr><td colspan="4" style="padding:32px;text-align:center;color:#6B7280">Tidak ada peminjaman pending</td></tr>
                    <?php else: ?>
                    <?php foreach ($recent_peminjaman as $p): ?>
                    <tr>
                        <td>
                            <p style="font-weight:600;color:#111827"><?= htmlspecialchars($p['nama_lengkap']) ?></p>
                            <p style="font-size:11px;color:#6B7280"><?= htmlspecialchars($p['keperluan'] ?? '-') ?></p>
                        </td>
                        <td style="color:#6B7280"><?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></td>
                        <td>
                            <span class="badge" style="background:rgba(245,158,11,0.1);color:#F59E0B"><?= $p['status'] ?></span>
                        </td>
                        <td style="text-align:right">
                            <a href="verifikasi_peminjaman.php" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#2a4dd7">
                                <span class="material-symbols-outlined" style="font-size:14px">visibility</span> Detail
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid #E5E7EB;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:16px;font-weight:600;margin:0">Bahan Kritis</h3>
            <a href="manajemen_bahan.php" style="font-size:11px;font-weight:600;color:#2a4dd7">Kelola</a>
        </div>
        <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px">
            <?php if (empty($bahan_alert)): ?>
            <p style="color:#6B7280;text-align:center;padding:16px">Semua stok bahan aman</p>
            <?php else: ?>
            <?php foreach ($bahan_alert as $b): ?>
            <div class="card" style="padding:12px;display:flex;align-items:center;justify-content:space-between;background:rgba(239,68,68,0.03);border-color:rgba(239,68,68,0.15)">
                <div>
                    <p style="font-size:13px;font-weight:600;color:#111827"><?= htmlspecialchars($b['nama_bahan']) ?></p>
                    <p style="font-size:11px;color:#6B7280">Stok: <?= $b['stok'] ?> / <?= $b['stok_minimum'] ?></p>
                </div>
                <span class="badge" style="background:rgba(239,68,68,0.1);color:#EF4444">Kritis</span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card" style="margin-top:24px">
    <div style="padding:16px 20px;border-bottom:1px solid #E5E7EB">
        <h3 style="font-size:16px;font-weight:600;margin:0">Aksi Cepat</h3>
    </div>
    <div class="card-body grid-4">
        <a href="verifikasi_peminjaman.php" style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px;border-radius:12px;background:#DBEAFE;color:#2a4dd7;font-size:11px;font-weight:600;text-align:center;transition:background 0.15s">
            <span class="material-symbols-outlined" style="font-size:24px">fact_check</span>
            Verifikasi Pinjam
        </a>
        <a href="verifikasi_peminjaman_lab.php" style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px;border-radius:12px;background:#EDE9FE;color:#9333EA;font-size:11px;font-weight:600;text-align:center;transition:background 0.15s">
            <span class="material-symbols-outlined" style="font-size:24px">meeting_room</span>
            Verifikasi Lab
        </a>
        <a href="manajemen_inventaris.php" style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px;border-radius:12px;background:#DCFCE7;color:#22C55E;font-size:11px;font-weight:600;text-align:center;transition:background 0.15s">
            <span class="material-symbols-outlined" style="font-size:24px">inventory_2</span>
            Kelola Alat
        </a>
        <a href="manajemen_bahan.php" style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px;border-radius:12px;background:#FEF3C7;color:#F59E0B;font-size:11px;font-weight:600;text-align:center;transition:background 0.15s">
            <span class="material-symbols-outlined" style="font-size:24px">science</span>
            Kelola Bahan
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
