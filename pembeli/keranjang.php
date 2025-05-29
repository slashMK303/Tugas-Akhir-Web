<?php
include '../config/koneksi.php'; // Sesuaikan path jika berbeda

// Cek apakah user sudah login dan role adalah pembeli
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pembeli') {
    header("Location: ../auth/login.php");
    exit();
}
$pembeli_id = $_SESSION['user']['id'];

$pesan = ''; // Inisialisasi pesan untuk halaman keranjang

// Logika Checkout Keranjang (Menjadi Pesanan)
if (isset($_POST['checkout'])) {
    // Ambil semua item di keranjang user
    $items_keranjang = mysqli_query($conn, "SELECT k.barang_id, k.jumlah, b.harga, b.stok, b.nama
                                             FROM keranjang k
                                             JOIN barang b ON k.barang_id = b.id
                                             WHERE k.pembeli_id = $pembeli_id");

    if (mysqli_num_rows($items_keranjang) == 0) {
        $pesan = "<div class='p-4 bg-red-100 border border-red-400 text-red-700 mt-4'>Keranjang Anda kosong, tidak bisa checkout!</div>";
    } else {
        $can_checkout = true;
        $pesanan_detail_list = [];

        // Validasi stok untuk semua item di keranjang
        while ($item = mysqli_fetch_assoc($items_keranjang)) {
            if ($item['jumlah'] > $item['stok']) {
                $can_checkout = false;
                $pesan = "<div class='p-4 bg-red-100 border border-red-400 text-red-700 mt-4'>Stok untuk barang '{$item['nama']}' tidak mencukupi ({$item['stok']} tersedia, Anda pesan {$item['jumlah']}). Silakan sesuaikan keranjang Anda.</div>";
                break; // Hentikan loop jika ada yang tidak mencukupi
            }
            $pesanan_detail_list[] = $item;
        }

        if ($can_checkout) {
            // Mulai transaksi
            mysqli_begin_transaction($conn);
            try {
                // Buat pesanan baru
                mysqli_query($conn, "INSERT INTO pesanan (pembeli_id, status, tanggal) VALUES ($pembeli_id, 'menunggu', NOW())");
                $pesanan_id = mysqli_insert_id($conn);

                foreach ($pesanan_detail_list as $item) {
                    $barang_id = $item['barang_id'];
                    $jumlah = $item['jumlah'];
                    $total_item = $item['harga'] * $jumlah;

                    // Masukkan ke detail_pesanan
                    mysqli_query($conn, "INSERT INTO detail_pesanan (pesanan_id, barang_id, jumlah, total) VALUES ($pesanan_id, $barang_id, $jumlah, $total_item)");

                    // Kurangi stok barang
                    mysqli_query($conn, "UPDATE barang SET stok = stok - $jumlah WHERE id = $barang_id");
                }

                // Pilih pegawai secara acak untuk pengantaran
                $pegawai = mysqli_query($conn, "SELECT id FROM users WHERE role = 'pegawai' ORDER BY RAND() LIMIT 1");
                if (mysqli_num_rows($pegawai) > 0) {
                    $data_pegawai = mysqli_fetch_assoc($pegawai);
                    $pegawai_id = $data_pegawai['id'];
                    // Tambahkan ke tabel pengantaran
                    mysqli_query($conn, "INSERT INTO pengantaran (pesanan_id, pegawai_id, status_pengantaran) VALUES ($pesanan_id, $pegawai_id, 'belum')");
                } else {
                    // Handle jika tidak ada pegawai yang tersedia
                    throw new Exception("Tidak ada pegawai pengantar yang tersedia. Pesanan dibatalkan.");
                }


                // Hapus item dari keranjang setelah checkout berhasil
                mysqli_query($conn, "DELETE FROM keranjang WHERE pembeli_id = $pembeli_id");

                mysqli_commit($conn); // Commit transaksi
                $pesan = "<div class='p-4 bg-green-100 border border-green-400 text-green-700 mt-4'>Pesanan Anda berhasil dibuat! Segera diproses.</div>";
            } catch (Exception $e) { // Tangkap custom exception
                mysqli_rollback($conn); // Rollback jika ada error
                $pesan = "<div class='p-4 bg-red-100 border border-red-400 text-red-700 mt-4'>Terjadi kesalahan saat checkout: " . $e->getMessage() . "</div>";
            } catch (mysqli_sql_exception $exception) { // Tangkap SQL exception
                mysqli_rollback($conn); // Rollback jika ada error
                $pesan = "<div class='p-4 bg-red-100 border border-red-400 text-red-700 mt-4'>Terjadi kesalahan database saat checkout. Pesanan dibatalkan.</div>";
            }
        }
    }
    // Redirect setelah operasi checkout untuk mencegah resubmission
    header("Location: keranjang.php?pesan=" . urlencode($pesan));
    exit();
}

// Logika Hapus Item dari Keranjang
if (isset($_GET['hapus_keranjang'])) {
    $keranjang_id = $_GET['hapus_keranjang'];
    mysqli_query($conn, "DELETE FROM keranjang WHERE id = $keranjang_id AND pembeli_id = $pembeli_id");
    $pesan = "<div class='p-4 bg-green-100 border border-green-400 text-green-700 mt-4'>Item berhasil dihapus dari keranjang.</div>";
    header("Location: keranjang.php?pesan=" . urlencode($pesan));
    exit();
}

// Ambil pesan dari URL setelah redirect
if (isset($_GET['pesan'])) {
    $pesan = urldecode($_GET['pesan']);
}

// Ambil item keranjang untuk user yang sedang login (untuk tampilan)
$keranjang_items = [];
$total_harga_keranjang = 0;
$keranjang_query = mysqli_query($conn, "SELECT k.id as keranjang_id, k.jumlah, b.nama, b.harga, b.gambar
                                       FROM keranjang k
                                       JOIN barang b ON k.barang_id = b.id
                                       WHERE k.pembeli_id = $pembeli_id");
while ($item = mysqli_fetch_assoc($keranjang_query)) {
    $keranjang_items[] = $item;
    $total_harga_keranjang += ($item['harga'] * $item['jumlah']);
}
?>

<?php include 'dashboard.php'; // Asumsi keranjang adalah bagian dari dashboard pembeli 
?>

<div class="max-w-4xl mx-auto mt-10 p-4">
    <h2 class="text-xl text-stone-800 font-bold mb-4">Keranjang Belanja Anda</h2>

    <?= $pesan ?>

    <?php if (empty($keranjang_items)) { ?>
        <p class="text-gray-600">Keranjang Anda kosong. Yuk, <a href="../lihat_barang.php" class="text-blue-500 hover:underline">mulai belanja</a>!</p>
    <?php } else { ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-amber-100">
                    <tr>
                        <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Gambar</th>
                        <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Nama Barang</th>
                        <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Harga</th>
                        <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Jumlah</th>
                        <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Subtotal</th>
                        <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($keranjang_items as $item) { ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b">
                                <img src="../admin/uploads/<?= $item['gambar'] ?>" alt="<?= $item['nama'] ?>" class="w-12 h-12 object-cover rounded-full">
                            </td>
                            <td class="py-2 px-4 border-b"><?= $item['nama'] ?></td>
                            <td class="py-2 px-4 border-b">Rp <?= number_format($item['harga']) ?></td>
                            <td class="py-2 px-4 border-b"><?= $item['jumlah'] ?></td>
                            <td class="py-2 px-4 border-b">Rp <?= number_format($item['harga'] * $item['jumlah']) ?></td>
                            <td class="py-2 px-4 border-b">
                                <a href="?hapus_keranjang=<?= $item['keranjang_id'] ?>" onclick="return confirm('Hapus item ini dari keranjang?')" class="text-red-500 hover:underline text-sm">Hapus</a>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="4" class="py-2 px-4 text-right font-semibold text-gray-700">Total Keranjang:</td>
                        <td class="py-2 px-4 font-bold text-lg text-green-700">Rp <?= number_format($total_harga_keranjang) ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form method="post" class="mt-4 text-right">
            <button type="submit" name="checkout" class="bg-green-500 text-white px-5 py-2 rounded-lg cursor-pointer hover:bg-green-600 text-lg">Checkout</button>
        </form>
    <?php } ?>
</div>