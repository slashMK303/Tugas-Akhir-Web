<?php

session_start();
include '../config/koneksi.php';

// Validasi login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user'] = $user;
        header("Location: ../index.php");
        exit();
    } else {
        $error = "Login gagal. Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-amber-50 overflow-hidden">

    <!-- Navigasi -->
    <nav class="bg-amber-50 shadow p-4 flex relative justify-between items-center">
        <h1 class="text-xl text-stone-800 font-bold"><a href="../index.php">Toko Sembako</a></h1>
        <div>
            <?php if (!isset($_SESSION['user'])) { ?>
                <a href="/auth/login.php" class="text-amber-500 cursor-pointer hover:underline mr-4">Login</a>
                <a href="../auth/register.php" class="text-amber-500 cursor-pointer hover:underline">Register</a>
            <?php } else { ?>
                <span class="mr-4">Halo, <?= $_SESSION['user']['username'] ?>!</span>
                <a href="/auth/logout.php" class="text-red-500 cursor-pointer hover:underline">Logout</a>
            <?php } ?>
        </div>
    </nav>

    <div class="h-screen flex items-center justify-center">
        <form method="post" class="bg-amber-950/70 p-6 md:p-12 rounded shadow-md w-full md:max-w-xl space-y-6">
            <h2 class="text-lg text-amber-50 font-bold text-center">Login</h2>
            <?php if (isset($error)) echo "<div class='text-red-500 text-sm'>$error</div>"; ?>
            <div class="flex flex-col space-y-4 md:space-y-6">
                <div class="flex flex-col md:flex-row space-x-0 md:space-x-4">
                    <input type="text" name="username" placeholder="Username" class="border text-amber-50 border-gray-300 w-full p-2 rounded focus:outline-amber-50" required>
                </div>
                <div class="flex flex-col md:flex-row space-x-0 md:space-x-4">
                    <input type="password" name="password" placeholder="Password" class="border text-amber-50 border-gray-300 w-full p-2 rounded focus:outline-amber-50" required>
                </div>
                <button type="submit" class="w-full bg-amber-500 text-amber-50 py-2 rounded cursor-pointer hover:bg-amber-600">Login</button>
                <p class="text-center text-amber-50 text-sm">Belum punya akun? <a href="register.php" class="text-amber-500 hover:underline">Daftar</a></p>
            </div>
        </form>
    </div>
</body>

</html>