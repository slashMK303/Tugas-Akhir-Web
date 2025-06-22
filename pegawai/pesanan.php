<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'pegawai') {
    header("Location: ../auth/login.php");
    exit;
}

$pegawai_id = $_SESSION['user']['id'];

if (isset($_POST['submit_status'])) {
    $pesanan_id = $_POST['pesanan_id'];
    $status = $_POST['status'];

    $query = mysqli_query($conn, "UPDATE pesanan SET status = '$status' WHERE id = $pesanan_id");

    $cek_pengantaran = mysqli_query($conn, "SELECT id FROM pengantaran WHERE pesanan_id = $pesanan_id");
    if (mysqli_num_rows($cek_pengantaran) == 0) {
        $default_status = ($status === 'dikirim') ? 'dikirim' : (($status === 'selesai') ? 'selesai' : 'belum');
        mysqli_query($conn, "INSERT INTO pengantaran (pesanan_id, pegawai_id, status_pengantaran) VALUES ($pesanan_id, $pegawai_id, '$default_status')");
    } else {
        if ($status === 'dikirim') {
            mysqli_query($conn, "UPDATE pengantaran SET status_pengantaran = 'dikirim' WHERE pesanan_id = $pesanan_id");
        } elseif ($status === 'selesai') {
            mysqli_query($conn, "UPDATE pengantaran SET status_pengantaran = 'selesai' WHERE pesanan_id = $pesanan_id");
        } elseif ($status === 'menunggu' || $status === 'diproses') {
            mysqli_query($conn, "UPDATE pengantaran SET status_pengantaran = 'belum' WHERE pesanan_id = $pesanan_id");
        }
    }

    if ($query) {
        $_SESSION['success'] = "Status pesanan berhasil diperbarui.";
    } else {
        $_SESSION['error'] = "Gagal memperbarui status pesanan.";
    }

    header("Location: pesanan.php");
    exit;
}

$pesanan = mysqli_query($conn, "SELECT p.id, u.username, p.status, p.tanggal 
                                FROM pesanan p
                                JOIN users u ON p.pembeli_id = u.id
                                WHERE p.status != 'selesai'
                                ORDER BY p.tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Pesanan</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-amber-50 font-sans antialiased">

    <nav class="bg-amber-950 shadow p-4 flex justify-between items-center fixed top-0 w-full z-20">
        <h1 class="text-xl font-bold text-white"><a href="../index.php">Berkah Jaya</a></h1>
        <div class="flex items-center space-x-6">
            <span class="text-amber-200">Halo, <span class="font-semibold"><?= $_SESSION['user']['username'] ?></span></span>
            <a href="dashboard.php" class="text-amber-200 hover:text-white transition">Dashboard</a>
            <a href="../auth/logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">Logout</a>
        </div>
    </nav>

    <div class="min-h-screen pt-20 pb-10">
        <div class="bg-amber-950/90 p-6 md:p-12 rounded-xl shadow-lg w-full max-w-5xl mx-auto mt-10 text-amber-50">
            <h2 class="text-3xl font-extrabold text-center mb-8">Daftar Pesanan</h2>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
                    <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php elseif (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="overflow-x-auto rounded-lg shadow-inner mb-4">
                <table class="min-w-full text-sm bg-amber-800">
                    <thead class="bg-amber-700 text-amber-100 uppercase text-xs leading-normal">
                        <tr>
                            <th class="py-3 px-6 text-left border border-amber-600">ID</th>
                            <th class="py-3 px-6 text-left border border-amber-600">Pembeli</th>
                            <th class="py-3 px-6 text-left border border-amber-600">Tanggal</th>
                            <th class="py-3 px-6 text-left border border-amber-600">Status</th>
                            <th class="py-3 px-6 text-left border border-amber-600">Update Status</th>
                            <th class="py-3 px-6 text-left border border-amber-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-amber-50">
                        <?php while ($p = mysqli_fetch_assoc($pesanan)): ?>
                            <tr class="border-b border-amber-700">
                                <td class="py-3 px-6 border border-amber-700"><?= $p['id'] ?></td>
                                <td class="py-3 px-6 border border-amber-700"><?= $p['username'] ?></td>
                                <td class="py-3 px-6 border border-amber-700"><?= date('d M Y H:i', strtotime($p['tanggal'])) ?></td>
                                <td class="py-3 px-6 border border-amber-700"><?= $p['status'] ?></td>
                                <form method="post">
                                    <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
                                    <td class="py-3 px-6 border border-amber-700">
                                        <select name="status" class="bg-amber-800 border border-amber-600 text-amber-50 px-4 py-2 rounded-md mr-2 focus:outline-none focus:ring-2 focus:ring-amber-500 transition duration-200">
                                            <option value="menunggu" <?= $p['status'] == 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                            <option value="diproses" <?= $p['status'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                            <option value="dikirim" <?= $p['status'] == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                            <option value="selesai" <?= $p['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                        </select>
                                    </td>
                                    <td class="py-3 px-6 border border-amber-700">
                                        <button type="submit" name="submit_status" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-1 rounded w-full">
                                            Update
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>