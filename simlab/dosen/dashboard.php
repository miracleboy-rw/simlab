<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('dosen')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Dashboard Dosen';
include '../includes/header.php';

$total_peminjaman = getCount('peminjaman');
$total_approved = getCount('peminjaman', "status = 'Approved'");
$total_alat = getCount('alat');
$total_mahasiswa = getCount('users', "role = 'mahasiswa'");
$pending_ta = getCount('verifikasi_ta', "status = 'Pending'");

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
                          ORDER BY bulan DESC LIMIT 6");
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-chalkboard-teacher mr-3"></i> Dashboard Dosen</h1>
    <p class="page-subtitle">Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?></p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="stat-icon" style="background:rgba(67,24,255,0.1);color:#4318FF;"><i class="fas fa-box"></i></div>
            <div>
                <div class="stat-value"><?= $total_peminjaman ?></div>
                <div class="stat-label">Total Peminjaman</div>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="stat-icon" style="background:rgba(5,205,153,0.1);color:#05CD99;"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-value"><?= $total_approved ?></div>
                <div class="stat-label">Disetujui</div>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="stat-icon" style="background:rgba(67,24,255,0.08);color:#6E38F7;"><i class="fas fa-microscope"></i></div>
            <div>
                <div class="stat-value"><?= $total_alat ?></div>
                <div class="stat-label">Total Alat</div>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="stat-icon" style="background:rgba(255,184,0,0.1);color:#B87A00;"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <div class="stat-value"><?= $pending_ta ?></div>
                <div class="stat-label">Menunggu Verifikasi TA</div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="glass-card p-6">
        <h5 class="font-bold text-navy mb-4"><i class="fas fa-chart-bar mr-2"></i> Alat Paling Sering Dipinjam</h5>
        <canvas id="alatChart" height="200"></canvas>
    </div>
    <div class="glass-card p-6">
        <h5 class="font-bold text-navy mb-4"><i class="fas fa-chart-line mr-2"></i> Tren Peminjaman per Bulan</h5>
        <canvas id="bulanChart" height="200"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('alatChart'), {
        type: 'bar',
        data: {
            labels: [<?php foreach ($stat_alat as $s) { echo "'" . addslashes($s['nama_alat']) . "',"; } ?>],
            datasets: [{
                label: 'Jumlah Dipinjam',
                data: [<?php foreach ($stat_alat as $s) { echo $s['total'] . ","; } ?>],
                backgroundColor: '#4318FF',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });

    new Chart(document.getElementById('bulanChart'), {
        type: 'line',
        data: {
            labels: [<?php foreach (array_reverse($stat_bulanan) as $s) { echo "'" . $s['bulan'] . "',"; } ?>],
            datasets: [{
                label: 'Peminjaman',
                data: [<?php foreach (array_reverse($stat_bulanan) as $s) { echo $s['total'] . ","; } ?>],
                borderColor: '#05CD99',
                tension: 0.3,
                fill: true,
                backgroundColor: 'rgba(5,205,153,0.1)'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
});
</script>
<?php include '../includes/footer.php'; ?>
