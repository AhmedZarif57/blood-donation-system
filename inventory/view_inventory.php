<?php
include '../includes/db_connect.php';
include '../admin/auth.php';

$details = [];
$bankList = $conn->query("SELECT bank_id, name FROM Blood_Bank ORDER BY name ASC");

if ($bankList) {
    while ($bank = $bankList->fetch_assoc()) {
        $bankId = (int)$bank['bank_id'];
        $stmt = $conn->prepare('CALL sp_get_bank_inventory(?)');
        if ($stmt) {
            $stmt->bind_param('i', $bankId);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $details[] = $row;
                }
            }
            $stmt->close();
            while ($conn->more_results()) {
                $conn->next_result();
            }
        }
    }
}

usort($details, function ($a, $b) {
    $bankCmp = strcmp((string)($a['bank_name'] ?? ''), (string)($b['bank_name'] ?? ''));
    if ($bankCmp !== 0) {
        return $bankCmp;
    }

    return strcmp((string)($a['blood_group'] ?? ''), (string)($b['blood_group'] ?? ''));
});

$summary = [];
foreach ($details as $row) {
    $group = (string)($row['blood_group'] ?? '');
    if (!isset($summary[$group])) {
        $summary[$group] = 0;
    }
    $summary[$group] += (int)$row['units_available'];
}
ksort($summary);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory (Admin)</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
                <div>
                    <span class="eyebrow">Inventory control</span>
                    <h1 class="page-title">Blood Inventory</h1>
                    <p>System-wide stock visibility and detailed bank-level inventory for administrators.</p>
                </div>
                <a class="button-outline" href="add_stock.php"><i class="bi bi-plus-circle"></i>Add Stock</a>
            </div>
        </section>

        <section class="section-block grid grid-4">
            <?php foreach ($summary as $bloodGroup => $totalUnits) { ?>
                <div class="stat-card card">
                    <div class="stat-label"><?= htmlspecialchars($bloodGroup) ?></div>
                    <div class="stat-value"><?= (int)$totalUnits ?></div>
                    <div class="stat-note">Total units available across all banks</div>
                </div>
            <?php } ?>
        </section>

        <section class="section-block">
            <div class="content-card">
                <h2 class="section-title">Detailed inventory by bank</h2>
                <div class="table-wrap" style="margin-top:14px;">
                    <table>
                        <thead>
                            <tr><th>Bank</th><th>Blood Group</th><th>Units</th><th>Last Updated</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $row) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['bank_name']) ?></td>
                                <td><span class="badge status-neutral"><?= htmlspecialchars($row['blood_group']) ?></span></td>
                                <td><span class="badge <?= ((int)$row['units_available'] <= 5) ? 'status-danger' : (((int)$row['units_available'] <= 10) ? 'status-warning' : 'status-success') ?>"><?= (int)$row['units_available'] ?></span></td>
                                <td><?= htmlspecialchars($row['last_updated']) ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>