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

<?php include 'dashboard.php'; ?>

<div class="max-w-4xl mx-auto mt-10 p-4">
    <h2 class="text-xl font-bold mb-4">Daftar Pengantaran</h2>

    <?php if (mysqli_num_rows($daftar_pengantaran) == 0) { ?>
        <p class="text-gray-600">Anda tidak memiliki pesanan yang belum selesai untuk diantar.</p>
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
            <div class="bg-white rounded shadow-md p-4 mb-6 border border-gray-200">
                <div class="flex justify-between items-center mb-3 pb-2 border-b">
                    <h3 class="text-lg font-semibold text-stone-800">Pesanan ID: <?= $dp['pesanan_id'] ?></h3>
                    <span class="text-gray-600 text-sm">Pembeli: <?= $dp['nama_pembeli'] ?></span>
                </div>
                <div class="mb-3">
                    <p class="text-gray-700">Alamat Penerima: <span class="font-medium"><?= htmlspecialchars($dp['alamat']) ?></span></p>
                    <p class="text-gray-700">Tanggal Pesanan: <span class="font-medium"><?= date('d M Y H:i', strtotime($dp['tanggal'])) ?></span></p>
                    <p class="text-gray-700">Status Pesanan (global): <span class="font-medium text-blue-600"><?= ucfirst($dp['status_pesanan']) ?></span></p>
                    <p class="text-gray-700">Status Pengantaran (Anda): <span class="font-medium text-green-600"><?= ucfirst($dp['status_pengantaran']) ?></span></p>
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
                        <?php while ($dbp = mysqli_fetch_assoc($detail_barang_pesanan)) {
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
                <div class="text-right font-bold text-lg text-blue-700 mb-4">
                    Total Pesanan: Rp <?= number_format($total_pesanan_ini) ?>
                </div>

                <form method="post" class="text-right">
                    <input type="hidden" name="pesanan_id" value="<?= $dp['pesanan_id'] ?>">
                    <select name="status_pengantaran" class="border border-amber-500 rounded px-2 py-1 mr-2">
                        <option value="belum" <?= $dp['status_pengantaran'] == 'belum' ? 'selected' : '' ?>>Belum</option>
                        <option value="dikirim" <?= $dp['status_pengantaran'] == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                        <option value="selesai" <?= $dp['status_pengantaran'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    </select>
                    <button type="submit" name="update_status" class="bg-amber-500 text-white px-3 py-1.5 cursor-pointer rounded hover:bg-amber-600">Update Status</button>
                </form>
            </div>
        <?php } ?>
    <?php } ?>
</div>