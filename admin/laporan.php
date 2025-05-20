<?php
include '../config/koneksi.php';

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Cek apakah form tambah barang disubmit
$laporan = mysqli_query($conn, "SELECT ps.id, ps.tanggal, u.username, dp.total FROM pesanan ps JOIN users u ON ps.pembeli_id = u.id JOIN detail_pesanan dp ON dp.pesanan_id = ps.id");
?>

<?php include 'dashboard.php'; ?>

<div class="max-w-4xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6">Laporan Penjualan</h2>

    <table class="w-full border text-sm">
        <thead class="bg-amber-100">
            <tr>
                <th class="p-2 border border-gray-200">ID</th>
                <th class="p-2 border border-gray-200">Tanggal</th>
                <th class="p-2 border border-gray-200">Pembeli</th>
                <th class="p-2 border border-gray-200">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = mysqli_fetch_assoc($laporan)) { ?>
                <tr class="border-b">
                    <td class="p-2 border border-gray-200"><?= $r['id'] ?></td>
                    <td class="p-2 border border-gray-200"><?= $r['tanggal'] ?></td>
                    <td class="p-2 border border-gray-200"><?= $r['username'] ?></td>
                    <td class="p-2 border border-gray-200">Rp <?= number_format($r['total']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>