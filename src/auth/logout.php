<?php
require '../../config.php';
$_SESSION['toast'] = ['message' => 'Anda telah berhasil logout.', 'type' => 'success'];
session_destroy();
// Kita butuh session_start lagi untuk membawa pesan toast ke halaman login
session_start();
$_SESSION['toast'] = ['message' => 'Anda telah berhasil logout.', 'type' => 'success'];
header('Location: login.php');
exit;
?>