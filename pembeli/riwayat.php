<?php
include '../config/koneksi.php';

// Cek apakah user sudah login
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pembeli') {
    header("Location: ../auth/login.php");
    exit();
}
$pembeli_id = $_SESSION['user']['id'];
$riwayat = mysqli_query($conn, "SELECT ps.id, ps.tanggal, ps.status, dp.barang_id, b.nama, SUM(dp.total) AS total FROM pesanan ps JOIN detail_pesanan dp ON ps.id = dp.pesanan_id JOIN barang b ON dp.barang_id = b.id WHERE ps.pembeli_id = $pembeli_id GROUP BY ps.id");
?>

<?php include 'dashboard.php'; ?>

<div class="max-w-4xl mx-auto mt-10">
    <h2 class="text-xl font-bold mb-4">Riwayat Pembelian</h2>
    <table class="w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th>ID Pesanan</th>
                <th>Nama Barang</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = mysqli_fetch_assoc($riwayat)) { ?>
                <tr class="border-b">
                    <td class="p-2 border"><?= $r['id'] ?></td>
                    <td class="p-2 border"><?= $r['nama'] ?></td>
                    <td class="p-2 border"><?= $r['tanggal'] ?></td>
                    <td class="p-2 border"><?= $r['status'] ?></td>
                    <td class="p-2 border">Rp <?= number_format($r['total']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>