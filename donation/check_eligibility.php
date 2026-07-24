<?php
include '../includes/db_connect.php';

$sql = "SELECT d.donor_id, d.name, d.blood_group, d.last_donation_date,
               CASE
                   WHEN d.last_donation_date IS NULL THEN 'Eligible (never donated)'
                   WHEN DATEDIFF(CURDATE(), d.last_donation_date) >= 90 THEN 'Eligible'
                   ELSE CONCAT('Not eligible — ', 90 - DATEDIFF(CURDATE(), d.last_donation_date), ' days left')
               END AS eligibility_status
        FROM Donor d
        ORDER BY d.last_donation_date ASC";
$result = $conn->query($sql);

// Bonus: a subquery example — donors who have donated more than once
$repeat_sql = "SELECT name FROM Donor
               WHERE donor_id IN (
                   SELECT donor_id FROM Donation GROUP BY donor_id HAVING COUNT(*) > 1
               )";
$repeat_donors = $conn->query($repeat_sql);
?>
<!DOCTYPE html>
<html>
<head><title>Donor Eligibility</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
    <h1>Donor Eligibility Status (90-Day Rule)</h1>
    <table>
        <tr><th>Name</th><th>Blood Group</th><th>Last Donation</th><th>Status</th></tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['name'] ?></td>
            <td><?= $row['blood_group'] ?></td>
            <td><?= $row['last_donation_date'] ?? 'Never' ?></td>
            <td><?= $row['eligibility_status'] ?></td>
        </tr>
        <?php } ?>
    </table>

    <h2>Repeat Donors (Subquery Example)</h2>
    <ul>
        <?php while ($row = $repeat_donors->fetch_assoc()) { ?>
            <li><?= $row['name'] ?></li>
        <?php } ?>
    </ul>
</body>
</html>
