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

<!DOCTYPE html>
<html>

<head>
    <title>Laporan Penjualan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50">

    <?php include 'dashboard.php'; ?>

    <div class="max-w-4xl mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-6">Laporan Penjualan</h2>

        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Tanggal</th>
                    <th class="p-2 border">Pembeli</th>
                    <th class="p-2 border">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = mysqli_fetch_assoc($laporan)) { ?>
                    <tr class="border-b">
                        <td class="p-2 border"><?= $r['id'] ?></td>
                        <td class="p-2 border"><?= $r['tanggal'] ?></td>
                        <td class="p-2 border"><?= $r['username'] ?></td>
                        <td class="p-2 border">Rp <?= number_format($r['total']) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</body>

</html>