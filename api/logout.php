<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

logoutUser();
setFlash('Anda telah keluar dari sistem.', 'info');
header('Location: ' . BASE_URL);
exit();
?>