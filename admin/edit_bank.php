<?php
include 'auth.php';
include '../includes/db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

if ($id <= 0) {
    header('Location: manage_banks.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);
    $area_id = (int)($_POST['area_id'] ?? 0);

    if ($name === '') {
        $error = 'Bank name is required.';
    } else {
        $stmt = $conn->prepare('UPDATE Blood_Bank SET name = ?, phone = ?, capacity = ?, area_id = ? WHERE bank_id = ?');
        $stmt->bind_param('ssiii', $name, $phone, $capacity, $area_id, $id);
        if ($stmt->execute()) {
            $message = 'Blood bank updated successfully.';
        } else {
            $error = 'Could not update the bank record.';
        }
    }
}

$bank = $conn->query("SELECT bank_id, name, phone, capacity, area_id FROM Blood_Bank WHERE bank_id = $id")->fetch_assoc();
if (!$bank) {
    header('Location: manage_banks.php');
    exit;
}

$areas = $conn->query('SELECT area_id, area_name FROM Area ORDER BY area_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blood Bank</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Admin update</span>
            <h1 class="page-title">Edit Blood Bank</h1>
            <p>Update the bank contact number, capacity, and area assignment.</p>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <p class="helper-text">This keeps the current bank records aligned with the public blood banks directory and admin inventory tools.</p>
            </div>
            <div class="content-card">
                <?php if ($message !== '') { ?><div class="message-box message-success" style="margin-bottom:12px;"><?= htmlspecialchars($message) ?></div><?php } ?>
                <?php if ($error !== '') { ?><div class="message-box message-error" style="margin-bottom:12px;"><?= htmlspecialchars($error) ?></div><?php } ?>
                <form method="POST">
                    <div>
                        <label>Name</label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($bank['name']) ?>">
                    </div>
                    <div>
                        <label>Primary Contact Number</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($bank['phone'] ?? '') ?>" placeholder="Enter contact number">
                    </div>
                    <div>
                        <label>Capacity</label>
                        <input type="number" name="capacity" min="0" value="<?= (int)$bank['capacity'] ?>">
                    </div>
                    <div>
                        <label>Area</label>
                        <select name="area_id">
                            <option value="0">-- Select --</option>
                            <?php while ($a = $areas->fetch_assoc()) { $selected = ((int)$a['area_id'] === (int)$bank['area_id']) ? 'selected' : ''; ?>
                                <option value="<?= (int)$a['area_id'] ?>" <?= $selected ?>><?= htmlspecialchars($a['area_name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="actions">
                        <button type="submit" class="button-primary">Save Changes</button>
                        <a class="button-outline" href="manage_banks.php">Back</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
