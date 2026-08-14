<?php
include 'auth.php';
include '../includes/db_connect.php';

if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    $deleteStmt = $conn->prepare('DELETE FROM Hospital WHERE hospital_id = ?');
    if ($deleteStmt) {
        $deleteStmt->bind_param('i', $deleteId);
        if (!$deleteStmt->execute()) {
            $_SESSION['admin_delete_error'] = 'Hospital could not be deleted because related records may prevent removal.';
        }
    }
    header('Location: manage_hospitals.php');
    exit;
}

$q = $conn->query("SELECT h.hospital_id, h.name, h.phone, a.area_name FROM Hospital h LEFT JOIN Area a ON h.area_id = a.area_id ORDER BY h.name ASC");
$deleteError = $_SESSION['admin_delete_error'] ?? '';
unset($_SESSION['admin_delete_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hospitals</title>
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
                    <h1 class="page-title">Manage Hospitals</h1>
                    <p>Keep hospital records aligned with the emergency request network.</p>
                </div>
                <a class="button-primary" href="add_hospital.php"><i class="bi bi-plus-circle"></i>Add Hospital</a>
            </div>
        </section>

        <section class="section-block">
            <?php if ($deleteError !== '') { ?><div class="message-box message-error" style="margin-bottom:12px;"><?= htmlspecialchars($deleteError) ?></div><?php } ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Area</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php while ($r = $q->fetch_assoc()) { ?>
                        <tr>
                            <td><?= (int)$r['hospital_id'] ?></td>
                            <td><?= htmlspecialchars($r['name']) ?></td>
                            <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['area_name'] ?? '') ?></td>
                            <td><a class="button-danger" href="?delete_id=<?= (int)$r['hospital_id'] ?>" onclick="return confirm('Delete this hospital?');">Delete</a></td>
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
