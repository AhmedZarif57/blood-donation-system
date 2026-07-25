<?php
include '../includes/db_connect.php';

// Detailed view
$detail_sql = "SELECT b.name AS bank_name, i.blood_group, i.units_available, i.last_updated
               FROM Blood_Inventory i
               JOIN Blood_Bank b ON i.bank_id = b.bank_id
               ORDER BY b.name, i.blood_group";
$details = $conn->query($detail_sql);

// Summary: total units per blood group, system-wide
$summary_sql = "SELECT blood_group, SUM(units_available) AS total_units
                FROM Blood_Inventory
                GROUP BY blood_group
                ORDER BY blood_group";
$summary = $conn->query($summary_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Blood Inventory</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h1>System-Wide Stock Summary</h1>
    <table>
        <tr><th>Blood Group</th><th>Total Units Available</th></tr>
        <?php while ($row = $summary->fetch_assoc()) { ?>
        <tr><td><?= $row['blood_group'] ?></td><td><?= $row['total_units'] ?></td></tr>
        <?php } ?>
    </table>

    <h1>Detailed Inventory by Bank</h1>
    <table>
        <tr><th>Bank</th><th>Blood Group</th><th>Units</th><th>Last Updated</th></tr>
        <?php while ($row = $details->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['bank_name'] ?></td>
            <td><?= $row['blood_group'] ?></td>
            <td><?= $row['units_available'] ?></td>
            <td><?= $row['last_updated'] ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>