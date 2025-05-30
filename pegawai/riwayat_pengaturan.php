<?php
include '../config/koneksi.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'pegawai') {
    header("Location: ../auth/login.php");
    exit;
}

$pegawai_id = $_SESSION['user']['id'];

// Mengambil pesanan yang telah diantar oleh pegawai ini
// Menambahkan u.alamat untuk menampilkan Alamat Penerima
$riwayat_pengantaran = mysqli_query($conn, "SELECT p.pesanan_id, u.username AS nama_pembeli, u.alamat, p.status_pengantaran, ps.tanggal
                                           FROM pengantaran p
                                           JOIN pesanan ps ON p.pesanan_id = ps.id
                                           JOIN users u ON ps.pembeli_id = u.id
                                           WHERE p.pegawai_id = $pegawai_id AND p.status_pengantaran = 'selesai'
                                           ORDER BY ps.tanggal DESC");
?>

<?php include 'dashboard.php'; ?>
<div class="max-w-4xl mx-auto mt-10 p-4">
    <h2 class="text-xl font-bold mb-4">Riwayat Pengantaran</h2>

    <?php if (mysqli_num_rows($riwayat_pengantaran) == 0) { ?>
        <p class="text-gray-600">Belum ada riwayat pengantaran yang selesai.</p>
    <?php } else { ?>
        <?php while ($rp = mysqli_fetch_assoc($riwayat_pengantaran)) {
            $pesanan_id = $rp['pesanan_id'];
            // Mengambil detail barang untuk setiap pesanan
            $detail_barang_riwayat_pengantaran = mysqli_query($conn, "SELECT dp.jumlah, dp.total AS subtotal_item, b.nama, b.harga
                                                                    FROM detail_pesanan dp
                                                                    JOIN barang b ON dp.barang_id = b.id
                                                                    WHERE dp.pesanan_id = $pesanan_id");
            $total_pesanan_ini = 0;
        ?>
            <div class="bg-white rounded shadow-md p-4 mb-6 border border-gray-200">
                <div class="flex justify-between items-center mb-3 pb-2 border-b">
                    <h3 class="text-lg font-semibold text-stone-800">Pesanan ID: <?= $rp['pesanan_id'] ?></h3>
                    <span class="text-gray-600 text-sm">Pembeli: <?= $rp['nama_pembeli'] ?></span>
                </div>
                <div class="mb-3">
                    <p class="text-gray-700">Alamat Penerima: <span class="font-medium"><?= htmlspecialchars($rp['alamat']) ?></span></p>
                    <p class="text-gray-700">Tanggal Pesanan: <span class="font-medium"><?= date('d M Y H:i', strtotime($rp['tanggal'])) ?></span></p>
                    <p class="text-gray-700">Status Pengantaran: <span class="font-medium text-green-600"><?= ucfirst($rp['status_pengantaran']) ?></span></p>
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
                        <?php while ($dbp = mysqli_fetch_assoc($detail_barang_riwayat_pengantaran)) {
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