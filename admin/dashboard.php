<?php
include '../includes/db_connect.php';
include '../includes/header.php';

$summary = $conn->query("SELECT * FROM vw_available_donors_by_group ORDER BY area_name, blood_group");
$pending_matches = $conn->query("
    SELECT dm.match_id, d.name AS donor_name, r.blood_group, r.urgency_level, dm.match_status
    FROM Donor_Match dm
    JOIN Donor d ON dm.donor_id = d.donor_id
    JOIN Emergency_Request r ON dm.request_id = r.request_id
    ORDER BY dm.match_date DESC
    LIMIT 20
");
?>
<h1>Admin Dashboard</h1>

<h2>Available Donors by Area & Blood Group</h2>
<table>
    <tr><th>Area</th><th>Blood Group</th><th>Available Donors</th></tr>
    <?php while ($row = $summary->fetch_assoc()) { ?>
    <tr><td><?= $row['area_name'] ?></td><td><?= $row['blood_group'] ?></td><td><?= $row['available_donors'] ?></td></tr>
    <?php } ?>
</table>

<h2>Recent Auto-Generated Matches</h2>
<table>
    <tr><th>Donor</th><th>Blood Group Needed</th><th>Urgency</th><th>Match Status</th></tr>
    <?php while ($row = $pending_matches->fetch_assoc()) { ?>
    <tr><td><?= $row['donor_name'] ?></td><td><?= $row['blood_group'] ?></td><td><?= $row['urgency_level'] ?></td><td><?= $row['match_status'] ?></td></tr>
    <?php } ?>
</table>