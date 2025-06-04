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
        $pesan = "<div class='p-4 bg-red-800 text-red-100 rounded-md mt-4'>Keranjang Anda kosong, tidak bisa checkout!</div>";
    } else {
        $can_checkout = true;
        $pesanan_detail_list = [];

        // Validasi stok untuk semua item di keranjang
        while ($item = mysqli_fetch_assoc($items_keranjang)) {
            if ($item['jumlah'] > $item['stok']) {
                $pesan = "<div class='p-4 bg-red-800 text-red-100 rounded-md mt-4'>Stok barang '{$item['nama']}' tidak cukup. Tersisa {$item['stok']} unit.</div>";
                $can_checkout = false;
                break;
            }
            $pesanan_detail_list[] = $item;
        }

        if ($can_checkout) {
            // Mulai transaksi
            mysqli_autocommit($conn, false);

            // 1. Buat entri pesanan baru
            $tanggal_pesanan = date('Y-m-d H:i:s');
            // Status default: menunggu. Akan berubah jika pengantaran sudah diproses
            $insert_pesanan_query = "INSERT INTO pesanan (pembeli_id, tanggal, status) VALUES ('$pembeli_id', '$tanggal_pesanan', 'menunggu')";
            if (!mysqli_query($conn, $insert_pesanan_query)) {
                mysqli_rollback($conn);
                $pesan = "<div class='p-4 bg-red-800 text-red-100 rounded-md mt-4'>Gagal membuat pesanan: " . mysqli_error($conn) . "</div>";
            } else {
                $pesanan_id_baru = mysqli_insert_id($conn);

                // 2. Tambahkan detail pesanan dan update stok barang
                $all_details_inserted = true;
                foreach ($pesanan_detail_list as $item) {
                    $barang_id = $item['barang_id'];
                    $jumlah = $item['jumlah'];
                    $harga_satuan = $item['harga'];
                    $total_item = $jumlah * $harga_satuan;

                    // Insert ke detail_pesanan
                    $insert_detail_query = "INSERT INTO detail_pesanan (pesanan_id, barang_id, jumlah, total) VALUES ('$pesanan_id_baru', '$barang_id', '$jumlah', '$total_item')";
                    if (!mysqli_query($conn, $insert_detail_query)) {
                        $all_details_inserted = false;
                        break;
                    }

                    // Update stok barang
                    $update_stok_query = "UPDATE barang SET stok = stok - $jumlah WHERE id = $barang_id";
                    if (!mysqli_query($conn, $update_stok_query)) {
                        $all_details_inserted = false;
                        break;
                    }
                }

                if ($all_details_inserted) {
                    // 3. Hapus item dari keranjang setelah berhasil checkout
                    $delete_keranjang_query = "DELETE FROM keranjang WHERE pembeli_id = $pembeli_id";
                    if (mysqli_query($conn, $delete_keranjang_query)) {
                        mysqli_commit($conn);
                        $pesan = "<div class='p-4 bg-green-800 text-green-100 rounded-md mt-4'>Checkout berhasil! Pesanan Anda sedang menunggu diproses.</div>";
                    } else {
                        mysqli_rollback($conn);
                        $pesan = "<div class='p-4 bg-red-800 text-red-100 rounded-md mt-4'>Gagal menghapus keranjang: " . mysqli_error($conn) . "</div>";
                    }
                } else {
                    mysqli_rollback($conn);
                    $pesan = "<div class='p-4 bg-red-800 text-red-100 rounded-md mt-4'>Gagal menambah detail pesanan atau update stok: " . mysqli_error($conn) . "</div>";
                }
            }
            mysqli_autocommit($conn, true); // Aktifkan autocommit kembali
        }
    }
}

// Logika Hapus Item dari Keranjang
if (isset($_GET['hapus_keranjang'])) {
    $keranjang_id_to_delete = $_GET['hapus_keranjang'];
    mysqli_query($conn, "DELETE FROM keranjang WHERE id = $keranjang_id_to_delete AND pembeli_id = $pembeli_id");
    header("Location: keranjang.php"); // Redirect untuk menghindari resubmission
    exit();
}

