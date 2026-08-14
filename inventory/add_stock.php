<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_id = $_POST['bank_id'];
    $blood_group = $_POST['blood_group'];
    $units_to_add = $_POST['units_to_add'];

    $stmt = $conn->prepare("UPDATE Blood_Inventory
                            SET units_available = units_available + ?, last_updated = NOW()
                            WHERE bank_id = ? AND blood_group = ?");
    $stmt->bind_param("iis", $units_to_add, $bank_id, $blood_group);
    $stmt->execute();
    echo "<p style='color:green;'>Stock updated!</p>";
}

$banks = $conn->query("SELECT bank_id, name FROM Blood_Bank ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Stock management</span>
            <h1 class="page-title">Add Blood Stock</h1>
            <p>Increase inventory units for an existing bank and blood group combination.</p>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <h2 class="section-title">Operational note</h2>
                <p>This updates the current Blood_Inventory row only. The backend logic remains unchanged.</p>
            </div>
            <div class="content-card">
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') { ?>
                    <div class="message-box message-success" style="margin-bottom:12px;">Stock updated!</div>
                <?php } ?>
                <form method="POST">
                    <div>
                        <label>Blood Bank</label>
                        <select name="bank_id" required>
                            <?php while ($row = $banks->fetch_assoc()) { ?>
                                <option value="<?= $row['bank_id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div>
                        <label>Blood Group</label>
                        <select name="blood_group" required>
                            <option value="A+">A+</option><option value="A-">A-</option>
                            <option value="B+">B+</option><option value="B-">B-</option>
                            <option value="AB+">AB+</option><option value="AB-">AB-</option>
                            <option value="O+">O+</option><option value="O-">O-</option>
                        </select>
                    </div>

                    <div>
                        <label>Units to Add</label>
                        <input type="number" name="units_to_add" min="1" required placeholder="Enter units">
                    </div>

                    <button type="submit" class="button-primary">Add Stock</button>
                </form>
            </div>
        </section>
    </div>
</main>
</body>
</html>