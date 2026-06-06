# Dokumentasi SIFLAB-BM (Sistem Informasi Manajemen Laboratorium Biomedis)

Dokumen ini menjelaskan fitur-fitur yang tersedia dalam sistem berdasarkan hak akses pengguna.

---

## 1. Role & Hak Akses

| Role | Deskripsi |
|------|-----------|
| **Mahasiswa** | Dapat meminjam alat/lab, melihat katalog, kalender, e-library, dan melapor kerusakan |
| **Laboran** | Admin utama: verifikasi peminjaman, CRUD inventaris, kelola stok bahan, kalibrasi, logbook |
| **Dosen** | Verifikasi riset TA, melihat statistik, export data akreditasi |

---

## 2. Fitur Umum (Semua Role)

### 2.1 Katalog Alat (`public/katalog.php`)
Menampilkan daftar alat laboratorium dalam bentuk kartu.

- **Search**: Mencari alat berdasarkan nama (text input + tombol submit otomatis via Enter)
- **Filter dropdown**: Menyaring alat berdasarkan status (Semua, Tersedia, Dipinjam, Rusak, Kalibrasi)
- **Lihat Detail**: Menampilkan informasi lengkap alat (spesifikasi, lokasi penyimpanan, stok, status)
- **Tambah Alat** (laboran): Membuka modal form tambah alat baru
- **Edit** (laboran): Membuka modal form edit data alat
- **Hapus** (laboran): Menghapus alat dengan konfirmasi

### 2.2 Kalender Jadwal (`public/kalender.php`)
Kalender interaktif yang menampilkan semua jadwal.

- **Tab filter lab**: Menyaring tampilan kalender berdasarkan laboratorium tertentu
- **Navigasi kalender**: Tombol prev/next bulan, tombol "Today" untuk kembali ke hari ini
- **Switch view**: Day (hari), Week (minggu), Month (bulan), List (daftar)
- **Klik event**: Membuka modal detail event (informasi lab/tanggal/jam/status)
- **Tambah Event** (laboran): Membuat event manual (judul, tanggal, tipe, warna)
- **Edit Event** (laboran): Mengubah event yang sudah ada
- **Hapus Event** (laboran): Menghapus event dengan konfirmasi

### 2.3 E-Library (`public/e_library.php`)
Portal unduhan dokumen digital.

- **Filter kategori**: Dropdown untuk memilih tipe dokumen (Semua, Modul Praktikum, Panduan Alat, K3)
- **Download**: Mengunduh file PDF dokumen
- **Upload** (laboran): Menambahkan dokumen baru (judul, kategori, file PDF)
- **Edit** (laboran): Mengubah judul/kategori dokumen
- **Hapus** (laboran): Menghapus dokumen dengan konfirmasi

---

## 3. Fitur Mahasiswa

### 3.1 Dashboard Mahasiswa (`mahasiswa/dashboard.php`)
Menampilkan ringkasan aktivitas mahasiswa.

- **Stat cards**: Total peminjaman yang pernah diajukan
- **Riwayat 5 peminjaman terakhir**: Tabel dengan kode, alat, tanggal, status
- **Lihat Status**: Tombol navigasi ke halaman tracking status

### 3.2 Form Peminjaman Alat (`mahasiswa/form_peminjaman.php`)
Form untuk mengajukan peminjaman alat laboratorium.

- **Upload Dokumen**: Memilih tipe dokumen (Izin Lab, Proposal TA, Lainnya) + upload file PDF
- **Daftar alat (checkbox)**: Memilih alat yang ingin dipinjam dari daftar tersedia
- **Tanggal Pinjam / Tanggal Kembali**: Input date untuk periode peminjaman
- **Tujuan Penggunaan**: Textarea untuk mengisi tujuan peminjaman
- **Ajukan Peminjaman**: Tombol submit untuk mengirim pengajuan
- **Batal**: Tombol kembali ke dashboard

### 3.3 Form Peminjaman Lab (`mahasiswa/form_peminjaman_lab.php`)
Form untuk mengajukan peminjaman ruang laboratorium.

- **Pilih Lab**: Radio button untuk memilih laboratorium tujuan
- **Tujuan Peminjaman**: Dropdown (Tugas Besar, Penelitian TA, Seminar KP, Seminar Proposal, Sidang Akhir)
- **Tanggal Pinjam / Tanggal Kembali**: Input date
- **Jam Mulai / Jam Selesai**: Input time
- **Deskripsi Kegiatan**: Textarea
- **Ajukan Peminjaman**: Tombol submit

