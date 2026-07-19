<?php
include '../includes/db_connect.php';

$sql = "SELECT d.name AS donor_name, b.name AS bank_name, dn.donation_date, dn.units_donated
        FROM Donation dn
        JOIN Donor d ON dn.donor_id = d.donor_id
        JOIN Blood_Bank b ON dn.bank_id = b.bank_id
        ORDER BY dn.donation_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head><title>Donation History</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
    <h1>All Donation History</h1>
    <table>
        <tr><th>Donor</th><th>Blood Bank</th><th>Date</th><th>Units</th></tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['donor_name'] ?></td>
            <td><?= $row['bank_name'] ?></td>
            <td><?= $row['donation_date'] ?></td>
            <td><?= $row['units_donated'] ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
