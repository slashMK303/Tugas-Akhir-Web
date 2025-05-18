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
    <title>Kelola Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-amber-50">

    <?php include 'dashboard.php'; ?>

    <div class="max-w-4xl mx-auto mt-10">
        <h1 class="text-2xl font-bold mb-6">Kelola Barang</h1>

        <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-6">
            <input type="hidden" name="id" value="<?= $editMode ? $editData['id'] : '' ?>">
            <input name="nama" placeholder="Nama Barang" class="border p-2 rounded" required value="<?= $editMode ? $editData['nama'] : '' ?>">
            <input name="harga" type="number" placeholder="Harga" class="border p-2 rounded" required value="<?= $editMode ? $editData['harga'] : '' ?>">
            <input name="stok" type="number" placeholder="Stok" class="border p-2 rounded" required value="<?= $editMode ? $editData['stok'] : '' ?>">
            <input type="file" name="gambar" accept="image/*" class="border p-2 rounded <?= $editMode ? '' : 'required' ?>">
            <button name="<?= $editMode ? 'update' : 'tambah' ?>" class="col-span-1 md:col-span-4 bg-<?= $editMode ? 'green' : 'blue' ?>-500 text-white p-2 rounded">
                <?= $editMode ? 'Update' : 'Tambah' ?>
            </button>
        </form>

        <?php if ($editMode && $editData['gambar']) { ?>
            <p class="text-sm mb-4">Gambar saat ini:</p>
            <img src="uploads/?= $editData['gambar'] ?>" class="w-32 h-32 object-cover rounded mb-4">
        <?php } ?>


        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border">Gambar</th>
                    <th class="p-2 border">Nama</th>
                    <th class="p-2 border">Harga</th>
                    <th class="p-2 border">Stok</th>
                    <th class="p-2 border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($b = mysqli_fetch_assoc($barang)) { ?>
                    <tr class="border-b">
                        <td class="p-2 border">
                            <img src="uploads/<?= $b['gambar'] ?>" alt="<?= $b['nama'] ?>" class="w-16 h-16 object-cover">
                        </td>

                        <td class="p-2 border"><?= $b['nama'] ?></td>
                        <td class="p-2 border">Rp <?= number_format($b['harga']) ?></td>
                        <td class="p-2 border"><?= $b['stok'] ?></td>
                        <td class="p-2 border space-x-2">
                            <a href="?edit=<?= $b['id'] ?>" class="text-blue-500 hover:underline">Edit</a>
                            <a href="?hapus=<?= $b['id'] ?>" onclick="return confirm('Hapus barang ini?')" class="text-red-500 hover:underline">Hapus</a>
                        </td>

                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</body>

</html>