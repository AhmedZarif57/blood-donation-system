<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor_id = $_POST['donor_id'];
    $bank_id = $_POST['bank_id'];
    $donation_date = $_POST['donation_date'];
    $units_donated = $_POST['units_donated'];

    $stmt = $conn->prepare("INSERT INTO Donation (donor_id, bank_id, donation_date, units_donated)
                            VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $donor_id, $bank_id, $donation_date, $units_donated);
    $stmt->execute();

    $stmt2 = $conn->prepare("UPDATE Donor SET last_donation_date = ? WHERE donor_id = ?");
    $stmt2->bind_param("si", $donation_date, $donor_id);
    $stmt2->execute();

    $donor_info = $conn->query("SELECT blood_group FROM Donor WHERE donor_id = $donor_id")->fetch_assoc();
    $bg = $donor_info['blood_group'];
    $stmt3 = $conn->prepare("UPDATE Blood_Inventory SET units_available = units_available + ?
                             WHERE bank_id = ? AND blood_group = ?");
    $stmt3->bind_param("iis", $units_donated, $bank_id, $bg);
    $stmt3->execute();

    echo "<p style='color:green;'>Donation logged, donor record updated, and inventory increased!</p>";
}

$donors = $conn->query("SELECT donor_id, name, blood_group FROM Donor ORDER BY name");
$banks = $conn->query("SELECT bank_id, name FROM Blood_Bank ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head><title>Log Donation</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
    <h1>Log a Donation</h1>
    <form method="POST">
        <label>Donor:</label>
        <select name="donor_id" required>
            <?php while ($row = $donors->fetch_assoc()) { ?>
                <option value="<?= $row['donor_id'] ?>"><?= $row['name'] ?> (<?= $row['blood_group'] ?>)</option>
            <?php } ?>
        </select><br><br>

        <label>Blood Bank:</label>
        <select name="bank_id" required>
            <?php while ($row = $banks->fetch_assoc()) { ?>
                <option value="<?= $row['bank_id'] ?>"><?= $row['name'] ?></option>
            <?php } ?>
        </select><br><br>

        <label>Donation Date:</label>
        <input type="date" name="donation_date" required><br><br>

        <label>Units Donated:</label>
        <input type="number" name="units_donated" value="1" min="1" required><br><br>

        <button type="submit">Log Donation</button>
    </form>
</body>
</html>
