<?php
require '../../config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ganti_password.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$password_lama = $_POST['password_lama'];
$password_baru = $_POST['password_baru'];
$konfirmasi_password = $_POST['konfirmasi_password'];

// 1. Validasi input dasar
if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
    $_SESSION['toast'] = ['message' => 'Semua field harus diisi.', 'type' => 'error'];
    header('Location: ganti_password.php');
    exit;
}

// 2. Cek apakah password baru dan konfirmasi sama
if ($password_baru !== $konfirmasi_password) {
    $_SESSION['toast'] = ['message' => 'Password baru dan konfirmasi tidak cocok.', 'type' => 'error'];
    header('Location: ganti_password.php');
    exit;
}

// 3. Cek panjang password baru
if (strlen($password_baru) < 8) {
    $_SESSION['toast'] = ['message' => 'Password baru minimal harus 8 karakter.', 'type' => 'error'];
    header('Location: ganti_password.php');
    exit;
}

// 4. Verifikasi password lama
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Seharusnya tidak terjadi jika user sudah login
    $_SESSION['toast'] = ['message' => 'User tidak ditemukan.', 'type' => 'error'];
    header('Location: ganti_password.php');
    exit;
}

$user = $result->fetch_assoc();
$hashed_password_lama = $user['password'];

if (!password_verify($password_lama, $hashed_password_lama)) {
    $_SESSION['toast'] = ['message' => 'Password saat ini yang Anda masukkan salah.', 'type' => 'error'];
    header('Location: ganti_password.php');
    exit;
}

// 5. Update password baru
$hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);

$update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update_stmt->bind_param("si", $hashed_password_baru, $user_id);

if ($update_stmt->execute()) {
    $_SESSION['toast'] = ['message' => 'Password berhasil diperbarui!', 'type' => 'success'];
    header('Location: profil.php');
} else {
    $_SESSION['toast'] = ['message' => 'Terjadi kesalahan saat memperbarui password.', 'type' => 'error'];
    header('Location: ganti_password.php');
}

$stmt->close();
$update_stmt->close();
$conn->close();
exit;
?>
