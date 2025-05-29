<?php
include 'config/koneksi.php';

// Cek apakah user sudah login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pembeli_id = isset($_SESSION['user']) && $_SESSION['user']['role'] == 'pembeli' ? $_SESSION['user']['id'] : null;

// Logika Tambah ke Keranjang (Ini akan diakses via AJAX)
if (isset($_POST['action']) && $_POST['action'] === 'tambah_keranjang_ajax') {
    header('Content-Type: application/json'); // Beri tahu browser bahwa respons adalah JSON

    if (!$pembeli_id) {
        echo json_encode(['success' => false, 'message' => 'Anda harus login untuk menambahkan barang ke keranjang.']);
        exit();
    }

    $barang_id = $_POST['barang_id'];
    $jumlah_ditambah = (int)$_POST['jumlah'];

    // Validasi input
    if ($jumlah_ditambah <= 0) {
        echo json_encode(['success' => false, 'message' => 'Jumlah barang harus lebih dari 0.']);
        exit();
    }

    // Cek stok barang
    $cek_barang = mysqli_query($conn, "SELECT stok, harga, nama FROM barang WHERE id = $barang_id");
    if (mysqli_num_rows($cek_barang) == 0) {
        echo json_encode(['success' => false, 'message' => 'Barang tidak ditemukan.']);
        exit();
    }
    $data_barang = mysqli_fetch_assoc($cek_barang);
    $stok_tersedia = $data_barang['stok'];
    $nama_barang = $data_barang['nama'];

    // Cek apakah barang sudah ada di keranjang pembeli ini
    $cek_keranjang = mysqli_query($conn, "SELECT id, jumlah FROM keranjang WHERE pembeli_id = $pembeli_id AND barang_id = $barang_id");
    if (mysqli_num_rows($cek_keranjang) > 0) {
        // Jika sudah ada, update jumlahnya
        $item_keranjang = mysqli_fetch_assoc($cek_keranjang);
        $new_jumlah = $item_keranjang['jumlah'] + $jumlah_ditambah;

        if ($new_jumlah <= $stok_tersedia) {
            mysqli_query($conn, "UPDATE keranjang SET jumlah = $new_jumlah WHERE id = " . $item_keranjang['id']);
            echo json_encode(['success' => true, 'message' => "Jumlah '$nama_barang' di keranjang berhasil diperbarui menjadi $new_jumlah."]);
        } else {
            echo json_encode(['success' => false, 'message' => "Tidak bisa menambahkan, stok '$nama_barang' tidak mencukupi! Stok tersedia: $stok_tersedia"]);
        }
    } else {
        // Jika belum ada, tambahkan item baru ke keranjang
        if ($jumlah_ditambah <= $stok_tersedia) {
            mysqli_query($conn, "INSERT INTO keranjang (pembeli_id, barang_id, jumlah) VALUES ($pembeli_id, $barang_id, $jumlah_ditambah)");
            echo json_encode(['success' => true, 'message' => "'$nama_barang' berhasil ditambahkan ke keranjang."]);
        } else {
            echo json_encode(['success' => false, 'message' => "Stok '$nama_barang' tidak mencukupi untuk jumlah ini! Stok tersedia: $stok_tersedia"]);
        }
    }
    exit(); // Penting: Hentikan eksekusi setelah mengirim respons JSON
}

// Ambil semua barang untuk ditampilkan
$barang = mysqli_query($conn, "SELECT * FROM barang");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Toko Sembako</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        /* CSS terkait keranjang ikon di kanan atas dihapus */
    </style>
</head>

