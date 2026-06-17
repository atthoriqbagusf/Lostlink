<?php
$pageTitle = 'Panel Admin';
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    setFlash('Akses ditolak. Halaman khusus admin.', 'danger');
    header('Location: ' . BASE_URL);
    exit();
}

$stats = getDashboardStats();
$users = getAllUsers();
$laporanHilang = getAllLaporanHilang();
$laporanTemuan = getAllLaporanTemuan();
$klaim = getAllKlaim();
$kategoriList = getAllKategori();
$logs = getAllLogAktivitas(50);

$activeTab = $_GET['tab'] ?? 'overview';
?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-20">
            <div class="bg-gradient-to-tr from-primary-900 to-primary-800 p-4 text-white">
                <h3 class="font-black text-base tracking-tight"><i class="fa-solid fa-user-shield"></i> Portal Admin</h3>
                <p class="text-[11px] text-sky-200 mt-0.5">Kendali Sistem LostLink</p>
            </div>
            <div class="flex flex-col">
                <a href="?tab=overview" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition border-l-4 <?php echo $activeTab === 'overview' ? 'border-primary-600 bg-slate-50' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-chart-line w-5 text-slate-400"></i> Ringkasan
                </a>
                <a href="?tab=user" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition border-l-4 <?php echo $activeTab === 'user' ? 'border-primary-600 bg-slate-50' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-users w-5 text-slate-400"></i> Pengguna
                </a>
                <a href="?tab=laporan" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition border-l-4 <?php echo $activeTab === 'laporan' ? 'border-primary-600 bg-slate-50' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-clipboard-check w-5 text-slate-400"></i> Verifikasi
                </a>
                <a href="?tab=klaim" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition border-l-4 <?php echo $activeTab === 'klaim' ? 'border-primary-600 bg-slate-50' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-hand-holding-hand w-5 text-slate-400"></i> Klaim
                </a>
                <a href="?tab=kategori" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition border-l-4 <?php echo $activeTab === 'kategori' ? 'border-primary-600 bg-slate-50' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-tags w-5 text-slate-400"></i> Kategori
                </a>
                <a href="?tab=log" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition border-l-4 <?php echo $activeTab === 'log' ? 'border-primary-600 bg-slate-50' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-receipt w-5 text-slate-400"></i> Log
                </a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-3 flex flex-col gap-6">

        <?php if ($activeTab === 'overview'): ?>
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 font-bold uppercase">Total Users</span>
                    <span class="block text-2xl font-black text-slate-800 mt-1"><?php echo $stats['total_users']; ?></span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 font-bold uppercase text-rose-500">Hilang</span>
                    <span class="block text-2xl font-black text-rose-500 mt-1"><?php echo $stats['total_hilang']; ?></span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 font-bold uppercase text-emerald-500">Temu</span>
                    <span class="block text-2xl font-black text-emerald-500 mt-1"><?php echo $stats['total_temuan']; ?></span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 font-bold uppercase text-indigo-500">Klaim</span>
                    <span class="block text-2xl font-black text-indigo-500 mt-1"><?php echo $stats['pending_klaim']; ?></span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 font-bold uppercase text-primary-500">Selesai</span>
                    <span class="block text-2xl font-black text-primary-500 mt-1"><?php echo $stats['selesai_hilang'] + $stats['selesai_temuan']; ?></span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                    <h4 class="font-bold text-slate-700 text-sm mb-4"><i class="fa-solid fa-chart-line text-blue-500 mr-2"></i>Tren Laporan</h4>
                    <div class="h-64">
                        <canvas id="chart-tren"></canvas>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                    <h4 class="font-bold text-slate-700 text-sm mb-4"><i class="fa-solid fa-chart-pie text-emerald-500 mr-2"></i>Sebaran Status</h4>
                    <div class="h-64 flex justify-center">
                        <canvas id="chart-status"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'user'): ?>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <h3 class="font-bold text-slate-800 text-lg mb-4">Pengaturan Akun</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold">
                            <th class="py-3">Nama</th>
                            <th class="py-3">Identitas</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Role</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($users as $u): 
                            $statusBadge = $u['status'] === 'aktif' ? 
                                '<span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-0.5 rounded font-bold uppercase">Aktif</span>' : 
                                '<span class="bg-slate-100 text-slate-500 text-xs px-2 py-0.5 rounded font-bold uppercase">Nonaktif</span>';
                            $roleBadge = $u['tipe_user'] === 'admin' ? 
                                '<span class="bg-indigo-100 text-indigo-800 text-[10px] px-1.5 py-0.5 rounded font-bold uppercase">Admin</span>' : 
                                ($u['tipe_user'] === 'mahasiswa' ? 
                                    '<span class="bg-blue-100 text-blue-800 text-[10px] px-1.5 py-0.5 rounded font-bold uppercase">Mahasiswa</span>' : 
                                    '<span class="bg-emerald-100 text-emerald-800 text-[10px] px-1.5 py-0.5 rounded font-bold uppercase">Umum</span>');
                        ?>
                        <tr>
                            <td class="py-3 font-semibold text-slate-800"><?php echo $u['nama']; ?></td>
                            <td class="py-3 text-slate-500 font-mono text-xs"><?php echo $u['identitas']; ?></td>
                            <td class="py-3 text-slate-500 text-xs"><?php echo $u['email']; ?></td>
                            <td class="py-3"><?php echo $roleBadge; ?></td>
                            <td class="py-3"><?php echo $statusBadge; ?></td>
                            <td class="py-3 text-right">
                                <a href="<?php echo BASE_URL; ?>api/admin-action.php?action=approve-user&id=<?php echo $u['id']; ?>" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded font-semibold mr-1">
                                    <?php echo $u['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                </a>
                                <button onclick="triggerConfirm('Hapus User?', 'Data user akan dihapus.', function() { window.location='<?php echo BASE_URL; ?>api/admin-action.php?action=delete-user&id=<?php echo $u['id']; ?>'; })" class="text-xs text-rose-600 hover:bg-rose-50 px-2 py-1 rounded font-semibold"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'laporan'): ?>
        <div class="flex flex-col gap-4">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h3 class="font-bold text-slate-800 text-lg mb-4">Laporan Kehilangan (Menunggu)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 font-semibold">
                                <th class="py-3">Barang</th>
                                <th class="py-3">Lokasi</th>
                                <th class="py-3">Pelapor</th>
                                <th class="py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php 
                            $pendingHilang = array_filter($laporanHilang, function($l) { return $l['status'] === 'Menunggu Verifikasi'; });
                            if (empty($pendingHilang)): ?>
                            <tr><td colspan="4" class="py-4 text-center text-slate-400">Tidak ada laporan menunggu</td></tr>
                            <?php else:
                            foreach ($pendingHilang as $l): ?>
                            <tr>
                                <td class="py-3 font-semibold text-slate-800"><?php echo $l['nama_barang']; ?></td>
                                <td class="py-3 text-slate-500"><?php echo $l['lokasi']; ?></td>
                                <td class="py-3 text-xs"><?php echo $l['pelapor_nama']; ?> <span class="text-slate-400">(<?php echo $l['pelapor_tipe']; ?>)</span></td>
                                <td class="py-3 text-right">
                                    <a href="<?php echo BASE_URL; ?>api/admin-action.php?action=approve-laporan-hilang&id=<?php echo $l['id']; ?>" class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-2 py-1 rounded font-bold mr-1"><i class="fa-solid fa-check"></i> Terima</a>
                                    <a href="<?php echo BASE_URL; ?>api/admin-action.php?action=reject-laporan-hilang&id=<?php echo $l['id']; ?>" class="text-xs bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white px-2 py-1 rounded font-bold"><i class="fa-solid fa-xmark"></i> Tolak</a>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h3 class="font-bold text-slate-800 text-lg mb-4">Laporan Temuan (Menunggu)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 font-semibold">
                                <th class="py-3">Barang</th>
                                <th class="py-3">Lokasi</th>
                                <th class="py-3">Penemu</th>
                                <th class="py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php 
                            $pendingTemuan = array_filter($laporanTemuan, function($l) { return $l['status'] === 'Menunggu Verifikasi'; });
                            if (empty($pendingTemuan)): ?>
                            <tr><td colspan="4" class="py-4 text-center text-slate-400">Tidak ada laporan menunggu</td></tr>
                            <?php else:
                            foreach ($pendingTemuan as $l): ?>
                            <tr>
                                <td class="py-3 font-semibold text-slate-800"><?php echo $l['nama_barang']; ?></td>
                                <td class="py-3 text-slate-500"><?php echo $l['lokasi']; ?></td>
                                <td class="py-3 text-xs"><?php echo $l['penemu_nama']; ?> <span class="text-slate-400">(<?php echo $l['penemu_tipe']; ?>)</span></td>
                                <td class="py-3 text-right">
                                    <a href="<?php echo BASE_URL; ?>api/admin-action.php?action=approve-laporan-temuan&id=<?php echo $l['id']; ?>" class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-2 py-1 rounded font-bold mr-1"><i class="fa-solid fa-check"></i> Terima</a>
                                    <a href="<?php echo BASE_URL; ?>api/admin-action.php?action=reject-laporan-temuan&id=<?php echo $l['id']; ?>" class="text-xs bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white px-2 py-1 rounded font-bold"><i class="fa-solid fa-xmark"></i> Tolak</a>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'klaim'): ?>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <h3 class="font-bold text-slate-800 text-lg mb-4">Daftar Pengajuan Klaim</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold">
                            <th class="py-3">Barang</th>
                            <th class="py-3">Pemohon</th>
                            <th class="py-3">Bukti</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($klaim)): ?>
                        <tr><td colspan="5" class="py-4 text-center text-slate-400">Belum ada klaim</td></tr>
                        <?php else:
                        foreach ($klaim as $k): 
                            $badgeClass = 'bg-amber-100 text-amber-800';
                            if ($k['status'] === 'Diterima') $badgeClass = 'bg-emerald-100 text-emerald-800';
                            if ($k['status'] === 'Ditolak') $badgeClass = 'bg-rose-100 text-rose-800';
                        ?>
                        <tr>
                            <td class="py-3 font-semibold text-slate-800"><?php echo $k['nama_barang']; ?></td>
                            <td class="py-3 text-slate-600"><?php echo $k['claimant_nama']; ?> <span class="text-[9px] px-1 bg-slate-100 text-slate-500 rounded"><?php echo $k['claimant_tipe']; ?></span></td>
                            <td class="py-3 text-xs text-slate-500 max-w-xs truncate"><?php echo $k['bukti']; ?></td>
                            <td class="py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo $badgeClass; ?>"><?php echo $k['status']; ?></span></td>
                            <td class="py-3 text-right">
                                <?php if ($k['status'] === 'Menunggu Verifikasi'): ?>
                                <a href="<?php echo BASE_URL; ?>api/admin-action.php?action=process-klaim&id=<?php echo $k['id']; ?>&status=Diterima" class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-2 py-1 rounded font-bold mr-1">Terima</a>
                                <a href="<?php echo BASE_URL; ?>api/admin-action.php?action=process-klaim&id=<?php echo $k['id']; ?>&status=Ditolak" class="text-xs bg-rose-600 hover:bg-rose-700 text-white px-2 py-1 rounded font-bold">Tolak</a>
                                <?php else: ?>
                                <span class="text-xs text-slate-400"><?php echo $k['status']; ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'kategori'): ?>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-lg">Kelola Kategori</h3>
                <button onclick="document.getElementById('form-kategori').classList.toggle('hidden')" class="bg-primary-600 hover:bg-primary-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">+ Tambah</button>
            </div>

            <form id="form-kategori" method="POST" action="<?php echo BASE_URL; ?>api/admin-action.php?action=add-kategori" class="hidden bg-slate-50 p-4 rounded-xl flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Kategori</label>
                    <input type="text" name="nama" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm" required>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Ikon</label>
                    <input type="text" name="ikon" value="fa-tag" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm" required>
                </div>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-bold">Simpan</button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach ($kategoriList as $kat): ?>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center">
                            <i class="fa-solid <?php echo $kat['ikon']; ?>"></i>
                        </span>
                        <span class="font-semibold text-slate-800 text-sm"><?php echo $kat['nama']; ?></span>
                    </div>
                    <button onclick="triggerConfirm('Hapus Kategori?', 'Kategori akan dihapus.', function() { window.location='<?php echo BASE_URL; ?>api/admin-action.php?action=delete-kategori&id=<?php echo $kat['id']; ?>'; })" class="text-xs text-rose-500 hover:text-white hover:bg-rose-500 bg-white p-1 rounded border border-slate-100"><i class="fa-solid fa-trash"></i></button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'log'): ?>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <h3 class="font-bold text-slate-800 text-lg mb-4"><i class="fa-solid fa-receipt mr-2 text-primary-500"></i>Log Aktivitas</h3>
            <div class="flex flex-col gap-3 max-h-96 overflow-y-auto custom-scrollbar">
                <?php foreach ($logs as $log): ?>
                <div class="flex items-start gap-3 p-2 hover:bg-slate-50 rounded-lg text-xs leading-normal">
                    <span class="text-[10px] text-slate-400 font-mono mt-0.5"><?php echo date('H:i', strtotime($log['waktu'])); ?></span>
                    <div class="flex-1 text-slate-600"><?php echo $log['pesan']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    <?php if ($activeTab === 'overview'): ?>
    const ctx1 = document.getElementById('chart-tren').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
                label: 'Hilang',
                data: [<?php echo $stats['total_hilang']; ?>, 15, 11, 18, 22, <?php echo $stats['total_hilang']; ?>],
                borderColor: '#ef4444',
                tension: 0.3
            }, {
                label: 'Temuan',
                data: [<?php echo $stats['total_temuan']; ?>, 12, 17, 14, 20, <?php echo $stats['total_temuan']; ?>],
                borderColor: '#10b981',
                tension: 0.3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    const ctx2 = document.getElementById('chart-status').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Hilang', 'Ditemukan', 'Diklaim', 'Selesai'],
            datasets: [{
                data: [
                    <?php echo count(array_filter($laporanHilang, function($l) { return $l['status'] === 'Hilang'; })); ?>,
                    <?php echo count(array_filter($laporanTemuan, function($l) { return $l['status'] === 'Ditemukan'; })); ?>,
                    <?php echo count(array_filter($laporanTemuan, function($l) { return $l['status'] === 'Diklaim'; })); ?>,
                    <?php echo $stats['selesai_hilang'] + $stats['selesai_temuan']; ?>
                ],
                backgroundColor: ['#ef4444', '#f59e0b', '#6366f1', '#10b981']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
    <?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>