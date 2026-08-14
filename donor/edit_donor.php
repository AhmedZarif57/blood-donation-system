<?php
include '../includes/db_connect.php';
include '../admin/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['availability_status'];
    $stmt = $conn->prepare("UPDATE Donor SET availability_status = ? WHERE donor_id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    header("Location: view_donors.php");
    exit;
}

$result = $conn->query("SELECT * FROM Donor WHERE donor_id = $id");
$donor = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Donor</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Admin edit</span>
            <h1 class="page-title">Edit Donor: <?= htmlspecialchars($donor['name']) ?></h1>
            <p>Update the availability state for matching and admin operations.</p>
        </section>

        <section class="section-block content-card" style="max-width:720px;">
            <form method="POST">
                <div>
                    <label>Availability Status</label>
                    <select name="availability_status">
                        <option value="Available" <?= $donor['availability_status']=='Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Unavailable" <?= $donor['availability_status']=='Unavailable' ? 'selected' : '' ?>>Unavailable</option>
                    </select>
                </div>
                <div class="actions">
                    <button type="submit" class="button-primary">Update</button>
                    <a class="button-outline" href="view_donors.php">Back</a>
                </div>
            </form>
        </section>
    </div>
</main>
</body>
</html>