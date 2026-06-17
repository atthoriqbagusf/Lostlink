<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if (isset($_SESSION['flash']) && isset($_SESSION['flash']['cleared'])) {
    unset($_SESSION['flash']);
}
$flash = getFlash();
if ($flash) {
    $_SESSION['flash_cleared'] = true;
}
$currentRole = getUserRole();
$isLogged = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>LostLink - Pusat Kehilangan & Temuan Kampus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: {
                            50: '#f0f9ff', 100: '#e0f2fe', 500: '#3b82f6',
                            600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col custom-scrollbar">

    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <?php if ($flash): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast("<?php echo addslashes($flash['message']); ?>", "<?php echo $flash['type']; ?>");
        });
    </script>
    <?php endif; ?>

    <nav class="bg-primary-900 text-white sticky top-0 z-40 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2 cursor-pointer" onclick="window.location='<?php echo BASE_URL; ?>'">
                    <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-sky-400 flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-link text-white text-lg"></i>
                    </span>
                    <div>
                        <span class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-white to-sky-100 bg-clip-text text-transparent">LostLink</span>
                        <span class="block text-[9px] text-sky-200 tracking-widest uppercase font-bold">Pusat Lost & Found Kampus</span>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-6">
                    <a href="<?php echo BASE_URL; ?>" class="text-sky-200 hover:text-white font-semibold text-sm transition">Halaman Utama</a>
                    <?php if ($isLogged): ?>
                        <?php if ($currentRole === 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>pages/admin.php" class="text-sky-100 hover:text-white font-bold text-sm transition">Panel Admin</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="text-sky-100 hover:text-white font-bold text-sm transition">Dashboard Saya</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-4">
                    <?php if ($isLogged): ?>
                        <div class="relative cursor-pointer" onclick="toggleNotifPanel()">
                            <i class="fa-solid fa-bell text-xl hover:text-sky-300 transition"></i>
                            <span id="notif-badge" class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">0</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <img src="https://placehold.co/150x150/2563eb/ffffff?text=<?php echo substr($_SESSION['nama'], 0, 1); ?>" class="w-8 h-8 rounded-full border border-sky-400 object-cover">
                            <span class="text-xs font-bold hidden sm:inline-block"><?php echo $_SESSION['nama']; ?></span>
                            <a href="<?php echo BASE_URL; ?>api/logout.php" class="text-xs bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg font-bold transition">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <button onclick="openModal('modal-auth')" class="bg-sky-600 hover:bg-sky-500 text-white font-bold px-4 py-2 rounded-xl text-xs transition flex items-center gap-1">
                            <i class="fa-solid fa-right-to-bracket"></i> Masuk / Daftar
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div id="notif-panel" class="fixed inset-y-0 right-0 w-80 bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 border-l border-slate-200 flex flex-col hidden">
        <div class="p-4 bg-primary-900 text-white flex items-center justify-between">
            <h3 class="font-bold flex items-center gap-2"><i class="fa-solid fa-bell"></i> Notifikasi</h3>
            <button onclick="toggleNotifPanel()" class="text-white hover:text-sky-300"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="p-2 border-b border-slate-100 flex justify-between text-xs bg-slate-50">
            <button onclick="markAllRead()" class="text-primary-600 hover:underline font-semibold">Tandai dibaca</button>
        </div>
        <div id="notif-list-container" class="flex-1 overflow-y-auto p-4 flex flex-col gap-3 custom-scrollbar">
            <div class="text-center text-slate-400 text-xs py-8">Belum ada notifikasi</div>
        </div>
    </div>

    <div id="modal-auth" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl border border-slate-100">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-primary-950 text-white">
                <h3 id="auth-title" class="font-extrabold text-base">Masuk Ke LostLink</h3>
                <button onclick="closeModal('modal-auth')" class="text-white hover:text-sky-300"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form id="form-login" action="<?php echo BASE_URL; ?>api/login.php" method="POST" class="p-6 flex flex-col gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Email / NIM / NIK</label>
                    <input type="text" name="identitas" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" placeholder="email@kampus.ac.id" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Password</label>
                    <input type="password" name="password" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" placeholder="••••••••" required>
                </div>
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 rounded-xl text-sm transition shadow-md">Masuk Akun</button>
                <div class="text-center text-xs text-slate-400 mt-2">
                    Belum punya akun? <a href="#" onclick="toggleAuthForm('register')" class="text-primary-600 font-bold hover:underline">Registrasi</a>
                </div>
            </form>

            <form id="form-register" action="<?php echo BASE_URL; ?>api/register.php" method="POST" class="p-6 flex flex-col gap-4 hidden max-h-[75vh] overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tipe Pengguna</label>
                    <select name="tipe_user" id="reg-tipe" onchange="toggleRegisterFields()" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm bg-white">
                        <option value="mahasiswa">Mahasiswa / Staf Kampus</option>
                        <option value="umum">Masyarakat Umum</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" placeholder="Andi Wijaya" required>
                </div>
                <div>
                    <label id="reg-identitas-label" class="block text-xs font-bold text-slate-500 uppercase mb-1">NIM</label>
                    <input type="text" name="identitas" id="reg-nim" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" placeholder="2201010145" required>
                </div>
                <div>
                    <label id="reg-email-label" class="block text-xs font-bold text-slate-500 uppercase mb-1">Email</label>
                    <input type="email" name="email" id="reg-email" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" placeholder="andi@mhs.kampus.ac.id" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">No. WhatsApp</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" placeholder="08123456789" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Password (Min 8 Karakter)</label>
                    <input type="password" name="password" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm" placeholder="••••••••" required minlength="8">
                </div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl text-sm transition shadow-md">Daftar Sekarang</button>
                <div class="text-center text-xs text-slate-400 mt-2">
                    Sudah punya akun? <a href="#" onclick="toggleAuthForm('login')" class="text-primary-600 font-bold hover:underline">Masuk</a>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-confirm" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 text-center">
            <div class="w-14 h-14 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-500">
                <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800" id="confirm-title">Konfirmasi</h3>
            <p class="text-slate-500 text-sm mt-2" id="confirm-message">Apakah Anda yakin?</p>
            <div class="flex gap-3 mt-6">
                <button onclick="closeModal('modal-confirm')" class="flex-1 py-2 text-sm font-semibold rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 transition">Batal</button>
                <button id="confirm-yes-btn" class="flex-1 py-2 text-sm font-bold rounded-xl bg-rose-600 hover:bg-rose-700 text-white transition">Ya</button>
            </div>
        </div>
    </div>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
