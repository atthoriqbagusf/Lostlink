    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 mt-auto py-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-link text-white text-sm"></i>
                </span>
                <span class="font-extrabold text-white text-lg tracking-tight">LostLink</span>
            </div>
            <div class="text-xs text-center sm:text-right">
                <p>&copy; 2026 LostLink Universitas. Terbuka untuk Mahasiswa & Umum.</p>
                <p class="text-slate-600 mt-1">Sistem Manajemen Kehilangan & Temuan Barang Kampus</p>
            </div>
        </div>
    </footer>

    <script>
        // Toast Notification System
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl border text-sm font-semibold transition-all duration-300 transform translate-y-2 opacity-0 pointer-events-auto bg-white`;

            let icon = '';
            if (type === 'success') {
                toast.classList.add('border-emerald-200', 'text-emerald-800');
                icon = '<i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>';
            } else if (type === 'danger') {
                toast.classList.add('border-rose-200', 'text-rose-800');
                icon = '<i class="fa-solid fa-circle-xmark text-rose-500 text-lg"></i>';
            } else {
                toast.classList.add('border-blue-200', 'text-blue-800');
                icon = '<i class="fa-solid fa-circle-info text-blue-500 text-lg"></i>';
            }

            toast.innerHTML = `${icon}<div class="flex-1">${message}</div><button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>`;
            container.appendChild(toast);

            setTimeout(() => { toast.classList.remove('translate-y-2', 'opacity-0'); }, 10);
            setTimeout(() => { toast.classList.add('translate-y-2', 'opacity-0'); setTimeout(() => toast.remove(), 300); }, 4000);
        }

        // Modal Functions
        function openModal(modalId) {
            const m = document.getElementById(modalId);
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeModal(modalId) {
            const m = document.getElementById(modalId);
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        // Auth Form Toggle
        function toggleAuthForm(form) {
            const loginF = document.getElementById('form-login');
            const regF = document.getElementById('form-register');
            const title = document.getElementById('auth-title');

            if (form === 'login') {
                loginF.classList.remove('hidden');
                regF.classList.add('hidden');
                title.innerText = "Masuk Ke LostLink";
            } else {
                loginF.classList.add('hidden');
                regF.classList.remove('hidden');
                title.innerText = "Pendaftaran Akun Baru";
                toggleRegisterFields();
            }
        }

        function toggleRegisterFields() {
            const tipe = document.getElementById('reg-tipe').value;
            const labelIdentitas = document.getElementById('reg-identitas-label');
            const inputIdentitas = document.getElementById('reg-nim');
            const labelEmail = document.getElementById('reg-email-label');

            if (tipe === 'mahasiswa') {
                labelIdentitas.innerText = "NIM (Nomor Induk Mahasiswa)";
                inputIdentitas.placeholder = "Contoh: 2201010145";
                labelEmail.innerText = "Email Kampus";
            } else {
                labelIdentitas.innerText = "NIK / No. Identitas Resmi";
                inputIdentitas.placeholder = "Contoh: 3273xxxxxxxxxxxx";
                labelEmail.innerText = "Email Pribadi";
            }
        }

        // Notification Panel
        function toggleNotifPanel() {
            const panel = document.getElementById('notif-panel');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                setTimeout(() => panel.classList.remove('translate-x-full'), 10);
            } else {
                panel.classList.add('translate-x-full');
                setTimeout(() => panel.classList.add('hidden'), 300);
            }
        }

        function markAllRead() {
            showToast('Semua notifikasi ditandai telah dibaca', 'success');
        }

        // Confirmation Modal
        function triggerConfirm(title, message, callback) {
            document.getElementById('confirm-title').innerText = title;
            document.getElementById('confirm-message').innerText = message;
            document.getElementById('confirm-yes-btn').onclick = function() {
                callback();
                closeModal('modal-confirm');
            };
            openModal('modal-confirm');
        }

        // Auto-hide flash messages
        document.addEventListener('DOMContentLoaded', function() {
            // Any page-specific initialization
        });
    </script>
</body>
</html>
