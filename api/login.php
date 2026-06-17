<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identitas = $_POST['identitas'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = loginUser($identitas, $password);

    if ($result['success']) {
        // ✅ SET SESSION DATA USER SETELAH LOGIN BERHASIL
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['nama'] = $result['user']['nama'];
        $_SESSION['email'] = $result['user']['email'];
        $_SESSION['phone'] = $result['user']['phone'] ?? '';
        $_SESSION['role'] = $result['user']['tipe_user'];
        $_SESSION['identitas'] = $result['user']['identitas'];

        setFlash($result['message'], 'success');

        if ($result['user']['tipe_user'] === 'admin') {
            header('Location: ' . BASE_URL . 'pages/admin.php');
        } else {
            header('Location: ' . BASE_URL . 'pages/dashboard.php');
        }
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