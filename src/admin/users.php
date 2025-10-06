<?php
$page_title = "Manajemen User";
require_once __DIR__ . '/../layouts/header.php';

// Ambil semua user beserta nama shiftnya
$sql = "SELECT u.id, u.nama_lengkap, u.username, s.nama_shift 
        FROM users u 
        LEFT JOIN shifts s ON u.shift_id = s.id
        WHERE u.role = 'user'
        ORDER BY u.nama_lengkap";
$result_users = $conn->query($sql);
?>
<div class="max-w-4xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">Daftar User</h1>
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama Lengkap</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Shift Saat Ini</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($user = $result_users->fetch_assoc()): ?>
                <tr>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($user['username']); ?></td>
                    <td class="py-3 px-4"><?php echo $user['nama_shift'] ?? '<span class="text-red-500">Belum diatur</span>'; ?></td>
                    <td class="py-3 px-4"><a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:underline">Atur Shift</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>