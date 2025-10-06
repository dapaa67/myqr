<?php
require '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'tambah') {
        $nama = $_POST['nama_shift'];
        $masuk = $_POST['jam_masuk'];
        $batas = $_POST['batas_jam_masuk'];
        $pulang = $_POST['jam_pulang'];

        $stmt = $conn->prepare("INSERT INTO shifts (nama_shift, jam_masuk, batas_jam_masuk, jam_pulang) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $masuk, $batas, $pulang);

        if ($stmt->execute()) {
            $_SESSION['toast'] = ['message' => 'Shift baru berhasil ditambahkan.', 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['message' => 'Gagal menambahkan shift.', 'type' => 'error'];
        }
        $stmt->close();
    }
    // Logika untuk 'edit' dan 'hapus' bisa ditambahkan di sini
}
header('Location: shifts.php');
exit;
?>