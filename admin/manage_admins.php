<?php
include 'auth.php';
include '../includes/db_connect.php';

$res = $conn->query('SELECT admin_id, full_name, username, phone FROM Admin ORDER BY full_name ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins</title>
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
                    <h1 class="page-title">Manage Admins</h1>
                    <p>Review existing admin accounts and create additional administrators securely.</p>
                </div>
                <a class="button-primary" href="create_admin.php"><i class="bi bi-person-plus"></i>Add Admin</a>
            </div>
        </section>

        <section class="section-block content-card">
            <p class="helper-text">Only an authenticated admin can create another admin.</p>
            <div class="table-wrap" style="margin-top:12px;">
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th>Username</th><th>Phone</th></tr></thead>
                    <tbody>
                        <?php while ($r = $res->fetch_assoc()) { ?>
                        <tr>
                            <td><?= (int)$r['admin_id'] ?></td>
                            <td><?= htmlspecialchars($r['full_name']) ?></td>
                            <td><?= htmlspecialchars($r['username']) ?></td>
                            <td><?= htmlspecialchars($r['phone']) ?></td>
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
