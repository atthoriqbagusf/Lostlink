<?php
$pageTitle = 'Detail Laporan';
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isset($_GET['id']) || !isset($_GET['tipe'])) {
    header('Location: ' . BASE_URL);
    exit();
}

$id = intval($_GET['id']);
$tipe = $_GET['tipe'];

if ($tipe === 'hilang') {
    $item = getLaporanHilangById($id);
    $tipeLabel = 'Barang Hilang';
    $tipeBadgeClass = 'bg-rose-600 text-white';
} else {
    $item = getLaporanTemuanById($id);
    $tipeLabel = 'Barang Temuan';
    $tipeBadgeClass = 'bg-amber-600 text-white';
}

if (!$item) {
    setFlash('Laporan tidak ditemukan.', 'danger');
    header('Location: ' . BASE_URL);
    exit();
}

// ✅ PERBAIKAN: Tambah BASE_URL untuk foto
$foto = !empty($item['foto']) ? BASE_URL . $item['foto'] : 'https://placehold.co/600x400/2563eb/ffffff?text='.urlencode($item['nama_barang']);

$statusBadgeClass = 'bg-slate-100 text-slate-700';
if ($item['status'] === 'Hilang') $statusBadgeClass = 'bg-rose-100 text-rose-800';
elseif ($item['status'] === 'Ditemukan') $statusBadgeClass = 'bg-amber-100 text-amber-800';
elseif ($item['status'] === 'Selesai') $statusBadgeClass = 'bg-emerald-100 text-emerald-800';
elseif ($item['status'] === 'Diklaim') $statusBadgeClass = 'bg-indigo-100 text-indigo-800';

$isOwner = isLoggedIn() && (
    ($tipe === 'hilang' && $item['user_id'] == $_SESSION['user_id']) ||
    ($tipe === 'temuan' && $item['penemu_id'] == $_SESSION['user_id'])
);
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="relative h-64 sm:h-80 bg-slate-100 overflow-hidden">
            <img src="<?php echo $foto; ?>" alt="<?php echo sanitize($item['nama_barang']); ?>" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/600x400?text=Barang'">
            <span class="absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?php echo $tipeBadgeClass; ?>"><?php echo $tipeLabel; ?></span>
            <a href="<?php echo BASE_URL; ?>" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-slate-900/60 hover:bg-slate-900/80 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </a>
        </div>

        <div class="p-6 sm:p-8 flex flex-col gap-5">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold mb-1">
                    <span class="px-2 py-0.5 rounded font-bold <?php echo $statusBadgeClass; ?>"><?php echo $item['status']; ?></span>
                    <span>•</span>
                    <span><?php echo $item['kategori_nama'] ?? 'Lainnya'; ?></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 leading-tight"><?php echo sanitize($item['nama_barang']); ?></h1>
            </div>

            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl text-xs font-semibold text-slate-600">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-rose-500 text-sm"></i>
                    <div>
                        <span class="block text-slate-400 font-medium">Lokasi</span>
                        <span><?php echo $item['lokasi']; ?></span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar text-sky-500 text-sm"></i>
                    <div>
                        <span class="block text-slate-400 font-medium">Tanggal</span>
                        <span><?php echo formatDate($item['tanggal']); ?></span>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase mb-2">Deskripsi Lengkap</h4>
                <p class="text-sm text-slate-600 leading-relaxed"><?php echo nl2br(sanitize($item['deskripsi'])); ?></p>
            </div>

            <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl">
                <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-lg">
                    <?php echo strtoupper(substr($item['pelapor_nama'] ?? $item['penemu_nama'], 0, 1)); ?>
                </div>
                <div class="flex-1">
                    <span class="block text-[10px] text-slate-400 font-bold uppercase">
                        <?php echo ($item['pelapor_tipe'] ?? $item['penemu_tipe']) === 'mahasiswa' ? 'PELAPOR (MAHASISWA)' : 'PELAPOR (UMUM)'; ?>
                    </span>
                    <span class="text-sm font-bold text-slate-700">
                        <?php echo $item['pelapor_nama'] ?? $item['penemu_nama']; ?>
                    </span>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <?php if (!$isLogged): ?>
                    <button onclick="openModal('modal-auth')" class="flex-1 text-center bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 rounded-xl text-sm transition shadow-md">
                        Masuk untuk Hubungi / Ajukan Klaim
                    </button>
                <?php elseif ($isOwner): ?>
                    <a href="<?php echo BASE_URL; ?>pages/form-laporan.php?tipe=<?php echo $tipe; ?>&edit=<?php echo $id; ?>" class="flex-1 text-center bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl text-sm transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Laporan
                    </a>
                    <button onclick="triggerConfirm('Hapus Laporan?', 'Data akan dihapus permanen.', function() { window.location='<?php echo BASE_URL; ?>api/delete-laporan.php?tipe=<?php echo $tipe; ?>&id=<?php echo $id; ?>'; })" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-xl text-sm transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-trash"></i> Hapus
                    </button>
                <?php else: ?>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $item['kontak']); ?>?text=Halo,%20saya%20menghubungi%20lewat%20LostLink%20terkait%20<?php echo urlencode($item['nama_barang']); ?>" target="_blank" class="flex-1 text-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl text-sm transition flex items-center justify-center gap-2">
                        <i class="fa-brands fa-whatsapp text-base"></i> Hubungi WA
                    </a>
                    <?php if ($tipe === 'temuan' && $item['status'] === 'Ditemukan'): ?>
                    <a href="<?php echo BASE_URL; ?>pages/form-klaim.php?laporan_id=<?php echo $id; ?>" class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl text-sm transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-receipt"></i> Ajukan Klaim
                    </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>