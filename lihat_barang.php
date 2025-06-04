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
    exit();
}

$barang = mysqli_query($conn, "SELECT * FROM barang");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Berkah Jaya - Produk</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-amber-50 font-sans antialiased">

    <nav class="bg-amber-950 shadow-lg p-4 flex justify-between items-center fixed w-full z-50 top-0">
        <a href="../index.php">
            <h1 class="text-2xl text-amber-50 font-extrabold tracking-wide">Berkah Jaya</h1>
        </a>
        <div class="flex items-center space-x-6">
            <?php if (!isset($_SESSION['user'])) { ?>
                <a href="auth/login.php" class="text-amber-200 hover:text-amber-50 transition duration-300 ease-in-out">Login</a>
                <a href="auth/register.php" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition duration-300 ease-in-out">Register</a>
            <?php } else if ($_SESSION['user']['role'] == 'pembeli') { ?>
                <span class="text-amber-200 text-lg">Halo, <span class="font-semibold"><?= $_SESSION['user']['username'] ?></span>!</span>
                <a href="pembeli/dashboard.php" class="text-amber-200 hover:text-amber-50 transition duration-300 ease-in-out">Dashboard</a>
                <a href="pembeli/keranjang.php" class="text-amber-200 hover:text-amber-50 transition duration-300 ease-in-out flex items-center">
                    <i class="fas fa-shopping-cart mr-1"></i> Keranjang
                </a>
                <a href="auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out">Logout</a>
            <?php } else { ?>
                <span class="text-amber-200 text-lg">Halo, <span class="font-semibold"><?= $_SESSION['user']['username'] ?></span>!</span>
                <a href="auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out">Logout</a>
            <?php } ?>
        </div>
    </nav>

    <div id="home" class="
        bg-cover bg-center h-screen flex items-center justify-center text-center text-white relative
    " style="background-image: url('/asset/img/banner.jpg');">
        <div class="bg-black/40 absolute top-0 left-0 w-full h-full"></div>
        <div class="relative z-10 p-8 max-w-4xl mx-auto">
            <h1 class="text-5xl md:text-7xl font-extrabold mb-6 drop-shadow-lg animate-fade-in-down">
                Selamat Datang di Berkah Jaya Kami!
            </h1>
            <p class="text-xl md:text-2xl mb-10 drop-shadow-md animate-fade-in-up">
                Penuhi kebutuhan dapur Anda dengan produk berkualitas, segar, dan terpercaya.
            </p>
            <a href="#daftar-barang" class="scroll-to-section inline-block bg-amber-500 text-white text-2xl font-bold px-10 py-4 rounded-full shadow-lg hover:bg-amber-600 transition duration-300 ease-in-out transform hover:scale-105 animate-bounce-in">
                Belanja Sekarang!
                <i class="fas fa-arrow-down ml-3"></i>
            </a>
        </div>
    </div>

    <div class="pt-24 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8" id="daftar-barang">
        <h2 class="text-4xl text-stone-800 font-extrabold mb-8 text-center">Produk Unggulan Kami</h2>

        <div id="pesan-notifikasi" class="hidden p-4 rounded-lg text-white font-medium text-center mb-8 shadow-md" role="alert"></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (mysqli_num_rows($barang) > 0) { ?>
                <?php while ($b = mysqli_fetch_assoc($barang)) { ?>
                    <div class="bg-amber-950/90 rounded-xl shadow-lg overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-xl group">
                        <div class="h-48 w-full overflow-hidden bg-amber-100 flex items-center justify-center">
                            <img src="admin/uploads/<?= $b['gambar'] ?>" alt="<?= $b['nama'] ?>" class="object-contain h-full w-full transition-transform duration-300 group-hover:scale-110">
                        </div>
                        <div class="p-5 text-amber-50">
                            <h3 class="text-xl font-bold mb-1 truncate"><?= $b['nama'] ?></h3>
                            <p class="text-2xl font-extrabold text-amber-300 mb-2">Rp <?= number_format($b['harga'], 0, ',', '.') ?></p>
                            <p class="text-amber-400 text-sm mb-4">Stok: <span class="font-semibold"><?= $b['stok'] ?></span></p>
                            <div class="flex flex-col space-y-3">
                                <?php if ($pembeli_id) { ?>
                                    <form class="add-to-cart-form flex flex-col space-y-2" data-barang-id="<?= $b['id'] ?>">
                                        <label for="jumlah-<?= $b['id'] ?>" class="sr-only">Jumlah</label>
                                        <input type="number" id="jumlah-<?= $b['id'] ?>" name="jumlah" min="1" value="1" max="<?= $b['stok'] ?>" class="w-full bg-amber-900 border border-amber-700 text-amber-50 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200" required>
                                        <button type="submit" class="bg-amber-500 text-white font-semibold py-2 rounded-md hover:bg-amber-600 transition duration-300 ease-in-out flex items-center justify-center">
                                            <i class="fas fa-cart-plus mr-2"></i> Tambah ke Keranjang
                                        </button>
                                    </form>
                                <?php } else { ?>
                                    <a href="auth/login.php" class="bg-amber-500 text-amber-50 font-semibold py-2 rounded-md hover:bg-amber-600 text-center transition duration-300 ease-in-out">
                                        Login untuk Beli
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-span-full text-center text-gray-500 text-2xl py-10 bg-amber-100 rounded-lg shadow-inner">
                    <i class="fas fa-box-open text-gray-400 text-5xl mb-4"></i>
                    <p>Belum ada barang tersedia saat ini.</p>
                </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pesanNotifikasi = document.getElementById('pesan-notifikasi');

            function showNotification(message, isSuccess) {
                pesanNotifikasi.textContent = message;
                pesanNotifikasi.classList.remove('hidden', 'bg-green-500', 'bg-red-500', 'border-green-400', 'border-red-400');
                if (isSuccess) {
                    pesanNotifikasi.classList.add('bg-green-500');
                } else {
                    pesanNotifikasi.classList.add('bg-red-500');
                }
                pesanNotifikasi.classList.remove('hidden');
                setTimeout(() => {
                    pesanNotifikasi.classList.add('hidden');
                }, 5000); // Notification disappears after 5 seconds
            }

            document.querySelectorAll('.add-to-cart-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const barangId = this.dataset.barangId;
                    const jumlahInput = this.querySelector('input[name="jumlah"]');
                    const jumlah = jumlahInput.value;
                    const maxStok = parseInt(jumlahInput.getAttribute('max'));

                    // Client-side validation for quantity
                    if (parseInt(jumlah) > maxStok) {
                        showNotification(`Jumlah yang diminta melebihi stok tersedia (${maxStok}).`, false);
                        return;
                    }
                    if (parseInt(jumlah) <= 0) {
                        showNotification('Jumlah barang harus lebih dari 0.', false);
                        return;
                    }


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
                        .catch(error => {
                            console.error('Error adding to cart:', error);
                            showNotification('Terjadi kesalahan saat menambahkan ke keranjang.', false);
                        });
                });
            });
        });
    </script>
</body>

</html>