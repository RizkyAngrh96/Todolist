<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit();
}

include 'koneksi.php';

$user = $_SESSION['user'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id) {
    echo "<script>alert('User tidak valid!'); window.location.href='login.php';</script>";
    exit();
}

if(isset($_POST['add'])){
    $task = mysqli_real_escape_string($conn, $_POST['task']);
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
                $subtask = mysqli_real_escape_string($conn, $subtask);
                if (!empty($subtask)) {
                    $q_insert_sub = "INSERT INTO subtasks (taskid, subtasklabel, subtaskstatus) VALUES ('$taskid', '$subtask', 'open')";
                    mysqli_query($conn, $q_insert_sub);
                }
            }
        }
        header('Location: index.php');
        exit();
    }
    
}


// Ambil tugas berdasarkan status dan deadline
$today = date('Y-m-d');

$q_active = "SELECT * FROM tasks WHERE user_id = '$user_id' AND taskstatus = 'open' AND deadline >= '$today' ORDER BY deadline ASC";
$q_completed = "SELECT * FROM tasks WHERE user_id = '$user_id' AND taskstatus = 'close' ORDER BY deadline ASC";
$q_late = "SELECT * FROM tasks WHERE user_id = '$user_id' AND taskstatus = 'open' AND deadline < '$today' ORDER BY deadline ASC";
$q_near_deadline = "SELECT * FROM tasks WHERE user_id = '$user_id' AND taskstatus = 'open' AND deadline BETWEEN '$today' AND DATE_ADD('$today', INTERVAL 1 DAY) ORDER BY deadline ASC";
$tasks_near_deadline = mysqli_query($conn, $q_near_deadline) or die("Query Error: " . mysqli_error($conn));


$tasks_active = mysqli_query($conn, $q_active) or die("Query Error: " . mysqli_error($conn));
$tasks_completed = mysqli_query($conn, $q_completed) or die("Query Error: " . mysqli_error($conn));
$tasks_late = mysqli_query($conn, $q_late) or die("Query Error: " . mysqli_error($conn));

