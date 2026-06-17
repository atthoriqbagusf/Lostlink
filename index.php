<?php
$pageTitle = 'Beranda';
require_once 'includes/header.php';
require_once 'includes/functions.php';

$kategoriList = getAllKategori();
$lokasiList = ["Perpustakaan", "Lab Komputer", "Ruang Kuliah", "Kantin", "Parkiran", "Masjid Kampus", "Lainnya"];

// Get stats
$stats = getDashboardStats();
$reports = getRecentReports(12);
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-primary-900 via-primary-800 to-blue-700 text-white rounded-3xl p-6 sm:p-10 mb-8 shadow-xl relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
    <div class="relative z-10 grid md:grid-cols-2 gap-8 items-center">
        <div>
            <span class="bg-sky-500/20 text-sky-200 text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wider">Universitas LostLink Hub</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold mt-3 tracking-tight leading-tight">Jendela Kehilangan & Temuan Barang Area Kampus</h1>
            <p class="text-sky-100 mt-2 text-sm sm:text-base">Kehilangan dompet, kunci, atau berkas di area kampus? Terbuka untuk mahasiswa, staf, kurir paket, dan seluruh masyarakat umum!</p>
            <div class="flex gap-3 mt-6">
                <?php if ($isLogged): ?>
                <a href="<?php echo BASE_URL; ?>pages/form-laporan.php?tipe=hilang" class="bg-white hover:bg-sky-50 text-primary-900 font-bold px-5 py-2.5 rounded-xl shadow-lg transition flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-magnifying-glass text-rose-500"></i> Laporkan Kehilangan
                </a>
                <a href="<?php echo BASE_URL; ?>pages/form-laporan.php?tipe=temuan" class="bg-sky-500 hover:bg-sky-600 text-white font-bold px-5 py-2.5 rounded-xl shadow-lg transition flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-hand-holding-hand text-amber-300"></i> Laporkan Temuan
                </a>
                <?php else: ?>
                <button onclick="openModal('modal-auth')" class="bg-white hover:bg-sky-50 text-primary-900 font-bold px-5 py-2.5 rounded-xl shadow-lg transition flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-magnifying-glass text-rose-500"></i> Laporkan Kehilangan
                </button>
                <button onclick="openModal('modal-auth')" class="bg-sky-500 hover:bg-sky-600 text-white font-bold px-5 py-2.5 rounded-xl shadow-lg transition flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-hand-holding-hand text-amber-300"></i> Laporkan Temuan
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 bg-white/10 p-4 rounded-2xl backdrop-blur-sm">
            <div class="text-center p-3 rounded-xl bg-white/5">
                <span class="block text-2xl sm:text-3xl font-extrabold text-rose-300"><?php echo $stats['total_hilang']; ?></span>
                <span class="text-xs text-sky-100 font-medium">Laporan Hilang</span>
            </div>
            <div class="text-center p-3 rounded-xl bg-white/5">
                <span class="block text-2xl sm:text-3xl font-extrabold text-emerald-300"><?php echo $stats['total_temuan']; ?></span>
                <span class="text-xs text-sky-100 font-medium">Laporan Temu</span>
            </div>
            <div class="text-center p-3 rounded-xl bg-white/5">
                <span class="block text-2xl sm:text-3xl font-extrabold text-sky-300"><?php echo $stats['selesai_hilang'] + $stats['selesai_temuan']; ?></span>
                <span class="text-xs text-sky-100 font-medium">Selesai Kembali</span>
            </div>
        </div>
    </div>
</div>

<!-- Kategori (tampilan saja, tanpa filter) -->
<div class="mb-8">
    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Kategori</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <?php foreach ($kategoriList as $kat): ?>
        <div class="bg-white border border-slate-100 rounded-xl p-3 flex flex-col items-center justify-center text-center shadow-sm">
            <span class="w-10 h-10 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center mb-2">
                <i class="fa-solid <?php echo $kat['ikon']; ?> text-base"></i>
            </span>
            <span class="block text-xs font-bold text-slate-700 truncate w-full"><?php echo $kat['nama']; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Feed Grid -->