<body class="bg-amber-50">

    <nav class="bg-amber-50 shadow p-4 flex justify-between items-center">
        <h1 class="text-xl text-stone-800 font-bold">Toko Sembako</h1>
        <div>
            <?php if (!isset($_SESSION['user'])) { ?>
                <a href="auth/login.php" class="text-amber-500 hover:underline mr-4">Login</a>
                <a href="auth/register.php" class="text-amber-500 hover:underline">Register</a>
            <?php } else if ($_SESSION['user']['role'] == 'pembeli') { ?>
                <span class="mr-4 text-stone-800">Halo, <?= $_SESSION['user']['username'] ?>!</span>
                <a href="pembeli/dashboard.php" class="text-amber-500 hover:underline mr-4">Dashboard</a>
                <a href="pembeli/keranjang.php" class="text-amber-500 hover:underline mr-4">Keranjang</a>
                <a href="auth/logout.php" class="text-red-500 hover:underline">Logout</a>
            <?php } else { ?>
                <span class="mr-4 text-stone-800">Halo, <?= $_SESSION['user']['username'] ?>!</span>
                <a href="auth/logout.php" class="text-red-500 hover:underline">Logout</a>
            <?php } ?>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto mt-10 p-4">
        <h2 class="text-xl text-stone-800 font-bold mb-4">Daftar Barang</h2>

        <div id="pesan-notifikasi" class="hidden p-4 bg-green-100 border border-green-400 text-green-700 mt-4 mb-4"></div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <?php if (mysqli_num_rows($barang) > 0) { ?>
                <?php while ($b = mysqli_fetch_assoc($barang)) { ?>
                    <div class="bg-amber-950/80 rounded shadow p-4 text-amber-50">
                        <img src="admin/uploads/<?= $b['gambar'] ?>" alt="<?= $b['nama'] ?>" class="w-full h-auto object-cover mb-2 rounded">
                        <h3 class="text-xl font-bold"><?= $b['nama'] ?></h3>
                        <p class="text-amber-100">Rp <?= number_format($b['harga']) ?></p>
                        <p class="text-amber-600">Stok: <?= $b['stok'] ?></p>
                        <div class="flex space-x-2 mt-2">
                            <?php if ($pembeli_id) { ?>
                                <form class="add-to-cart-form flex space-x-2 items-center" data-barang-id="<?= $b['id'] ?>">
                                    <input type="number" name="jumlah" min="1" value="1" max="<?= $b['stok'] ?>" class="border border-amber-50 rounded px-2 w-16 text-amber-50" required>
                                    <button type="submit" class="bg-amber-500 text-white px-3 py-1 rounded cursor-pointer hover:bg-amber-600 text-sm">Tambah ke Keranjang</button>
                                </form>
                            <?php } else { ?>
                                <a href="auth/login.php" class="bg-amber-500 cursor-pointer hover:bg-amber-600 text-amber-50 px-3 py-1 rounded text-sm">Login untuk Beli</a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-span-full text-center text-gray-500">Belum ada barang tersedia.</div>
            <?php } ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pesanNotifikasi = document.getElementById('pesan-notifikasi');

            function showNotification(message, isSuccess) {
                pesanNotifikasi.textContent = message;
                pesanNotifikasi.classList.remove('hidden', 'bg-green-100', 'border-green-400', 'text-green-700', 'bg-red-100', 'border-red-400', 'text-red-700');
                if (isSuccess) {
                    pesanNotifikasi.classList.add('bg-green-100', 'border-green-400', 'text-green-700');
                } else {
                    pesanNotifikasi.classList.add('bg-red-100', 'border-red-400', 'text-red-700');
                }
                pesanNotifikasi.classList.remove('hidden');
                setTimeout(() => {
                    pesanNotifikasi.classList.add('hidden');
                }, 5000);
            }

            // Event listener untuk semua form "Tambah ke Keranjang"
            document.querySelectorAll('.add-to-cart-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const barangId = this.dataset.barangId;
                    const jumlah = this.querySelector('input[name="jumlah"]').value;

                    fetch('lihat_barang.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=tambah_keranjang_ajax&barang_id=${barangId}&jumlah=${jumlah}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            showNotification(data.message, data.success);
                        })
                        .catch(error => console.error('Error adding to cart:', error));
                });
            });
        });
    </script>
</body>

</html>