<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $capacity = $_POST['capacity'];
    $area_id = $_POST['area_id'];

    $stmt = $conn->prepare("INSERT INTO Blood_Bank (name, capacity, area_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $name, $capacity, $area_id);
    $stmt->execute();
    $new_bank_id = $stmt->insert_id;

    // Automatically create empty inventory rows for all 8 blood groups
    $groups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
    foreach ($groups as $g) {
        $stmt2 = $conn->prepare("INSERT INTO Blood_Inventory (bank_id, blood_group, units_available) VALUES (?, ?, 0)");
        $stmt2->bind_param("is", $new_bank_id, $g);
        $stmt2->execute();
    }

    echo "<p style='color:green;'>Blood bank registered with empty inventory for all 8 groups!</p>";
}

$areas = $conn->query("SELECT area_id, area_name FROM Area ORDER BY area_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Blood Bank</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h1>Register a Blood Bank</h1>
    <form method="POST">
        <label>Bank Name:</label>
        <input type="text" name="name" required><br><br>

        <label>Capacity (total units storable):</label>
        <input type="number" name="capacity" required><br><br>

        <label>Area:</label>
        <select name="area_id" required>
            <?php while ($row = $areas->fetch_assoc()) { ?>
                <option value="<?= $row['area_id'] ?>"><?= $row['area_name'] ?></option>
            <?php } ?>
        </select><br><br>

        <button type="submit">Register Bank</button>
    </form>
</body>
</html>