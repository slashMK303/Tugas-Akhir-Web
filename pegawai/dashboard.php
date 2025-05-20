<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pegawai') {
    header("Location: ../auth/login.php");
    exit();
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Pegawai</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-amber-50">

    <div class="max-w-4xl mx-auto mt-10">
        <h1 class="text-2xl font-bold mb-6">Dashboard Pegawai</h1>
        <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
            <p class="text-sm mb-6">Halo, <?= $_SESSION['user']['username'] ?>!</p>
            <a href="../auth/logout.php" class="p-2 bg-red-500 text-amber-50 rounded shadow hover:bg-red-700 text-center justify-self-end">Logout</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
            <a href="pesanan.php" class="p-4 bg-amber-500 text-amber-50 rounded shadow hover:bg-amber-600 text-center col-span-3">Daftar Pengantaran</a>
            <a href="riwayat_pengaturan.php" class="p-4 bg-amber-500 text-amber-50 rounded shadow hover:bg-amber-600 text-center col-span-3">Riwayat Pengantaran</a>
        </div>

</body>

</html>