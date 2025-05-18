<?php
session_start();
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = 'pembeli'; // default role

    // Cek apakah username sudah ada
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan.";
    } else {
        // Simpan ke database
        $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
        mysqli_query($conn, $query);

        // Login otomatis
        $user_id = mysqli_insert_id($conn);
        $_SESSION['user'] = [
            'id' => $user_id,
            'username' => $username,
            'role' => $role
        ];

        header("Location: ../index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-amber-50 overflow-hidden">
    <!-- Navigasi -->
    <nav class="bg-amber-50 shadow p-4 flex justify-between items-center">
        <h1 class="text-xl text-stone-800 font-bold"><a href="../index.php">Toko Sembako</a></h1>
        <div>
            <?php if (!isset($_SESSION['user'])) { ?>
                <a href="/auth/login.php" class="text-amber-500 hover:underline mr-4">Login</a>
                <a href="/auth/register.php" class="text-amber-500 hover:underline">Register</a>
            <?php } else { ?>
                <span class="mr-4">Halo, <?= $_SESSION['user']['username'] ?>!</span>
                <a href="/auth/logout.php" class="text-red-500 hover:underline">Logout</a>
            <?php } ?>
        </div>
    </nav>

    <div class="h-screen flex items-center justify-center">
        <form method="post" class="bg-amber-950/70 p-6 rounded shadow-md w-80 space-y-4">
            <h2 class="text-lg text-amber-50 font-bold text-center">Daftar Akun Pembeli</h2>
            <?php if (isset($error)) echo "<div class='text-red-500 text-sm'>$error</div>"; ?>
            <input type="text" name="username" placeholder="Username" class="border border-gray-300 text-amber-50 w-full p-2 rounded focus:outline-amber-50" required>
            <input type="password" name="password" placeholder="Password" class="border border-gray-300 text-amber-50 w-full p-2 rounded focus:outline-amber-50" required>
            <button type="submit" class="w-full bg-amber-500 text-white py-2 rounded cursor-pointer hover:bg-amber-600">Daftar</button>
            <p class="text-center text-amber-50 text-sm">Sudah punya akun? <a href="login.php" class="text-amber-500 hover:underline">Login</a></p>
        </form>
    </div>
</body>

</html>