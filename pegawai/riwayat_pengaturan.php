<?php
include '../config/koneksi.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'pegawai') {
    header("Location: ../login.php");
    exit;
}

$pegawai_id = $_SESSION['user']['id'];
$riwayat = mysqli_query($conn, "SELECT p.pesanan_id, u.username, p.status_pengantaran FROM pengantaran p JOIN pesanan ps ON p.pesanan_id = ps.id JOIN users u ON ps.pembeli_id = u.id WHERE p.pegawai_id = $pegawai_id AND p.status_pengantaran = 'selesai'");
?>

<?php include 'dashboard.php'; ?>
<div class="max-w-4xl mx-auto mt-10">
    <h2 class="text-xl font-bold mb-4">Riwayat Pengantaran</h2>
    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th>ID Pesanan</th>
                <th>Pembeli</th>
                <th>Status Pengantaran</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = mysqli_fetch_assoc($riwayat)) { ?>
                <tr class="border-b">
                    <td class="p-2 border"><?= $r['pesanan_id'] ?></td>
                    <td class="p-2 border"><?= $r['username'] ?></td>
                    <td class="p-2 border"><?= $r['status_pengantaran'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>