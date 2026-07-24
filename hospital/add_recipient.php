<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $blood_group = $_POST['blood_group'];
    $phone = $_POST['phone'];
    $hospital_id = $_POST['hospital_id'];

    $stmt = $conn->prepare("INSERT INTO Recipient (name, blood_group, phone, hospital_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $blood_group, $phone, $hospital_id);
    $stmt->execute();
    echo "<p style='color:green;'>Recipient added!</p>";
}

$hospitals = $conn->query("SELECT hospital_id, name FROM Hospital ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head><title>Add Recipient</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
    <h1>Add a Recipient</h1>
    <form method="POST">
        <label>Recipient Name:</label>
        <input type="text" name="name" required><br><br>

        <label>Blood Group Needed:</label>
        <select name="blood_group" required>
            <option value="A+">A+</option><option value="A-">A-</option>
            <option value="B+">B+</option><option value="B-">B-</option>
            <option value="AB+">AB+</option><option value="AB-">AB-</option>
            <option value="O+">O+</option><option value="O-">O-</option>
        </select><br><br>

        <label>Phone:</label>
        <input type="text" name="phone"><br><br>

        <label>Admitted At:</label>
        <select name="hospital_id" required>
            <?php while ($row = $hospitals->fetch_assoc()) { ?>
                <option value="<?= $row['hospital_id'] ?>"><?= $row['name'] ?></option>
            <?php } ?>
        </select><br><br>

        <button type="submit">Add Recipient</button>
    </form>
</body>
</html>