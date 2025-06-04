<?php
include '../config/koneksi.php';

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'pegawai') {
    header("Location: ../auth/login.php"); // Mengarahkan ke login.php di folder auth
    exit;
}

// Cek apakah pegawai sudah login
$pegawai_id = $_SESSION['user']['id'];

// Cek apakah form update status disubmit
if (isset($_POST['update_status'])) {
    $pesanan_id = $_POST['pesanan_id'];
    $status = $_POST['status_pengantaran'];

    // Update status di tabel pengantaran
    mysqli_query($conn, "UPDATE pengantaran SET status_pengantaran = '$status' WHERE pesanan_id = $pesanan_id AND pegawai_id = $pegawai_id");

    // Sinkronkan juga ke tabel pesanan berdasarkan status pengantaran
    if ($status == 'dikirim') {
        mysqli_query($conn, "UPDATE pesanan SET status = 'diproses' WHERE id = $pesanan_id");
    } elseif ($status == 'selesai') {
        mysqli_query($conn, "UPDATE pesanan SET status = 'selesai' WHERE id = $pesanan_id");
    } elseif ($status == 'belum') { // Jika pegawai mengembalikan ke status 'belum', pesanan juga kembali 'menunggu'
        mysqli_query($conn, "UPDATE pesanan SET status = 'menunggu' WHERE id = $pesanan_id");
    }
    header("Location: pesanan.php"); // Redirect untuk menghindari resubmission form
    exit();
}

// Mengambil pesanan yang perlu diantar oleh pegawai ini
// Menambahkan kolom u.alamat untuk mengambil alamat pembeli
$daftar_pengantaran = mysqli_query($conn, "SELECT p.pesanan_id, u.username AS nama_pembeli, u.alamat, p.status_pengantaran, ps.tanggal, ps.status AS status_pesanan
                                           FROM pengantaran p
                                           JOIN pesanan ps ON p.pesanan_id = ps.id
                                           JOIN users u ON ps.pembeli_id = u.id
                                           WHERE p.pegawai_id = $pegawai_id AND p.status_pengantaran != 'selesai'
                                           ORDER BY ps.tanggal DESC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Daftar Pengantaran</title>
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
            <a href="../auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out">Logout</a>
        </div>
    </nav>

    <div class="min-h-screen pt-20 pb-10">
        <div class="bg-amber-950/90 p-6 md:p-12 rounded-xl shadow-lg w-full max-w-5xl mx-auto mt-10 text-amber-50">
            <h2 class="text-3xl font-extrabold text-center mb-8">Daftar Pengantaran</h2>

            <?php if (mysqli_num_rows($daftar_pengantaran) == 0) { ?>
                <div class="bg-amber-900 p-6 rounded-lg text-center text-amber-200">
                    <i class="fas fa-box-open text-5xl mb-4 text-amber-400"></i>
                    <p class="text-lg">Anda tidak memiliki pesanan yang perlu diantar.</p>
                </div>
            <?php } else { ?>
                <?php while ($dp = mysqli_fetch_assoc($daftar_pengantaran)) {
                    $pesanan_id = $dp['pesanan_id'];
                    // Mengambil detail barang untuk setiap pesanan
                    $detail_barang_pesanan = mysqli_query($conn, "SELECT dp.jumlah, dp.total AS subtotal_item, b.nama, b.harga
                                                        FROM detail_pesanan dp
                                                        JOIN barang b ON dp.barang_id = b.id
                                                        WHERE dp.pesanan_id = $pesanan_id");
                    $total_pesanan_ini = 0;
                ?>
                    <div class="bg-amber-900 rounded-xl shadow-md p-6 mb-6 border border-amber-800">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-4 border-b border-amber-800">
                            <h3 class="text-xl font-bold text-amber-50 mb-2 md:mb-0">Pesanan ID: <span class="font-extrabold text-amber-300">#<?= $dp['pesanan_id'] ?></span></h3>
                            <span class="text-amber-200 text-sm">Pembeli: <span class="font-semibold"><?= $dp['nama_pembeli'] ?></span></span>
                        </div>
                        <div class="mb-4 space-y-2 text-amber-100">
                            <p>Alamat Penerima: <span class="font-medium"><?= htmlspecialchars($dp['alamat']) ?></span></p>
                            <p>Tanggal Pesanan: <span class="font-medium"><?= date('d M Y H:i', strtotime($dp['tanggal'])) ?></span></p>
                            <p>Status Pesanan (global): <span class="font-medium text-blue-400"><?= ucfirst($dp['status_pesanan']) ?></span></p>
                            <p>Status Pengantaran (Anda): <span class="font-medium text-green-400"><?= ucfirst($dp['status_pengantaran']) ?></span></p>
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
                                    <?php while ($dbp = mysqli_fetch_assoc($detail_barang_pesanan)) {
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

                        <form method="post" class="text-right mt-6">
                            <input type="hidden" name="pesanan_id" value="<?= $dp['pesanan_id'] ?>">
                            <select name="status_pengantaran" class="bg-amber-800 border border-amber-600 text-amber-50 px-4 py-2 rounded-md mr-2 focus:outline-none focus:ring-2 focus:ring-amber-500 transition duration-200">
                                <option value="belum" <?= $dp['status_pengantaran'] == 'belum' ? 'selected' : '' ?>>Belum</option>
                                <option value="dikirim" <?= $dp['status_pengantaran'] == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                <option value="selesai" <?= $dp['status_pengantaran'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                            <button type="submit" name="update_status" class="bg-amber-500 text-white font-semibold px-5 py-2.5 rounded-md hover:bg-amber-600 transition duration-300 ease-in-out">Update Status</button>
                        </form>
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