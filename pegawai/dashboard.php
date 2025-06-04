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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-amber-50 font-sans antialiased">

    <nav class="bg-amber-950 shadow-lg p-4 flex justify-between items-center fixed w-full z-20 top-0">
        <h1 class="text-2xl text-amber-50 font-extrabold tracking-wide"><a href="../index.php">Berkah Jaya</a></h1>
        <div class="flex items-center space-x-6">
            <span class="text-amber-200 text-lg">Halo, <span class="font-semibold"><?= $_SESSION['user']['username'] ?></span>!</span>
            <a href="../auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out">Logout</a>
        </div>
    </nav>

    <div class="min-h-screen flex items-center justify-center pt-20 pb-10">
        <div class="bg-amber-950/90 p-6 md:p-12 rounded-xl shadow-lg w-full max-w-2xl space-y-6 text-amber-50">
            <h2 class="text-3xl font-extrabold text-center mb-6">Dashboard Pegawai</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <a href="pesanan.php" class="
                    p-6 bg-amber-800 rounded-lg shadow-md hover:bg-amber-700
                    flex flex-col items-center justify-center text-center
                    transition duration-300 ease-in-out transform hover:scale-105
                ">
                    <i class="fas fa-truck-loading text-4xl mb-3 text-amber-300"></i>
                    <span class="text-xl font-semibold">Daftar Pengantaran</span>
                </a>

                <a href="riwayat_pengaturan.php" class="
                    p-6 bg-amber-800 rounded-lg shadow-md hover:bg-amber-700
                    flex flex-col items-center justify-center text-center
                    transition duration-300 ease-in-out transform hover:scale-105
                ">
                    <i class="fas fa-history text-4xl mb-3 text-amber-300"></i>
                    <span class="text-xl font-semibold">Riwayat Pengantaran</span>
                </a>
            </div>
        </div>
    </div>

    <footer class="bg-amber-950 text-amber-200 text-center p-6 mt-16">
        <p>&copy; <?= date('Y') ?> Berkah Jaya. All rights reserved.</p>
        <div class="flex justify-center space-x-4 mt-3">
            <a href="#" class="hover:text-amber-50 transition duration-300"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="hover:text-amber-50 transition duration-300"><i class="fab fa-instagram"></i></a>
            <a href="#" class="hover:text-amber-50 transition duration-300"><i class="fab fa-twitter"></i></a>
        </div>
    </footer>

</body>

</html>