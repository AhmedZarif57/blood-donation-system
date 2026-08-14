<?php
include 'auth.php';
include '../includes/db_connect.php';

$q = $conn->query("SELECT d.donor_id, d.name, d.blood_group, d.availability_status, a.area_name FROM Donor d LEFT JOIN Area a ON d.area_id = a.area_id ORDER BY d.name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donors</title>
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
                    <h1 class="page-title">Manage Donors</h1>
                    <p>Review donor profiles and update availability status when needed.</p>
                </div>
                <a class="button-primary" href="../donor/register_donor.php"><i class="bi bi-person-plus"></i>Add Donor</a>
            </div>
        </section>

        <section class="section-block">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th>Blood Group</th><th>Area</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php while ($r = $q->fetch_assoc()) { ?>
                        <tr>
                            <td><?= (int)$r['donor_id'] ?></td>
                            <td><?= htmlspecialchars($r['name']) ?></td>
                            <td><span class="badge status-neutral"><?= htmlspecialchars($r['blood_group']) ?></span></td>
                            <td><?= htmlspecialchars($r['area_name'] ?? '') ?></td>
                            <td><span class="badge <?= strtolower($r['availability_status']) === 'available' ? 'status-success' : 'status-warning' ?>"><?= htmlspecialchars($r['availability_status']) ?></span></td>
                            <td><a class="button-outline" href="../donor/edit_donor.php?id=<?= $r['donor_id'] ?>">Edit</a></td>
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
