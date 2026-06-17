<?php
/**
 * LostLink - Functions & Database Operations
 * File ini berisi semua fungsi CRUD dan helper untuk aplikasi
 */

require_once 'config.php';

// ============================================================
// USER FUNCTIONS
// ============================================================

/**
 * Register new user
 */
function registerUser($nama, $identitas, $email, $password, $phone, $tipe_user = 'umum') {
    $db = getDB();

    // Check if email or identitas already exists
    $stmt = $db->prepare("SELECT id FROM user WHERE email = ? OR identitas = ?");
    $stmt->execute([$email, $identitas]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email atau identitas sudah terdaftar.'];
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $status = 'aktif';

    $stmt = $db->prepare("INSERT INTO user (nama, identitas, email, password, phone, tipe_user, status) VALUES (?, ?, ?, ?, ?, ?, ?)");

    try {
        $stmt->execute([$nama, $identitas, $email, $hashedPassword, $phone, $tipe_user, $status]);
        $userId = $db->lastInsertId();

        // Log activity
        logActivity("User baru terdaftar: {$nama} (Tipe: {$tipe_user})");

        return ['success' => true, 'user_id' => $userId, 'message' => 'Registrasi berhasil!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registrasi gagal: ' . $e->getMessage()];
    }
}

/**
 * Login user
 */
function loginUser($identitas, $password) {
    $db = getDB();

    $stmt = $db->prepare("SELECT * FROM user WHERE email = ? OR identitas = ?");
    $stmt->execute([$identitas, $identitas]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Akun tidak ditemukan.'];
    }

    if ($user['status'] !== 'aktif') {
        return ['success' => false, 'message' => 'Akun Anda dinonaktifkan. Hubungi admin.'];
    }

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['tipe_user'];
        $_SESSION['identitas'] = $user['identitas'];

        logActivity("User {$user['nama']} login ke sistem");

        return ['success' => true, 'user' => $user, 'message' => 'Login berhasil!'];
    }

    return ['success' => false, 'message' => 'Password salah.'];
}

/**
 * Logout user
 */
function logoutUser() {
    if (isset($_SESSION['user_id'])) {
        logActivity("User {$_SESSION['nama']} logout dari sistem");
    }
    session_destroy();
}

/**
 * Get user by ID
 */
function getUserById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, nama, identitas, email, phone, tipe_user, status, foto FROM user WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get all users
 */
function getAllUsers() {
    $db = getDB();
    $stmt = $db->query("SELECT id, nama, identitas, email, phone, tipe_user, status, foto FROM user ORDER BY id DESC");
    return $stmt->fetchAll();
}

/**
 * Update user profile
 */
function updateUserProfile($id, $nama, $email, $phone) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE user SET nama = ?, email = ?, phone = ? WHERE id = ?");

    try {
        $stmt->execute([$nama, $email, $phone, $id]);
        logActivity("User #{$id} memperbarui profil");
        return ['success' => true, 'message' => 'Profil berhasil diperbarui!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Gagal memperbarui profil.'];
    }
}

/**
 * Update user password
 */
function updateUserPassword($id, $newPassword) {
    $db = getDB();
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE user SET password = ? WHERE id = ?");

    try {
        $stmt->execute([$hashedPassword, $id]);
        logActivity("User #{$id} mengganti password");
        return ['success' => true, 'message' => 'Password berhasil diperbarui!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Gagal mengganti password.'];
    }
}

/**
 * Toggle user status
 */
