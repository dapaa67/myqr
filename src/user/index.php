<?php
$page_title = "User Dashboard";
require_once __DIR__ . '/../layouts/header.php';

$user_id = $_SESSION['user_id'];
$today = date("Y-m-d");

// Query 1: Cek status absensi hari ini untuk pesan di atas
$q_status = "SELECT waktu_masuk, waktu_keluar FROM absensi WHERE user_id = '$user_id' AND tanggal_absensi = '$today'";
$res_status = $conn->query($q_status);
$status_hari_ini = $res_status->fetch_assoc();

// Query 2: Ambil riwayat absensi terakhir (kolom sudah diperbaiki)
$history = $conn->query("SELECT tanggal_absensi, waktu_masuk, waktu_keluar FROM absensi WHERE user_id = '$user_id' ORDER BY tanggal_absensi DESC LIMIT 10");
?>

<div class="max-w-5xl mx-auto py-8 sm:px-6 lg:px-8">
    <?php
    $pesan_status = "Untuk mencatat kehadiran hari ini, silakan klik tombol di bawah.";
    if ($status_hari_ini && !$status_hari_ini['waktu_keluar']) {
        $pesan_status = "Anda sudah absen masuk pukul " . date("H:i:s", strtotime($status_hari_ini['waktu_masuk'])) . ". Silakan scan lagi untuk absen pulang.";
    } elseif ($status_hari_ini && $status_hari_ini['waktu_keluar']) {
        $pesan_status = "Anda sudah menyelesaikan absensi hari ini. Terima kasih.";
    }
    ?>
    <div class="bg-white p-8 rounded-xl shadow-lg text-center border border-gray-200">
        <h1 class="text-2xl font-bold mb-2 text-slate-800">Selamat Datang!</h1>
        <p class="text-slate-600 mb-6"><?php echo $pesan_status; ?></p>
        <?php if (!$status_hari_ini || !$status_hari_ini['waktu_keluar']): ?>
        <a href="scan.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg transition-transform duration-300 hover:scale-105">
            Scan QR Code Absensi
        </a>
        <?php endif; ?>
    </div>

    <div class="mt-8 bg-white p-6 rounded-xl shadow-lg border border-gray-200">
        <h2 class="text-xl font-bold mb-4 text-slate-800">Riwayat Absensi Terakhir</h2>
         <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Tanggal</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Jam Masuk</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Jam Pulang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if ($history->num_rows > 0): ?>
                        <?php while($row = $history->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 text-slate-700"><?php echo date("d F Y", strtotime($row['tanggal_absensi'])); ?></td>
                            <td class="py-3 px-4 text-slate-600 font-mono"><?php echo $row['waktu_masuk'] ? date("H:i:s", strtotime($row['waktu_masuk'])) : '-'; ?></td>
                            <td class="py-3 px-4 text-slate-600 font-mono"><?php echo $row['waktu_keluar'] ? date("H:i:s", strtotime($row['waktu_keluar'])) : '-'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="py-8 text-center text-gray-500">Belum ada riwayat absensi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>