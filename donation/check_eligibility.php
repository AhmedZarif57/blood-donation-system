<?php
include '../includes/db_connect.php';

$sql = "SELECT d.donor_id, d.name, d.blood_group, d.last_donation_date,
               CASE
                   WHEN d.last_donation_date IS NULL THEN 'Eligible (never donated)'
                   WHEN DATEDIFF(CURDATE(), d.last_donation_date) >= 90 THEN 'Eligible'
                   ELSE CONCAT('Not eligible — ', 90 - DATEDIFF(CURDATE(), d.last_donation_date), ' days left')
               END AS eligibility_status
        FROM Donor d
        ORDER BY d.name ASC";
$result = $conn->query($sql);

// Bonus: a subquery example — donors who have donated more than once
$repeat_sql = "SELECT name FROM Donor
               WHERE donor_id IN (
                   SELECT donor_id FROM Donation GROUP BY donor_id HAVING COUNT(*) > 1
               )";
$repeat_donors = $conn->query($repeat_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Eligibility</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Donation safety</span>
            <h1 class="page-title">Donor Eligibility Status</h1>
            <p>Eligibility is evaluated using the existing 90-day rule and repeat donor subquery.</p>
        </section>

        <section class="section-block">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Name</th><th>Blood Group</th><th>Last Donation</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><span class="badge status-neutral"><?= htmlspecialchars($row['blood_group']) ?></span></td>
                            <td><?= htmlspecialchars($row['last_donation_date'] ?? 'Never') ?></td>
                            <td>
                                <?php
                                    $eligibilityClass = strpos(strtolower($row['eligibility_status']), 'not eligible') !== false ? 'status-warning' : 'status-success';
                                ?>
                                <span class="badge <?= $eligibilityClass ?>"><?= htmlspecialchars($row['eligibility_status']) ?></span>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section-block content-card">
            <h2 class="section-title">Repeat Donors</h2>
            <p class="helper-text">Subquery example based on donors with more than one recorded donation.</p>
            <div class="actions" style="margin-top:12px;">
                <?php while ($row = $repeat_donors->fetch_assoc()) { ?>
                    <span class="badge status-info"><?= htmlspecialchars($row['name']) ?></span>
                <?php } ?>
            </div>
        </section>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
