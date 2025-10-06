<?php
require '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $token = trim($_POST['token']);
    $user_id = $_SESSION['user_id'];
    $today = date("Y-m-d");
    $currentTime = date("H:i:s");

    // Validasi token di database
    $stmt_token = $conn->prepare("SELECT id FROM qr_tokens WHERE token = ? AND berlaku_sampai > NOW()");
    $stmt_token->bind_param("s", $token);
    $stmt_token->execute();
    if ($stmt_token->get_result()->num_rows === 0) {
        $_SESSION['toast'] = ['message' => 'Token tidak valid atau sudah kedaluwarsa.', 'type' => 'error'];
        header('Location: index.php');
        exit;
    }
    $stmt_token->close();

    // Ambil jadwal kerja berdasarkan shift user (logika ini tetap sama)
    $stmt_jadwal = $conn->prepare("SELECT s.* FROM users u JOIN shifts s ON u.shift_id = s.id WHERE u.id = ?");
    $stmt_jadwal->bind_param("i", $user_id);
    $stmt_jadwal->execute();
    $jadwal_result = $stmt_jadwal->get_result();
    if ($jadwal_result->num_rows === 0) {
        $_SESSION['toast'] = ['message' => 'Anda belum memiliki jadwal shift.', 'type' => 'error'];
        header('Location: index.php');
        exit;
    }
    $jadwal = $jadwal_result->fetch_assoc();
    $batas_jam_masuk = $jadwal['batas_jam_masuk'];
    $jam_pulang = $jadwal['jam_pulang'];
    $stmt_jadwal->close();

    // Cek absensi hari ini (logika ini juga tetap sama)
    $stmt_absensi = $conn->prepare("SELECT id, waktu_masuk, waktu_keluar FROM absensi WHERE user_id = ? AND tanggal_absensi = ?");
    $stmt_absensi->bind_param("is", $user_id, $today);
    $stmt_absensi->execute();
    $data_absensi_hari_ini = $stmt_absensi->get_result()->fetch_assoc();
    $stmt_absensi->close();

    if (!$data_absensi_hari_ini) {
        // ABSEN MASUK
        $status_masuk = ($currentTime > $batas_jam_masuk) ? 'Terlambat' : 'Tepat Waktu';
        $stmt_insert = $conn->prepare("INSERT INTO absensi (user_id, tanggal_absensi, waktu_masuk, status_masuk) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("isss", $user_id, $today, $currentTime, $status_masuk);
        if ($stmt_insert->execute()) {
            $_SESSION['toast'] = ['message' => 'Absen MASUK berhasil dicatat!', 'type' => 'success'];
            $conn->query("DELETE FROM qr_tokens WHERE token = '$token'");
        }
        $stmt_insert->close();

    } elseif ($data_absensi_hari_ini['waktu_masuk'] && !$data_absensi_hari_ini['waktu_keluar']) {
        // ABSEN PULANG
        $status_keluar = ($currentTime < $jam_pulang) ? 'Pulang Cepat' : 'Selesai';
        $stmt_update = $conn->prepare("UPDATE absensi SET waktu_keluar = ?, status_keluar = ? WHERE id = ?");
        $stmt_update->bind_param("ssi", $currentTime, $status_keluar, $data_absensi_hari_ini['id']);
        if ($stmt_update->execute()) {
            $_SESSION['toast'] = ['message' => 'Absen PULANG berhasil dicatat!', 'type' => 'success'];
            $conn->query("DELETE FROM qr_tokens WHERE token = '$token'");
        }
        $stmt_update->close();
    } else {
        $_SESSION['toast'] = ['message' => 'Anda sudah menyelesaikan absensi hari ini.', 'type' => 'error'];
    }
}

$conn->close();
header('Location: index.php');
exit;