<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('dosen')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Statistik Penggunaan Laboratorium';
include '../includes/header.php';

$stat_alat = fetchAll("SELECT a.nama_alat, a.kode_alat, a.merk, a.stok_total, a.status, COUNT(pi.id) as dipinjam
                       FROM alat a
                       LEFT JOIN peminjaman_items pi ON a.id = pi.alat_id
                       LEFT JOIN peminjaman p ON pi.peminjaman_id = p.id AND p.status IN ('Approved','Returned')
                       GROUP BY a.id, a.nama_alat, a.kode_alat, a.merk, a.stok_total, a.status
                       ORDER BY dipinjam DESC");

$stat_bulan = fetchAll("SELECT DATE_FORMAT(p.created_at, '%Y-%m') as bulan, COUNT(*) as total
                        FROM peminjaman p WHERE p.status IN ('Approved','Returned')
                        GROUP BY bulan ORDER BY bulan ASC");

$stat_mahasiswa = fetchAll("SELECT u.nama_lengkap, u.nim_nidn, COUNT(p.id) as total_pinjam
                            FROM users u
                            LEFT JOIN peminjaman p ON u.id = p.user_id AND p.status IN ('Approved','Returned')
                            WHERE u.role = 'mahasiswa'
                            GROUP BY u.id, u.nama_lengkap, u.nim_nidn
                            ORDER BY total_pinjam DESC");

$total_peminjaman = getCount('peminjaman', "status IN ('Approved','Returned')");
$total_mahasiswa_aktif = getCount('peminjaman', "status IN ('Approved','Returned')");
$total_alat_digunakan = count($stat_alat);
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-chart-bar mr-3"></i> Statistik Penggunaan Laboratorium</h1>
    <p class="page-subtitle">Visualisasi data penggunaan laboratorium</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="stat-card text-center">
        <div class="stat-value text-primary"><?= $total_peminjaman ?></div>
        <div class="stat-label">Total Peminjaman</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value" style="color:#05CD99"><?= count(fetchAll("SELECT DISTINCT user_id FROM peminjaman WHERE status IN ('Approved','Returned')")) ?></div>
        <div class="stat-label">Mahasiswa Aktif</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value" style="color:#6E38F7"><?= $total_alat_digunakan ?></div>
        <div class="stat-label">Total Alat</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-8 gap-6 mb-6">
    <div class="lg:col-span-5">
        <div class="glass-card p-6">
            <h5 class="font-bold text-navy mb-4"><i class="fas fa-chart-line mr-2"></i> Tren Peminjaman per Bulan</h5>
            <canvas id="trenChart" height="250"></canvas>
        </div>
    </div>
    <div class="lg:col-span-3">
        <div class="glass-card p-6">
            <h5 class="font-bold text-navy mb-4"><i class="fas fa-trophy mr-2"></i> 5 Alat Terpopuler</h5>
            <ol class="space-y-3">
                <?php foreach (array_slice($stat_alat, 0, 5) as $i => $a): ?>
                <li class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <span class="text-navy"><?= $i+1 ?>. <?= htmlspecialchars($a['nama_alat']) ?></span>
                    <span class="glass-badge badge-primary"><?= (int)$a['dipinjam'] ?>x</span>
                </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="glass-card p-6">
        <h5 class="font-bold text-navy mb-4"><i class="fas fa-users mr-2"></i> Mahasiswa Aktif</h5>
        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead><tr><th>Nama</th><th>NIM</th><th>Total Pinjam</th></tr></thead>
                <tbody>
                    <?php foreach ($stat_mahasiswa as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($m['nim_nidn'] ?: '-') ?></td>
                        <td><span class="glass-badge badge-primary"><?= (int)$m['total_pinjam'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="glass-card p-6">
        <h5 class="font-bold text-navy mb-4"><i class="fas fa-box mr-2"></i> Status Alat Saat Ini</h5>
        <canvas id="statusChart" height="200"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('trenChart'), {
        type: 'bar',
        data: {
            labels: [<?php foreach ($stat_bulan as $s) { echo "'" . $s['bulan'] . "',"; } ?>],
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: [<?php foreach ($stat_bulan as $s) { echo $s['total'] . ","; } ?>],
                backgroundColor: '#4318FF',
                borderRadius: 6
            }]
        },
        options: { responsive: true }
    });

    <?php
    $status_counts = fetchAll("SELECT status, COUNT(*) as total FROM alat GROUP BY status");
    ?>
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: [<?php foreach ($status_counts as $s) { echo "'" . $s['status'] . "',"; } ?>],
            datasets: [{
                data: [<?php foreach ($status_counts as $s) { echo $s['total'] . ","; } ?>],
                backgroundColor: ['#05CD99','#ffc107','#FF5B75','#0dcaf0','#A3AED0']
            }]
        },
        options: { responsive: true }
    });
});
</script>
<?php include '../includes/footer.php'; ?>
