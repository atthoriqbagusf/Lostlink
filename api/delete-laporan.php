<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL);
    exit();
}

$tipe = $_GET['tipe'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($tipe === 'hilang') {
    $item = getLaporanHilangById($id);
    if ($item && ($item['user_id'] == $_SESSION['user_id'] || isAdmin())) {
        deleteLaporanHilang($id);
        setFlash('Laporan kehilangan berhasil dihapus.', 'success');
    }
} else {
    $item = getLaporanTemuanById($id);
    if ($item && ($item['penemu_id'] == $_SESSION['user_id'] || isAdmin())) {
        deleteLaporanTemuan($id);
        setFlash('Laporan temuan berhasil dihapus.', 'success');
    }
}

header('Location: ' . BASE_URL . 'pages/dashboard.php?tab=laporan');
exit();
?>