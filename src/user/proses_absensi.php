<?php
// Mulai output buffering untuk menangkap semua output liar (warning/notice)
ob_start();

require '../../config.php';

// Inisialisasi array balasan
$response = ['success' => false, 'message' => 'Terjadi kesalahan tidak diketahui.'];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    $response['message'] = 'Akses ditolak.';
} else {
    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';
    $user_id = $_SESSION['user_id'];
    $today = date("Y-m-d");
    $currentTime = date("H:i:s");

    // 1. Validasi token
    $stmt_token = $conn->prepare("SELECT id FROM qr_tokens WHERE token = ? AND berlaku_sampai > NOW()");
    $stmt_token->bind_param("s", $token);
    $stmt_token->execute();
    $token_result = $stmt_token->get_result();

    if ($token_result->num_rows === 0) {
        $response['message'] = 'QR Code tidak valid atau sudah kedaluwarsa.';
    } else {
        // 2. Ambil jadwal kerja berdasarkan shift user
        $stmt_jadwal = $conn->prepare("SELECT s.* FROM users u JOIN shifts s ON u.shift_id = s.id WHERE u.id = ?");
        $stmt_jadwal->bind_param("i", $user_id);
        $stmt_jadwal->execute();
        $jadwal_result = $stmt_jadwal->get_result();

        if ($jadwal_result->num_rows === 0) {
            $response['message'] = 'Anda belum memiliki jadwal shift. Hubungi admin.';
        } else {
            $jadwal = $jadwal_result->fetch_assoc();
            $batas_jam_masuk = $jadwal['batas_jam_masuk'];
            $jam_pulang = $jadwal['jam_pulang'];

            // 3. Cek data absensi hari ini
            $stmt_absensi = $conn->prepare("SELECT id, waktu_masuk, waktu_keluar FROM absensi WHERE user_id = ? AND tanggal_absensi = ?");
            $stmt_absensi->bind_param("is", $user_id, $today);
            $stmt_absensi->execute();
            $data_absensi_hari_ini = $stmt_absensi->get_result()->fetch_assoc();

            if (!$data_absensi_hari_ini) {
                // KASUS 1: ABSEN MASUK
                $status_masuk = ($currentTime > $batas_jam_masuk) ? 'Terlambat' : 'Tepat Waktu';
                $stmt_insert = $conn->prepare("INSERT INTO absensi (user_id, tanggal_absensi, waktu_masuk, status_masuk) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("isss", $user_id, $today, $currentTime, $status_masuk);
                if ($stmt_insert->execute()) {
                    $_SESSION['toast'] = ['message' => 'Absen MASUK berhasil dicatat pukul ' . $currentTime, 'type' => 'success'];
                    $conn->query("DELETE FROM qr_tokens WHERE token = '$token'");
                    $response = ['success' => true];
                } else {
                    $response['message'] = 'Gagal menyimpan data absen masuk.';
                }
            } elseif ($data_absensi_hari_ini['waktu_masuk'] && !$data_absensi_hari_ini['waktu_keluar']) {
                // KASUS 2: ABSEN PULANG
                $status_keluar = ($currentTime < $jam_pulang) ? 'Pulang Cepat' : 'Selesai';
                $stmt_update = $conn->prepare("UPDATE absensi SET waktu_keluar = ?, status_keluar = ? WHERE id = ?");
                $stmt_update->bind_param("ssi", $currentTime, $status_keluar, $data_absensi_hari_ini['id']);
                if ($stmt_update->execute()) {
                    $_SESSION['toast'] = ['message' => 'Absen PULANG berhasil dicatat pukul ' . $currentTime, 'type' => 'success'];
                    $conn->query("DELETE FROM qr_tokens WHERE token = '$token'");
                    $response = ['success' => true];
                } else {
                    $response['message'] = 'Gagal menyimpan data absen pulang.';
                }
            } else {
                // KASUS 3: SUDAH LENGKAP
                $response['message'] = 'Anda sudah menyelesaikan absensi hari ini.';
            }
        }
    }
}

// Hapus semua output liar (warning/notice) yang mungkin sudah tertangkap
ob_clean();

// Kirim balasan JSON yang bersih
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>