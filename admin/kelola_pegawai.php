<?php
include '../config/koneksi.php';

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Cek apakah form tambah pegawai disubmit
if (isset($_POST['tambah'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'pegawai')");
    header("Location: kelola_pegawai.php");
    exit();
}

// Cek apakah form update pegawai disubmit
if (isset($_POST['update'])) {
    $id = $_POST['id_pegawai'];
    $username = $_POST['username_edit'];
    $password = $_POST['password_edit'];

    // Cek apakah password diisi (jika tidak, jangan update password)
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET username='$username', password='$hashed_password' WHERE id=$id");
    } else {
        mysqli_query($conn, "UPDATE users SET username='$username' WHERE id=$id");
    }
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

// Untuk mengisi form edit jika ada parameter edit
$edit_pegawai = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $result_edit = mysqli_query($conn, "SELECT * FROM users WHERE id=$id_edit AND role='pegawai'");
    if (mysqli_num_rows($result_edit) > 0) {
        $edit_pegawai = mysqli_fetch_assoc($result_edit);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Pegawai - Admin</title>
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
        <div class="bg-amber-950/90 p-6 md:p-12 rounded-xl shadow-lg w-full max-w-2xl mx-auto mt-10 text-amber-50">
            <h2 class="text-3xl font-extrabold text-center mb-8">Kelola Pegawai</h2>

            <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <input name="username" placeholder="Username"
                    class="w-full bg-amber-900 border border-amber-700 text-amber-50 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200 placeholder-amber-200" required>
                <input name="password" type="password" placeholder="Password"
                    class="w-full bg-amber-900 border border-amber-700 text-amber-50 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200 placeholder-amber-200" required>
                <button name="tambah"
                    class="col-span-full bg-amber-500 text-white font-semibold py-3 rounded-md hover:bg-amber-600 transition duration-300 ease-in-out">
                    Tambah Pegawai
                </button>
            </form>

            <?php if ($edit_pegawai) { ?>
                <h3 class="text-2xl font-bold mb-6 text-center">Edit Pegawai: <?= $edit_pegawai['username'] ?></h3>
                <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 p-4 border border-amber-700 rounded-md bg-amber-900">
                    <input type="hidden" name="id_pegawai" value="<?= $edit_pegawai['id'] ?>">
                    <input name="username_edit" placeholder="Username"
                        class="w-full bg-amber-800 border border-amber-700 text-amber-50 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200 placeholder-amber-200"
                        value="<?= $edit_pegawai['username'] ?>" required>
                    <input name="password_edit" type="password" placeholder="Password (Kosongkan jika tidak diubah)"
                        class="w-full bg-amber-800 border border-amber-700 text-amber-50 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200 placeholder-amber-200">
                    <button name="update"
                        class="col-span-full bg-amber-500 text-white font-semibold py-3 rounded-md hover:bg-amber-600 transition duration-300 ease-in-out">
                        Update Pegawai
                    </button>
                    <a href="kelola_pegawai.php"
                        class="col-span-full text-center text-amber-200 hover:text-amber-50 transition duration-300 ease-in-out py-2 rounded-md">
                        Batal
                    </a>
                </form>
            <?php } ?>

            <h3 class="text-2xl font-bold mb-6 text-center">Daftar Pegawai</h3>
            <div class="overflow-x-auto rounded-lg shadow-md">
                <table class="min-w-full text-sm bg-amber-900">
                    <thead class="bg-amber-800 text-amber-100 uppercase text-xs leading-normal">
                        <tr>
                            <th class="py-3 px-6 text-left border border-amber-700">Username</th>
                            <th class="py-3 px-6 text-center border border-amber-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-amber-50">
                        <?php if (mysqli_num_rows($pegawai) > 0) { ?>
                            <?php while ($p = mysqli_fetch_assoc($pegawai)) { ?>
                                <tr class="border-b border-amber-700 hover:bg-amber-800 transition duration-200">
                                    <td class="py-3 px-6 border border-amber-700 font-medium"><?= $p['username'] ?></td>
                                    <td class="py-3 px-6 border border-amber-700 text-center whitespace-nowrap">
                                        <a href="?edit=<?= $p['id'] ?>" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>
                                        <a href="?hapus=<?= $p['id'] ?>" onclick="return confirm('Hapus pegawai ini?')" class="text-red-400 hover:text-red-300">Hapus</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="2" class="py-4 px-6 text-center text-amber-200">Tidak ada data pegawai.</td>
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