### 3.4 Tracking Status Peminjaman Alat (`mahasiswa/tracking_status.php`)
Memantau status pengajuan peminjaman alat.

- **Filter status**: Dropdown untuk memfilter status (Semua, Pending, Approved, Rejected, Returned, Overdue)
- **Tabel riwayat**: Kode peminjaman, daftar alat, tanggal pinjam/kembali, status (badge warna), alasan penolakan
- **Tabel dokumen**: Daftar dokumen pendukung yang sudah diupload

### 3.5 Tracking Peminjaman Lab (`mahasiswa/tracking_peminjaman_lab.php`)
Memantau status pengajuan peminjaman laboratorium.

- **Stat cards**: Total, Pending, Disetujui, Ditolak
- **Filter status**: Dropdown untuk filter status
- **Pinjam Lab**: Tombol navigasi ke form peminjaman lab
- **Tabel riwayat**: Kode peminjaman, nama lab, tujuan, tanggal, jam, status (badge), alasan penolakan

### 3.6 Laporan Kerusakan (`mahasiswa/laporan_kerusakan.php`)
Form pelaporan kerusakan alat saat praktikum.

- **Pilih Alat**: Dropdown daftar alat
- **Kronologi Kejadian**: Textarea
- **Gejala Kerusakan**: Textarea
- **Kirim Laporan**: Tombol submit
- **Tabel riwayat laporan**: Daftar laporan yang pernah dibuat (alat, tanggal, status)

---

## 4. Fitur Laboran (Admin)

### 4.1 Dashboard Laboran (`laboran/dashboard.php`)
Ringkasan kondisi laboratorium.

- **Stat cards**: Total alat, total bahan, peminjaman pending, approved, alat rusak, alert bahan kritis
- **Tabel pending**: 5 peminjaman terbaru yang perlu diverifikasi
- **Alert stok bahan**: Daftar bahan dengan stok di bawah minimum
- **Tombol Verifikasi**: Navigasi ke halaman verifikasi peminjaman

### 4.2 Verifikasi Peminjaman Alat (`laboran/verifikasi_peminjaman.php`)
Meninjau dan memproses pengajuan peminjaman alat.

- **Detail peminjaman**: Informasi peminjam, alat yang dipinjam, dokumen pendukung, tanggal
- **Approve**: Tombol menyetujui peminjaman (stok alat otomatis berkurang, event masuk kalender, notifikasi ke mahasiswa)
- **Reject**: Input alasan penolakan + tombol tolak (notifikasi ke mahasiswa)
- **Return**: Tombol mengembalikan status alat (stok bertambah)
- **Overdue**: Tombol menandai peminjaman terlambat
- **Filter status**: Dropdown filter status peminjaman
- **Tabel semua peminjaman**: Daftar lengkap dengan aksi verifikasi

### 4.3 Verifikasi Peminjaman Lab (`laboran/verifikasi_peminjaman_lab.php`)
Meninjau dan memproses peminjaman laboratorium.

- **Filter status**: Tombol-tombol filter (Semua, Pending, Approved, Rejected, Returned)
- **Approve**: Tombol setujui (status lab berubah jadi "Digunakan", event masuk kalender)
- **Reject**: Tombol tolak dengan alasan
- **Return**: Tombol kembalikan (status lab jadi "Tersedia")
- **Tabel peminjaman**: Kode, peminjam, lab, tujuan, tanggal, jam, status, aksi

### 4.4 Manajemen Inventaris (`laboran/manajemen_inventaris.php`)
CRUD penuh untuk data alat laboratorium.

- **Tambah Alat**: Modal form (kode alat, nama, merk, spesifikasi, lokasi, stok total, stok tersedia, foto, status)
- **Edit Alat**: Modal form mengubah data alat
- **Hapus Alat**: Konfirmasi hapus
- **Tabel inventaris**: Kode, nama, merk, stok, status, aksi edit/hapus

### 4.5 Manajemen Bahan Habis Pakai (`laboran/manajemen_bahan.php`)
Manajemen stok bahan habis pakai.

- **Tambah Bahan**: Modal form (nama, satuan, stok awal, stok minimum)
- **Edit Bahan**: Modal form mengubah data bahan
- **Hapus Bahan**: Konfirmasi hapus
- **Tambah Stok (Top-up)**: Tombol untuk menambah stok bahan tertentu
- **Alert merah**: Bahan dengan stok <= minimum otomatis ditandai

### 4.6 Manajemen Laboratorium (`laboran/manajemen_laboratorium.php`)
CRUD data ruang laboratorium.

