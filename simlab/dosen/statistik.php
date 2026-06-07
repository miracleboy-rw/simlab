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
    <h1 class="page-title"><span class="material-symbols-outlined" style="margin-right:12px">bar_chart</span> Statistik Penggunaan Laboratorium</h1>
    <p class="text-muted" style="margin-top:4px;font-size:13px">Visualisasi data penggunaan laboratorium</p>
</div>

<div class="grid-3 mb-6">
    <div class="card p-5 text-center">
        <div style="font-size:28px;font-weight:700;color:#2a4dd7"><?= $total_peminjaman ?></div>
        <div class="stat-card-label">Total Peminjaman</div>
    </div>
    <div class="card p-5 text-center">
        <div style="font-size:28px;font-weight:700;color:#05CD99"><?= count(fetchAll("SELECT DISTINCT user_id FROM peminjaman WHERE status IN ('Approved','Returned')")) ?></div>
        <div class="stat-card-label">Mahasiswa Aktif</div>
    </div>
    <div class="card p-5 text-center">
        <div style="font-size:28px;font-weight:700;color:#6E38F7"><?= $total_alat_digunakan ?></div>
        <div class="stat-card-label">Total Alat</div>
    </div>
</div>

<div style="display:flex;gap:24px;margin-bottom:24px;flex-wrap:wrap">
    <div class="flex-1" style="min-width:280px">
        <div class="card p-5">
            <div class="section-header"><span class="material-symbols-outlined" style="margin-right:8px">show_chart</span> Tren Peminjaman per Bulan</div>
            <canvas id="trenChart" height="250"></canvas>
        </div>
    </div>
    <div class="flex-1" style="min-width:240px">
        <div class="card p-5">
            <div class="section-header"><span class="material-symbols-outlined" style="margin-right:8px">trophy</span> 5 Alat Terpopuler</div>
            <ol style="display:flex;flex-direction:column;gap:12px">
                <?php foreach (array_slice($stat_alat, 0, 5) as $i => $a): ?>
                <li style="display:flex;align-items:center;justify-content:space-between;padding-bottom:8px;border-bottom:1px solid #E5E7EB">
                    <span><?= $i+1 ?>. <?= htmlspecialchars($a['nama_alat']) ?></span>
                    <span class="badge" style="background:#DBEAFE;color:#2a4dd7"><?= (int)$a['dipinjam'] ?>x</span>
                </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card p-5">
        <div class="section-header"><span class="material-symbols-outlined" style="margin-right:8px">group</span> Mahasiswa Aktif</div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Nama</th><th>NIM</th><th>Total Pinjam</th></tr></thead>
                <tbody>
                    <?php foreach ($stat_mahasiswa as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['nama_lengkap']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($m['nim_nidn'] ?: '-') ?></td>
                        <td><span class="badge" style="background:#DBEAFE;color:#2a4dd7"><?= (int)$m['total_pinjam'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card p-5">
        <div class="section-header"><span class="material-symbols-outlined" style="margin-right:8px">inventory_2</span> Status Alat Saat Ini</div>
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
