<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';
if (!isRole('dosen')) { alert('danger', 'Akses ditolak!'); redirect('../index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mahasiswa_id = $_POST['mahasiswa_id'];
    $judul = $_POST['judul_penelitian'];
    $dosen_id = $_SESSION['user_id'];

    $file_path = null;
    if (isset($_FILES['file_proposal']) && $_FILES['file_proposal']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['file_proposal']['name'], PATHINFO_EXTENSION);
        $filename = 'proposal_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['file_proposal']['tmp_name'], '../uploads/dokumen/' . $filename);
        $file_path = $filename;
    }

    query("INSERT INTO verifikasi_ta (mahasiswa_id, dosen_id, judul_penelitian, file_proposal, status) VALUES (?, ?, ?, ?, 'Pending')",
           [$mahasiswa_id, $dosen_id, $judul, $file_path]);

    alert('success', 'Pengajuan verifikasi berhasil dikirim!');
} else {
    alert('danger', 'Akses tidak valid.');
}

redirect('verifikasi_riset.php');
