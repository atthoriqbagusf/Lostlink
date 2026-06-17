<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    setFlash('Akses ditolak.', 'danger');
    header('Location: ' . BASE_URL);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'approve-user':
        toggleUserStatus(intval($_GET['id'] ?? 0));
        setFlash('Status user diperbarui.', 'success');
        break;

    case 'delete-user':
        deleteUser(intval($_GET['id'] ?? 0));
        setFlash('User dihapus.', 'success');
        break;

    case 'approve-laporan-hilang':
        updateStatusLaporanHilang(intval($_GET['id'] ?? 0), 'Hilang');
        setFlash('Laporan hilang disetujui.', 'success');
        break;

    case 'approve-laporan-temuan':
        updateStatusLaporanTemuan(intval($_GET['id'] ?? 0), 'Ditemukan');
        setFlash('Laporan temuan disetujui.', 'success');
        break;

    case 'reject-laporan-hilang':
        deleteLaporanHilang(intval($_GET['id'] ?? 0));
        setFlash('Laporan ditolak dan dihapus.', 'success');
        break;

    case 'reject-laporan-temuan':
        deleteLaporanTemuan(intval($_GET['id'] ?? 0));
        setFlash('Laporan ditolak dan dihapus.', 'success');
        break;

    case 'process-klaim':
        processKlaim(intval($_GET['id'] ?? 0), $_GET['status'] ?? '');
        setFlash('Klaim diproses.', 'success');
        break;

    case 'add-kategori':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            addKategori(sanitize($_POST['nama'] ?? ''), sanitize($_POST['ikon'] ?? 'fa-tag'));
            setFlash('Kategori ditambahkan.', 'success');
        }
        break;

    case 'delete-kategori':
        deleteKategori(intval($_GET['id'] ?? 0));
        setFlash('Kategori dihapus.', 'success');
        break;
}

header('Location: ' . BASE_URL . 'pages/admin.php');
exit();
?>