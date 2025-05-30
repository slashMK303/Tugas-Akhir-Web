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

<?php include 'dashboard.php'; ?>

<div class="max-w-4xl mx-auto mt-10 p-4">
    <h2 class="text-xl font-bold mb-4">Riwayat Pembelian</h2>

    <?php if (mysqli_num_rows($riwayat_pesanan) == 0) { ?>
        <p class="text-gray-600">Anda belum memiliki riwayat pembelian.</p>
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
            <div class="bg-white rounded shadow-md p-4 mb-6 border border-gray-200">
                <div class="flex justify-between items-center mb-3 pb-2 border-b">
                    <h3 class="text-lg font-semibold text-stone-800">Pesanan ID: <?= $rp['pesanan_id'] ?></h3>
                    <span class="text-gray-600 text-sm">Tanggal: <?= date('d M Y H:i', strtotime($rp['tanggal'])) ?></span>
                </div>
                <div class="mb-3">
                    <p class="text-gray-700">Alamat Penerima: <span class="font-medium"><?= htmlspecialchars($rp['alamat']) ?></span></p>
                    <p class="text-gray-700">Status Pesanan: <span class="font-medium text-blue-600"><?= ucfirst($rp['status']) ?></span></p>
                    <p class="text-gray-700">Status Pengantaran: <span class="font-medium text-green-600"><?= ucfirst($rp['status_pengantaran'] ?? 'Belum ada') ?></span></p>
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
                        <?php while ($dbp = mysqli_fetch_assoc($detail_barang_riwayat)) {
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
        <?php } ?>
    <?php } ?>
</div>