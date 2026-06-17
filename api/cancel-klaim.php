<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL);
    exit();
}

$id = intval($_GET['id'] ?? 0);
$klaim = getKlaimById($id);

if ($klaim && $klaim['claimant_id'] == $_SESSION['user_id']) {
    cancelKlaim($id);
    setFlash('Klaim berhasil dibatalkan.', 'success');
} else {
    setFlash('Akses ditolak.', 'danger');
}

header('Location: ' . BASE_URL . 'pages/dashboard.php?tab=klaim');
exit();
?>