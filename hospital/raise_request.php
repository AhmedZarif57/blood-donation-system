<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_id = $_POST['recipient_id'];
    $hospital_id = $_POST['hospital_id'];
    $blood_group = $_POST['blood_group'];
    $units_needed = $_POST['units_needed'];
    $urgency_level = $_POST['urgency_level'];

    $stmt = $conn->prepare("INSERT INTO Emergency_Request
        (recipient_id, hospital_id, blood_group, units_needed, urgency_level)
        VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisis", $recipient_id, $hospital_id, $blood_group, $units_needed, $urgency_level);
    $stmt->execute();
    echo "<p style='color:green;'>Request submitted!</p>";
}

$recipients = $conn->query("SELECT recipient_id, name, blood_group, hospital_id FROM Recipient");
$hospitals = $conn->query("SELECT hospital_id, name FROM Hospital");
?>
<!DOCTYPE html>
<html>
<head><title>Raise Emergency Request</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
    <h1>Raise an Emergency Blood Request</h1>
    <form method="POST">
        <label>Recipient:</label>
        <select name="recipient_id" required>
            <?php while ($row = $recipients->fetch_assoc()) { ?>
                <option value="<?= $row['recipient_id'] ?>"><?= $row['name'] ?> (<?= $row['blood_group'] ?>)</option>
            <?php } ?>
        </select><br><br>

        <label>Hospital:</label>
        <select name="hospital_id" required>
            <?php $hospitals->data_seek(0); while ($row = $hospitals->fetch_assoc()) { ?>
                <option value="<?= $row['hospital_id'] ?>"><?= $row['name'] ?></option>
            <?php } ?>
        </select><br><br>

        <label>Blood Group Needed:</label>
        <select name="blood_group" required>
            <option value="A+">A+</option><option value="A-">A-</option>
            <option value="B+">B+</option><option value="B-">B-</option>
            <option value="AB+">AB+</option><option value="AB-">AB-</option>
            <option value="O+">O+</option><option value="O-">O-</option>
        </select><br><br>

        <label>Units Needed:</label>
        <input type="number" name="units_needed" min="1" required><br><br>

        <label>Urgency:</label>
        <select name="urgency_level" required>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="Critical">Critical</option>
        </select><br><br>

        <button type="submit">Submit Request</button>
    </form>
</body>
</html>