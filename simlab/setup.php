<?php
// Setup SIFLAB-BM
// Jalankan: http://localhost/simlab/setup.php

$host = 'sql313.infinityfree.com';
$dbname = 'if0_42116915_simlab';
$username = 'if0_42116915';
$password = 'DWikOywMWjq';

echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'>";
echo "<title>Setup SIFLAB-BM</title>";
echo "<script src='https://cdn.tailwindcss.com'></script>";
echo "<script>tailwind.config={theme:{extend:{colors:{primary:'#4318FF',secondary:'#6E38F7',accent:'#FF5B75',navy:'#2B3674',soft:'#F4F7FE'},borderRadius:{'3xl':'24px'}}}}</script>";
echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>";
echo "<link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap' rel='stylesheet'>";
echo "<style>body{font-family:'Inter',sans-serif;background:#F4F7FE;min-height:100vh;padding:2rem}</style></head><body>";
echo "<div class='max-w-2xl mx-auto'><div style='background:rgba(255,255,255,0.85);backdrop-filter:blur(24px);border-radius:24px;box-shadow:0 20px 60px rgba(153,153,153,0.12);padding:2.5rem;border:1px solid rgba(255,255,255,0.5)'>";
echo "<div class='flex items-center gap-4 mb-6'><div class='w-12 h-12 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center'><i class='fas fa-flask text-white text-xl'></i></div><div><h1 class='text-2xl font-extrabold text-navy'>Setup SIFLAB-BM</h1><p class='text-gray-400 text-sm font-medium'>Sistem Informasi Manajemen Lab Biomedis</p></div></div>";

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");
    echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 text-emerald-700 mb-3'><i class='fas fa-check-circle text-emerald-500'></i><span class='font-medium'>Database <strong>$dbname</strong> siap.</span></div>";

    // Hapus SEMUA data lama dulu biar gak bentrok UNIQUE constraint
    $tables = ['logbook_kerusakan','laporan_kerusakan','peminjaman_lab','peminjaman_items','dokumen_pendukung','peminjaman','kalibrasi_maintenance','e_library','kalender_events','verifikasi_ta','notifikasi','bahan_habis_pakai','laboratorium','alat','users'];
    foreach ($tables as $t) { try { $pdo->exec("DELETE FROM `$t`"); } catch (PDOException $e) {} }

    // Jalankan database.sql untuk buat tabel + seed data
    $sql = file_get_contents(__DIR__ . '/database.sql');
    // Hapus baris CREATE DATABASE & USE — sudah dibuat di atas
    $sql_clean = preg_replace('/^(CREATE DATABASE|USE ).*;?$/m', '', $sql);
    $statements = explode(";\n", trim($sql_clean));
    $errors = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt)) {
            try { $pdo->exec($stmt); } catch (PDOException $e) { $errors++; }
        }
    }
    $msg = $errors ? "Tabel dibuat ($errors error ringan — seed data mungkin sudah ada)" : "Tabel dan seed berhasil dibuat.";
    echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-" . ($errors ? 'yellow' : 'emerald') . "-50 text-" . ($errors ? 'yellow' : 'emerald') . "-700 mb-3'><i class='fas fa-" . ($errors ? 'exclamation-triangle' : 'check') . "-circle'></i><span class='font-medium'>$msg</span></div>";

    // Reset password users — pakai prepared statement biar aman
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $st = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
    foreach (['laboran','dosen1','mahasiswa1','mahasiswa2'] as $u) { $st->execute([$hash, $u]); }
    echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 text-emerald-700 mb-3'><i class='fas fa-check-circle text-emerald-500'></i><span class='font-medium'>Password akun demo direset — semua password: <strong>admin123</strong></span></div>";
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    try { $pdo->exec("INSERT IGNORE INTO users (username, password, role, nama_lengkap, nim_nidn) VALUES
        ('laboran', '$hash', 'laboran', 'Dr. Andi Laboran', '197501012010011001'),
        ('dosen1', '$hash', 'dosen', 'Prof. Siti Dosen', '198002152005012002'),
        ('mahasiswa1', '$hash', 'mahasiswa', 'Ahmad Mahasiswa', '2101234567'),
        ('mahasiswa2', '$hash', 'mahasiswa', 'Budi Pratama', '2101234568')");
    echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 text-emerald-700 mb-3'><i class='fas fa-check-circle text-emerald-500'></i><span class='font-medium'>Akun demo dibuat — password: <strong>admin123</strong></span></div>";
    } catch (PDOException $e) {
    echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-amber-50 text-amber-700 mb-3'><i class='fas fa-info-circle'></i><span class='font-medium'>Akun demo sudah ada (skip).</span></div>";
    }

    try { $pdo->exec("DELETE FROM laboratorium");
    $pdo->exec("INSERT INTO laboratorium (kode_lab, nama_lab, lokasi, kapasitas, deskripsi, fasilitas) VALUES
        ('LAB-SJ','Laboratorium Rekayasa Sel dan Jaringan','Gedung Biomedis Lt.2',15,'Laboratorium kultur jaringan dan biomedis regeneratif','Mikroskop Inverted, Laminar Air Flow, Inkubator CO2'),
        ('LAB-BM','Laboratorium Biomaterial','Gedung Biomedis Lt.2',15,'Laboratorium pengembangan material biomedis','Spektrofotometer FTIR, Universal Testing Machine'),
        ('LAB-IN','Laboratorium Instrumentasi Biomedis','Gedung Biomedis Lt.1',20,'Laboratorium perancangan alat medis','Oscilloscope, Function Generator, EKG Trainer'),
        ('LAB-PC','Laboratorium Pencitraan Biomedis','Gedung Biomedis Lt.1',15,'Laboratorium pengolahan citra medis','Workstation, MATLAB, Software DICOM')");
    echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 text-emerald-700 mb-3'><i class='fas fa-check-circle text-emerald-500'></i><span class='font-medium'>Data 4 laboratorium berhasil diisi.</span></div>";
    } catch (PDOException $e) { echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-amber-50 text-amber-700 mb-3'><i class='fas fa-info-circle'></i><span class='font-medium'>Data laboratorium (skip).</span></div>"; }

    try { $pdo->exec("DELETE FROM alat");
    $pdo->exec("INSERT INTO alat (kode_alat, nama_alat, merk, spesifikasi, lokasi_penyimpanan, stok_total, stok_tersedia) VALUES
        ('EKG-001', 'Elektrokardiograf (EKG) 3-Lead', 'GE Healthcare', '3-channel, 12-lead interpretation', 'Rak A-01', 3, 3),
        ('USG-001', 'Ultrasound Diagnostic Scanner', 'Siemens', 'Color Doppler, convex & linear probe', 'Rak A-02', 2, 2),
        ('SPIRO-001', 'Spirometer Digital', 'Jaeger', 'Flow/volume, FVC, FEV1, PEF', 'Rak B-01', 5, 5),
        ('DEFIB-001', 'Defibrillator Training Unit', 'Philips', 'Biphasic, AED mode', 'Rak B-02', 2, 2),
        ('BPM-001', 'Blood Pressure Monitor Digital', 'Omron', 'Automatic, LCD display', 'Rak C-01', 10, 10),
        ('PULSE-001', 'Pulse Oximeter', 'Masimo', 'SpO2, pulse rate', 'Rak C-02', 8, 8)");
    echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 text-emerald-700 mb-3'><i class='fas fa-check-circle text-emerald-500'></i><span class='font-medium'>Data alat berhasil diisi.</span></div>";
    } catch (PDOException $e) { echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-amber-50 text-amber-700 mb-3'><i class='fas fa-info-circle'></i><span class='font-medium'>Data alat (skip).</span></div>"; }

    try { $pdo->exec("DELETE FROM bahan_habis_pakai");
    $pdo->exec("INSERT INTO bahan_habis_pakai (nama_bahan, satuan, stok, stok_minimum) VALUES
        ('Elektroda EKG','buah',100,20),('Gel EKG','tube',15,5),('Kertas ECG','roll',30,10),
        ('Alkohol Swab','pack',25,5),('Sarung Tangan Lateks','box',10,3),('Masker Medis','box',20,5),('Cairan Disinfektan','liter',8,2)");
    echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 text-emerald-700 mb-3'><i class='fas fa-check-circle text-emerald-500'></i><span class='font-medium'>Data bahan habis pakai berhasil diisi.</span></div>";
    } catch (PDOException $e) { echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-amber-50 text-amber-700 mb-3'><i class='fas fa-info-circle'></i><span class='font-medium'>Data bahan (skip).</span></div>"; }

    try { $pdo->exec("DELETE FROM kalender_events");
    $pdo->exec("INSERT INTO kalender_events (judul, tgl_mulai, tgl_selesai, tipe, warna) VALUES
        ('Praktikum Fisiologi','2026-06-08','2026-06-08','Praktikum','#28a745'),
        ('Praktikum Instrumentasi Biomedis','2026-06-10','2026-06-10','Praktikum','#28a745'),
        ('Kalibrasi EKG','2026-06-15','2026-06-16','Kalibrasi','#dc3545'),
        ('Maintenance USG','2026-06-20','2026-06-21','Maintenance','#ffc107')");
    echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 text-emerald-700 mb-4'><i class='fas fa-check-circle text-emerald-500'></i><span class='font-medium'>Data sample berhasil diisi.</span></div>";
    } catch (PDOException $e) { echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-amber-50 text-amber-700 mb-4'><i class='fas fa-info-circle'></i><span class='font-medium'>Data kalender (skip).</span></div>"; }

    $dirs = [__DIR__ . '/uploads/dokumen', __DIR__ . '/uploads/foto_alat', __DIR__ . '/uploads/ebook'];
    foreach ($dirs as $dir) { if (!is_dir($dir)) mkdir($dir, 0777, true); }

    echo "<hr class='my-6 border-gray-100'>";
    echo "<div class='p-6 rounded-2xl bg-gradient-to-br from-primary/5 to-secondary/5 border border-primary/10'>";
    echo "<h3 class='text-lg font-bold text-navy mb-4'><i class='fas fa-check-circle text-primary mr-2'></i>Setup Selesai!</h3>";
    echo "<p class='text-sm text-muted mb-4'>Silakan login dengan akun demo:</p>";
    echo "<div class='space-y-2 mb-6'>";
    echo "<div class='flex items-center gap-3 p-3 rounded-xl bg-white/60'><div class='w-8 h-8 rounded-xl bg-gradient-to-br from-primary to-secondary text-white flex items-center justify-center text-xs font-bold'><i class='fas fa-tools'></i></div><div><span class='font-semibold text-sm'>Laboran</span><div class='text-xs text-gray-400'>laboran / admin123</div></div></div>";
    echo "<div class='flex items-center gap-3 p-3 rounded-xl bg-white/60'><div class='w-8 h-8 rounded-xl bg-gradient-to-br from-secondary to-purple-500 text-white flex items-center justify-center text-xs font-bold'><i class='fas fa-chalkboard-teacher'></i></div><div><span class='font-semibold text-sm'>Dosen</span><div class='text-xs text-gray-400'>dosen1 / admin123</div></div></div>";
    echo "<div class='flex items-center gap-3 p-3 rounded-xl bg-white/60'><div class='w-8 h-8 rounded-xl bg-gradient-to-br from-amber-400 to-orange-400 text-white flex items-center justify-center text-xs font-bold'><i class='fas fa-user-graduate'></i></div><div><span class='font-semibold text-sm'>Mahasiswa</span><div class='text-xs text-gray-400'>mahasiswa1 / admin123</div></div></div>";
    echo "</div>";
    echo "<a href='auth/login.php' style='background:linear-gradient(135deg,#4318FF,#6E38F7);color:white;border-radius:16px;padding:0.75rem 2rem;font-weight:700;display:inline-flex;align-items:center;gap:0.5rem;text-decoration:none;box-shadow:0 4px 15px rgba(67,24,255,0.3)'>";
    echo "<i class='fas fa-arrow-right-to-bracket'></i> Login Sekarang</a></div>";

} catch (PDOException $e) {
    echo "<div class='flex items-center gap-3 p-4 rounded-2xl bg-red-50 text-red-600 mb-3'><i class='fas fa-exclamation-circle'></i><span class='font-medium'>ERROR: " . $e->getMessage() . "</span></div>";
    echo "<p class='text-sm text-gray-400'>Pastikan XAMPP MySQL sudah berjalan.</p>";
}

echo "</div></div></body></html>";
