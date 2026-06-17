<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// ====== CEK LOGIN ======
if (!isLoggedIn()) {
    setFlash('Silakan masuk terlebih dahulu.', 'info');
    header('Location: ' . BASE_URL);
    exit();
}

// ====== HANDLE FORM SUBMISSION DULU ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $result = updateUserProfile($_SESSION['user_id'], sanitize($_POST['nama']), sanitize($_POST['email']), sanitize($_POST['phone']));
    setFlash($result['message'], $result['success'] ? 'success' : 'danger');
    header('Location: ' . BASE_URL . 'pages/dashboard.php?tab=pengaturan');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    if ($_POST['password'] !== $_POST['password_confirm']) {
        setFlash('Konfirmasi password tidak cocok!', 'danger');
    } elseif (strlen($_POST['password']) < 8) {
        setFlash('Password minimal 8 karakter!', 'danger');
    } else {
        $result = updateUserPassword($_SESSION['user_id'], $_POST['password']);
        setFlash($result['message'], $result['success'] ? 'success' : 'danger');
    }
    header('Location: ' . BASE_URL . 'pages/dashboard.php?tab=pengaturan');
    exit();
}

$pageTitle = 'Dashboard';

$user = getUserById($_SESSION['user_id']);
$stats = getUserDashboardStats($_SESSION['user_id']);
$myHilang = getLaporanHilangByUser($_SESSION['user_id']);
$myTemuan = getLaporanTemuanByUser($_SESSION['user_id']);
$myKlaim = getKlaimByUser($_SESSION['user_id']);

$activeTab = $_GET['tab'] ?? 'dashboard';

