<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit();
}
include 'koneksi.php';

$user = $_SESSION['user'];
$user_id = $_SESSION['user_id'];

if(isset($_POST['add'])){
    $task = $_POST['task'];
    $deadline = $_POST['deadline']; 
    $today = date('Y-m-d');

    if ($deadline < $today) {
        echo "<script>alert('Tanggal deadline tidak boleh di masa lalu!'); window.location.href='index.php';</script>";
        exit();
    }
    $q_insert = "INSERT INTO tasks (user_id, tasklabel, taskstatus, deadline) VALUES ('$user_id', '$task', 'open', '$deadline')";
    $run_q_insert = mysqli_query($conn, $q_insert);
    
    if($run_q_insert){
        $taskid = mysqli_insert_id($conn);
        
        if (!empty($_POST['subtasks'])) {
            foreach ($_POST['subtasks'] as $subtask) {
                if (!empty($subtask)) {
                    $q_insert_sub = "INSERT INTO subtasks (taskid, subtasklabel, subtaskstatus) VALUES ('$taskid', '$subtask', 'open')";
                    mysqli_query($conn, $q_insert_sub);
                }
            }
        }
        header('Refresh:0; url=index.php');
    }
}

if(isset($_GET['subdelete'])) {
    $subtaskid = $_GET['subdelete'];
    $q_delete_sub = "DELETE FROM subtasks WHERE subtaskid = '$subtaskid'";
    mysqli_query($conn, $q_delete_sub);
    header('Refresh:0; url=index.php');
}

$search = "";
if(isset($_GET['search'])){
    $search = $_GET['search'];
    $q_select = "SELECT * FROM tasks WHERE user_id = '$user_id' AND tasklabel LIKE '%$search%' ORDER BY deadline ASC, taskstatus DESC";
} else {
    $q_select = "SELECT * FROM tasks WHERE user_id = '$user_id' ORDER BY deadline ASC, taskstatus DESC";
}
$run_q_select = mysqli_query($conn, $q_select);

if(isset($_GET['delete'])){
    $q_delete = "DELETE FROM tasks WHERE taskid = '".$_GET['delete']."' AND user_id = '$user_id'";
    mysqli_query($conn, $q_delete);
    header('Refresh:0; url=index.php');
}

if(isset($_GET['done'])){
    $status = $_GET['status'] == 'open' ? 'close' : 'open';
    $taskid = $_GET['done'];
    
    $q_update = "UPDATE tasks SET taskstatus = '$status' WHERE taskid = '$taskid' AND user_id = '$user_id'";
    mysqli_query($conn, $q_update);
    
    $q_update_subtasks = "UPDATE subtasks SET subtaskstatus = '$status' WHERE taskid = '$taskid'";
    mysqli_query($conn, $q_update_subtasks);
    
    header('Refresh:0; url=index.php');
}

if(isset($_GET['subdone'])){
    $subtaskid = $_GET['subdone'];
    $status = $_GET['status'] == 'open' ? 'close' : 'open';
    
    $q_update_sub = "UPDATE subtasks SET subtaskstatus = '$status' WHERE subtaskid = '$subtaskid'";
    mysqli_query($conn, $q_update_sub);
    
    header('Refresh:0; url=index.php');
}

if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">
                <i class='bx bx-list-check'></i>
                <span>To-Do List</span>
            </div>
            <div class="description">
                <?= date("l, d M Y") ?>
            </div>
            <div class="logout">
                <form action="" method="get">
                    <button type="submit" name="logout" class="btn-logout">Logout</button>
                </form>
            </div>
        </div>

        <div class="content">
            <div class="card">
                <form action="" method="post">
                    <input type="text" name="task" class="input-control" placeholder="Tambahkan tugas..." required>
                    <input type="date" name="deadline" class="input-control" required>
                    <div id="subtask-container">
                        <input type="text" name="subtasks[]" class="input-control" placeholder="Tambahkan subtask...">
                    </div>
                    <button type="button" id="add-subtask">Tambah Sub Tugas</button>
                    <div class="text-right">
                        <button type="submit" name="add">Tambah</button>
                    </div>
                </form>
            </div>

            <div class="search-box">
                <form action="" method="get">
                    <input type="text" name="search" class="input-control" placeholder="Cari tugas..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Cari</button>
                </form>
            </div>

            <?php while($r = mysqli_fetch_array($run_q_select)): ?>
                <div class="card">
                    <div class="task-item <?= $r['taskstatus'] == 'close' ? 'done':'' ?>">
                        <div>
                            <input type="checkbox" onclick="window.location.href = '?done=<?= $r['taskid'] ?>&status=<?= $r['taskstatus'] ?>'" <?= $r['taskstatus'] == 'close' ? 'checked disabled' : '' ?>>
                            <span><?= $r['tasklabel'] ?></span>
                        </div>
                        <div class="actions">
                            <button onclick="window.location.href='edit.php?id=<?= $r['taskid'] ?>'" class="btn-edit">Edit Tugas</button>
                            <button onclick="if(confirm('Hapus tugas ini?')) window.location.href='?delete=<?= $r['taskid'] ?>'" class="btn-delete">Hapus Tugas</button>
                        </div>
                    </div>

                    <!-- Tampilkan Subtasks -->
                    <div class="subtasks">
                        <?php
                        $q_subtasks = "SELECT * FROM subtasks WHERE taskid = '".$r['taskid']."' ";
                        $run_q_subtasks = mysqli_query($conn, $q_subtasks);
                        while($sub = mysqli_fetch_array($run_q_subtasks)):
                        ?>
                            <div class="subtask-item <?= $sub['subtaskstatus'] == 'close' ? 'done':'' ?>">
                                <input type="checkbox" onclick="window.location.href = '?subdone=<?= $sub['subtaskid'] ?>&status=<?= $sub['subtaskstatus'] ?>'" <?= $sub['subtaskstatus'] == 'close' ? 'checked disabled' : '' ?>>
                                <span><?= $sub['subtasklabel'] ?></span>
                                <a href="?subdelete=<?= $sub['subtaskid'] ?>" class="delete" onclick="return confirm('Hapus subtask ini?')"><i class="bx bx-trash"></i></a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="task-meta">
                        Dibuat: <?= date("d M Y", strtotime($r['createdat'])) ?> | Deadline: <?= date("d M Y", strtotime($r['deadline'])) ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>

    <!-- Pindahkan script ini ke bawah dan di luar loop -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("add-subtask").addEventListener("click", function() {
                let container = document.getElementById("subtask-container");
                let newInput = document.createElement("input");
                newInput.type = "text";
                newInput.name = "subtasks[]";
                newInput.className = "input-control";
                newInput.placeholder = "Tambahkan subtask...";
                container.appendChild(newInput);
            });
        });
    </script>
</body>
</html>
