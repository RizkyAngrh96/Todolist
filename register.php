<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi username (hanya huruf dan angka)
    if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        echo "<script>alert('Username hanya boleh berisi huruf dan angka.'); window.location='register.php';</script>";
        exit;
    }

    // Validasi password (hanya huruf dan angka)
    if (!preg_match('/^[a-zA-Z0-9]+$/', $password)) {
        echo "<script>alert('Password hanya boleh berisi huruf dan angka.'); window.location='register.php';</script>";
        exit;
    }

    // Validasi panjang password (minimal 8 karakter)
    if (strlen($password) < 8) {
        echo "<script>alert('Password harus memiliki minimal 8 karakter.'); window.location='register.php';</script>";
        exit;
    }

    // Hash password setelah validasi
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Cek apakah username sudah ada
    $check_sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        echo "<script>alert('Username sudah digunakan. Silakan pilih username lain.'); window.location='register.php';</script>";
    } else {
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST" action="register.php">
            <input type="text" name="username" placeholder="Username (tanpa simbol)" required>
            <input type="password" name="password" placeholder="Password (minimal 8 karakter, tanpa simbol)" required>
            <button type="submit" name="register">Register</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
