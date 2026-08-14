<?php
include '../includes/db_connect.php';

$sql = "SELECT * FROM vw_recent_donations ORDER BY donation_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation History</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Donation records</span>
            <h1 class="page-title">Donation History</h1>
            <p>Completed donations recorded across all banks and donors.</p>
        </section>

        <section class="section-block">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Donor</th><th>Blood Bank</th><th>Date</th><th>Units</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['donor_name']) ?></td>
                            <td><?= htmlspecialchars($row['bank_name']) ?></td>
                            <td><?= htmlspecialchars($row['donation_date']) ?></td>
                            <td><span class="badge status-success"><?= (int)$row['units_donated'] ?></span></td>
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
