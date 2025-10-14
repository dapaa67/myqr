<?php
$page_title = "Jadwal Saya";
require_once __DIR__ . '/../layouts/header.php';

$user_id = $_SESSION['user_id'];

// 1. Ambil shift pengguna
$stmt_shift = $conn->prepare(
    "SELECT s.nama_shift, s.jam_masuk, s.jam_pulang 
     FROM users u 
     JOIN shifts s ON u.shift_id = s.id 
     WHERE u.id = ?"
);
$stmt_shift->bind_param("i", $user_id);
$stmt_shift->execute();
$shift_result = $stmt_shift->get_result();
$shift_info = $shift_result->fetch_assoc();
$stmt_shift->close();

// 2. Logika pembuatan kalender
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day_of_month);
$day_of_week_of_first_day = date('w', $first_day_of_month); // 0 for Sunday, 6 for Saturday
$month_name = date('F Y', $first_day_of_month);

$days_of_week = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

// Navigasi bulan
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month == 13) {
    $next_month = 1;
    $next_year++;
}
?>

<div class="max-w-4xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
        
        <!-- Header Kalender dan Navigasi -->
        <div class="flex justify-between items-center mb-6">
            <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="p-2 rounded-full hover:bg-gray-100">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-slate-800 text-center"><?php echo $month_name; ?></h1>
            <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="p-2 rounded-full hover:bg-gray-100">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <!-- Tabel Kalender -->
        <div class="overflow-x-auto">
            <table class="min-w-full table-fixed border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <?php foreach ($days_of_week as $day): ?>
                            <th class="py-3 px-2 text-center text-sm font-semibold text-slate-500 uppercase border border-gray-200"><?php echo $day; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $current_day = 1;
                    echo "<tr>";
                    // Sel kosong sebelum hari pertama bulan ini
                    for ($i = 0; $i < $day_of_week_of_first_day; $i++) {
                        echo "<td class='h-28 border border-gray-200 bg-gray-50'></td>";
                    }

                    // Loop melalui semua hari dalam bulan
                    while ($current_day <= $days_in_month) {
                        $day_of_week = date('w', mktime(0, 0, 0, $month, $current_day, $year));
                        
                        // Jika hari pertama, mulai baris baru
                        if ($day_of_week == 0 && $current_day != 1) {
                            echo "</tr><tr>";
                        }

                        $cell_class = 'h-28 border border-gray-200 p-2 align-top';
                        $is_today = ($current_day == date('d') && $month == date('m') && $year == date('Y'));
                        $day_number_class = $is_today ? 'bg-blue-600 text-white rounded-full w-7 h-7 flex items-center justify-center font-bold' : 'font-medium text-slate-600';

                        echo "<td class='$cell_class'>";
                        echo "<div class='$day_number_class'>$current_day</div>";
                        
                        // Tampilkan info shift jika ada dan bukan akhir pekan (Sabtu/Minggu)
                        if ($shift_info && $day_of_week != 0 && $day_of_week != 6) {
                            echo "<div class='mt-1 text-xs p-1 rounded bg-blue-100 text-blue-800 text-center truncate'>";
                            echo htmlspecialchars($shift_info['nama_shift']);
                            echo "</div>";
                        } elseif ($day_of_week == 0 || $day_of_week == 6) {
                            echo "<div class='mt-1 text-xs p-1 rounded bg-gray-200 text-gray-600 text-center'>Libur</div>";
                        }

                        echo "</td>";

                        // Jika hari terakhir dalam seminggu, tutup baris
                        if ($day_of_week == 6) {
                            echo "</tr>";
                        }
                        $current_day++;
                    }

                    // Sel kosong setelah hari terakhir bulan ini
                    if ($day_of_week != 6) {
                        while ($day_of_week < 6) {
                            echo "<td class='h-28 border border-gray-200 bg-gray-50'></td>";
                            $day_of_week++;
                        }
                    }
                    echo "</tr>";
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
