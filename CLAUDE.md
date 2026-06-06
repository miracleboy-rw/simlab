# Dokumen Spesifikasi Desain Sistem: SIM-Lab Biomedis

Dokumen ini menjelaskan spesifikasi fitur, alur kerja (workflow), dan rancangan antarmuka dari **Sistem Informasi Manajemen Laboratorium Biomedis (SIM-Lab)**.

---

## 1. Arsitektur Fitur Berdasarkan Hak Akses

### A. Fitur Umum (Semua Pengguna)
*   **Katalog Alat & Bahan Digital:** Menampilkan foto, nama alat, merk, spesifikasi teknis, lokasi penyimpanan, dan status ketersediaan secara *real-time*.
*   **Kalender Jadwal Lab:** Kalender interaktif untuk memantau jadwal praktikum kelas, riset mandiri, dan jadwal perawatan alat.
*   **E-Library K3 & Modul:** Portal unduhan file PDF Modul Praktikum dan Panduan Pengoperasian Alat Medis.

### B. Dashboard Mahasiswa
*   **Form Peminjaman Alat:** Antarmuka untuk memilih alat dari katalog, menentukan tanggal pinjam/kembali, dan menuliskan tujuan penggunaan.
*   **Portal Upload PDF:** Fitur mengunggah dokumen pendukung (Surat Izin Lab atau Proposal Penelitian).
*   **Tracking Status Pinjaman:** Halaman riwayat untuk memantau apakah status pengajuan berada dalam kondisi *Pending, Approved, Rejected,* atau *Overdue*.
*   **Form Laporan Kerusakan Insidental:** Form pelaporan cepat jika ditemukan alat *error* saat praktikum berjalan (input: kronologi dan gejala kerusakan).

### C. Dashboard Laboran (Admin Utama)
*   **Verifikasi & Approval Peminjaman:** Modul untuk meninjau, menyetujui, atau menolak pengajuan pinjaman mahasiswa beserta kolom alasan penolakan.
*   **Manajemen Inventaris (CRUD):** Fitur penuh untuk menambah, mengubah data spesifikasi, atau menghapus aset alat laboratorium.
*   **Manajemen Bahan Habis Pakai:** Pencatatan stok bahan habis pakai (seperti elektroda, gel EKG, cairan kimia). Sistem wajib memberikan **Alert Warna Merah** jika stok berada di bawah batas minimum.
*   **Modul Kalibrasi & Maintenance:** Kalender kerja dan pencatatan riwayat servis serta jadwal kalibrasi periodik alat-alat medis.
*   **Logbook Tindakan Kerusakan:** Log untuk mengubah status operasional alat berdasarkan laporan kerusakan dari mahasiswa.

### D. Dashboard Dosen
*   **Verifikasi Riset TA:** Fitur bagi Dosen Pembimbing untuk memberikan persetujuan digital terhadap mahasiswa bimbingannya yang ingin menggunakan lab.
*   **Statistik Penggunaan Lab (Charts):** Visualisasi grafis mengenai intensitas penggunaan laboratorium, alat yang paling sering dipinjam, dan total mahasiswa aktif per bulan.
*   **Modul Export Akreditasi:** Fitur konversi instan seluruh data inventaris dan aktivitas laboratorium menjadi dokumen cetak siap pakai berformat **Excel (.xlsx)** atau **PDF**.

---

## 2. Alur Kerja Sistem (Workflow)

### Alur Peminjaman Alat oleh Mahasiswa:
1.  **Mahasiswa** mencari alat di Katalog -> Mengisi Form Pinjam -> Mengunggah Dokumen PDF -> *Submit*.
2.  Status transaksi menjadi `Pending`. Stok alat di katalog belum berkurang.
3.  **Laboran** menerima notifikasi pengajuan -> Memeriksa kecocokan data dan ketersediaan fisik alat.
4.  Jika Laboran klik **`Approve`**: Status berubah menjadi `Approved`, stok alat di katalog otomatis berkurang 1, dan jadwal peminjaman tercatat di Kalender Lab.
5.  Jika Laboran klik **`Reject`**: Status berubah menjadi `Rejected`, mahasiswa menerima alasan penolakan, stok alat tidak berubah.
6.  Saat batas waktu habis dan alat dikembalikan, Laboran mengubah status menjadi `Returned`, stok alat bertambah kembali.

---

## 3. Skema Validasi Pengujian (Metodologi TA)

Untuk pemenuhan syarat akademis Tugas Akhir, pengujian sistem dirancang tanpa melibatkan instansi luar:
1.  **Black-Box Testing:** Pengujian fungsi seluruh tombol, pembatasan hak akses *role*, dan keakuratan pengurangan stok database.
2.  **User Acceptance Testing (UAT):** Pengujian aspek kegunaan menggunakan metode **System Usability Scale (SUS)** dengan membagikan kuesioner kepada responden internal kampus:
    *   10 Mahasiswa Teknik Biomedis (Aktor Mahasiswa)
    *   1-2 Laboran Jurusan (Aktor Laboran)
    *   2 Dosen Teknik Biomedis (Aktor Dosen)