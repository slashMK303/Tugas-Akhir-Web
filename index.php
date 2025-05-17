<?php
session_start();

if (!isset($_SESSION['user'])) {
    // Belum login, tampilkan halaman publik
    include 'lihat_barang.php';
    exit();
}

$role = $_SESSION['user']['role'];

switch ($role) {
    case 'admin':
        header('Location: admin/dashboard.php');
        exit();
    case 'pegawai':
        header('Location: pegawai/dashboard.php');
        exit();
    case 'pembeli':
        header('Location: pembeli/dashboard.php');
        exit();
    default:
        echo "Role tidak dikenali.";
        session_destroy();
        exit();
}
