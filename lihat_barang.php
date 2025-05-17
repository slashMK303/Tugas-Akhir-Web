<?php
include 'config/koneksi.php';

// Cek apakah user sudah login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pembeli_id = isset($_SESSION['user']) && $_SESSION['user']['role'] == 'pembeli' ? $_SESSION['user']['id'] : null;

// Cek apakah ada form beli yang dikirim
if (isset($_POST['beli'])) {
    if (!$pembeli_id) {
        header("Location: auth/login.php");
        exit();
    }

    $barang_id = $_POST['barang_id'];
    $jumlah = $_POST['jumlah'];

    $cek = mysqli_query($conn, "SELECT stok, harga FROM barang WHERE id = $barang_id");
    $barang = mysqli_fetch_assoc($cek);

    // Cek apakah stok mencukupi
    if ($barang['stok'] >= $jumlah) {
        $total = $barang['harga'] * $jumlah;

        mysqli_query($conn, "INSERT INTO pesanan (pembeli_id, status, tanggal) VALUES ($pembeli_id, 'menunggu', NOW())");
        $pesanan_id = mysqli_insert_id($conn);

        mysqli_query($conn, "INSERT INTO detail_pesanan (pesanan_id, barang_id, jumlah, total) VALUES ($pesanan_id, $barang_id, $jumlah, $total)");

        mysqli_query($conn, "UPDATE barang SET stok = stok - $jumlah WHERE id = $barang_id");

        $pegawai = mysqli_query($conn, "SELECT id FROM users WHERE role = 'pegawai' ORDER BY RAND() LIMIT 1");
        $data_pegawai = mysqli_fetch_assoc($pegawai);
        $pegawai_id = $data_pegawai['id'];

        mysqli_query($conn, "INSERT INTO pengantaran (pesanan_id, pegawai_id, status_pengantaran) VALUES ($pesanan_id, $pegawai_id, 'belum')");

        $pesan = "<div class='p-4 bg-green-100 border mt-4'>Pesanan berhasil dibuat.</div>";
    } else {
        $pesan = "<div class='p-4 bg-red-100 border mt-4'>Stok tidak mencukupi!</div>";
    }
}

$barang = mysqli_query($conn, "SELECT * FROM barang");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Toko Sembako</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50">

    <!-- Navigasi -->
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">Toko Sembako</h1>
        <div>
            <?php if (!isset($_SESSION['user'])) { ?>
                <a href="auth/login.php" class="text-blue-500 hover:underline mr-4">Login</a>
                <a href="auth/register.php" class="text-blue-500 hover:underline">Register</a>
            <?php } else if ($_SESSION['user']['role'] == 'pembeli') { ?>
                <span class="mr-4">Halo, <?= $_SESSION['user']['username'] ?>!</span>
                <a href="pembeli/dashboard.php" class="text-blue-500 hover:underline mr-4">Dashboard</a>
                <a href="auth/logout.php" class="text-red-500 hover:underline">Logout</a>
            <?php } else { ?>
                <span class="mr-4">Halo, <?= $_SESSION['user']['username'] ?>!</span>
                <a href="auth/logout.php" class="text-red-500 hover:underline">Logout</a>
            <?php } ?>
        </div>
    </nav>

    <!-- Daftar Barang -->
    <div class="max-w-4xl mx-auto mt-10">
        <h2 class="text-xl font-bold mb-4">Daftar Barang</h2>

        <?= isset($pesan) ? $pesan : '' ?>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <?php while ($b = mysqli_fetch_assoc($barang)) { ?>
                <div class="bg-white rounded shadow p-4">
                    <img src="admin/uploads/<?= $b['gambar'] ?>" alt="<?= $b['nama'] ?>" class="w-full h-auto object-cover mb-2 rounded">
                    <h3 class="text-xl font-bold"><?= $b['nama'] ?></h3>
                    <p class="text-gray-600">Rp <?= number_format($b['harga']) ?></p>
                    <p class="text-gray-600">Stok: <?= $b['stok'] ?></p>
                    <div class="flex space-x-2 mt-2">
                        <?php if ($pembeli_id) { ?>
                            <form method="post" class="flex space-x-2">
                                <input type="hidden" name="barang_id" value="<?= $b['id'] ?>">
                                <input type="number" name="jumlah" min="1" max="<?= $b['stok'] ?>" class="border rounded px-2 w-16" required>
                                <button type="submit" name="beli" class="bg-blue-500 text-white px-3 py-1 rounded">Beli</button>
                            </form>
                        <?php } else { ?>
                            <a href="auth/login.php" class="bg-yellow-400 text-black px-3 py-1 rounded">Login untuk Beli</a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

</body>

</html>