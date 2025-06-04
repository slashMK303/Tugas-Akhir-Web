<?php
include '../config/koneksi.php';

// Cek apakah user sudah login
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pembeli') {
    header("Location: ../auth/login.php");
    exit();
}
$pembeli_id = $_SESSION['user']['id'];

// Mengambil data pesanan utama untuk pembeli
// Menambahkan u.alamat untuk menampilkan Alamat Penerima
$riwayat_pesanan = mysqli_query($conn, "SELECT ps.id AS pesanan_id, ps.tanggal, ps.status, p.status_pengantaran, u.alamat
                                        FROM pesanan ps
                                        LEFT JOIN pengantaran p ON ps.id = p.pesanan_id
                                        JOIN users u ON ps.pembeli_id = u.id
                                        WHERE ps.pembeli_id = $pembeli_id
                                        ORDER BY ps.tanggal DESC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Riwayat Pembelian</title>
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
            <a href="dashboard.php" class="text-amber-200 hover:text-amber-50 transition duration-300 ease-in-out">Dashboard</a>
            <a href="keranjang.php" class="text-amber-200 hover:text-amber-50 transition duration-300 ease-in-out">Keranjang</a>
            <a href="../auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out">Logout</a>
        </div>
    </nav>

    <div class="min-h-screen pt-20 pb-10">
        <div class="bg-amber-950/90 p-6 md:p-12 rounded-xl shadow-lg w-full max-w-5xl mx-auto mt-10 text-amber-50">
            <h2 class="text-3xl font-extrabold text-center mb-8">Riwayat Pembelian</h2>

            <?php if (mysqli_num_rows($riwayat_pesanan) == 0) { ?>
                <div class="bg-amber-900 p-6 rounded-lg text-center text-amber-200">
                    <i class="fas fa-clipboard-list text-5xl mb-4 text-amber-400"></i>
                    <p class="text-lg">Anda belum memiliki riwayat pembelian.</p>
                    <a href="../index.php" class="mt-4 inline-block bg-amber-500 text-white px-6 py-3 rounded-md hover:bg-amber-600 transition duration-300 ease-in-out">Mulai Belanja</a>
                </div>
            <?php } else { ?>
                <?php while ($rp = mysqli_fetch_assoc($riwayat_pesanan)) {
                    $pesanan_id = $rp['pesanan_id'];
                    // Mengambil detail barang untuk setiap pesanan
                    $detail_barang_riwayat = mysqli_query($conn, "SELECT dp.jumlah, dp.total AS subtotal_item, b.nama, b.harga
                                                                    FROM detail_pesanan dp
                                                                    JOIN barang b ON dp.barang_id = b.id
                                                                    WHERE dp.pesanan_id = $pesanan_id");
                    $total_pesanan_ini = 0;
                ?>
                    <div class="bg-amber-900 rounded-xl shadow-md p-6 mb-6 border border-amber-800">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-4 border-b border-amber-800">
                            <h3 class="text-xl font-bold text-amber-50 mb-2 md:mb-0">Pesanan ID: <span class="font-extrabold text-amber-300">#<?= $rp['pesanan_id'] ?></span></h3>
                            <span class="text-amber-200 text-sm">Tanggal Pesanan: <span class="font-semibold"><?= date('d M Y H:i', strtotime($rp['tanggal'])) ?></span></span>
                        </div>
                        <div class="mb-4 space-y-2 text-amber-100">
                            <p>Alamat Penerima: <span class="font-medium"><?= htmlspecialchars($rp['alamat']) ?></span></p>
                            <p>Status Pesanan: <span class="font-medium text-blue-400"><?= ucfirst($rp['status']) ?></span></p>
                            <p>Status Pengantaran: <span class="font-medium text-green-400"><?= ucfirst($rp['status_pengantaran'] ?? 'Belum ada') ?></span></p>
                        </div>

                        <div class="overflow-x-auto rounded-lg shadow-inner mb-4">
                            <table class="min-w-full text-sm bg-amber-800">
                                <thead class="bg-amber-700 text-amber-100 uppercase text-xs leading-normal">
                                    <tr>
                                        <th class="py-3 px-6 text-left border border-amber-600">Barang</th>
                                        <th class="py-3 px-6 text-left border border-amber-600">Harga Satuan</th>
                                        <th class="py-3 px-6 text-left border border-amber-600">Jumlah</th>
                                        <th class="py-3 px-6 text-left border border-amber-600">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="text-amber-50">
                                    <?php while ($dbp = mysqli_fetch_assoc($detail_barang_riwayat)) {
                                        $total_pesanan_ini += $dbp['subtotal_item'];
                                    ?>
                                        <tr class="border-b border-amber-700">
                                            <td class="py-3 px-6 border border-amber-700"><?= $dbp['nama'] ?></td>
                                            <td class="py-3 px-6 border border-amber-700">Rp <?= number_format($dbp['harga'], 0, ',', '.') ?></td>
                                            <td class="py-3 px-6 border border-amber-700"><?= $dbp['jumlah'] ?></td>
                                            <td class="py-3 px-6 border border-amber-700">Rp <?= number_format($dbp['subtotal_item'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-right font-bold text-xl text-amber-300 border-t border-amber-700 pt-4">
                            Total Pesanan: Rp <?= number_format($total_pesanan_ini, 0, ',', '.') ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
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