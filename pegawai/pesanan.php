<?php
include '../config/koneksi.php';

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'pegawai') {
    header("Location: ../login.php");
    exit;
}

// Cek apakah pegawai sudah login
$pegawai_id = $_SESSION['user']['id'];

// Cek apakah form update status disubmit
if (isset($_POST['update_status'])) {
    $pesanan_id = $_POST['pesanan_id'];
    $status = $_POST['status_pengantaran'];
    mysqli_query($conn, "UPDATE pengantaran SET status_pengantaran = '$status' WHERE pesanan_id = $pesanan_id AND pegawai_id = $pegawai_id");

    // Sinkronkan juga ke tabel pesanan
    if ($status == 'dikirim') {
        mysqli_query($conn, "UPDATE pesanan SET status = 'diproses' WHERE id = $pesanan_id");
    } elseif ($status == 'selesai') {
        mysqli_query($conn, "UPDATE pesanan SET status = 'selesai' WHERE id = $pesanan_id");
    }
}

$pesanan = mysqli_query($conn, "SELECT p.pesanan_id, u.username, p.status_pengantaran FROM pengantaran p JOIN pesanan ps ON p.pesanan_id = ps.id JOIN users u ON ps.pembeli_id = u.id WHERE p.pegawai_id = $pegawai_id");
?>

<?php include 'dashboard.php'; ?>

<div class="max-w-4xl mx-auto mt-10">
    <h2 class="text-xl font-bold mb-4">Daftar Pengantaran</h2>
    <table class="w-full border">
        <thead class="bg-amber-100">
            <tr>
                <th class="p-2 border border-gray-200">ID Pesanan</th>
                <th class="p-2 border border-gray-200">Pembeli</th>
                <th class="p-2 border border-gray-200">Status</th>
                <th class="p-2 border border-gray-200">Update</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = mysqli_fetch_assoc($pesanan)) { ?>
                <tr class="border-b">
                    <td class="p-2 border border-gray-200"><?= $p['pesanan_id'] ?></td>
                    <td class="p-2 border border-gray-200"><?= $p['username'] ?></td>
                    <td class="p-2 border border-gray-200"><?= $p['status_pengantaran'] ?></td>
                    <td class="p-2 border border-gray-200">
                        <form method="post">
                            <input type="hidden" name="pesanan_id" value="<?= $p['pesanan_id'] ?>">
                            <select name="status_pengantaran" class="border border-amber-500 rounded px-2">
                                <option value="belum" <?= $p['status_pengantaran'] == 'belum' ? 'selected' : '' ?>>Belum</option>
                                <option value="dikirim" <?= $p['status_pengantaran'] == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                <option value="selesai" <?= $p['status_pengantaran'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                            <button type="submit" name="update_status" class="bg-amber-500 text-white px-3 py-1 cursor-pointer rounded">Update</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>