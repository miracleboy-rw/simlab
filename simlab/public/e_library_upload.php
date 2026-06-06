<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/auth_check.php';

if (!isRole('laboran')) {
    alert('danger', 'Akses ditolak!');
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $tipe = $_POST['tipe'];

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . str_replace(' ', '_', $_FILES['file']['name']);
        $dest = '../uploads/ebook/' . $filename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
            query("INSERT INTO e_library (judul, tipe, file_path, uploaded_by) VALUES (?, ?, ?, ?)",
                   [$judul, $tipe, $filename, $_SESSION['user_id']]);
            alert('success', 'Dokumen berhasil diupload!');
        } else {
            alert('danger', 'Gagal mengupload file.');
        }
    } else {
        alert('danger', 'File tidak valid.');
    }
}

redirect('e_library.php');
