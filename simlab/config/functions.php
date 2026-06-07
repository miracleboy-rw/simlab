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
        $colors = [
            'success' => ['bg' => '#DCFCE7', 'color' => '#166534', 'border' => '#BBF7D0'],
            'danger' => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'border' => '#FECACA'],
            'warning' => ['bg' => '#FEF3C7', 'color' => '#92400E', 'border' => '#FDE68A'],
            'info' => ['bg' => '#DBEAFE', 'color' => '#1E40AF', 'border' => '#93C5FD'],
        ];
        $c = $colors[$type] ?? $colors['info'];
        return "<div role=\"alert\" style=\"padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:16px;background:{$c['bg']};color:{$c['color']};border:1px solid {$c['border']}\">{$msg}</div>";
    }
    return '';
}

function statusBadge($status) {
    $map = [
        'Pending' => ['color' => '#D97706', 'bg' => '#FEF3C7'],
        'Approved' => ['color' => '#16A34A', 'bg' => '#DCFCE7'],
        'Rejected' => ['color' => '#DC2626', 'bg' => '#FEE2E2'],
        'Returned' => ['color' => '#2563EB', 'bg' => '#DBEAFE'],
        'Overdue' => ['color' => '#DC2626', 'bg' => '#FEE2E2'],
        'Tersedia' => ['color' => '#16A34A', 'bg' => '#DCFCE7'],
        'Dipinjam' => ['color' => '#D97706', 'bg' => '#FEF3C7'],
        'Rusak' => ['color' => '#DC2626', 'bg' => '#FEE2E2'],
        'Kalibrasi' => ['color' => '#2563EB', 'bg' => '#DBEAFE'],
        'Tidak Tersedia' => ['color' => '#6B7280', 'bg' => '#E5E7EB'],
        'Dilaporkan' => ['color' => '#DC2626', 'bg' => '#FEE2E2'],
        'Ditangani' => ['color' => '#2563EB', 'bg' => '#DBEAFE'],
        'Selesai' => ['color' => '#16A34A', 'bg' => '#DCFCE7'],
        'Disetujui' => ['color' => '#16A34A', 'bg' => '#DCFCE7'],
        'Ditolak' => ['color' => '#DC2626', 'bg' => '#FEE2E2'],
        'Terjadwal' => ['color' => '#2563EB', 'bg' => '#DBEAFE'],
        'Sedang Berjalan' => ['color' => '#D97706', 'bg' => '#FEF3C7'],
    ];
    $c = $map[$status] ?? ['color' => '#6B7280', 'bg' => '#E5E7EB'];
    return "<span class=\"badge\" style=\"background:{$c['bg']};color:{$c['color']}\">{$status}</span>";
}

function getCount($table, $condition = '1=1', $params = []) {
    $sql = "SELECT COUNT(*) as total FROM `$table` WHERE $condition";
    $row = fetchOne($sql, $params);
    return $row['total'] ?? 0;
}
