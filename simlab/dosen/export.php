<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('dosen')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }
$base_url = '../';
$page_title = 'Export Data Akreditasi';
include '../includes/header.php';
?>
<div class="mb-6">
    <h1 class="page-title"><i class="fas fa-file-export mr-3"></i> Export Data Akreditasi</h1>
    <p class="page-subtitle">Export data inventaris dan aktivitas laboratorium untuk keperluan akreditasi</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="glass-card p-6 text-center">
        <div class="py-6">
            <i class="fas fa-box text-primary" style="font-size: 4rem;"></i>
            <h5 class="font-bold text-navy mt-4 mb-2">Data Inventaris Alat</h5>
            <p class="text-muted text-sm mb-6">Export seluruh data alat laboratorium termasuk spesifikasi dan status</p>
            <a href="export_action.php?type=alat&format=xlsx" class="btn-glass btn-glass-success w-full mb-3 text-center">
                <i class="fas fa-file-excel mr-2"></i> Export Excel (.xlsx)
            </a>
            <a href="export_action.php?type=alat&format=pdf" class="btn-glass btn-glass-accent w-full text-center">
                <i class="fas fa-file-pdf mr-2"></i> Export PDF
            </a>
        </div>
    </div>
    <div class="glass-card p-6 text-center">
        <div class="py-6">
            <i class="fas fa-exchange-alt" style="font-size: 4rem;color:#05CD99;"></i>
            <h5 class="font-bold text-navy mt-4 mb-2">Aktivitas Peminjaman</h5>
            <p class="text-muted text-sm mb-6">Export histori peminjaman alat oleh mahasiswa</p>
            <a href="export_action.php?type=peminjaman&format=xlsx" class="btn-glass btn-glass-success w-full mb-3 text-center">
                <i class="fas fa-file-excel mr-2"></i> Export Excel (.xlsx)
            </a>
            <a href="export_action.php?type=peminjaman&format=pdf" class="btn-glass btn-glass-accent w-full text-center">
                <i class="fas fa-file-pdf mr-2"></i> Export PDF
            </a>
        </div>
    </div>
    <div class="glass-card p-6 text-center">
        <div class="py-6">
            <i class="fas fa-chart-pie" style="font-size: 4rem;color:#0dcaf0;"></i>
            <h5 class="font-bold text-navy mt-4 mb-2">Laporan Lengkap</h5>
            <p class="text-muted text-sm mb-6">Export semua data (inventaris + peminjaman + statistik)</p>
            <a href="export_action.php?type=lengkap&format=xlsx" class="btn-glass btn-glass-success w-full mb-3 text-center">
                <i class="fas fa-file-excel mr-2"></i> Export Excel (.xlsx)
            </a>
            <a href="export_action.php?type=lengkap&format=pdf" class="btn-glass btn-glass-accent w-full text-center">
                <i class="fas fa-file-pdf mr-2"></i> Export PDF
            </a>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
