<?php
include '../includes/db_connect.php';

$threshold = 5; // units below this count as "low stock"

$sql = "SELECT b.name AS bank_name, i.blood_group, i.units_available
        FROM Blood_Inventory i
        JOIN Blood_Bank b ON i.bank_id = b.bank_id
        WHERE i.units_available < $threshold
        ORDER BY i.units_available ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Inventory alert</span>
            <h1 class="page-title">Low Stock Alert</h1>
            <p>Items below <?= $threshold ?> units are highlighted for immediate operational review.</p>
        </section>

        <section class="section-block">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Bank</th><th>Blood Group</th><th>Units Left</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['bank_name']) ?></td>
                            <td><span class="badge status-neutral"><?= htmlspecialchars($row['blood_group']) ?></span></td>
                            <td><span class="badge status-danger"><?= (int)$row['units_available'] ?></span></td>
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