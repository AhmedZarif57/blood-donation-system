<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area_name = $_POST['area_name'];
    $district = $_POST['district'];

    $stmt = $conn->prepare("INSERT INTO Area (area_name, district) VALUES (?, ?)");
    $stmt->bind_param("ss", $area_name, $district);
    $stmt->execute();

    echo "<p style='color:green;'>Area added successfully!</p>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Area</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h1>Add a New Area</h1>
    <form method="POST">
        <label>Area Name:</label>
        <input type="text" name="area_name" required><br><br>

        <label>District:</label>
        <input type="text" name="district" required><br><br>

        <button type="submit">Add Area</button>
    </form>
</body>
</html>