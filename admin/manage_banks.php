<?php
include 'auth.php';
include '../includes/db_connect.php';

if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];

    $deleteStmt = $conn->prepare(
        'DELETE FROM Blood_Bank WHERE bank_id = ?'
    );

    if ($deleteStmt) {
        $deleteStmt->bind_param('i', $deleteId);

        if ($deleteStmt->execute()) {
            $_SESSION['admin_delete_success'] = 'Blood bank deleted successfully.';
        } else {
            $_SESSION['admin_delete_error'] =
                'Blood bank could not be deleted. Related records may prevent deletion.';
        }

        $deleteStmt->close();
    } else {
        $_SESSION['admin_delete_error'] =
            'Blood bank could not be deleted because of a database error.';
    }

    header('Location: manage_banks.php');
    exit;
}

$q = $conn->query("
    SELECT
        b.bank_id,
        b.name,
        b.phone,
        a.area_name
    FROM Blood_Bank b
    LEFT JOIN Area a
        ON b.area_id = a.area_id
    ORDER BY b.name ASC
");

$deleteError = $_SESSION['admin_delete_error'] ?? '';
$deleteSuccess = $_SESSION['admin_delete_success'] ?? '';

unset($_SESSION['admin_delete_error']);
unset($_SESSION['admin_delete_success']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Blood Banks</title>

    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>

<?php include '../includes/header.php'; ?>

<main class="page-shell">

    <div class="page-shell">

        <section class="page-hero">

            <div
                class="space-between"
                style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;"
            >

                <div>

                    <span class="eyebrow">
                        Admin data
                    </span>

                    <h1 class="page-title">
                        Manage Blood Banks
                    </h1>

                    <p>
                        Maintain blood bank records, contact details, and capacity values.
                    </p>

                </div>

                <div class="actions">

                    <a
                        class="button-primary"
                        href="add_bank.php"
                    >
                        <i class="bi bi-plus-circle"></i>
                        Add Bank
                    </a>

                    <a
                        class="button-outline"
                        href="../inventory/blood_banks.php"
                    >
                        Public Banks
                    </a>

                </div>

            </div>

        </section>


        <section class="section-block">

            <?php if ($deleteSuccess !== '') { ?>

                <div
                    class="message-box message-success"
                    style="margin-bottom:12px;"
                >
                    <?= htmlspecialchars($deleteSuccess) ?>
                </div>

            <?php } ?>


            <?php if ($deleteError !== '') { ?>

                <div
                    class="message-box message-error"
                    style="margin-bottom:12px;"
                >
                    <?= htmlspecialchars($deleteError) ?>
                </div>

            <?php } ?>


            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Area</th>
                            <th>Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if ($q && $q->num_rows > 0) { ?>

                            <?php while ($r = $q->fetch_assoc()) { ?>

                                <tr>

                                    <td>
                                        #<?= (int)$r['bank_id'] ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($r['name']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($r['phone'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($r['area_name'] ?? '') ?>
                                    </td>

                                    <td>

                                        <div
                                            class="actions"
                                            style="display:flex; gap:8px; flex-wrap:wrap;"
                                        >

                                            <a
                                                class="button-outline"
                                                href="edit_bank.php?id=<?= (int)$r['bank_id'] ?>"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                class="button-danger"
                                                href="?delete_id=<?= (int)$r['bank_id'] ?>"
                                                onclick="return confirm('Delete this blood bank? This action cannot be undone.');"
                                            >
                                                Delete
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php } ?>

                        <?php } else { ?>

                            <tr>

                                <td colspan="5">

                                    <div class="empty-state">

                                        <h3 class="section-title">
                                            No blood banks found
                                        </h3>

                                        <p>
                                            There are currently no blood banks in the system.
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