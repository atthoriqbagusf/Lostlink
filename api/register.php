<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama'] ?? '');
    $identitas = sanitize($_POST['identitas'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $tipe_user = $_POST['tipe_user'] ?? 'umum';

    $result = registerUser($nama, $identitas, $email, $password, $phone, $tipe_user);

    if ($result['success']) {
        // Auto login after registration
        loginUser($email, $password);
        setFlash('Pendaftaran berhasil! Selamat datang.', 'success');
        header('Location: ' . BASE_URL . 'pages/dashboard.php');
        exit();
    } else {
        setFlash($result['message'], 'danger');
        header('Location: ' . BASE_URL);
        exit();
    }
} else {
    // Jika diakses langsung via GET, redirect ke home
    header('Location: ' . BASE_URL);
    exit();
}
?>