<?php
// ============================================
// STEP 1: START SESSION PALING AWAL
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// STEP 2: CLEAR FLASH LAMA (jika ada)
// ============================================
if (isset($_SESSION['flash'])) {
    unset($_SESSION['flash']);
}

// ============================================
// STEP 3: LOAD CONFIG & FUNCTIONS
// ============================================
require_once '../includes/config.php';
require_once '../includes/functions.php';

// ============================================
// STEP 4: CEK LOGIN SEBELUM ADA OUTPUT
// ============================================
if (!isLoggedIn()) {
    setFlash('Silakan masuk terlebih dahulu.', 'info');
    header('Location: ' . BASE_URL);
    exit();
}

// ============================================
// STEP 5: SET PAGE TITLE & HANDLE POST
// ============================================
$pageTitle = 'Form Laporan';

$tipe = $_GET['tipe'] ?? 'hilang';
$editId = $_GET['edit'] ?? null;
$isEdit = false;
$item = null;

// Handle edit mode
if ($editId) {
    $isEdit = true;
    if ($tipe === 'hilang') {
        $item = getLaporanHilangById($editId);
    } else {
        $item = getLaporanTemuanById($editId);
    }

    if (!$item) {
        setFlash('Laporan tidak ditemukan.', 'danger');
        header('Location: ' . BASE_URL);
        exit();
    }

    $ownerId = $tipe === 'hilang' ? $item['user_id'] : $item['penemu_id'];
    if ($ownerId != $_SESSION['user_id'] && !isAdmin()) {
        setFlash('Anda tidak memiliki akses.', 'danger');
        header('Location: ' . BASE_URL);
        exit();
    }
}

$kategoriList = getAllKategori();
$lokasiList = ["Perpustakaan", "Lab Komputer", "Ruang Kuliah", "Kantin", "Parkiran", "Masjid Kampus", "Lainnya"];

// ============================================
// STEP 6: HANDLE FORM SUBMISSION (POST)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaBarang = sanitize($_POST['nama_barang']);
    $kategoriId = intval($_POST['kategori_id']);
    $lokasi = sanitize($_POST['lokasi']);
    $tanggal = $_POST['tanggal'];
    $deskripsi = sanitize($_POST['deskripsi']);
    $kontak = sanitize($_POST['kontak']);

    $foto = null;
    if (!empty($_FILES['foto']['name'])) {
        $upload = uploadFile($_FILES['foto'], 'uploads/');
        if ($upload['success']) {
            $foto = $upload['path'];
        }
    }

    if ($isEdit) {
        if ($tipe === 'hilang') {
            updateLaporanHilang($editId, $namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $foto);
        } else {
            updateLaporanTemuan($editId, $namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $foto);
        }
        setFlash('Laporan berhasil diperbarui!', 'success');
    } else {
        if ($tipe === 'hilang') {
            $result = createLaporanHilang($namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $_SESSION['user_id'], $foto);
        } else {
            $result = createLaporanTemuan($namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $_SESSION['user_id'], $foto);
        }

        if ($result['success']) {
            setFlash($result['message'], 'success');
        } else {
            setFlash($result['message'], 'danger');
        }
    }

    // ✅ Redirect SEKARANG, sebelum output HTML
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit();
}

// ============================================
// STEP 7: BARU OUTPUT HTML (include header)
// ============================================
require_once '../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-primary-900 text-white">
            <h3 class="font-extrabold text-lg"><?php echo $isEdit ? 'Edit' : 'Buat'; ?> Laporan <?php echo $tipe === 'hilang' ? 'Kehilangan' : 'Temuan'; ?></h3>
        </div>

        <form method="POST" enctype="multipart/form-data" class="p-6 flex flex-col gap-4">
            <input type="hidden" name="tipe" value="<?php echo $tipe; ?>">

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_barang" value="<?php echo $isEdit ? sanitize($item['nama_barang']) : ''; ?>" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" placeholder="Contoh: Dompet Kulit Coklat" required>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Kategori <span class="text-rose-500">*</span></label>
                    <select name="kategori_id" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm bg-white" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($kategoriList as $kat): ?>
                        <option value="<?php echo $kat['id']; ?>" <?php echo ($isEdit && $item['kategori_id'] == $kat['id']) ? 'selected' : ''; ?>><?php echo $kat['nama']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Lokasi <span class="text-rose-500">*</span></label>
                    <select name="lokasi" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm bg-white" required>
                        <option value="">Pilih Lokasi</option>
                        <?php foreach ($lokasiList as $loc): ?>
                        <option value="<?php echo $loc; ?>" <?php echo ($isEdit && $item['lokasi'] == $loc) ? 'selected' : ''; ?>><?php echo $loc; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal" value="<?php echo $isEdit ? $item['tanggal'] : date('Y-m-d'); ?>" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Kontak WA <span class="text-rose-500">*</span></label>
                    <input type="text" name="kontak" value="<?php echo $isEdit ? $item['kontak'] : ($_SESSION['phone'] ?? ''); ?>" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" placeholder="08123456789" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi & Ciri Spesifik</label>
                <textarea name="deskripsi" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" placeholder="Sebutkan kondisi barang, detail fisik, isi di dalamnya..."><?php echo $isEdit ? sanitize($item['deskripsi']) : ''; ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Foto Barang</label>
                <div class="border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center cursor-pointer hover:border-primary-500 transition relative flex flex-col items-center justify-center bg-slate-50">
                    <input type="file" name="foto" id="foto-input" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                    <div id="preview-container" class="hidden w-full mb-2">
                        <img id="preview-img" src="" class="max-h-36 rounded-xl mx-auto object-cover">
                    </div>
                    <div id="placeholder">
                        <i class="fa-solid fa-cloud-arrow-up text-slate-400 text-2xl mb-1"></i>
                        <span class="block text-xs font-semibold text-slate-500">Pilih berkas JPG/PNG</span>
                    </div>
                </div>
                <?php if ($isEdit && $item['foto']): ?>
                <p class="text-xs text-slate-400 mt-1">Foto saat ini: <?php echo basename($item['foto']); ?></p>
                <?php endif; ?>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="flex-1 text-center py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 rounded-xl text-sm transition shadow-lg">
                    <?php echo $isEdit ? 'Simpan Perubahan' : 'Kirim Laporan'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('foto-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('preview-container').classList.remove('hidden');
                document.getElementById('placeholder').classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>