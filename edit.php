<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$taskid = $_GET['taskid'] ?? '';

if (!$taskid) {
    echo "<script>alert('Tugas tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

// Ambil data tugas utama
$q_task = "SELECT * FROM tasks WHERE taskid = '$taskid' AND user_id = '$user_id'";
$task = mysqli_query($conn, $q_task);
$task_data = mysqli_fetch_assoc($task);

if (!$task_data) {
    echo "<script>alert('Tugas tidak valid!'); window.location.href='index.php';</script>";
    exit();
}

// Ambil data subtugas
$q_subtasks = "SELECT * FROM subtasks WHERE taskid = '$taskid'";
$subtasks = mysqli_query($conn, $q_subtasks);

// Proses update tugas utama
if (isset($_POST['update_task'])) {
    $tasklabel = mysqli_real_escape_string($conn, $_POST['tasklabel']);
    $deadline = $_POST['deadline'];
    $today = date('Y-m-d');

    if (empty($tasklabel)) {
        echo "<script>alert('Nama tugas tidak boleh kosong!');</script>";
    } elseif ($deadline <= $today) {
        echo "<script>alert('Deadline hanya bisa diatur untuk besok atau lebih!');</script>";
    } else {
        $q_update_task = "UPDATE tasks SET tasklabel = '$tasklabel', deadline = '$deadline' WHERE taskid = '$taskid'";
        mysqli_query($conn, $q_update_task);
        echo "<script>alert('Tugas berhasil diperbarui!'); window.location.href='index.php';</script>";
    }
}

// Proses update subtugas
if (isset($_POST['update_subtask'])) {
    $subtaskid = $_POST['subtaskid'];
    $subtasklabel = mysqli_real_escape_string($conn, $_POST['subtasklabel']);

    if (empty(trim($subtasklabel))) {
        echo "<script>alert('Subtugas tidak boleh kosong!'); window.location.href='edit.php?taskid=$taskid';</script>";
        exit();
    }
    
    $q_update_subtask = "UPDATE subtasks SET subtasklabel = '$subtasklabel' WHERE subtaskid = '$subtaskid'";
    if (mysqli_query($conn, $q_update_subtask)) {
        echo "<script>alert('Subtugas berhasil diperbarui!'); window.location.href='edit.php?taskid=$taskid';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui subtugas!');</script>";
    }
    
}

// Proses tambah subtugas
if (isset($_POST['add_subtask'])) {
    $new_subtasklabel = mysqli_real_escape_string($conn, $_POST['new_subtasklabel']);

    if (empty($new_subtasklabel)) {
        echo "<script>alert('Subtugas tidak boleh kosong!');</script>";
    } else {
        $q_add_subtask = "INSERT INTO subtasks (taskid, subtasklabel) VALUES ('$taskid', '$new_subtasklabel')";
        mysqli_query($conn, $q_add_subtask);
        echo "<script>alert('Subtugas berhasil ditambahkan!'); window.location.href='edit.php?taskid=$taskid';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Tugas</h2>
        <form action="" method="post">
            <label>Nama Tugas</label>
            <input type="text" name="tasklabel" class="input-control" value="<?= $task_data['tasklabel'] ?>" required>

            <label>Deadline</label>
            <input type="date" name="deadline" class="input-control" value="<?= $task_data['deadline'] ?>" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">

            <button type="submit" name="update_task">Simpan Perubahan</button>
        </form>

        <h3>Edit Subtugas</h3>
        <?php while ($subtask = mysqli_fetch_array($subtasks)) : ?>
            <form action="" method="post" style="display: flex; gap: 5px; margin-bottom: 10px;">
                <input type="hidden" name="subtaskid" value="<?= $subtask['subtaskid'] ?>">
                <input type="text" name="subtasklabel" class="input-control" value="<?= $subtask['subtasklabel'] ?>" required>
                <button type="submit" name="update_subtask">Simpan</button>
            </form>
        <?php endwhile; ?>

        <h3>Tambah Subtugas</h3>
        <form action="" method="post" style="display: flex; gap: 5px; margin-bottom: 10px;">
            <input type="text" name="new_subtasklabel" class="input-control" placeholder="Masukkan subtugas baru" required>
            <button type="submit" name="add_subtask">Tambah</button>
        </form>

        <br>
        <button onclick="window.location.href='index.php';">Kembali</button>
    </div>
</body>
</html>
