<?php
include 'auth.php';
include '../includes/db_connect.php';

if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    $deleteStmt = $conn->prepare('DELETE FROM Area WHERE area_id = ?');
    if ($deleteStmt) {
        $deleteStmt->bind_param('i', $deleteId);
        if (!$deleteStmt->execute()) {
            $_SESSION['admin_delete_error'] = 'Area could not be deleted because related records may prevent removal.';
        }
    }
    header('Location: manage_areas.php');
    exit;
}

$q = $conn->query("SELECT area_id, area_name, district FROM Area ORDER BY district, area_name");
$deleteError = $_SESSION['admin_delete_error'] ?? '';
unset($_SESSION['admin_delete_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Areas</title>
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
                    <h1 class="page-title">Manage Areas</h1>
                    <p>Organize districts and areas for donor, hospital, and bank workflows.</p>
                </div>
                <a class="button-primary" href="add_area.php"><i class="bi bi-plus-circle"></i>Add Area</a>
            </div>
        </section>

        <section class="section-block">
            <?php if ($deleteError !== '') { ?><div class="message-box message-error" style="margin-bottom:12px;"><?= htmlspecialchars($deleteError) ?></div><?php } ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Area</th><th>District</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php while ($r = $q->fetch_assoc()) { ?>
                        <tr>
                            <td><?= (int)$r['area_id'] ?></td>
                            <td><?= htmlspecialchars($r['area_name']) ?></td>
                            <td><span class="badge status-neutral"><?= htmlspecialchars($r['district']) ?></span></td>
                            <td><a class="button-danger" href="?delete_id=<?= (int)$r['area_id'] ?>" onclick="return confirm('Delete this area?');">Delete</a></td>
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
