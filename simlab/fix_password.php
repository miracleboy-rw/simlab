<?php
// Fix Password SIM-Lab
// Jalankan: http://localhost/simlab/fix_password.php
// Akan reset semua password akun demo menjadi 'admin123'

$host = 'localhost';
$dbname = 'simlab';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $hash = password_hash('admin123', PASSWORD_DEFAULT);

    $st = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
    $users = ['laboran', 'dosen1', 'mahasiswa1', 'mahasiswa2'];

    foreach ($users as $u) {
        $st->execute([$hash, $u]);
    }

    echo "<html><head><title>Fix Password</title>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "</head><body><div class='container mt-5'>";
    echo "<div class='alert alert-success'>";
    echo "<h4>Password Berhasil Direset!</h4>";
    echo "<p>Semua akun demo sekarang menggunakan password: <code>admin123</code></p>";
    echo "<hr>";
    echo "<p><strong>laboran</strong> / admin123</p>";
    echo "<p><strong>dosen1</strong> / admin123</p>";
    echo "<p><strong>mahasiswa1</strong> / admin123</p>";
    echo "<p><strong>mahasiswa2</strong> / admin123</p>";
    echo "<a href='auth/login.php' class='btn btn-primary'>Login Sekarang</a>";
    echo "</div></div></body></html>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
