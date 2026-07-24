<?php
include '../includes/db_connect.php';
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE Emergency_Request SET status = ? WHERE request_id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    header("Location: view_requests.php");
    exit;
}

$result = $conn->query("SELECT * FROM Emergency_Request WHERE request_id = $id");
$req = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head><title>Update Request</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
    <h1>Update Request #<?= $req['request_id'] ?></h1>
    <form method="POST">
        <select name="status">
            <option value="Pending" <?= $req['status']=='Pending'?'selected':'' ?>>Pending</option>
            <option value="Matched" <?= $req['status']=='Matched'?'selected':'' ?>>Matched</option>
            <option value="Fulfilled" <?= $req['status']=='Fulfilled'?'selected':'' ?>>Fulfilled</option>
            <option value="Cancelled" <?= $req['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
        </select><br><br>
        <button type="submit">Update</button>
    </form>
</body>
</html>