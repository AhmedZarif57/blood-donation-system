<?php
include '../includes/db_connect.php';

$sql = "SELECT d.donor_id, d.name, d.blood_group, d.phone, d.availability_status, a.area_name
        FROM Donor d
        JOIN Area a ON d.area_id = a.area_id
        ORDER BY d.name";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Donors</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h1>Registered Donors</h1>
    <table>
        <tr>
            <th>Name</th><th>Blood Group</th><th>Phone</th><th>Area</th><th>Status</th><th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['name'] ?></td>
            <td><?= $row['blood_group'] ?></td>
            <td><?= $row['phone'] ?></td>
            <td><?= $row['area_name'] ?></td>
            <td><?= $row['availability_status'] ?></td>
            <td><a href="edit_donor.php?id=<?= $row['donor_id'] ?>">Edit</a></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>