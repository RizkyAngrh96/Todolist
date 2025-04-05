<?php
// Konfigurasi database
$host = "localhost";       // Host database (biasanya "localhost")
$username = "root";        // Username MySQL (default: "root")
$password = "";            // Password MySQL (kosong jika default di XAMPP)
$database = "todo_app"; // Ganti dengan nama database Anda

// Membuat koneksi ke database
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Jika koneksi berhasil
// echo "Koneksi berhasil!"; // Uncomment untuk debugging
?>
