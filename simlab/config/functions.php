<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function formatTanggal($date) {
    return date('d/m/Y', strtotime($date));
}

function formatTanggalIndo($date) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $t = strtotime($date);
    $d = date('j', $t);
    $m = (int)date('n', $t);
    $y = date('Y', $t);
    return "$d $bulan[$m] $y";
}

function alert($type, $message) {
    $_SESSION['alert_type'] = $type;
    $_SESSION['alert_message'] = $message;
}

function showAlert() {
    if (isset($_SESSION['alert_type']) && isset($_SESSION['alert_message'])) {
        $type = $_SESSION['alert_type'];
        $msg = $_SESSION['alert_message'];
        unset($_SESSION['alert_type'], $_SESSION['alert_message']);
        $icons = ['success' => 'fa-check-circle', 'danger' => 'fa-exclamation-circle', 'warning' => 'fa-exclamation-triangle', 'info' => 'fa-info-circle'];
        $icon = $icons[$type] ?? 'fa-info-circle';
        return "<div class=\"glass-alert alert-{$type}\" role=\"alert\">
                    <i class=\"fas {$icon} mr-2\"></i> {$msg}
                </div>";
    }
    return '';
}

function statusBadge($status) {
    $map = [
        'Pending' => 'warning',
        'Approved' => 'success',
        'Rejected' => 'danger',
        'Returned' => 'primary',
        'Overdue' => 'dark',
        'Tersedia' => 'success',
        'Dipinjam' => 'warning',
        'Rusak' => 'danger',
        'Kalibrasi' => 'info',
        'Tidak Tersedia' => 'secondary',
        'Dilaporkan' => 'danger',
        'Ditangani' => 'info',
        'Selesai' => 'success',
        'Disetujui' => 'success',
        'Ditolak' => 'danger',
        'Terjadwal' => 'info',
        'Sedang Berjalan' => 'warning',
    ];
    $color = $map[$status] ?? 'secondary';
    return "<span class=\"glass-badge badge-{$color}\">{$status}</span>";
}

function getCount($table, $condition = '1=1', $params = []) {
    $sql = "SELECT COUNT(*) as total FROM `$table` WHERE $condition";
    $row = fetchOne($sql, $params);
    return $row['total'] ?? 0;
}
