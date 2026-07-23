<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'];
    $blood_group = $_POST['blood_group'];
    $phone = $_POST['phone'];
    $hospital_id = $_POST['hospital_id'];

    $stmt = $conn->prepare("INSERT INTO recipient (name, blood_group, phone, hospital_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $blood_group, $phone, $hospital_id);
    $stmt->execute();

    echo "<p style='color:green;'>Recipient added successfully!</p>";
}

$hospital_result = $conn->query("SELECT hospital_id, name FROM hospital");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Recipient</title>
</head>
<body>

<h2>Add Recipient</h2>

<form method="POST">

    <label>Recipient Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Blood Group:</label><br>
    <select name="blood_group" required>
        <option value="">Select Blood Group</option>
        <option value="A+">A+</option>
        <option value="A-">A-</option>
        <option value="B+">B+</option>
        <option value="B-">B-</option>
        <option value="AB+">AB+</option>
        <option value="AB-">AB-</option>
        <option value="O+">O+</option>
        <option value="O-">O-</option>
    </select>
    <br><br>

    <label>Phone Number:</label><br>
    <input type="text" name="phone" required><br><br>

    <label>Hospital:</label><br>
    <select name="hospital_id" required>
        <option value="">Select Hospital</option>

        <?php while ($row = $hospital_result->fetch_assoc()) { ?>
            <option value="<?php echo $row['hospital_id']; ?>">
                <?php echo $row['name']; ?>
            </option>
        <?php } ?>

    </select>
    <br><br>

    <input type="submit" value="Add Recipient">

</form>

</body>
</html>