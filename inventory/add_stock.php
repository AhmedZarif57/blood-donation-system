<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_id = $_POST['bank_id'];
    $blood_group = $_POST['blood_group'];
    $units_to_add = $_POST['units_to_add'];

    $stmt = $conn->prepare("UPDATE Blood_Inventory
                            SET units_available = units_available + ?, last_updated = NOW()
                            WHERE bank_id = ? AND blood_group = ?");
    $stmt->bind_param("iis", $units_to_add, $bank_id, $blood_group);
    $stmt->execute();
    echo "<p style='color:green;'>Stock updated!</p>";
}

$banks = $conn->query("SELECT bank_id, name FROM Blood_Bank ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Stock</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h1>Add Blood Stock</h1>
    <form method="POST">
        <label>Blood Bank:</label>
        <select name="bank_id" required>
            <?php while ($row = $banks->fetch_assoc()) { ?>
                <option value="<?= $row['bank_id'] ?>"><?= $row['name'] ?></option>
            <?php } ?>
        </select><br><br>

        <label>Blood Group:</label>
        <select name="blood_group" required>
            <option value="A+">A+</option><option value="A-">A-</option>
            <option value="B+">B+</option><option value="B-">B-</option>
            <option value="AB+">AB+</option><option value="AB-">AB-</option>
            <option value="O+">O+</option><option value="O-">O-</option>
        </select><br><br>

        <label>Units to Add:</label>
        <input type="number" name="units_to_add" min="1" required><br><br>

        <button type="submit">Add Stock</button>
    </form>
</body>
</html>