// Ambil semua item di keranjang user
$keranjang = mysqli_query($conn, "SELECT k.id AS keranjang_id, k.jumlah, b.nama, b.harga, b.gambar
                                  FROM keranjang k
                                  JOIN barang b ON k.barang_id = b.id
                                  WHERE k.pembeli_id = $pembeli_id");

$total_harga_keranjang = 0;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Keranjang Belanja</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-amber-50 font-sans antialiased">

    <nav class="bg-amber-950 shadow-lg p-4 flex justify-between items-center fixed w-full z-20 top-0">
        <h1 class="text-2xl text-amber-50 font-extrabold tracking-wide"><a href="../index.php">Berkah Jaya</a></h1>
        <div class="flex items-center space-x-6">
            <span class="text-amber-200 text-lg">Halo, <span class="font-semibold"><?= $_SESSION['user']['username'] ?></span>!</span>
            <a href="dashboard.php" class="text-amber-200 hover:text-amber-50 transition duration-300 ease-in-out">Dashboard</a>
            <a href="riwayat.php" class="text-amber-200 hover:text-amber-50 transition duration-300 ease-in-out">Riwayat</a>
            <a href="../auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out">Logout</a>
        </div>
    </nav>

    <div class="min-h-screen pt-20 pb-10">
        <div class="bg-amber-950/90 p-6 md:p-12 rounded-xl shadow-lg w-full max-w-4xl mx-auto mt-10 text-amber-50">
            <h2 class="text-3xl font-extrabold text-center mb-8">Keranjang Belanja</h2>

            <?php if (!empty($pesan)) {
                echo $pesan;
            } ?>

            <?php if (mysqli_num_rows($keranjang) == 0) { ?>
                <div class="bg-amber-900 p-6 rounded-lg text-center text-amber-200">
                    <i class="fas fa-shopping-basket text-5xl mb-4 text-amber-400"></i>
                    <p class="text-lg">Keranjang belanja Anda kosong.</p>
                    <a href="../lihat_barang.php" class="mt-4 inline-block bg-amber-500 text-white px-6 py-3 rounded-md hover:bg-amber-600 transition duration-300 ease-in-out">Mulai Belanja</a>
                </div>
            <?php } else { ?>
                <div class="overflow-x-auto rounded-lg shadow-md mb-6">
                    <table class="min-w-full text-sm bg-amber-900">
                        <thead class="bg-amber-800 text-amber-100 uppercase text-xs leading-normal">
                            <tr>
                                <th class="py-3 px-6 text-left border border-amber-700">Gambar</th>
                                <th class="py-3 px-6 text-left border border-amber-700">Nama Barang</th>
                                <th class="py-3 px-6 text-left border border-amber-700">Harga Satuan</th>
                                <th class="py-3 px-6 text-left border border-amber-700">Jumlah</th>
                                <th class="py-3 px-6 text-left border border-amber-700">Subtotal</th>
                                <th class="py-3 px-6 text-center border border-amber-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-amber-50">
                            <?php while ($item = mysqli_fetch_assoc($keranjang)) {
                                $subtotal = $item['harga'] * $item['jumlah'];
                                $total_harga_keranjang += $subtotal;
                            ?>
                                <tr class="border-b border-amber-700 hover:bg-amber-800 transition duration-200">
                                    <td class="py-3 px-6 border border-amber-700">
                                        <img src="../admin/uploads/<?= $item['gambar'] ?>" alt="<?= $item['nama'] ?>" class="w-12 h-12 object-cover rounded-full">
                                    </td>
                                    <td class="py-3 px-6 border border-amber-700 font-medium"><?= $item['nama'] ?></td>
                                    <td class="py-3 px-6 border border-amber-700">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                    <td class="py-3 px-6 border border-amber-700"><?= $item['jumlah'] ?></td>
                                    <td class="py-3 px-6 border border-amber-700">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                    <td class="py-3 px-6 border border-amber-700 text-center">
                                        <a href="?hapus_keranjang=<?= $item['keranjang_id'] ?>" onclick="return confirm('Hapus item ini dari keranjang?')" class="text-red-400 hover:text-red-300 text-sm">Hapus</a>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr class="bg-amber-800 font-bold text-lg text-amber-100">
                                <td colspan="4" class="py-3 px-6 text-right border-t border-amber-700">Total Keranjang:</td>
                                <td class="py-3 px-6 border-t border-amber-700">Rp <?= number_format($total_harga_keranjang, 0, ',', '.') ?></td>
                                <td class="py-3 px-6 border-t border-amber-700"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <form method="post" class="mt-6 text-right">
                    <button type="submit" name="checkout" class="bg-green-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-green-700 transition duration-300 ease-in-out">
                        Checkout Sekarang
                    </button>
                </form>
            <?php } ?>
        </div>
    </div>

    <footer class="bg-amber-950 text-amber-200 text-center p-6 mt-16">
        <p>&copy; <?= date('Y') ?> Berkah Jaya. All rights reserved.</p>
        <div class="flex justify-center space-x-4 mt-3">
            <a href="#" class="hover:text-amber-50 transition duration-300"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="hover:text-amber-50 transition duration-300"><i class="fab fa-instagram"></i></a>
            <a href="#" class="hover:text-amber-50 transition duration-300"><i class="fab fa-twitter"></i></a>
        </div>
    </footer>

</body>

</html>