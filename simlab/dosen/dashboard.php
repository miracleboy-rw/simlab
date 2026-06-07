<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('dosen')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Dashboard';
$user = fetchRow("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
$dosen_id = $_SESSION['user_id'];

$total_peminjaman = getCount('peminjaman');
$total_approved = getCount('peminjaman', "status IN ('Approved','Returned')");
$total_alat = getCount('alat');
$total_mahasiswa = getCount('users', "role = 'mahasiswa'");
$pending_ta = getCount('verifikasi_ta', "status = 'Pending' AND dosen_id = ?", [$dosen_id]);

$stat_alat = fetchAll("SELECT a.nama_alat, COUNT(pi.id) as total
                       FROM peminjaman_items pi
                       JOIN alat a ON pi.alat_id = a.id
                       JOIN peminjaman p ON pi.peminjaman_id = p.id
                       WHERE p.status IN ('Approved','Returned')
                       GROUP BY a.id, a.nama_alat
                       ORDER BY total DESC LIMIT 5");

$stat_bulanan = fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total
                          FROM peminjaman
                          WHERE status IN ('Approved','Returned')
                          GROUP BY bulan
                          ORDER BY bulan ASC LIMIT 6");

$mahasiswa_aktif = fetchAll("SELECT u.nama_lengkap, u.nim_nidn, COUNT(p.id) as total_pinjam
                             FROM users u
                             LEFT JOIN peminjaman p ON u.id = p.user_id AND p.status IN ('Approved','Returned')
                             WHERE u.role = 'mahasiswa'
                             GROUP BY u.id, u.nama_lengkap, u.nim_nidn
                             ORDER BY total_pinjam DESC LIMIT 5");
include '../includes/header.php';
?>
<div class="grid-4 mb-6">
    <div class="card p-5">
        <div class="stat-card-top">
            <div class="stat-card-title">Total Peminjaman</div>
            <div class="stat-card-icon" style="background:#DBEAFE;color:#2a4dd7">
                <span class="material-symbols-outlined">assignment</span>
            </div>
        </div>
        <div class="stat-card-number"><?= $total_peminjaman ?></div>
    </div>
    <div class="card p-5">
        <div class="stat-card-top">
            <div class="stat-card-title">Disetujui</div>
            <div class="stat-card-icon" style="background:#DCFCE7;color:#22C55E">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
        </div>
        <div class="stat-card-number"><?= $total_approved ?></div>
        <div class="stat-card-label">Bulan ini</div>
    </div>
    <div class="card p-5">
        <div class="stat-card-top">
            <div class="stat-card-title">Alat Tersedia</div>
            <div class="stat-card-icon" style="background:#EDE9FE;color:#4868f1">
                <span class="material-symbols-outlined">inventory</span>
            </div>
        </div>
        <div class="stat-card-number"><?= $total_alat ?></div>
        <div class="stat-card-label">Total alat</div>
    </div>
    <div class="card p-5">
        <div class="stat-card-top">
            <div class="stat-card-title">Pending TA</div>
            <div class="stat-card-icon" style="background:#FEF3C7;color:#F59E0B">
                <span class="material-symbols-outlined">pending_actions</span>
            </div>
        </div>
        <div class="stat-card-number"><?= $pending_ta ?></div>
        <div class="stat-card-label" style="display:flex;align-items:center;gap:2px;color:#EF4444"><span class="material-symbols-outlined" style="font-size:14px">warning</span> Butuh review</div>
    </div>
</div>

<div class="grid-2 mb-6">
    <div class="card p-5">
        <div class="section-header" style="margin-bottom:24px">5 Alat Terpopuler</div>
        <div style="position:relative;height:256px;width:100%">
            <canvas id="popularItemsChart"></canvas>
        </div>
    </div>
    <div class="card p-5">
        <div class="section-header" style="margin-bottom:24px">Tren Peminjaman Bulanan</div>
        <div style="position:relative;height:256px;width:100%">
            <canvas id="monthlyTrendChart"></canvas>
        </div>
    </div>
</div>

<div class="card" style="overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid #E5E7EB;display:flex;justify-content:space-between;align-items:center;background:#f9fafb">
        <div class="section-header" style="margin-bottom:0">Mahasiswa Aktif</div>
        <a href="statistik.php" style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#2a4dd7">Lihat Semua</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Mahasiswa</th>
                    <th>NIM</th>
                    <th>Proyek</th>
                    <th style="text-align:right">Total Pinjam</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mahasiswa_aktif)): ?>
                <tr><td colspan="4" style="text-align:center;padding:32px;color:#9CA3AF">Belum ada data mahasiswa</td></tr>
                <?php else: ?>
                <?php foreach ($mahasiswa_aktif as $m): ?>
                <tr>
                    <td>
                        <div class="flex items-center" style="gap:12px">
                            <div class="avatar avatar-sm"><?= strtoupper(substr($m['nama_lengkap'], 0, 2)) ?></div>
                            <span style="font-weight:500"><?= htmlspecialchars($m['nama_lengkap']) ?></span>
                        </div>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($m['nim_nidn'] ?? '-') ?></td>
                    <td>Penelitian TA</td>
                    <td style="text-align:right;font-weight:500"><?= $m['total_pinjam'] ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = {
        primary: '#2a4dd7',
        primaryLight: 'rgba(42, 77, 215, 0.2)',
        textSecondary: '#6B7280',
        gridBorder: '#E5E7EB'
    };
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#191c1e',
                titleFont: { family: 'Plus Jakarta Sans', size: 13 },
                bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                padding: 10,
                cornerRadius: 8,
                displayColors: false
            }
        },
        scales: {
            x: {
                grid: { display: false, drawBorder: false },
                ticks: { font: { family: 'Plus Jakarta Sans', size: 11 }, color: colors.textSecondary }
            },
            y: {
                grid: { color: colors.gridBorder, borderDash: [4, 4], drawBorder: false },
                ticks: { font: { family: 'Plus Jakarta Sans', size: 11 }, color: colors.textSecondary, padding: 10 }
            }
        }
    };

    const ctxBar = document.getElementById('popularItemsChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: [<?php foreach ($stat_alat as $s) { echo "'" . addslashes($s['nama_alat']) . "',"; } ?>],
            datasets: [{
                label: 'Total Dipinjam',
                data: [<?php foreach ($stat_alat as $s) { echo $s['total'] . ","; } ?>],
                backgroundColor: colors.primary,
                borderRadius: 6,
                barThickness: 24,
                hoverBackgroundColor: '#0034c0'
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                ...commonOptions.scales,
                y: { ...commonOptions.scales.y, beginAtZero: true }
            }
        }
    });

    const ctxLine = document.getElementById('monthlyTrendChart').getContext('2d');
    const gradient = ctxLine.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, colors.primaryLight);
    gradient.addColorStop(1, 'rgba(42, 77, 215, 0)');

    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: [<?php foreach ($stat_bulanan as $s) { echo "'" . $s['bulan'] . "',"; } ?>],
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: [<?php foreach ($stat_bulanan as $s) { echo $s['total'] . ","; } ?>],
                borderColor: colors.primary,
                backgroundColor: gradient,
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: colors.primary,
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                ...commonOptions.scales,
                y: { ...commonOptions.scales.y, beginAtZero: true }
            }
        }
    });
});
</script>
<?php include '../includes/footer.php'; ?>
