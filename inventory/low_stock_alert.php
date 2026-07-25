<?php
include '../includes/db_connect.php';

$threshold = 5; // units below this count as "low stock"

$sql = "SELECT b.name AS bank_name, i.blood_group, i.units_available
        FROM Blood_Inventory i
        JOIN Blood_Bank b ON i.bank_id = b.bank_id
        WHERE i.units_available < $threshold
        ORDER BY i.units_available ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Low Stock Alert</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h1>⚠ Low Stock Alert (below <?= $threshold ?> units)</h1>
    <table>
        <tr><th>Bank</th><th>Blood Group</th><th>Units Left</th></tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr style="background:#ffe5e5;">
            <td><?= $row['bank_name'] ?></td>
            <td><?= $row['blood_group'] ?></td>
            <td><?= $row['units_available'] ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>