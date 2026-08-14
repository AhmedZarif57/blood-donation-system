<?php
include '../includes/db_connect.php';

$request_id = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;
$fatalMessage = '';
if ($request_id <= 0) {
    http_response_code(400);
    $fatalMessage = 'Invalid request identifier.';
}

// Verify request exists
$request = null;
if ($fatalMessage === '') {
    $rq = $conn->prepare("SELECT r.request_id, r.blood_group, r.units_needed, r.urgency_level, h.name AS hospital_name FROM Emergency_Request r LEFT JOIN Hospital h ON h.hospital_id = r.hospital_id WHERE r.request_id = ?");
    $rq->bind_param('i', $request_id);
    $rq->execute();
    $rres = $rq->get_result();
    if (!$rres || $rres->num_rows === 0) {
        $fatalMessage = 'Request not found.';
    } else {
        $request = $rres->fetch_assoc();
    }
}

// Get matched donors for this request
$res = null;
if ($fatalMessage === '') {
    $stmt = $conn->prepare("SELECT dm.match_id, dm.match_date, dm.match_status, d.donor_id, d.name, d.blood_group, d.phone, d.availability_status, a.area_name, a.district FROM Donor_Match dm JOIN Donor d ON d.donor_id = dm.donor_id LEFT JOIN Area a ON a.area_id = d.area_id WHERE dm.request_id = ? ORDER BY dm.match_date ASC");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $res = $stmt->get_result();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matched Donors</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <?php if ($fatalMessage !== '') { ?>
            <div class="empty-state">
                <h1 class="page-title"><?= htmlspecialchars($fatalMessage) ?></h1>
                <p><a class="button-primary" href="raise_request.php">Back to Request Form</a></p>
            </div>
        <?php } else { ?>
            <section class="page-hero">
                <div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
                    <div>
                        <span class="eyebrow">Matching results</span>
                        <h1 class="page-title">Matched Donors</h1>
                        <p>Request #<?= htmlspecialchars($request['request_id']) ?> · Blood group <?= htmlspecialchars($request['blood_group']) ?> · <?= (int)$request['units_needed'] ?> units · <?= htmlspecialchars($request['urgency_level']) ?> urgency</p>
                    </div>
                    <a class="button-outline" href="raise_request.php">New Request</a>
                </div>
            </section>

            <section class="section-block grid grid-4">
                <div class="stat-card card"><div class="stat-label">Hospital</div><div class="stat-value" style="font-size:1.45rem;"><?= htmlspecialchars($request['hospital_name'] ?? '') ?></div></div>
                <div class="stat-card card"><div class="stat-label">Blood group</div><div class="stat-value" style="font-size:1.45rem;"><?= htmlspecialchars($request['blood_group']) ?></div></div>
                <div class="stat-card card"><div class="stat-label">Units needed</div><div class="stat-value" style="font-size:1.45rem;"><?= (int)$request['units_needed'] ?></div></div>
                <div class="stat-card card"><div class="stat-label">Urgency</div><div class="stat-value" style="font-size:1.45rem;"><?= htmlspecialchars($request['urgency_level']) ?></div></div>
            </section>

            <section class="section-block">
                <?php if ($res && $res->num_rows > 0) { ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Donor</th>
                                    <th>Blood Group</th>
                                    <th>District</th>
                                    <th>Area</th>
                                    <th>Availability</th>
                                    <th>Phone</th>
                                    <th>Matched At</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $res->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><span class="badge status-neutral"><?= htmlspecialchars($row['blood_group']) ?></span></td>
                                        <td><?= htmlspecialchars($row['district'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['area_name'] ?? '') ?></td>
                                        <td><span class="badge <?= strtolower($row['availability_status']) === 'available' ? 'status-success' : 'status-warning' ?>"><?= htmlspecialchars($row['availability_status']) ?></span></td>
                                        <td><?= htmlspecialchars($row['phone']) ?></td>
                                        <td><?= htmlspecialchars($row['match_date']) ?></td>
                                        <td><span class="badge <?= strtolower($row['match_status']) === 'matched' ? 'status-success' : 'status-neutral' ?>"><?= htmlspecialchars($row['match_status']) ?></span></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="helper-text" style="margin-top:12px;">Phone numbers are shown only on this page so staff can contact matched donors directly.</p>
                <?php } else { ?>
                    <div class="empty-state">
                        <h2 class="section-title">No matching donors found</h2>
                        <p>Try broadening the search or contact nearby hospitals and blood banks.</p>
                    </div>
                <?php } ?>
            </section>

            <section class="section-block actions">
                <a class="button-primary" href="raise_request.php">Back to Request Form</a>
                <a class="button-outline" href="/blood_donation/index.php">Home</a>
            </section>
        <?php } ?>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>

<?php
if (isset($stmt) && $stmt) { $stmt->close(); }
if (isset($rq) && $rq) { $rq->close(); }
?>