- **Tambah Lab**: Modal form (kode lab, nama, lokasi, kapasitas, deskripsi, fasilitas, status)
- **Edit Lab**: Modal form mengubah data laboratorium
- **Hapus Lab**: Konfirmasi hapus
- **Tabel laboratorium**: Kode, nama, lokasi, kapasitas, status, aksi

### 4.7 Kalibrasi & Maintenance (`laboran/kalibrasi_maintenance.php`)
Penjadwalan dan pencatatan kalibrasi dan perawatan alat.

- **Tambah Jadwal**: Modal form (pilih alat, tipe: Kalibrasi/Maintenance/Servis, tanggal mulai/selesai, keterangan)
- **Tandai Selesai**: Tombol untuk menandai jadwal selesai
- **Filter status**: Dropdown filter (Semua, Terjadwal, Sedang Berjalan, Selesai)
- **Tabel jadwal**: Alat, tipe, tanggal, status, aksi

### 4.8 Logbook Kerusakan (`laboran/logbook_kerusakan.php`)
Mencatat dan menindaklanjuti laporan kerusakan alat.

- **Detail laporan**: Informasi pelapor, alat, kronologi, gejala
- **Catat Tindakan**: Modal form (tindakan yang dilakukan, pilih status alat baru: Tersedia/Dipinjam/Rusak/Kalibrasi)
- **Tabel laporan masuk**: Daftar laporan kerusakan dari mahasiswa
- **Logbook terbaru**: Riwayat tindakan yang sudah dicatat

---

## 5. Fitur Dosen

### 5.1 Dashboard Dosen (`dosen/dashboard.php`)
Ringkasan aktivitas laboratorium untuk dosen.

- **Stat cards**: Total Peminjaman, Disetujui, Alat Tersedia, Pending TA
- **Chart alat terpopuler**: Bar chart (Chart.js) menampilkan 5 alat paling sering dipinjam
- **Chart tren bulanan**: Line chart peminjaman per bulan
- **Export Data**: Tombol navigasi ke halaman export

### 5.2 Verifikasi Riset TA (`dosen/verifikasi_riset.php`)
Persetujuan digital untuk mahasiswa bimbingan yang ingin menggunakan lab.

- **Buat Pengajuan**: Tombol membuka form verifikasi baru (pilih mahasiswa, judul TA, upload proposal PDF)
- **Setujui**: Tombol approve pengajuan verifikasi
- **Tolak**: Tombol reject dengan catatan
- **Tabel pengajuan**: Mahasiswa, judul TA, status, aksi

### 5.3 Statistik Penggunaan Lab (`dosen/statistik.php`)
Visualisasi data penggunaan laboratorium.

- **Bar chart**: Tren peminjaman per bulan
- **Bar chart horizontal**: 5 alat terpopuler
- **Doughnut chart**: Status alat (Tersedia, Dipinjam, Rusak, Kalibrasi)
- **Tabel mahasiswa aktif**: Daftar mahasiswa dengan total peminjaman

### 5.4 Export Data Akreditasi (`dosen/export.php`)
Export data untuk keperluan akreditasi.

- **Export Inventaris Alat**: Tombol Export Excel dan Export PDF
- **Export Aktivitas Peminjaman**: Tombol Export Excel dan Export PDF
- **Export Laporan Lengkap**: Tombol Export Excel dan Export PDF (gabungan alat + peminjaman + bahan)

---

## 6. Autentikasi

### 6.1 Login (`auth/login.php`)
- **Input Username**: Text input
- **Input Password**: Password input
- **Masuk**: Tombol submit login

### 6.2 Registrasi Mahasiswa (`auth/register.php`)
- **Input Nama Lengkap**: Text input
- **Input NIM**: Text input (9 digit, validasi format Teknik Biomedis: kode tahun + 430 + nomor urut 001-150)
- **Input Password**: Password input
- **Konfirmasi Password**: Password input
- **Daftar**: Tombol submit registrasi

### 6.3 Logout (`auth/logout.php`)
- Tombol logout di sidebar (semua halaman) → menghancurkan session

---

## 7. Catatan Teknis

- **Database**: MySQL dengan PDO (parameterized query untuk keamanan SQL injection)
- **Session**: PHP native session untuk autentikasi
- **Password**: Di-hash menggunakan `password_hash()` dengan algoritma bcrypt (`PASSWORD_DEFAULT`)
- **File upload**: Disimpan di `uploads/` (foto alat, dokumen, e-library)
- **Library eksternal**: FullCalendar (jadwal), Chart.js (grafik statistik), Tailwind CSS (styling), Font Awesome (ikon)
