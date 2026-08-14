<?php
include 'auth.php';
include '../includes/db_connect.php';

$q = $conn->query("SELECT dn.donation_id, dn.donation_date, dn.units_donated, d.name AS donor_name, b.name AS bank_name FROM Donation dn LEFT JOIN Donor d ON dn.donor_id = d.donor_id LEFT JOIN Blood_Bank b ON dn.bank_id = b.bank_id ORDER BY dn.donation_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donations</title>
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
                    <h1 class="page-title">Manage Donations</h1>
                    <p>Review logged donations and bank assignments.</p>
                </div>
                <a class="button-primary" href="log_donation.php"><i class="bi bi-plus-circle"></i>Log Donation</a>
            </div>
        </section>

        <section class="section-block">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Date</th><th>Donor</th><th>Bank</th><th>Units</th></tr></thead>
                    <tbody>
                        <?php while ($r = $q->fetch_assoc()) { ?>
                        <tr>
                            <td><?= (int)$r['donation_id'] ?></td>
                            <td><?= htmlspecialchars($r['donation_date']) ?></td>
                            <td><?= htmlspecialchars($r['donor_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['bank_name'] ?? '') ?></td>
                            <td><span class="badge status-success"><?= (int)$r['units_donated'] ?></span></td>
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
