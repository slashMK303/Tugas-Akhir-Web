<?php
include '../config/koneksi.php';

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Cek apakah form tambah barang disubmit
if (isset($_POST['tambah'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'pegawai')");
    header("Location: kelola_pegawai.php");
    exit();
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    header("Location: kelola_pegawai.php");
    exit();
}

// Ambil data pegawai
$pegawai = mysqli_query($conn, "SELECT * FROM users WHERE role='pegawai'");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Pegawai</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-amber-50">

    <?php include 'dashboard.php'; ?>

    <div class="max-w-4xl mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-6">Kelola Pegawai</h2>

        <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-6">
            <input name="username" placeholder="Username" class="border p-2 rounded" required>
            <input name="password" type="password" placeholder="Password" class="border p-2 rounded" required>
            <button name="tambah" class="col-span-1 md:col-span-2 bg-green-500 text-white p-2 rounded">Tambah Pegawai</button>
        </form>

        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border">Username</th>
                    <th class="p-2 border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = mysqli_fetch_assoc($pegawai)) { ?>
                    <tr class="border-b">
                        <td class="p-2 border"><?= $p['username'] ?></td>
                        <td class="p-2 border">
                            <a href="?hapus=<?= $p['id'] ?>" onclick="return confirm('Hapus pegawai ini?')" class="text-red-500 hover:underline">Hapus</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</body>

</html>