function toggleUserStatus($id) {
    $db = getDB();
    $user = getUserById($id);
    if (!$user) return false;

    $newStatus = $user['status'] === 'aktif' ? 'nonaktif' : 'aktif';
    $stmt = $db->prepare("UPDATE user SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);

    logActivity("Admin mengubah status user #{$id} menjadi {$newStatus}");
    return true;
}

/**
 * Delete user
 */
function deleteUser($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM user WHERE id = ?");

    try {
        $stmt->execute([$id]);
        logActivity("Admin menghapus user #{$id}");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// ============================================================
// KATEGORI FUNCTIONS
// ============================================================

/**
 * Get all categories
 */
function getAllKategori() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM kategori ORDER BY id ASC");
    return $stmt->fetchAll();
}

/**
 * Get category by ID
 */
function getKategoriById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM kategori WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Add new category
 */
function addKategori($nama, $ikon) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO kategori (nama, ikon) VALUES (?, ?)");

    try {
        $stmt->execute([$nama, $ikon]);
        logActivity("Admin menambah kategori: {$nama}");
        return ['success' => true, 'id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Update category
 */
function updateKategori($id, $nama, $ikon) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE kategori SET nama = ?, ikon = ? WHERE id = ?");

    try {
        $stmt->execute([$nama, $ikon, $id]);
        logActivity("Admin mengupdate kategori #{$id}");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Delete category
 */
function deleteKategori($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM kategori WHERE id = ?");

    try {
        $stmt->execute([$id]);
        logActivity("Admin menghapus kategori #{$id}");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// ============================================================
// LAPORAN HILANG FUNCTIONS
// ============================================================

/**
 * Create lost item report
 */
function createLaporanHilang($namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $userId, $foto = null) {
    $db = getDB();
    $status = 'Menunggu Verifikasi';

    $stmt = $db->prepare("INSERT INTO laporan_hilang (nama_barang, kategori_id, lokasi, tanggal, deskripsi, kontak, user_id, status, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    try {
        $stmt->execute([$namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $userId, $status, $foto]);
        $id = $db->lastInsertId();

        logActivity("User #{$userId} membuat laporan kehilangan: {$namaBarang}");

        return ['success' => true, 'id' => $id, 'message' => 'Laporan kehilangan berhasil dibuat!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get all lost reports with user and category info
 */
function getAllLaporanHilang($status = null) {
    $db = getDB();
    $sql = "SELECT lh.*, k.nama as kategori_nama, k.ikon as kategori_ikon, u.nama as pelapor_nama, u.tipe_user as pelapor_tipe 
            FROM laporan_hilang lh 
            LEFT JOIN kategori k ON lh.kategori_id = k.id 
            LEFT JOIN user u ON lh.user_id = u.id";

    if ($status) {
        $sql .= " WHERE lh.status = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$status]);
    } else {
        $sql .= " ORDER BY lh.id DESC";
        $stmt = $db->query($sql);
    }

    return $stmt->fetchAll();
}

/**
 * Get lost report by ID
 */
function getLaporanHilangById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT lh.*, k.nama as kategori_nama, k.ikon as kategori_ikon, u.nama as pelapor_nama, u.tipe_user as pelapor_tipe, u.phone as pelapor_phone 
                          FROM laporan_hilang lh 
                          LEFT JOIN kategori k ON lh.kategori_id = k.id 
                          LEFT JOIN user u ON lh.user_id = u.id 
                          WHERE lh.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get lost reports by user ID
 */
function getLaporanHilangByUser($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT lh.*, k.nama as kategori_nama FROM laporan_hilang lh LEFT JOIN kategori k ON lh.kategori_id = k.id WHERE lh.user_id = ? ORDER BY lh.id DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Update lost report
 */
function updateLaporanHilang($id, $namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $foto = null) {
    $db = getDB();

    if ($foto) {
        $stmt = $db->prepare("UPDATE laporan_hilang SET nama_barang = ?, kategori_id = ?, lokasi = ?, tanggal = ?, deskripsi = ?, kontak = ?, foto = ? WHERE id = ?");
        $stmt->execute([$namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $foto, $id]);
    } else {
        $stmt = $db->prepare("UPDATE laporan_hilang SET nama_barang = ?, kategori_id = ?, lokasi = ?, tanggal = ?, deskripsi = ?, kontak = ? WHERE id = ?");
        $stmt->execute([$namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $id]);
    }

    logActivity("Laporan kehilangan #{$id} diperbarui");
    return true;
}

/**
 * Update lost report status
 */
function updateStatusLaporanHilang($id, $status) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE laporan_hilang SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    logActivity("Status laporan kehilangan #{$id} diubah menjadi {$status}");
    return true;
}

/**
 * Delete lost report
 */
function deleteLaporanHilang($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM laporan_hilang WHERE id = ?");
    $stmt->execute([$id]);
    logActivity("Laporan kehilangan #{$id} dihapus");
    return true;
}

// ============================================================
// LAPORAN TEMUAN FUNCTIONS
// ============================================================

/**
 * Create found item report
 */
function createLaporanTemuan($namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $penemuId, $foto = null) {
    $db = getDB();
    $status = 'Menunggu Verifikasi';

    $stmt = $db->prepare("INSERT INTO laporan_temuan (nama_barang, kategori_id, lokasi, tanggal, deskripsi, kontak, penemu_id, status, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    try {
        $stmt->execute([$namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $penemuId, $status, $foto]);
        $id = $db->lastInsertId();

        logActivity("User #{$penemuId} membuat laporan temuan: {$namaBarang}");

        return ['success' => true, 'id' => $id, 'message' => 'Laporan temuan berhasil dibuat!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get all found reports with user and category info
 */
function getAllLaporanTemuan($status = null) {
    $db = getDB();
    $sql = "SELECT lt.*, k.nama as kategori_nama, k.ikon as kategori_ikon, u.nama as penemu_nama, u.tipe_user as penemu_tipe 
            FROM laporan_temuan lt 
            LEFT JOIN kategori k ON lt.kategori_id = k.id 
            LEFT JOIN user u ON lt.penemu_id = u.id";

    if ($status) {
        $sql .= " WHERE lt.status = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$status]);
    } else {
        $sql .= " ORDER BY lt.id DESC";
        $stmt = $db->query($sql);
    }

    return $stmt->fetchAll();
}

/**
 * Get found report by ID
 */
function getLaporanTemuanById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT lt.*, k.nama as kategori_nama, k.ikon as kategori_ikon, u.nama as penemu_nama, u.tipe_user as penemu_tipe, u.phone as penemu_phone 
                          FROM laporan_temuan lt 
                          LEFT JOIN kategori k ON lt.kategori_id = k.id 
                          LEFT JOIN user u ON lt.penemu_id = u.id 
                          WHERE lt.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get found reports by user ID
 */
function getLaporanTemuanByUser($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT lt.*, k.nama as kategori_nama FROM laporan_temuan lt LEFT JOIN kategori k ON lt.kategori_id = k.id WHERE lt.penemu_id = ? ORDER BY lt.id DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Update found report
 */
function updateLaporanTemuan($id, $namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $foto = null) {
    $db = getDB();

    if ($foto) {
        $stmt = $db->prepare("UPDATE laporan_temuan SET nama_barang = ?, kategori_id = ?, lokasi = ?, tanggal = ?, deskripsi = ?, kontak = ?, foto = ? WHERE id = ?");
        $stmt->execute([$namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $foto, $id]);
    } else {
        $stmt = $db->prepare("UPDATE laporan_temuan SET nama_barang = ?, kategori_id = ?, lokasi = ?, tanggal = ?, deskripsi = ?, kontak = ? WHERE id = ?");
        $stmt->execute([$namaBarang, $kategoriId, $lokasi, $tanggal, $deskripsi, $kontak, $id]);
    }

    logActivity("Laporan temuan #{$id} diperbarui");
    return true;
}

/**
 * Update found report status
 */
function updateStatusLaporanTemuan($id, $status) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE laporan_temuan SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    logActivity("Status laporan temuan #{$id} diubah menjadi {$status}");
    return true;
}

/**
 * Delete found report
 */
function deleteLaporanTemuan($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM laporan_temuan WHERE id = ?");
    $stmt->execute([$id]);
    logActivity("Laporan temuan #{$id} dihapus");
    return true;
}

// ============================================================
// KLAIM BARANG FUNCTIONS
// ============================================================

/**
 * Create claim
 */
function createKlaim($laporanId, $claimantId, $bukti, $fotoBukti = null) {
    $db = getDB();
    $status = 'Menunggu Verifikasi';
    $tanggal = date('Y-m-d');

    $stmt = $db->prepare("INSERT INTO klaim_barang (laporan_id, claimant_id, bukti, tanggal_klaim, status, foto_bukti) VALUES (?, ?, ?, ?, ?, ?)");

    try {
        $stmt->execute([$laporanId, $claimantId, $bukti, $tanggal, $status, $fotoBukti]);
        $id = $db->lastInsertId();

        // Update laporan temuan status to Diklaim
        $stmt2 = $db->prepare("UPDATE laporan_temuan SET status = 'Diklaim' WHERE id = ?");
        $stmt2->execute([$laporanId]);

        logActivity("User #{$claimantId} mengajukan klaim untuk laporan #{$laporanId}");

        return ['success' => true, 'id' => $id, 'message' => 'Klaim berhasil diajukan!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get all claims with related info
 */
function getAllKlaim() {
    $db = getDB();
    $stmt = $db->query("SELECT kb.*, lt.nama_barang, lt.lokasi, u.nama as claimant_nama, u.tipe_user as claimant_tipe 
                          FROM klaim_barang kb 
                          LEFT JOIN laporan_temuan lt ON kb.laporan_id = lt.id 
                          LEFT JOIN user u ON kb.claimant_id = u.id 
                          ORDER BY kb.id DESC");
    return $stmt->fetchAll();
}

/**
 * Get claims by user ID
 */
function getKlaimByUser($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT kb.*, lt.nama_barang, lt.lokasi, lt.foto as barang_foto 
                          FROM klaim_barang kb 
                          LEFT JOIN laporan_temuan lt ON kb.laporan_id = lt.id 
                          WHERE kb.claimant_id = ? 
                          ORDER BY kb.id DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Get claim by ID
 */
function getKlaimById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT kb.*, lt.nama_barang, lt.status as laporan_status, u.nama as claimant_nama 
                          FROM klaim_barang kb 
                          LEFT JOIN laporan_temuan lt ON kb.laporan_id = lt.id 
                          LEFT JOIN user u ON kb.claimant_id = u.id 
                          WHERE kb.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Process claim (approve/reject)
 */
function processKlaim($id, $status) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE klaim_barang SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    $klaim = getKlaimById($id);
    if ($klaim) {
        if ($status === 'Diterima') {
            $stmt2 = $db->prepare("UPDATE laporan_temuan SET status = 'Selesai' WHERE id = ?");
            $stmt2->execute([$klaim['laporan_id']]);
        } else if ($status === 'Ditolak') {
            $stmt2 = $db->prepare("UPDATE laporan_temuan SET status = 'Ditemukan' WHERE id = ?");
            $stmt2->execute([$klaim['laporan_id']]);
        }
    }

    logActivity("Klaim #{$id} diproses dengan status: {$status}");
    return true;
}

/**
 * Cancel claim
 */
function cancelKlaim($id) {
    $db = getDB();
    $klaim = getKlaimById($id);

    if ($klaim) {
        $stmt = $db->prepare("UPDATE laporan_temuan SET status = 'Ditemukan' WHERE id = ?");
        $stmt->execute([$klaim['laporan_id']]);
    }

    $stmt = $db->prepare("DELETE FROM klaim_barang WHERE id = ?");
    $stmt->execute([$id]);

    logActivity("Klaim #{$id} dibatalkan");
    return true;
}

// ============================================================
// LOG AKTIVITAS FUNCTIONS
// ============================================================

/**
 * Log activity
 */
function logActivity($pesan) {
    $db = getDB();
    $waktu = date('Y-m-d H:i:s');
    $stmt = $db->prepare("INSERT INTO log_aktivitas (waktu, pesan) VALUES (?, ?)");
    $stmt->execute([$waktu, $pesan]);
}

/**
 * Get all activity logs
 */
function getAllLogAktivitas($limit = 100) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM log_aktivitas ORDER BY id DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// ============================================================
// DASHBOARD & STATISTICS FUNCTIONS
// ============================================================

/**
 * Get dashboard statistics
 */
function getDashboardStats() {
    $db = getDB();

    $stats = [];

    // Total users
    $stmt = $db->query("SELECT COUNT(*) as total FROM user");
    $stats['total_users'] = $stmt->fetch()['total'];

    // Total lost reports
    $stmt = $db->query("SELECT COUNT(*) as total FROM laporan_hilang");
    $stats['total_hilang'] = $stmt->fetch()['total'];

    // Total found reports
    $stmt = $db->query("SELECT COUNT(*) as total FROM laporan_temuan");
    $stats['total_temuan'] = $stmt->fetch()['total'];

    // Total claims
    $stmt = $db->query("SELECT COUNT(*) as total FROM klaim_barang");
    $stats['total_klaim'] = $stmt->fetch()['total'];

    // Pending claims
    $stmt = $db->query("SELECT COUNT(*) as total FROM klaim_barang WHERE status = 'Menunggu Verifikasi'");
    $stats['pending_klaim'] = $stmt->fetch()['total'];

    // Completed reports
    $stmt = $db->query("SELECT COUNT(*) as total FROM laporan_hilang WHERE status = 'Selesai'");
    $stats['selesai_hilang'] = $stmt->fetch()['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM laporan_temuan WHERE status = 'Selesai'");
    $stats['selesai_temuan'] = $stmt->fetch()['total'];

    return $stats;
}

/**
 * Get user dashboard statistics
 */
function getUserDashboardStats($userId) {
    $db = getDB();

    $stats = [];

    // Total lost reports by user
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM laporan_hilang WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['total_hilang'] = $stmt->fetch()['total'];

    // Total found reports by user
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM laporan_temuan WHERE penemu_id = ?");
    $stmt->execute([$userId]);
    $stats['total_temuan'] = $stmt->fetch()['total'];

    // Total claims by user
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM klaim_barang WHERE claimant_id = ?");
    $stmt->execute([$userId]);
    $stats['total_klaim'] = $stmt->fetch()['total'];

    // Active lost reports
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM laporan_hilang WHERE user_id = ? AND status = 'Hilang'");
    $stmt->execute([$userId]);
    $stats['active_hilang'] = $stmt->fetch()['total'];

    // Active found reports
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM laporan_temuan WHERE penemu_id = ? AND status = 'Ditemukan'");
    $stmt->execute([$userId]);
    $stats['active_temuan'] = $stmt->fetch()['total'];

    return $stats;
}

/**
 * Get recent reports for homepage
 */
function getRecentReports($limit = 12) {
    $db = getDB();

    $sql = "(SELECT 'hilang' as tipe, lh.id, lh.nama_barang, lh.lokasi, lh.tanggal, lh.status, lh.deskripsi, lh.kontak, lh.foto, lh.user_id, lh.kategori_id, k.nama as kategori_nama, k.ikon as kategori_ikon, u.nama as pelapor_nama, u.tipe_user as pelapor_tipe 
            FROM laporan_hilang lh 
            LEFT JOIN kategori k ON lh.kategori_id = k.id 
            LEFT JOIN user u ON lh.user_id = u.id 
            WHERE lh.status != 'Menunggu Verifikasi')
            UNION ALL
            (SELECT 'temuan' as tipe, lt.id, lt.nama_barang, lt.lokasi, lt.tanggal, lt.status, lt.deskripsi, lt.kontak, lt.foto, lt.penemu_id as user_id, lt.kategori_id, k.nama as kategori_nama, k.ikon as kategori_ikon, u.nama as pelapor_nama, u.tipe_user as pelapor_tipe 
            FROM laporan_temuan lt 
            LEFT JOIN kategori k ON lt.kategori_id = k.id 
            LEFT JOIN user u ON lt.penemu_id = u.id 
            WHERE lt.status != 'Menunggu Verifikasi')
            ORDER BY id DESC LIMIT ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Search reports
 */
function searchReports($keyword = '', $kategoriId = '', $lokasi = '', $status = '') {
    $db = getDB();

    $params = [];
    $whereConditions = [];

    $sql = "(SELECT 'hilang' as tipe, lh.id, lh.nama_barang, lh.lokasi, lh.tanggal, lh.status, lh.deskripsi, lh.kontak, lh.foto, lh.user_id, lh.kategori_id, k.nama as kategori_nama, k.ikon as kategori_ikon, u.nama as pelapor_nama, u.tipe_user as pelapor_tipe 
            FROM laporan_hilang lh 
            LEFT JOIN kategori k ON lh.kategori_id = k.id 
            LEFT JOIN user u ON lh.user_id = u.id 
            WHERE lh.status != 'Menunggu Verifikasi')";

    if ($keyword) {
        $sql .= " AND (lh.nama_barang LIKE ? OR lh.deskripsi LIKE ?)";
        $params[] = "%{$keyword}%";
        $params[] = "%{$keyword}%";
    }
    if ($kategoriId) {
        $sql .= " AND lh.kategori_id = ?";
        $params[] = $kategoriId;
    }
    if ($lokasi) {
        $sql .= " AND lh.lokasi = ?";
        $params[] = $lokasi;
    }
    if ($status) {
        $sql .= " AND lh.status = ?";
        $params[] = $status;
    }

    $sql .= " UNION ALL 
              (SELECT 'temuan' as tipe, lt.id, lt.nama_barang, lt.lokasi, lt.tanggal, lt.status, lt.deskripsi, lt.kontak, lt.foto, lt.penemu_id as user_id, lt.kategori_id, k.nama as kategori_nama, k.ikon as kategori_ikon, u.nama as pelapor_nama, u.tipe_user as pelapor_tipe 
              FROM laporan_temuan lt 
              LEFT JOIN kategori k ON lt.kategori_id = k.id 
              LEFT JOIN user u ON lt.penemu_id = u.id 
              WHERE lt.status != 'Menunggu Verifikasi')";

    if ($keyword) {
        $sql .= " AND (lt.nama_barang LIKE ? OR lt.deskripsi LIKE ?)";
        $params[] = "%{$keyword}%";
        $params[] = "%{$keyword}%";
    }
    if ($kategoriId) {
        $sql .= " AND lt.kategori_id = ?";
        $params[] = $kategoriId;
    }
    if ($lokasi) {
        $sql .= " AND lt.lokasi = ?";
        $params[] = $lokasi;
    }
    if ($status) {
        $sql .= " AND lt.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}