if(isset($_GET['subdelete'])) {
    $subtaskid = intval($_GET['subdelete']);
    $q_delete_sub = "DELETE FROM subtasks WHERE subtaskid = '$subtaskid'";
    mysqli_query($conn, $q_delete_sub);
    header('Location: index.php');
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$search_condition = ($search !== '') ? "AND tasklabel LIKE '%$search%'" : '';

// Update query untuk berbagai kategori tugas
$q_active = "SELECT * FROM tasks WHERE user_id = '$user_id' AND taskstatus = 'open' AND deadline >= '$today' $search_condition ORDER BY deadline ASC";
$q_completed = "SELECT * FROM tasks WHERE user_id = '$user_id' AND taskstatus = 'close' $search_condition ORDER BY deadline ASC";
$q_late = "SELECT * FROM tasks WHERE user_id = '$user_id' AND taskstatus = 'open' AND deadline < '$today' $search_condition ORDER BY deadline ASC";
$q_near_deadline = "SELECT * FROM tasks WHERE user_id = '$user_id' AND taskstatus = 'open' AND deadline BETWEEN '$today' AND DATE_ADD('$today', INTERVAL 1 DAY) $search_condition ORDER BY deadline ASC";

$tasks_near_deadline = mysqli_query($conn, $q_near_deadline) or die("Query Error: " . mysqli_error($conn));
$tasks_active = mysqli_query($conn, $q_active) or die("Query Error: " . mysqli_error($conn));
$tasks_completed = mysqli_query($conn, $q_completed) or die("Query Error: " . mysqli_error($conn));
$tasks_late = mysqli_query($conn, $q_late) or die("Query Error: " . mysqli_error($conn));


if(isset($_GET['delete'])){
    $taskid = intval($_GET['delete']);
    $q_delete = "DELETE FROM tasks WHERE taskid = '$taskid' AND user_id = '$user_id'";
    mysqli_query($conn, $q_delete);
    header('Location: index.php');
    exit();
}

if(isset($_GET['done'])){
    $status = ($_GET['status'] == 'open') ? 'close' : 'open';
    $taskid = intval($_GET['done']);

    $q_update = "UPDATE tasks SET taskstatus = '$status' WHERE taskid = '$taskid' AND user_id = '$user_id'";
    mysqli_query($conn, $q_update);

    $q_update_subtasks = "UPDATE subtasks SET subtaskstatus = '$status' WHERE taskid = '$taskid'";
    mysqli_query($conn, $q_update_subtasks);

    header('Location: index.php');
    exit();
}

if(isset($_GET['subdone'])){
    $subtaskid = intval($_GET['subdone']);
    $status = ($_GET['status'] == 'open') ? 'close' : 'open';

    $q_update_sub = "UPDATE subtasks SET subtaskstatus = '$status' WHERE subtaskid = '$subtaskid'";
    mysqli_query($conn, $q_update_sub);

    header('Location: index.php');
    exit();
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
                <form action="login.php" method="get">
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
                        <input type="text" name="subtasks[]" class="input-control" placeholder="Tambahkan subtugas...">
                    </div>
                    <button type="button" id="add-subtask">Tambah Subtugas</button>
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
            <?php if (mysqli_num_rows($tasks_near_deadline) > 0) : ?>
                <div class="notification">
    <i class='bx bx-bell'></i>
    <div class="notification-content">
        <span>Tugas yang akan segera deadline:</span>
        <ul>
            <?php while ($task = mysqli_fetch_array($tasks_near_deadline)) : ?>
                <li>
                    <strong><?= $task['tasklabel'] ?></strong> - Deadline: <?= date("d M Y", strtotime($task['deadline'])) ?>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

<?php endif; ?>


<h2>Tugas Aktif</h2>
<?php while ($task = mysqli_fetch_array($tasks_active)) : ?>
    <div class="task">
        <input type="checkbox" onclick="window.location.href='?done=<?= $task['taskid'] ?>&status=<?= $task['taskstatus'] ?>'">
        <span><?= $task['tasklabel'] ?></span>
        <button onclick="confirmDelete(<?= $task['taskid'] ?>)">Hapus</button>
        <a href="edit.php?taskid=<?= $task['taskid'] ?>" class="btn-edit">Edit</a>
    </div>

<script>
    function confirmDelete(taskid) {
        if (confirm("Apakah Anda yakin ingin menghapus tugas ini?")) {
            window.location.href = '?delete=' + taskid;
        }
    }
</script>


    <?php
    $taskid = $task['taskid'];
    $q_subtasks = "SELECT * FROM subtasks WHERE taskid = '$taskid' ORDER BY subtaskid ASC";
    $subtasks = mysqli_query($conn, $q_subtasks);
    ?>

    <?php if (mysqli_num_rows($subtasks) > 0) : ?>
        <ul class="subtask-list">
            <?php while ($subtask = mysqli_fetch_array($subtasks)) : ?>
                <li class="subtask <?= $subtask['subtaskstatus'] == 'close' ? 'completed' : '' ?>">
                    <input type="checkbox" onclick="window.location.href='?subdone=<?= $subtask['subtaskid'] ?>&status=<?= $subtask['subtaskstatus'] ?>'" <?= $subtask['subtaskstatus'] == 'close' ? 'checked' : '' ?>>
                    <?= $subtask['subtasklabel'] ?>
                    <button onclick="window.location.href='?subdelete=<?= $subtask['subtaskid'] ?>'">Hapus</button>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>

    <div class="task-date">
        Deadline: <?= date("d M Y", strtotime($task['deadline'])) ?>
    </div>
<?php endwhile; ?>


<h2>Tugas Selesai</h2>
<?php while ($task = mysqli_fetch_array($tasks_completed)) : ?>
    <div class="task completed">
        <?php $isLate = (strtotime($task['deadline']) < time()); ?>
        <span><?= $task['tasklabel'] ?> <?= $isLate ? '<strong>(Terlambat)</strong>' : '' ?></span>
        <button onclick="window.location.href='?delete=<?= $task['taskid'] ?>'">Hapus</button>
    </div>

    <?php
    $taskid = $task['taskid'];
    $q_subtasks = "SELECT * FROM subtasks WHERE taskid = '$taskid' ORDER BY subtaskid ASC";
    $subtasks = mysqli_query($conn, $q_subtasks);
    ?>

    <?php if (mysqli_num_rows($subtasks) > 0) : ?>
        <ul class="subtask-list">
            <?php while ($subtask = mysqli_fetch_array($subtasks)) : ?>
                <li class="subtask completed">
                    <?= $subtask['subtasklabel'] ?>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>
<?php endwhile; ?>




<h2>Tugas Terlambat</h2>
<?php while ($task = mysqli_fetch_array($tasks_late)) : ?>
    <div class="task late">
        <?php $isLate = (strtotime($task['deadline']) < time()); ?>
        <input type="checkbox" onclick="window.location.href='?done=<?= $task['taskid'] ?>&status=<?= $task['taskstatus'] ?>'">
        <span><?= $task['tasklabel'] ?></span>
        <button onclick="window.location.href='?delete=<?= $task['taskid'] ?>'">Hapus</button>
    </div>

    <?php
    $taskid = $task['taskid'];
    $q_subtasks = "SELECT * FROM subtasks WHERE taskid = '$taskid' ORDER BY subtaskid ASC";
    $subtasks = mysqli_query($conn, $q_subtasks);
    ?>

    <?php if (mysqli_num_rows($subtasks) > 0) : ?>
        <ul class="subtask-list">
            <?php while ($subtask = mysqli_fetch_array($subtasks)) : ?>
                <li class="subtask <?= $subtask['subtaskstatus'] == 'close' ? 'completed' : '' ?>">
                    <input type="checkbox" onclick="window.location.href='?subdone=<?= $subtask['subtaskid'] ?>&status=<?= $subtask['subtaskstatus'] ?>'" <?= $subtask['subtaskstatus'] == 'close' ? 'checked disabled' : '' ?>>
                    <?= $subtask['subtasklabel'] ?>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>
<?php endwhile; ?>



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
