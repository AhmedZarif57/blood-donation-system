<?php
include '../includes/db_connect.php';

$filter = $_GET['urgency'] ?? 'All';

$sql = "SELECT r.request_id, rec.name AS recipient_name, h.name AS hospital_name,
               r.blood_group, r.units_needed, r.urgency_level, r.status, r.request_date
        FROM Emergency_Request r
        JOIN Recipient rec ON r.recipient_id = rec.recipient_id
        JOIN Hospital h ON r.hospital_id = h.hospital_id";

if ($filter !== 'All') {
    $sql .= " WHERE r.urgency_level = '" . $conn->real_escape_string($filter) . "'";
}
$sql .= " ORDER BY r.request_date DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head><title>Emergency Requests</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
    <h1>Emergency Requests</h1>
    <p>
        Filter: <a href="?urgency=All">All</a> |
        <a href="?urgency=Critical">Critical</a> |
        <a href="?urgency=Medium">Medium</a> |
        <a href="?urgency=Low">Low</a>
    </p>
    <table>
        <tr><th>Recipient</th><th>Hospital</th><th>Blood Group</th><th>Units</th><th>Urgency</th><th>Status</th><th>Action</th></tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['recipient_name'] ?></td>
            <td><?= $row['hospital_name'] ?></td>
            <td><?= $row['blood_group'] ?></td>
            <td><?= $row['units_needed'] ?></td>
            <td><?= $row['urgency_level'] ?></td>
            <td><?= $row['status'] ?></td>
            <td><a href="update_request_status.php?id=<?= $row['request_id'] ?>">Update</a></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>