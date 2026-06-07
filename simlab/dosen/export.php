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
    <h1 class="page-title"><span class="material-symbols-outlined" style="margin-right:12px">file_upload</span> Export Data Akreditasi</h1>
    <p class="text-muted" style="margin-top:4px;font-size:13px">Export data inventaris dan aktivitas laboratorium untuk keperluan akreditasi</p>
</div>

<div class="grid-3">
    <div class="card p-5 text-center">
        <div style="padding:24px 0">
            <span class="material-symbols-outlined" style="font-size:4rem;color:#2a4dd7">inventory_2</span>
            <h5 class="font-bold mt-4 mb-2" style="color:#1e3a5f">Data Inventaris Alat</h5>
            <p class="text-muted text-sm mb-6">Export seluruh data alat laboratorium termasuk spesifikasi dan status</p>
            <a href="export_action.php?type=alat&format=xlsx" class="btn btn-success w-full mb-3 text-center" style="display:flex">
                <span class="material-symbols-outlined" style="margin-right:8px">table_chart</span> Export Excel (.xlsx)
            </a>
            <a href="export_action.php?type=alat&format=pdf" class="btn btn-outline w-full text-center" style="display:flex">
                <span class="material-symbols-outlined" style="margin-right:8px">picture_as_pdf</span> Export PDF
            </a>
        </div>
    </div>
    <div class="card p-5 text-center">
        <div style="padding:24px 0">
            <span class="material-symbols-outlined" style="font-size:4rem;color:#05CD99">swap_horiz</span>
            <h5 class="font-bold mt-4 mb-2" style="color:#1e3a5f">Aktivitas Peminjaman</h5>
            <p class="text-muted text-sm mb-6">Export histori peminjaman alat oleh mahasiswa</p>
            <a href="export_action.php?type=peminjaman&format=xlsx" class="btn btn-success w-full mb-3 text-center" style="display:flex">
                <span class="material-symbols-outlined" style="margin-right:8px">table_chart</span> Export Excel (.xlsx)
            </a>
            <a href="export_action.php?type=peminjaman&format=pdf" class="btn btn-outline w-full text-center" style="display:flex">
                <span class="material-symbols-outlined" style="margin-right:8px">picture_as_pdf</span> Export PDF
            </a>
        </div>
    </div>
    <div class="card p-5 text-center">
        <div style="padding:24px 0">
            <span class="material-symbols-outlined" style="font-size:4rem;color:#0dcaf0">dashboard</span>
            <h5 class="font-bold mt-4 mb-2" style="color:#1e3a5f">Laporan Lengkap</h5>
            <p class="text-muted text-sm mb-6">Export semua data (inventaris + peminjaman + statistik)</p>
            <a href="export_action.php?type=lengkap&format=xlsx" class="btn btn-success w-full mb-3 text-center" style="display:flex">
                <span class="material-symbols-outlined" style="margin-right:8px">table_chart</span> Export Excel (.xlsx)
            </a>
            <a href="export_action.php?type=lengkap&format=pdf" class="btn btn-outline w-full text-center" style="display:flex">
                <span class="material-symbols-outlined" style="margin-right:8px">picture_as_pdf</span> Export PDF
            </a>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
