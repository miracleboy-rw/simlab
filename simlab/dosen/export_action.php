<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('dosen')) { die('Akses ditolak!'); }

$type = $_GET['type'] ?? 'alat';
$format = $_GET['format'] ?? 'xlsx';

$filename = '';

if ($type == 'alat') {
    $data = fetchAll("SELECT * FROM alat ORDER BY nama_alat ASC");
    $filename = 'Data_Inventaris_Alat_Lab';
} elseif ($type == 'peminjaman') {
    $data = fetchAll("SELECT p.*, u.nama_lengkap FROM peminjaman p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
    $filename = 'Data_Aktivitas_Peminjaman';
} elseif ($type == 'lengkap') {
    $alat = fetchAll("SELECT * FROM alat ORDER BY nama_alat ASC");
    $peminjaman = fetchAll("SELECT p.*, u.nama_lengkap FROM peminjaman p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
    $data = ['alat' => $alat, 'peminjaman' => $peminjaman];
    $filename = 'Laporan_Lengkap_Laboratorium';
} else {
    die('Tipe data tidak valid.');
}

if ($format == 'xlsx') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Ymd') . '.xls"');
    header('Cache-Control: max-age=0');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta charset="UTF-8"><style>td, th { border: 1px solid #ccc; padding: 5px; } th { background: #4472c4; color: white; }</style></head>';
    echo '<body>';

    if ($type == 'alat' || $type == 'lengkap') {
        $items = $type == 'lengkap' ? $data['alat'] : $data;
        echo '<h2>DATA INVENTARIS ALAT LABORATORIUM</h2>';
        echo '<table>';
        echo '<tr><th>No</th><th>Kode Alat</th><th>Nama Alat</th><th>Merk</th><th>Spesifikasi</th><th>Lokasi</th><th>Stok Total</th><th>Stok Tersedia</th><th>Status</th></tr>';
        foreach ($items as $i => $a) {
            echo "<tr><td>" . ($i+1) . "</td><td>{$a['kode_alat']}</td><td>{$a['nama_alat']}</td><td>{$a['merk']}</td><td>{$a['spesifikasi']}</td><td>{$a['lokasi_penyimpanan']}</td><td>{$a['stok_total']}</td><td>{$a['stok_tersedia']}</td><td>{$a['status']}</td></tr>";
        }
        echo '</table>';
    }

    if ($type == 'peminjaman' || $type == 'lengkap') {
        $items = $type == 'lengkap' ? $data['peminjaman'] : $data;
        echo '<h2>DATA AKTIVITAS PEMINJAMAN</h2>';
        echo '<table>';
        echo '<tr><th>No</th><th>Kode</th><th>Peminjam</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Tujuan</th><th>Status</th></tr>';
        foreach ($items as $i => $p) {
            echo "<tr><td>" . ($i+1) . "</td><td>{$p['kode_peminjaman']}</td><td>{$p['nama_lengkap']}</td><td>{$p['tgl_pinjam']}</td><td>{$p['tgl_kembali']}</td><td>{$p['tujuan']}</td><td>{$p['status']}</td></tr>";
        }
        echo '</table>';
    }

    if ($type == 'lengkap') {
        $bahan = fetchAll("SELECT * FROM bahan_habis_pakai ORDER BY nama_bahan ASC");
        echo '<h2>DATA BAHAN HABIS PAKAI</h2>';
        echo '<table><tr><th>No</th><th>Nama Bahan</th><th>Satuan</th><th>Stok</th><th>Stok Minimum</th></tr>';
        foreach ($bahan as $i => $b) {
            echo "<tr><td>" . ($i+1) . "</td><td>{$b['nama_bahan']}</td><td>{$b['satuan']}</td><td>{$b['stok']}</td><td>{$b['stok_minimum']}</td></tr>";
        }
        echo '</table>';
    }

    echo '</body></html>';

} elseif ($format == 'pdf') {
    $html = '<html><head><meta charset="UTF-8"><style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { color: #0d6efd; border-bottom: 2px solid #0d6efd; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #4472c4; color: white; padding: 6px; text-align: left; font-size: 11px; }
        td { border: 1px solid #ccc; padding: 5px; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { color: #666; font-size: 12px; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #999; border-top: 1px solid #ccc; padding-top: 10px; }
    </style></head><body>';

    $html .= '<div class="header">
        <h1>LAPORAN DATA LABORATORIUM BIOMEDIS</h1>
        <p>Sistem Informasi Manajemen Laboratorium (SIM-Lab)</p>
        <p>Tanggal: ' . date('d/m/Y') . '</p>
    </div>';

    if ($type == 'alat' || $type == 'lengkap') {
        $items = $type == 'lengkap' ? $data['alat'] : $data;
        $html .= '<h2>A. DATA INVENTARIS ALAT</h2>';
        $html .= '<table><tr><th>No</th><th>Kode</th><th>Nama Alat</th><th>Merk</th><th>Lokasi</th><th>Stok</th><th>Status</th></tr>';
        foreach ($items as $i => $a) {
            $html .= "<tr><td>" . ($i+1) . "</td><td>{$a['kode_alat']}</td><td>{$a['nama_alat']}</td><td>{$a['merk']}</td><td>{$a['lokasi_penyimpanan']}</td><td>{$a['stok_tersedia']}/{$a['stok_total']}</td><td>{$a['status']}</td></tr>";
        }
        $html .= '</table>';
    }

    if ($type == 'peminjaman' || $type == 'lengkap') {
        $items = $type == 'lengkap' ? $data['peminjaman'] : $data;
        $html .= '<h2>B. DATA AKTIVITAS PEMINJAMAN</h2>';
        $html .= '<table><tr><th>No</th><th>Kode</th><th>Peminjam</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th></tr>';
        foreach ($items as $i => $p) {
            $html .= "<tr><td>" . ($i+1) . "</td><td>{$p['kode_peminjaman']}</td><td>{$p['nama_lengkap']}</td><td>{$p['tgl_pinjam']}</td><td>{$p['tgl_kembali']}</td><td>{$p['status']}</td></tr>";
        }
        $html .= '</table>';
    }

    if ($type == 'lengkap') {
        $bahan = fetchAll("SELECT * FROM bahan_habis_pakai ORDER BY nama_bahan ASC");
        $html .= '<h2>C. DATA BAHAN HABIS PAKAI</h2>';
        $html .= '<table><tr><th>No</th><th>Nama Bahan</th><th>Satuan</th><th>Stok</th><th>Stok Minimum</th></tr>';
        foreach ($bahan as $i => $b) {
            $html .= "<tr><td>" . ($i+1) . "</td><td>{$b['nama_bahan']}</td><td>{$b['satuan']}</td><td>{$b['stok']}</td><td>{$b['stok_minimum']}</td></tr>";
        }
        $html .= '</table>';
    }

    $html .= '<div class="footer">
        <p>Dokumen ini diexport dari SIM-Lab Biomedis pada ' . date('d/m/Y H:i:s') . '</p>
        <p>Status: Dokumen Resmi untuk Keperluan Akreditasi</p>
    </div>';

    $html .= '</body></html>';

    if (extension_loaded('dompdf')) {
        require_once 'vendor/autoload.php';
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($filename . '_' . date('Ymd') . '.pdf', ['Attachment' => true]);
    } else {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Ymd') . '.pdf"');
        echo $html;
    }
}

exit;
