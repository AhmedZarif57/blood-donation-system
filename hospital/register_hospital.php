<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$name = $_POST['name'];
$phone = $_POST['phone'];
$area_id = $_POST['area_id'];
$stmt = $conn->prepare("INSERT INTO Hospital (name, phone, area_id) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $name, $phone, $area_id);
$stmt->execute();

echo "<p style='color:green;'>Hospital registered successfully!</p>";
}
$areas = $conn->query("SELECT area_id, area_name FROM Area ORDER BY area_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Hospital</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<h1>Register a Hospital</h1>

<form method="POST">

<label>Hospital Name:</label>
<input type="text" name="name" required><br><br>
<label>Phone:</label>
<input type="text" name="phone" required><br><br>
<label>Area:</label>
<select name="area_id" required>
    <?php while ($row = $areas->fetch_assoc()) { ?>
        <option value="<?= $row['area_id'] ?>">
            <?= $row['area_name'] ?>
        </option>
    <?php } ?>
</select><br><br>
<button type="submit">Register</button>
</form>

</body>
</html>