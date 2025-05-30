<?php
include '../config/koneksi.php';

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil semua pesanan untuk laporan
// Menambahkan u.alamat untuk menampilkan Alamat Penerima
$laporan_pesanan = mysqli_query($conn, "SELECT ps.id AS pesanan_id, ps.tanggal, u.username AS nama_pembeli, u.alamat, ps.status, p.status_pengantaran
                                       FROM pesanan ps
                                       JOIN users u ON ps.pembeli_id = u.id
                                       LEFT JOIN pengantaran p ON ps.id = p.pesanan_id
                                       ORDER BY ps.tanggal DESC");

$grand_total_penjualan = 0; // Untuk menghitung total penjualan keseluruhan
?>

<?php include 'dashboard.php'; ?>

<div class="max-w-4xl mx-auto mt-10 p-4">
    <h2 class="text-2xl font-bold mb-6">Laporan Penjualan</h2>

    <?php if (mysqli_num_rows($laporan_pesanan) == 0) { ?>
        <p class="text-gray-600">Belum ada data penjualan.</p>
    <?php } else { ?>
        <?php while ($lp = mysqli_fetch_assoc($laporan_pesanan)) {
            $pesanan_id = $lp['pesanan_id'];
            // Ambil detail barang untuk setiap pesanan
            $detail_barang_laporan = mysqli_query($conn, "SELECT dp.jumlah, dp.total AS subtotal_item, b.nama, b.harga
                                                         FROM detail_pesanan dp
                                                         JOIN barang b ON dp.barang_id = b.id
                                                         WHERE dp.pesanan_id = $pesanan_id");
            $total_pesanan_ini = 0;
        ?>
            <div class="bg-white rounded shadow-md p-4 mb-6 border border-gray-200">
                <div class="flex justify-between items-center mb-3 pb-2 border-b">
                    <h3 class="text-lg font-semibold text-stone-800">Pesanan ID: <?= $lp['pesanan_id'] ?></h3>
                    <span class="text-gray-600 text-sm">Pembeli: <?= $lp['nama_pembeli'] ?></span>
                </div>
                <div class="mb-3">
                    <p class="text-gray-700">Alamat Penerima: <span class="font-medium"><?= htmlspecialchars($lp['alamat']) ?></span></p>
                    <p class="text-gray-700">Tanggal Pesanan: <span class="font-medium"><?= date('d M Y H:i', strtotime($lp['tanggal'])) ?></span></p>
                    <p class="text-gray-700">Status Pesanan: <span class="font-medium text-blue-600"><?= ucfirst($lp['status']) ?></span></p>
                    <p class="text-gray-700">Status Pengantaran: <span class="font-medium text-green-600"><?= ucfirst($lp['status_pengantaran'] ?? 'Belum ada') ?></span></p>
                </div>

                <table class="w-full text-sm mb-4">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-2 border text-left font-normal text-gray-600">Barang</th>
                            <th class="p-2 border text-left font-normal text-gray-600">Harga Satuan</th>
                            <th class="p-2 border text-left font-normal text-gray-600">Jumlah</th>
                            <th class="p-2 border text-left font-normal text-gray-600">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($dbp = mysqli_fetch_assoc($detail_barang_laporan)) {
                            $total_pesanan_ini += $dbp['subtotal_item'];
                        ?>
                            <tr class="border-b">
                                <td class="p-2 border"><?= $dbp['nama'] ?></td>
                                <td class="p-2 border">Rp <?= number_format($dbp['harga']) ?></td>
                                <td class="p-2 border"><?= $dbp['jumlah'] ?></td>
                                <td class="p-2 border">Rp <?= number_format($dbp['subtotal_item']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="text-right font-bold text-lg text-blue-700">
                    Total Pesanan: Rp <?= number_format($total_pesanan_ini) ?>
                </div>
            </div>
        <?php
            $grand_total_penjualan += $total_pesanan_ini;
        } ?>
        <div class="text-right mt-6 p-4 bg-amber-100 rounded">
            <h3 class="text-xl font-bold text-stone-800">Grand Total Penjualan: Rp <?= number_format($grand_total_penjualan) ?></h3>
        </div>
    <?php } ?>
</div>