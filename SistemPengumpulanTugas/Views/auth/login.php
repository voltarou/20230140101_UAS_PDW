<?php
// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi.";
    } else {
        // Query database for user using the Users table and password_hash column
        $stmt = $pdo->prepare("SELECT user_id, username, password_hash, role FROM Users WHERE username = ?");

        $stmt->execute([$username]);
        $user = $stmt->fetch();

     if ($user && verifyPassword($password, $user['password_hash'])) {
              // Login berhasil
             $_SESSION['user_id'] = $user['user_id'];
             $_SESSION['username'] = $user['username'];
             $_SESSION['role'] = $user['role'];
             $_SESSION['nama'] = $user['nama']; // Tambahkan ini!

             redirect('index.php?page=home');



            if ($user['role'] === 'asisten') {
                redirect('index.php?page=home'); // Redirect to asisten dashboard
            } else {
                redirect('index.php?page=home'); // Redirect to mahasiswa dashboard
            }
        } else {
            $error = "Username atau password salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SIMPRAK</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="index.php?page=login" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <button type="submit">Login</button>
        </form>
        <p>Belum punya akun? <a href="index.php?page=register">Daftar di sini</a>.</p>
    </div>
</body>
</html>