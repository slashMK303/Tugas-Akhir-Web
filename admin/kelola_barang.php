<?php
include '../config/koneksi.php';

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Cek apakah form tambah barang disubmit
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    $folder = "uploads/";

    // Buat folder jika belum ada
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    move_uploaded_file($tmp, $folder . $gambar);

    mysqli_query($conn, "INSERT INTO barang (nama, harga, stok, gambar) VALUES ('$nama', '$harga', '$stok', '$gambar')");

    header("Location: kelola_barang.php");
    exit();
}

$editMode = false;
$editData = null;

// Cek apakah ada barang yang akan diedit
if (isset($_GET['edit'])) {
    $editMode = true;
    $idEdit = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM barang WHERE id = $idEdit");
    $editData = mysqli_fetch_assoc($result);
}

// Cek apakah form update barang disubmit
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    if ($_FILES['gambar']['name']) {
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];
        $folder = "uploads/";
        move_uploaded_file($tmp, $folder . $gambar);

        mysqli_query($conn, "UPDATE barang SET nama='$nama', harga='$harga', stok='$stok', gambar='$gambar' WHERE id=$id");
    } else {
        mysqli_query($conn, "UPDATE barang SET nama='$nama', harga='$harga', stok='$stok' WHERE id=$id");
    }

    header("Location: kelola_barang.php");
    exit();
}


if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM barang WHERE id=$id");
    header("Location: kelola_barang.php");
    exit();
}

// Ambil data barang
$barang = mysqli_query($conn, "SELECT * FROM barang");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Barang - Admin</title>
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
            <a href="../auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out">Logout</a>
        </div>
    </nav>

    <div class="min-h-screen pt-20 pb-10">
        <div class="bg-amber-950/90 p-6 md:p-12 rounded-xl shadow-lg w-full max-w-5xl mx-auto mt-10 text-amber-50">
            <h2 class="text-3xl font-extrabold text-center mb-8">Kelola Barang</h2>

            <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <input type="hidden" name="id" value="<?= $editMode ? $editData['id'] : '' ?>">
                <input name="nama" placeholder="Nama Barang"
                    class="w-full bg-amber-900 border border-amber-700 text-amber-50 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200 placeholder-amber-200 col-span-full md:col-span-2"
                    required value="<?= $editMode ? $editData['nama'] : '' ?>">
                <input name="harga" type="number" placeholder="Harga"
                    class="w-full bg-amber-900 border border-amber-700 text-amber-50 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200 placeholder-amber-200"
                    required value="<?= $editMode ? $editData['harga'] : '' ?>">
                <input name="stok" type="number" placeholder="Stok"
                    class="w-full bg-amber-900 border border-amber-700 text-amber-50 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200 placeholder-amber-200"
                    required value="<?= $editMode ? $editData['stok'] : '' ?>">
                <input type="file" name="gambar" accept="image/*"
                    class="w-full bg-amber-900 border border-amber-700 text-amber-50 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200 placeholder-amber-200 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-amber-500 file:text-white hover:file:bg-amber-600 file:cursor-pointer col-span-full"
                    <?= $editMode ? '' : 'required' ?>>
                <?php if ($editMode && $editData['gambar']) { ?>
                    <div class="col-span-full flex items-center space-x-4">
                        <p class="text-sm">Gambar saat ini:</p>
                        <img src="uploads/<?= $editData['gambar'] ?>" class="w-24 h-24 object-cover rounded shadow">
                    </div>
                <?php } ?>
                <button name="<?= $editMode ? 'update' : 'tambah' ?>"
                    class="col-span-full bg-amber-500 text-white font-semibold py-3 rounded-md hover:bg-amber-600 transition duration-300 ease-in-out">
                    <?= $editMode ? 'Update Barang' : 'Tambah Barang' ?>
                </button>
            </form>

            <h3 class="text-2xl font-bold mb-6 text-center">Daftar Barang</h3>
            <div class="overflow-x-auto rounded-lg shadow-md">
                <table class="min-w-full text-sm bg-amber-900">
                    <thead class="bg-amber-800 text-amber-100 uppercase text-xs leading-normal">
                        <tr>
                            <th class="py-3 px-6 text-left border border-amber-700">Gambar</th>
                            <th class="py-3 px-6 text-left border border-amber-700">Nama</th>
                            <th class="py-3 px-6 text-left border border-amber-700">Harga</th>
                            <th class="py-3 px-6 text-left border border-amber-700">Stok</th>
                            <th class="py-3 px-6 text-center border border-amber-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-amber-50">
                        <?php if (mysqli_num_rows($barang) > 0) { ?>
                            <?php while ($b = mysqli_fetch_assoc($barang)) { ?>
                                <tr class="border-b border-amber-700 hover:bg-amber-800 transition duration-200">
                                    <td class="py-3 px-6 border border-amber-700">
                                        <img class="w-16 h-16 object-cover rounded m-auto" src="uploads/<?= $b['gambar'] ?>" alt="<?= $b['nama'] ?>">
                                    </td>
                                    <td class="py-3 px-6 border border-amber-700 font-medium"><?= $b['nama'] ?></td>
                                    <td class="py-3 px-6 border border-amber-700">Rp <?= number_format($b['harga'], 0, ',', '.') ?></td>
                                    <td class="py-3 px-6 border border-amber-700"><?= $b['stok'] ?></td>
                                    <td class="py-3 px-6 border border-amber-700 text-center whitespace-nowrap">
                                        <a href="?edit=<?= $b['id'] ?>" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>
                                        <a href="?hapus=<?= $b['id'] ?>" onclick="return confirm('Hapus barang ini?')" class="text-red-400 hover:text-red-300">Hapus</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="py-4 px-6 text-center text-amber-200">Tidak ada barang tersedia.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
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