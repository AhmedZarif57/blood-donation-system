<?php
include 'auth.php';
include '../includes/db_connect.php';

$sql = "
    SELECT
        r.request_id,
        rec.name AS recipient_name,
        h.name AS hospital_name,
        r.blood_group,
        r.units_needed,
        r.urgency_level,
        r.status,
        r.request_date,
        COUNT(DISTINCT dm.donor_id) AS matched_donors
    FROM Emergency_Request r
    LEFT JOIN Recipient rec
        ON r.recipient_id = rec.recipient_id
    LEFT JOIN Hospital h
        ON r.hospital_id = h.hospital_id
    LEFT JOIN Donor_Match dm
        ON r.request_id = dm.request_id
    GROUP BY
        r.request_id,
        rec.name,
        h.name,
        r.blood_group,
        r.units_needed,
        r.urgency_level,
        r.status,
        r.request_date
    ORDER BY r.request_date DESC
";

$q = $conn->query($sql);

function request_status_class($status, $matchedDonors)
{
    if ($matchedDonors > 0 || strtolower($status) === 'fulfilled') {
        return 'status-success';
    }

    if (strtolower($status) === 'pending') {
        return 'status-warning';
    }

    if (strtolower($status) === 'cancelled') {
        return 'status-danger';
    }

    return 'status-neutral';
}

function request_display_status($status, $matchedDonors)
{
    if ($matchedDonors > 0 || strtolower($status) === 'fulfilled') {
        return 'Fulfilled';
    }

    return $status;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>

<?php include '../includes/header.php'; ?>

<main class="page-shell">

    <div class="page-shell">

        <section class="page-hero">
            <div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">

                <div>
                    <span class="eyebrow">Admin data</span>

                    <h1 class="page-title">Manage Requests</h1>

                    <p>
                        Review emergency blood requests and their current matching status.
                    </p>
                </div>

                <a class="button-outline" href="../hospital/view_requests.php">
                    <i class="bi bi-eye"></i>
                    Public View
                </a>

            </div>
        </section>


        <section class="section-block">

            <div class="table-wrap">

                <table>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Recipient</th>
                            <th>Hospital</th>
                            <th>Blood Group</th>
                            <th>Units</th>
                            <th>Urgency</th>
                            <th>Matched</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($q && $q->num_rows > 0) { ?>

                        <?php while ($r = $q->fetch_assoc()) { ?>

                            <?php
                            $matchedDonors = (int)$r['matched_donors'];
                            $displayStatus = request_display_status(
                                $r['status'],
                                $matchedDonors
                            );
                            ?>

                            <tr>

                                <td>
                                    #<?= (int)$r['request_id'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r['recipient_name'] ?? '') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r['hospital_name'] ?? '') ?>
                                </td>

                                <td>
                                    <span class="badge status-neutral">
                                        <?= htmlspecialchars($r['blood_group']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= (int)$r['units_needed'] ?>
                                </td>

                                <td>

                                    <?php
                                    $urgency = strtolower($r['urgency_level']);

                                    if ($urgency === 'critical') {
                                        $urgencyClass = 'status-danger';
                                    } elseif ($urgency === 'medium') {
                                        $urgencyClass = 'status-warning';
                                    } else {
                                        $urgencyClass = 'status-neutral';
                                    }
                                    ?>

                                    <span class="badge <?= $urgencyClass ?>">
                                        <?= htmlspecialchars($r['urgency_level']) ?>
                                    </span>

                                </td>

                                <td>

                                    <?php if ($matchedDonors > 0) { ?>

                                        <span class="badge status-success">
                                            <?= $matchedDonors ?>
                                        </span>

                                    <?php } else { ?>

                                        <span class="badge status-neutral">
                                            0
                                        </span>

                                    <?php } ?>

                                </td>

                                <td>

                                    <span class="badge <?= request_status_class($r['status'], $matchedDonors) ?>">
                                        <?= htmlspecialchars($displayStatus) ?>
                                    </span>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>
                            <td colspan="8">

                                <div class="empty-state">

                                    <h3 class="section-title">
                                        No emergency requests
                                    </h3>

                                    <p>
                                        There are currently no emergency blood requests in the system.
                                    </p>

                                </div>

                            </td>
                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </section>

    </div>

</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>