<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-slate-800">Daftar Laporan Terkini</h2>
        <span class="text-xs bg-slate-100 px-3 py-1 rounded-full font-semibold text-slate-600">Menampilkan <?php echo count($reports); ?> laporan</span>
    </div>

    <?php if (empty($reports)): ?>
    <div class="text-center py-16 bg-white border rounded-2xl p-8">
        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
            <i class="fa-solid fa-box-open text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-700">Laporan tidak ditemukan</h3>
        <p class="text-slate-400 mt-1">Belum ada laporan yang tersedia.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($reports as $item): 
            $statusBadge = '';
            if ($item['status'] === 'Hilang') {
                $statusBadge = '<span class="bg-rose-100 text-rose-800 text-xs px-2.5 py-1 rounded-full font-bold uppercase"><i class="fa-solid fa-circle-exclamation mr-1"></i>Hilang</span>';
            } else if ($item['status'] === 'Ditemukan') {
                $statusBadge = '<span class="bg-amber-100 text-amber-800 text-xs px-2.5 py-1 rounded-full font-bold uppercase"><i class="fa-solid fa-hand-holding-hand mr-1"></i>Ditemukan</span>';
            } else if ($item['status'] === 'Selesai') {
                $statusBadge = '<span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-1 rounded-full font-bold uppercase"><i class="fa-solid fa-circle-check mr-1"></i>Selesai</span>';
            } else {
                $statusBadge = '<span class="bg-indigo-100 text-indigo-800 text-xs px-2.5 py-1 rounded-full font-bold uppercase">'.$item['status'].'</span>';
            }

            // ✅ PERBAIKAN: Tambah BASE_URL untuk foto
            $foto = !empty($item['foto']) ? BASE_URL . $item['foto'] : 'https://placehold.co/600x400/2563eb/ffffff?text='.urlencode($item['nama_barang']);
        ?>
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-xl transition duration-300 flex flex-col group">
            <div class="relative h-48 bg-slate-100 overflow-hidden">
                <img src="<?php echo $foto; ?>" alt="<?php echo sanitize($item['nama_barang']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" onerror="this.src='https://placehold.co/600x400?text=Barang'">
                <div class="absolute top-4 left-4 z-10"><?php echo $statusBadge; ?></div>
                <div class="absolute bottom-4 left-4 bg-slate-900/70 text-white text-[10px] font-bold tracking-wider uppercase px-2 py-1 rounded backdrop-blur-sm">
                    <?php echo $item['kategori_nama'] ?? 'Lainnya'; ?>
                </div>
            </div>
            <div class="p-5 flex-1 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg group-hover:text-primary-600 transition leading-snug truncate" title="<?php echo sanitize($item['nama_barang']); ?>"><?php echo sanitize($item['nama_barang']); ?></h3>
                    <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold mt-2">
                        <span class="flex items-center gap-1"><i class="fa-solid fa-location-dot text-rose-500"></i> <?php echo $item['lokasi']; ?></span>
                        <span>•</span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-calendar"></i> <?php echo formatDate($item['tanggal']); ?></span>
                    </div>
                    <p class="text-xs text-slate-500 mt-3 line-clamp-3 leading-relaxed"><?php echo sanitize($item['deskripsi']); ?></p>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-50 flex items-center justify-between">
                    <span class="text-[10px] text-slate-400 font-semibold uppercase">ID: #<?php echo $item['id']; ?></span>
                    <a href="<?php echo BASE_URL; ?>pages/detail.php?id=<?php echo $item['id']; ?>&tipe=<?php echo $item['tipe']; ?>" class="text-xs font-bold text-primary-600 hover:text-primary-800 transition flex items-center gap-1">
                        Lihat Detail <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>