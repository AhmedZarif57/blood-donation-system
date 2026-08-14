<?php
include '../includes/db_connect.php';
include '../admin/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Request</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Admin update</span>
            <h1 class="page-title">Update Request #<?= (int)$req['request_id'] ?></h1>
            <p>Change the request status without altering the request record.</p>
        </section>

        <section class="section-block content-card" style="max-width:720px;">
            <form method="POST">
                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="Pending" <?= $req['status']=='Pending'?'selected':'' ?>>Pending</option>
                        <option value="Matched" <?= $req['status']=='Matched'?'selected':'' ?>>Matched</option>
                        <option value="Fulfilled" <?= $req['status']=='Fulfilled'?'selected':'' ?>>Fulfilled</option>
                        <option value="Cancelled" <?= $req['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="actions">
                    <button type="submit" class="button-primary">Update</button>
                    <a class="button-outline" href="view_requests.php">Back</a>
                </div>
            </form>
        </section>
    </div>
</main>
</body>
</html>