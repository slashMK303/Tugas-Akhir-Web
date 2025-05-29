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

<?php include 'dashboard.php'; ?>

<div class="max-w-4xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6">Kelola Pegawai</h2>

    <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-6">
        <input name="username" placeholder="Username" class="border p-2 border-amber-500 rounded focus:outline-amber-500" required>
        <input name="password" type="password" placeholder="Password" class="border p-2 border-amber-500 rounded focus:outline-amber-500" required>
        <button name="tambah" class="col-span-1 md:col-span-2 bg-amber-500 cursor-pointer text-white p-2 rounded hover:bg-amber-600">Tambah Pegawai</button>
    </form>

    <?php if ($edit_pegawai) { ?>
        <h3 class="text-xl font-semibold mb-4 mt-8">Edit Pegawai: <?= $edit_pegawai['username'] ?></h3>
        <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-6">
            <input type="hidden" name="id_pegawai" value="<?= $edit_pegawai['id'] ?>">
            <input name="username_edit" placeholder="Username" class="border p-2 border-amber-500 rounded focus:outline-amber-500" value="<?= $edit_pegawai['username'] ?>" required>
            <input name="password_edit" type="password" placeholder="Password (Kosongkan jika tidak diubah)" class="border p-2 border-amber-500 rounded focus:outline-amber-500">
            <button name="update" class="col-span-1 md:col-span-2 bg-amber-500 cursor-pointer text-white p-2 rounded hover:bg-amber-600">Update Pegawai</button>
            <a href="kelola_pegawai.php" class="col-span-1 md:col-span-2 text-center text-gray-600 cursor-pointer p-2 rounded hover:text-gray-800">Batal</a>
        </form>
    <?php } ?>

    <table class="w-full border text-sm">
        <thead class="bg-amber-100">
            <tr>
                <th class="p-2 border border-gray-200">Username</th>
                <th class="p-2 border border-gray-200">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = mysqli_fetch_assoc($pegawai)) { ?>
                <tr class="border-b">
                    <td class="p-2 border border-gray-200"><?= $p['username'] ?></td>
                    <td class="p-2 border border-gray-200">
                        <a href="?edit=<?= $p['id'] ?>" class="text-blue-500 hover:underline">Edit</a>
                        <a href="?hapus=<?= $p['id'] ?>" onclick="return confirm('Hapus pegawai ini?')" class="text-red-500 hover:underline">Hapus</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>