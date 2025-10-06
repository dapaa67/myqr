<?php
$page_title = "Rekap Absensi";
require_once __DIR__ . '/../layouts/header.php';

// Tentukan bulan dan tahun yang akan ditampilkan
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$nama_bulan = DateTime::createFromFormat('!m', $bulan)->format('F');

// Query untuk mengambil rekap
$sql_rekap = "SELECT u.nama_lengkap, a.tanggal_absensi, a.waktu_masuk, a.status_masuk, a.waktu_keluar, a.status_keluar
              FROM absensi a
              JOIN users u ON a.user_id = u.id
              WHERE MONTH(a.tanggal_absensi) = ? AND YEAR(a.tanggal_absensi) = ?
              ORDER BY a.tanggal_absensi, u.nama_lengkap";

$stmt = $conn->prepare($sql_rekap);
$stmt->bind_param("ss", $bulan, $tahun);
$stmt->execute();
$result_rekap = $stmt->get_result();
?>

<div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
        <h1 class="text-2xl font-bold mb-4">Rekap Absensi - <?php echo "$nama_bulan $tahun"; ?></h1>

        <form method="GET" class="mb-6 flex items-center space-x-4">
            <div>
                <label for="bulan" class="text-sm font-medium">Bulan:</label>
                <select name="bulan" id="bulan" class="border-gray-300 rounded-md">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" <?php if ($i == $bulan) echo 'selected'; ?>>
                            <?php echo DateTime::createFromFormat('!m', $i)->format('F'); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label for="tahun" class="text-sm font-medium">Tahun:</label>
                <input type="number" name="tahun" id="tahun" value="<?php echo $tahun; ?>" class="border-gray-300 rounded-md w-24">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">Tampilkan</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama Lengkap</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Masuk</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Pulang</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result_rekap->num_rows > 0): ?>
                        <?php while ($row = $result_rekap->fetch_assoc()): ?>
                            <tr>
                                <td class="py-3 px-4"><?php echo date("d-m-Y", strtotime($row['tanggal_absensi'])); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td class="py-3 px-4 font-mono"><?php echo $row['waktu_masuk']; ?></td>
                                <td class="py-3 px-4"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['status_masuk'] == 'Tepat Waktu' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>"><?php echo $row['status_masuk']; ?></span></td>
                                <td class="py-3 px-4 font-mono"><?php echo $row['waktu_keluar'] ?? '-'; ?></td>
                                <td class="py-3 px-4">
                                <?php if ($row['status_keluar']): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['status_keluar'] == 'Selesai' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'; ?>"><?php echo $row['status_keluar']; ?></span>
                                <?php else: echo '-'; endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4">Tidak ada data absensi untuk periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>