require_once '../includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- Sidebar -->
    <div class="lg:col-span-1 flex flex-col gap-3">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 text-center relative overflow-hidden">
            <span class="absolute top-2 right-2 text-[9px] font-extrabold uppercase px-2 py-0.5 rounded <?php echo $user['tipe_user'] === 'mahasiswa' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800'; ?>">
                <?php echo $user['tipe_user'] === 'mahasiswa' ? 'Akademika' : 'Umum'; ?>
            </span>
            <div class="relative w-20 h-20 mx-auto mb-3 mt-2">
                <img src="https://placehold.co/150x150/2563eb/ffffff?text=<?php echo substr($user['nama'], 0, 1); ?>" class="w-20 h-20 rounded-full border-2 border-primary-500 object-cover">
            </div>
            <h3 class="font-bold text-slate-800 text-base"><?php echo $user['nama']; ?></h3>
            <p class="text-xs text-slate-400 font-medium">
                <?php echo $user['tipe_user'] === 'mahasiswa' ? 'NIM: ' : 'NIK: '; ?>
                <?php echo $user['tipe_user'] === 'mahasiswa' ? $user['identitas'] : substr($user['identitas'], 0, 6) . '******'; ?>
            </p>
            <div class="mt-4 pt-4 border-t border-slate-100 text-left flex flex-col gap-2 text-xs text-slate-600">
                <div class="truncate"><i class="fa-solid fa-envelope mr-1 text-slate-400"></i> <?php echo $user['email']; ?></div>
                <div><i class="fa-solid fa-phone mr-1 text-slate-400"></i> <?php echo $user['phone']; ?></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="flex flex-col">
                <a href="?tab=dashboard" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition border-l-4 <?php echo $activeTab === 'dashboard' ? 'border-primary-600 bg-slate-50' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-chart-pie w-5 text-slate-400"></i> Dashboard
                </a>
                <a href="?tab=laporan" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition border-l-4 <?php echo $activeTab === 'laporan' ? 'border-primary-600 bg-slate-50' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-clipboard-list w-5 text-slate-400"></i> Kelola Laporan
                </a>
                <a href="?tab=klaim" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition border-l-4 <?php echo $activeTab === 'klaim' ? 'border-primary-600 bg-slate-50' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-receipt w-5 text-slate-400"></i> Klaim Saya
                </a>
                <a href="?tab=pengaturan" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition border-l-4 <?php echo $activeTab === 'pengaturan' ? 'border-primary-600 bg-slate-50' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-user-gear w-5 text-slate-400"></i> Edit Profil
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="lg:col-span-3">

        <?php if ($activeTab === 'dashboard'): ?>
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 font-bold uppercase">Laporan Dibuat</span>
                    <span class="block text-2xl font-black text-slate-800 mt-1"><?php echo count($myHilang) + count($myTemuan); ?></span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 font-bold uppercase text-rose-500">Hilang</span>
                    <span class="block text-2xl font-black text-rose-500 mt-1"><?php echo $stats['active_hilang']; ?></span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 font-bold uppercase text-emerald-500">Temu</span>
                    <span class="block text-2xl font-black text-emerald-500 mt-1"><?php echo $stats['active_temuan']; ?></span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 font-bold uppercase text-primary-500">Selesai</span>
                    <span class="block text-2xl font-black text-primary-500 mt-1"><?php echo $stats['total_hilang'] + $stats['total_temuan'] - $stats['active_hilang'] - $stats['active_temuan']; ?></span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 font-bold uppercase text-indigo-500">Klaim</span>
                    <span class="block text-2xl font-black text-indigo-500 mt-1"><?php echo $stats['total_klaim']; ?></span>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-slate-800 text-lg">Ringkasan Aktivitas</h3>
                    <a href="<?php echo BASE_URL; ?>pages/form-laporan.php?tipe=hilang" class="text-xs bg-primary-600 hover:bg-primary-700 text-white px-3 py-1.5 rounded-lg font-semibold transition">+ Tambah Laporan</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 font-semibold">
                                <th class="py-3">Nama Barang</th>
                                <th class="py-3">Tipe</th>
                                <th class="py-3">Tanggal</th>
                                <th class="py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php 
                            $allReports = array_merge(
                                array_map(function($r) { $r['tipe'] = 'hilang'; return $r; }, $myHilang),
                                array_map(function($r) { $r['tipe'] = 'temuan'; return $r; }, $myTemuan)
                            );
                            usort($allReports, function($a, $b) { return $b['id'] - $a['id']; });
                            $recent = array_slice($allReports, 0, 5);

                            if (empty($recent)): ?>
                            <tr><td colspan="4" class="py-4 text-center text-slate-400">Belum ada aktivitas</td></tr>
                            <?php else: 
                            foreach ($recent as $r): 
                                $tipeClass = $r['tipe'] === 'hilang' ? 'text-rose-500' : 'text-amber-500';
                                $statusClass = $r['status'] === 'Selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800';
                            ?>
                            <tr>
                                <td class="py-3 font-semibold text-slate-700"><?php echo sanitize($r['nama_barang']); ?></td>
                                <td class="py-3 uppercase text-xs font-bold <?php echo $tipeClass; ?>"><?php echo $r['tipe']; ?></td>
                                <td class="py-3 text-slate-500"><?php echo formatDate($r['tanggal']); ?></td>
                                <td class="py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo $statusClass; ?>"><?php echo $r['status']; ?></span></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'laporan'): ?>
        <div class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 bg-white p-4 rounded-2xl border border-slate-100">
                <h3 class="font-bold text-slate-800 text-lg">Daftar Semua Laporan Anda</h3>
                <a href="<?php echo BASE_URL; ?>pages/form-laporan.php?tipe=hilang" class="bg-primary-600 hover:bg-primary-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">+ Tambah Baru</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php 
                $allMyReports = array_merge(
                    array_map(function($r) { $r['tipe'] = 'hilang'; return $r; }, $myHilang),
                    array_map(function($r) { $r['tipe'] = 'temuan'; return $r; }, $myTemuan)
                );

                if (empty($allMyReports)): ?>
                <div class="col-span-2 text-center py-12 bg-white rounded-2xl border border-slate-100">
                    <p class="text-slate-400 text-sm">Belum ada laporan. Buat laporan baru sekarang!</p>
                </div>
                <?php else:
                foreach ($allMyReports as $r):
                    $statusClass = $r['status'] === 'Selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800';
                    // ✅ PERBAIKAN: Tambah BASE_URL untuk foto
                    $foto = !empty($r['foto']) ? BASE_URL . $r['foto'] : 'https://placehold.co/600x400/2563eb/ffffff?text='.urlencode($r['nama_barang']);
                ?>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex gap-4">
                    <img src="<?php echo $foto; ?>" class="w-20 h-20 rounded-xl object-cover border border-slate-100" onerror="this.src='https://placehold.co/600x400?text=Barang'">
                    <div class="flex-1 flex flex-col justify-between">
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm truncate"><?php echo sanitize($r['nama_barang']); ?></h4>
                            <span class="text-[10px] text-slate-400 font-semibold uppercase"><?php echo $r['lokasi']; ?> | <?php echo formatDate($r['tanggal']); ?></span>
                            <div class="mt-1">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold <?php echo $statusClass; ?>"><?php echo $r['status']; ?></span>
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end mt-2">
                            <a href="<?php echo BASE_URL; ?>pages/form-laporan.php?tipe=<?php echo $r['tipe']; ?>&edit=<?php echo $r['id']; ?>" class="text-xs font-bold text-slate-600 hover:text-primary-600 px-2 py-1 bg-slate-100 rounded-lg"><i class="fa-solid fa-pen"></i></a>
                            <button onclick="triggerConfirm('Hapus?', 'Data akan dihapus permanen.', function() { window.location='<?php echo BASE_URL; ?>api/delete-laporan.php?tipe=<?php echo $r['tipe']; ?>&id=<?php echo $r['id']; ?>'; })" class="text-xs font-bold text-rose-600 hover:text-white hover:bg-rose-500 px-2 py-1 bg-slate-100 rounded-lg"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'klaim'): ?>
        <div class="bg-white p-5 rounded-2xl border border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg mb-4">Daftar Klaim yang Anda Ajukan</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold">
                            <th class="py-3">Barang</th>
                            <th class="py-3">Bukti</th>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($myKlaim)): ?>
                        <tr><td colspan="5" class="py-4 text-center text-slate-400">Belum ada pengajuan klaim</td></tr>
                        <?php else:
                        foreach ($myKlaim as $k):
                            $badgeClass = 'bg-amber-100 text-amber-800';
                            if ($k['status'] === 'Diterima') $badgeClass = 'bg-emerald-100 text-emerald-800';
                            if ($k['status'] === 'Ditolak') $badgeClass = 'bg-rose-100 text-rose-800';
                        ?>
                        <tr>
                            <td class="py-3 font-semibold text-slate-700"><?php echo sanitize($k['nama_barang']); ?></td>
                            <td class="py-3 text-xs text-slate-500 max-w-xs truncate"><?php echo sanitize($k['bukti']); ?></td>
                            <td class="py-3 text-slate-500"><?php echo formatDate($k['tanggal_klaim']); ?></td>
                            <td class="py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo $badgeClass; ?>"><?php echo $k['status']; ?></span></td>
                            <td class="py-3 text-right">
                                <?php if ($k['status'] === 'Menunggu Verifikasi'): ?>
                                <button onclick="triggerConfirm('Batalkan?', 'Klaim akan dibatalkan.', function() { window.location='<?php echo BASE_URL; ?>api/cancel-klaim.php?id=<?php echo $k['id']; ?>'; })" class="text-xs bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white px-2.5 py-1 rounded-lg font-bold transition">Batalkan</button>
                                <?php else: ?>
                                <span class="text-xs text-slate-400">Telah diproses</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'pengaturan'): ?>
        <div class="flex flex-col gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h3 class="font-bold text-slate-800 text-lg mb-4">Informasi Profil</h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" value="<?php echo $user['nama']; ?>" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1"><?php echo $user['tipe_user'] === 'mahasiswa' ? 'NIM' : 'NIK'; ?></label>
                        <input type="text" value="<?php echo $user['identitas']; ?>" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-400 text-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Email</label>
                        <input type="email" name="email" value="<?php echo $user['email']; ?>" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">No. WhatsApp</label>
                        <input type="text" name="phone" value="<?php echo $user['phone']; ?>" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" required>
                    </div>
                    <div class="md:col-span-2 text-right">
                        <button type="submit" name="update_profile" class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2 rounded-xl text-sm font-bold transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h3 class="font-bold text-slate-800 text-lg mb-4">Ganti Password</h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Password Baru (min. 8 karakter)</label>
                        <input type="password" name="password" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" required minlength="8">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirm" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" required minlength="8">
                    </div>
                    <div class="md:col-span-2 text-right">
                        <button type="submit" name="update_password" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-sm font-bold transition">Ganti Password</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>