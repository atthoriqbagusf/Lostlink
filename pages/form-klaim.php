<?php
// ============================================
// STEP 1: SESSION & CONFIG (TANPA OUTPUT)
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEBUG MODE: Ganti 0 jadi 1 untuk aktifkan debug
$DEBUG = 0;

if ($DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

require_once '../includes/config.php';
require_once '../includes/functions.php';

// ============================================
// STEP 2: CEK LOGIN
// ============================================
if (!isLoggedIn()) {
    setFlash('Silakan masuk terlebih dahulu.', 'info');
    header('Location: ' . BASE_URL);
    exit();
}

// ============================================
// STEP 3: HANDLE FORM SUBMISSION (POST)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $laporanId = intval($_POST['laporan_id'] ?? 0);
    $bukti = sanitize($_POST['bukti'] ?? '');
    $fotoBukti = null;

    if (!empty($_FILES['foto_bukti']['name'])) {
        $upload = uploadFile($_FILES['foto_bukti'], 'uploads/bukti/');
        if ($upload['success']) {
            $fotoBukti = $upload['path'];
        }
    }

    $result = createKlaim($laporanId, $_SESSION['user_id'], $bukti, $fotoBukti);
    setFlash($result['message'], $result['success'] ? 'success' : 'danger');

    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit();
}

// ============================================
// STEP 4: CEK PARAMETER & LOAD DATA
// ============================================
if (!isset($_GET['laporan_id'])) {
    setFlash('ID laporan tidak valid.', 'danger');
    header('Location: ' . BASE_URL);
    exit();
}

$laporanId = intval($_GET['laporan_id']);
$laporan = getLaporanTemuanById($laporanId);

// DEBUG: Tampilkan data laporan
if ($DEBUG) {
    echo '<pre style="background:#f0f0f0;padding:10px;margin:10px;">';
    echo '<strong>DEBUG INFO:</strong><br>';
    echo 'laporanId: ' . $laporanId . '<br>';
    echo 'laporan data:<br>';
    print_r($laporan);
    echo '<br>Status check: ';
    var_dump($laporan['status'] ?? 'NULL');
    echo '<br>isLoggedIn: ' . (isLoggedIn() ? 'YES' : 'NO');
    echo '<br>user_id: ' . ($_SESSION['user_id'] ?? 'NULL');
    echo '</pre>';
    exit();
}

// Cek dengan trim dan lowercase untuk aman
$status = isset($laporan['status']) ? strtolower(trim($laporan['status'])) : '';

if (!$laporan) {
    setFlash('Laporan tidak ditemukan di database.', 'danger');
    header('Location: ' . BASE_URL);
    exit();
}

if ($status !== 'ditemukan') {
    setFlash('Laporan tidak tersedia untuk klaim. Status saat ini: ' . ($laporan['status'] ?? 'null'), 'danger');
    header('Location: ' . BASE_URL);
    exit();
}

$pageTitle = 'Ajukan Klaim';

// ============================================
// STEP 5: BARU OUTPUT HTML (include header)
// ============================================
require_once '../includes/header.php';
?>

<div class="max-w-md mx-auto">
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-indigo-900 text-white">
            <h3 class="font-bold">Ajukan Klaim Kepemilikan</h3>
        </div>

        <div class="p-6 bg-slate-50 border-b border-slate-100">
            <span class="block text-xs text-slate-400 font-bold uppercase mb-1">Barang yang Diklaim</span>
            <p class="font-bold text-slate-800 text-base"><?php echo sanitize($laporan['nama_barang']); ?></p>
            <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-location-dot text-rose-500 mr-1"></i> <?php echo $laporan['lokasi']; ?></p>
        </div>

        <form method="POST" enctype="multipart/form-data" class="p-6 flex flex-col gap-4">
            <input type="hidden" name="laporan_id" value="<?php echo $laporanId; ?>">

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Unggah Bukti Fisik</label>
                <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center cursor-pointer hover:border-indigo-500 transition relative flex flex-col items-center justify-center bg-slate-50">
                    <input type="file" name="foto_bukti" id="bukti-input" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                    <div id="bukti-preview-container" class="hidden w-full mb-2">
                        <img id="bukti-preview-img" src="" class="max-h-32 rounded-lg mx-auto object-cover">
                    </div>
                    <div id="bukti-placeholder">
                        <i class="fa-solid fa-upload text-slate-400 text-xl mb-1"></i>
                        <span class="block text-xs font-semibold text-slate-500">Upload Foto Bukti</span>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi Pembuktian <span class="text-rose-500">*</span></label>
                <textarea name="bukti" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-xs" placeholder="Sebutkan isi file, nomor seri, detail warna, struk pembelian..." required></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="<?php echo BASE_URL; ?>pages/detail.php?id=<?php echo $laporanId; ?>&tipe=temuan" class="flex-1 text-center py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl text-sm transition shadow-lg">
                    Kirim Ajuan Klaim
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('bukti-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('bukti-preview-img').src = e.target.result;
                document.getElementById('bukti-preview-container').classList.remove('hidden');
                document.getElementById('bukti-placeholder').classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>