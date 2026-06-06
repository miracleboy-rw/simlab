-- ============================================================
-- SIM-Lab Biomedis - Database Schema
-- Sistem Informasi Manajemen Laboratorium Biomedis
-- ============================================================
-- INSTRUKSI: Jangan import SQL ini langsung melalui phpMyAdmin!
-- WAJIB jalankan setup.php melalui browser untuk hash password yang benar.
-- ============================================================

CREATE DATABASE IF NOT EXISTS simlab;
USE simlab;

-- -----------------------------------------------------------
-- 1. Tabel Users (Mahasiswa, Laboran, Dosen)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('mahasiswa','laboran','dosen') NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    nim_nidn VARCHAR(30) DEFAULT NULL,
    prodi VARCHAR(100) DEFAULT 'Teknik Biomedis',
    foto VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 2. Tabel Alat (Inventaris Alat Lab)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS alat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_alat VARCHAR(30) UNIQUE NOT NULL,
    nama_alat VARCHAR(150) NOT NULL,
    merk VARCHAR(100) DEFAULT NULL,
    spesifikasi TEXT,
    lokasi_penyimpanan VARCHAR(100) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT 'default_alat.png',
    stok_total INT NOT NULL DEFAULT 1,
    stok_tersedia INT NOT NULL DEFAULT 1,
    status ENUM('Tersedia','Dipinjam','Rusak','Kalibrasi','Tidak Tersedia') DEFAULT 'Tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 3. Tabel Bahan Habis Pakai
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS bahan_habis_pakai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_bahan VARCHAR(150) NOT NULL,
    satuan VARCHAR(30) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    stok_minimum INT NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 4. Tabel Peminjaman
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kode_peminjaman VARCHAR(30) UNIQUE NOT NULL,
    tgl_pinjam DATE NOT NULL,
    tgl_kembali DATE NOT NULL,
    tujuan TEXT,
    status ENUM('Pending','Approved','Rejected','Returned','Overdue') DEFAULT 'Pending',
    alasan_penolakan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 5. Tabel Detail Peminjaman (alat yang dipinjam)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS peminjaman_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL,
    alat_id INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (alat_id) REFERENCES alat(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 6. Tabel Dokumen Pendukung (Upload PDF)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS dokumen_pendukung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL,
    user_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    tipe ENUM('izin_lab','proposal','lainnya') DEFAULT 'lainnya',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 7. Tabel Laporan Kerusakan (dari mahasiswa)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS laporan_kerusakan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    alat_id INT NOT NULL,
    kronologi TEXT NOT NULL,
    gejala_kerusakan TEXT NOT NULL,
    tgl_kejadian DATE NOT NULL,
    status ENUM('Dilaporkan','Ditangani','Selesai') DEFAULT 'Dilaporkan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (alat_id) REFERENCES alat(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 8. Tabel Logbook Kerusakan (tindakan laboran)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS logbook_kerusakan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laporan_id INT NOT NULL,
    laboran_id INT NOT NULL,
    tindakan TEXT NOT NULL,
    status_operasional ENUM('Rusak Ringan','Rusak Berat','Servis','Operasional') DEFAULT 'Operasional',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (laporan_id) REFERENCES laporan_kerusakan(id) ON DELETE CASCADE,
    FOREIGN KEY (laboran_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 9. Tabel Kalibrasi & Maintenance
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS kalibrasi_maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alat_id INT NOT NULL,
    tipe ENUM('Kalibrasi','Maintenance','Servis') NOT NULL,
    tgl_mulai DATE NOT NULL,
    tgl_selesai DATE DEFAULT NULL,
    keterangan TEXT,
    status ENUM('Terjadwal','Sedang Berjalan','Selesai') DEFAULT 'Terjadwal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alat_id) REFERENCES alat(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 10. Tabel E-Library (Modul & Panduan)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS e_library (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    tipe ENUM('Modul Praktikum','Panduan Alat','K3','Lainnya') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 11. Tabel Kalender Events
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS kalender_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT DEFAULT NULL,
    peminjaman_lab_id INT DEFAULT NULL,
    alat_id INT DEFAULT NULL,
    lab_id INT DEFAULT NULL,
    judul VARCHAR(200) NOT NULL,
    tgl_mulai DATE NOT NULL,
    tgl_selesai DATE DEFAULT NULL,
    tipe ENUM('Praktikum','Riset','Peminjaman Lab','Maintenance','Kalibrasi','Lainnya') DEFAULT 'Lainnya',
    warna VARCHAR(7) DEFAULT '#3788d8',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 12. Tabel Verifikasi Riset TA (Dosen)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS verifikasi_ta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    dosen_id INT NOT NULL,
    judul_penelitian VARCHAR(255) NOT NULL,
    file_proposal VARCHAR(255) DEFAULT NULL,
    status ENUM('Pending','Disetujui','Ditolak') DEFAULT 'Pending',
    catatan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dosen_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 13. Tabel Laboratorium
CREATE TABLE IF NOT EXISTS laboratorium (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_lab VARCHAR(30) UNIQUE NOT NULL,
    nama_lab VARCHAR(150) NOT NULL,
    lokasi VARCHAR(100) DEFAULT NULL,
    kapasitas INT DEFAULT 10,
    deskripsi TEXT,
    fasilitas TEXT,
    status ENUM('Tersedia','Digunakan','Perbaikan') DEFAULT 'Tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 14. Tabel Peminjaman Lab
CREATE TABLE IF NOT EXISTS peminjaman_lab (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lab_id INT NOT NULL,
    kode_peminjaman VARCHAR(30) UNIQUE NOT NULL,
    tgl_pinjam DATE NOT NULL,
    tgl_kembali DATE NOT NULL,
    jam_mulai TIME NOT NULL DEFAULT '08:00',
    jam_selesai TIME NOT NULL DEFAULT '17:00',
    tujuan_peminjaman ENUM('Tugas Besar','Penelitian TA','Seminar KP','Seminar Proposal','Sidang Akhir','Lainnya') NOT NULL,
    deskripsi TEXT,
    status ENUM('Pending','Approved','Rejected','Returned') DEFAULT 'Pending',
    alasan_penolakan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lab_id) REFERENCES laboratorium(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 15. Tabel Notifikasi
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    pesan TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- DATA AWAL (Seeder) - Password di-set oleh setup.php!
-- ============================================================

INSERT INTO users (username, password, role, nama_lengkap, nim_nidn) VALUES
('laboran', '', 'laboran', 'Dr. Andi Laboran', '197501012010011001'),
('dosen1', '', 'dosen', 'Prof. Siti Dosen', '198002152005012002'),
('mahasiswa1', '', 'mahasiswa', 'Ahmad Mahasiswa', '2101234567'),
('mahasiswa2', '', 'mahasiswa', 'Budi Pratama', '2101234568');

-- Sample Alat
INSERT INTO alat (kode_alat, nama_alat, merk, spesifikasi, lokasi_penyimpanan, stok_total, stok_tersedia) VALUES
('EKG-001', 'Elektrokardiograf (EKG) 3-Lead', 'GE Healthcare', '3-channel, 12-lead interpretation, digital filter', 'Rak A-01', 3, 3),
('USG-001', 'Ultrasound Diagnostic Scanner', 'Siemens', 'Color Doppler, 2-5MHz convex probe, 5-10MHz linear probe', 'Rak A-02', 2, 2),
('SPIRO-001', 'Spirometer Digital', 'Jaeger', 'Flow/volume measurement, FVC, FEV1, PEF analysis', 'Rak B-01', 5, 5),
('DEFIB-001', 'Defibrillator Training Unit', 'Philips', 'Biphasic waveform, AED mode, training pads included', 'Rak B-02', 2, 2),
('BPM-001', 'Blood Pressure Monitor Digital', 'Omron', 'Automatic inflation, LCD display, memory storage', 'Rak C-01', 10, 10),
('PULSE-001', 'Pulse Oximeter', 'Masimo', 'SpO2, pulse rate, perfusion index, motion tolerant', 'Rak C-02', 8, 8);

-- Sample Bahan Habis Pakai
INSERT INTO bahan_habis_pakai (nama_bahan, satuan, stok, stok_minimum) VALUES
('Elektroda EKG', 'buah', 100, 20),
('Gel EKG', 'tube', 15, 5),
('Kertas ECG', 'roll', 30, 10),
('Alkohol Swab', 'pack', 25, 5),
('Sarung Tangan Lateks', 'box', 10, 3),
('Masker Medis', 'box', 20, 5),
('Cairan Disinfektan', 'liter', 8, 2);

-- Sample Events (Kalender)
INSERT INTO kalender_events (judul, tgl_mulai, tgl_selesai, tipe, warna) VALUES
('Praktikum Fisiologi', '2026-06-08', '2026-06-08', 'Praktikum', '#28a745'),
('Praktikum Instrumentasi Biomedis', '2026-06-10', '2026-06-10', 'Praktikum', '#28a745'),
('Kalibrasi EKG', '2026-06-15', '2026-06-16', 'Kalibrasi', '#dc3545'),
('Maintenance USG', '2026-06-20', '2026-06-21', 'Maintenance', '#ffc107');

-- Sample Laboratorium (4 Lab Teknik Biomedis)
INSERT INTO laboratorium (kode_lab, nama_lab, lokasi, kapasitas, deskripsi, fasilitas) VALUES
('LAB-SJ', 'Laboratorium Rekayasa Sel dan Jaringan', 'Gedung Biomedis Lt.2', 15, 'Laboratorium untuk penelitian rekayasa sel, kultur jaringan, dan biomedis regeneratif', 'Mikroskop Inverted, Laminar Air Flow, Inkubator CO2, Centrifuge, Autoclave'),
('LAB-BM', 'Laboratorium Biomaterial', 'Gedung Biomedis Lt.2', 15, 'Laboratorium untuk penelitian dan pengembangan material biomedis', 'Spektrofotometer FTIR, Universal Testing Machine, Hot Plate Stirrer, pH Meter, Oven'),
('LAB-IN', 'Laboratorium Instrumentasi Biomedis', 'Gedung Biomedis Lt.1', 20, 'Laboratorium untuk perancangan dan pengujian alat-alat medis', 'Oscilloscope, Function Generator, Multimeter Digital, Soldering Station, Power Supply, EKG Trainer'),
('LAB-PC', 'Laboratorium Pencitraan Biomedis', 'Gedung Biomedis Lt.1', 15, 'Laboratorium untuk pengolahan citra medis dan pencitraan diagnostik', 'Computer Workstation, MATLAB Lisensi, Software RadiAnt DICOM, 3D Slicer, Phantom MRI');

-- Sample E-Library
INSERT INTO e_library (judul, tipe, file_path, deskripsi, uploaded_by) VALUES
('Modul Praktikum Fisiologi 2026', 'Modul Praktikum', 'modul_fisiologi_2026.pdf', 'Modul praktikum fisiologi untuk semester genap 2025/2026', 1),
('Panduan Pengoperasian EKG', 'Panduan Alat', 'panduan_ekg.pdf', 'Panduan lengkap penggunaan alat EKG 3-lead', 1),
('Prosedur K3 Laboratorium', 'K3', 'prosedur_k3_lab.pdf', 'Standar operasional prosedur keselamatan dan kesehatan kerja', 1);
