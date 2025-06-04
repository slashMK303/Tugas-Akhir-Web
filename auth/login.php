<?php

session_start();
include '../config/koneksi.php';

// Validasi login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Ambil data user berdasarkan username
    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        // Verifikasi password yang dimasukkan dengan hashed password di database
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Login gagal. Username atau password salah.";
        }
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-amber-50 font-sans antialiased">

    <nav class="bg-amber-950 shadow-lg p-4 flex justify-between items-center fixed w-full z-20 top-0">
        <h1 class="text-2xl text-amber-50 font-extrabold tracking-wide"><a href="../index.php">Berkah Jaya</a></h1>
        <div class="flex items-center space-x-6">
            <?php if (!isset($_SESSION['user'])) { ?>
                <a href="/auth/login.php" class="text-amber-200 hover:text-amber-50 transition duration-300 ease-in-out">Login</a>
                <a href="../auth/register.php" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition duration-300 ease-in-out">Register</a>
            <?php } else { ?>
                <span class="text-amber-200 text-lg">Halo, <span class="font-semibold"><?= $_SESSION['user']['username'] ?></span>!</span>
                <a href="/auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out">Logout</a>
            <?php } ?>
        </div>
    </nav>

    <div class="min-h-screen flex items-center justify-center pt-20 pb-10">
        <form method="post" class="bg-amber-950/90 p-6 md:p-12 rounded-xl shadow-lg w-full max-w-md space-y-6">
            <h2 class="text-3xl text-amber-50 font-extrabold text-center mb-6">Login ke Akun Anda</h2>
            <?php if (isset($error)) echo "<div class='bg-red-500 text-white p-3 rounded-md text-center text-sm'>$error</div>"; ?>
            <div class="space-y-4">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <input type="text" id="username" name="username" placeholder="Username"
                        class="w-full bg-amber-900 border border-amber-700 text-amber-50 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200 placeholder-amber-200" required>
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input type="password" id="password" name="password" placeholder="Password"
                        class="w-full bg-amber-900 border border-amber-700 text-amber-50 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200 placeholder-amber-200" required>
                </div>
                <button type="submit"
                    class="w-full bg-amber-500 text-white font-semibold py-3 rounded-md hover:bg-amber-600 transition duration-300 ease-in-out">
                    Login
                </button>
                <p class="text-center text-amber-200 text-sm mt-4">Belum punya akun? <a href="register.php"
                        class="text-amber-300 hover:underline font-medium">Daftar Sekarang</a></p>
            </div>
        </form>
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