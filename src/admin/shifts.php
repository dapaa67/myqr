<?php
$page_title = "Manajemen Shift";
require_once __DIR__ . '/../layouts/header.php';

$result = $conn->query("SELECT * FROM shifts ORDER BY jam_masuk");
?>

<div class="max-w-4xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Daftar Shift</h1>
            <a href="tambah_shift.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                + Tambah Shift Baru
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama Shift</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Jam Masuk</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Batas Masuk</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Jam Pulang</th>
                        </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($row['nama_shift']); ?></td>
                                <td class="py-3 px-4 font-mono"><?php echo $row['jam_masuk']; ?></td>
                                <td class="py-3 px-4 font-mono"><?php echo $row['batas_jam_masuk']; ?></td>
                                <td class="py-3 px-4 font-mono"><?php echo $row['jam_pulang']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-4">Belum ada data shift.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>