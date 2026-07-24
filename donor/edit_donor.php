<?php
include '../includes/db_connect.php';

$id = $_GET['id'];

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
<html>
<head><title>Edit Donor</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
    <h1>Edit Donor: <?= $donor['name'] ?></h1>
    <form method="POST">
        <label>Availability Status:</label>
        <select name="availability_status">
            <option value="Available" <?= $donor['availability_status']=='Available' ? 'selected' : '' ?>>Available</option>
            <option value="Unavailable" <?= $donor['availability_status']=='Unavailable' ? 'selected' : '' ?>>Unavailable</option>
        </select><br><br>
        <button type="submit">Update</button>
    </form>
</body>
</html>