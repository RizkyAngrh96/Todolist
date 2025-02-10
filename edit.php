<?php
	include 'koneksi.php';

	// select data yang akan diedit
	$q_select = "SELECT * FROM tasks WHERE taskid = '".$_GET['id']."' ";
	$run_q_select = mysqli_query($conn, $q_select);
	$d = mysqli_fetch_object($run_q_select);

	// select subtasks
	$q_subtasks = "SELECT * FROM subtasks WHERE taskid = '".$_GET['id']."'";
	$run_q_subtasks = mysqli_query($conn, $q_subtasks);

	// cek apakah deadline sudah lewat
	$today = date('Y-m-d');
	$deadline_passed = strtotime($d->deadline) < time();

	// proses edit data
	if(isset($_POST['edit'])){
		$new_deadline = $_POST['deadline'];
		if (!$deadline_passed && strtotime($new_deadline) >= strtotime($today)) {
			$q_update = "UPDATE tasks SET tasklabel = '".$_POST['task']."', deadline = '".$new_deadline."' WHERE taskid = '".$_GET['id']."'";
		} else {
			$q_update = "UPDATE tasks SET tasklabel = '".$_POST['task']."' WHERE taskid = '".$_GET['id']."'";
		}
		$run_q_update = mysqli_query($conn, $q_update);

		// update subtasks
		foreach ($_POST['subtasks'] as $subtask_id => $subtask_label) {
			mysqli_query($conn, "UPDATE subtasks SET subtasklabel = '$subtask_label' WHERE subtaskid = '$subtask_id'");
		}

		header('Refresh:0; url=index.php');
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>To Do List</title>
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<style type="text/css">
		@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
		* {
			padding:0;
			margin:0;
			box-sizing: border-box;
		}
		body {
			font-family: 'Roboto', sans-serif;
			background: linear-gradient(to right, #8f94fb, #4e54c8);
		}
		.container {
			width: 590px;
			height: 100vh;
			margin:0 auto;
		}
		.header {
			padding: 15px;
			color: #fff;
		}
		.content {
			padding: 15px;
		}
		.card {
			background-color: #fff;
			padding:15px;
			border-radius: 5px;
			margin-bottom: 10px;
		}
		.input-control {
			width:100%;
			display: block;
			padding:0.5rem;
			font-size: 1rem;
			margin-bottom: 10px;
		}
		.text-right {
			text-align: right;
		}
		button {
			padding:0.5rem 1rem;
			font-size: 1rem;
			cursor: pointer;
			background: linear-gradient(to right, #8f94fb, #4e54c8);
			color: #fff;
			border:1px solid;
			border-radius: 3px;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<div class="title">
				<a href="index.php"><i class='bx bx-chevron-left'></i></a>
				<span>Back</span>
			</div>
			<div class="description">
				<?= date("l, d M Y") ?>
			</div>
		</div>
		<div class="content">
			<div class="card">
				<form action="" method="post">
					<input type="text" name="task" class="input-control" placeholder="Edit task" value="<?= $d->tasklabel ?>" required>
					<?php if (!$deadline_passed): ?>
						<input type="date" name="deadline" class="input-control" value="<?= $d->deadline ?>" min="<?= $today ?>">
					<?php else: ?>
						<p><strong>Deadline:</strong> <?= $d->deadline ?> (Tidak bisa diedit)</p>
					<?php endif; ?>
					<h4>Edit Subtasks:</h4>
					<?php while ($sub = mysqli_fetch_object($run_q_subtasks)): ?>
						<input type="text" name="subtasks[<?= $sub->subtaskid ?>]" class="input-control" value="<?= $sub->subtasklabel ?>">
					<?php endwhile; ?>
					<div class="text-right">
						<button type="submit" name="edit">Edit</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>