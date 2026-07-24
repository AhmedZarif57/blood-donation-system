<?php
include '../includes/db_connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $blood_group = $_POST['blood_group'];
    $phone = $_POST['phone'];
    $last_donation_date = $_POST['last_donation_date'] ?: NULL;
    $area_id = $_POST['area_id'];

    $stmt = $conn->prepare("INSERT INTO Donor (name, blood_group, phone, last_donation_date, area_id)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $blood_group, $phone, $last_donation_date, $area_id);
    $stmt->execute();

    echo "<p style='color:green;'>Donor registered successfully!</p>";
}

// Fetch areas for the dropdown
$areas = $conn->query("SELECT area_id, area_name FROM Area ORDER BY area_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Donor Registration</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h1>Register as a Donor</h1>
    <form method="POST">
        <label>Full Name:</label>
        <input type="text" name="name" required><br><br>

        <label>Blood Group:</label>
        <select name="blood_group" required>
            <option value="A+">A+</option>
            <option value="A-">A-</option>
            <option value="B+">B+</option>
            <option value="B-">B-</option>
            <option value="AB+">AB+</option>
            <option value="AB-">AB-</option>
            <option value="O+">O+</option>
            <option value="O-">O-</option>
        </select><br><br>

        <label>Phone:</label>
        <input type="text" name="phone" required><br><br>

        <label>Last Donation Date (leave blank if never donated):</label>
        <input type="date" name="last_donation_date"><br><br>

        <label>Area:</label>
        <select name="area_id" required>
            <?php while ($row = $areas->fetch_assoc()) { ?>
                <option value="<?= $row['area_id'] ?>"><?= $row['area_name'] ?></option>
            <?php } ?>
        </select><br><br>

        <button type="submit">Register</button>
    </form>
</body>
</html>