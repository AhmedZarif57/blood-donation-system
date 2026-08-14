<?php
include 'auth.php';
include '../includes/db_connect.php';

$areas = $conn->query("SELECT area_id, area_name FROM Area ORDER BY area_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $capacity = (int)($_POST['capacity'] ?? 0);
    $area_id = (int)($_POST['area_id'] ?? 0);
    if ($name) {
        $stmt = $conn->prepare('INSERT INTO Blood_Bank (name, phone, capacity, area_id) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssii', $name, $phone, $capacity, $area_id);
        $stmt->execute();
        header('Location: manage_banks.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blood Bank</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Admin create</span>
            <h1 class="page-title">Add Blood Bank</h1>
            <p>Create a bank record and prepare it for inventory management.</p>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <p class="helper-text">The bank is stored with the entered capacity and area selection, then appears in the admin management page.</p>
            </div>
            <div class="content-card">
                <form method="POST">
                    <div>
                        <label>Name</label>
                        <input name="name" required placeholder="Enter bank name">
                    </div>
                    <div>
                        <label>Contact Number</label>
                        <input name="phone" placeholder="Enter primary contact number">
                    </div>
                    <div>
                        <label>Initial Capacity (units)</label>
                        <input name="capacity" type="number" min="0" value="0" placeholder="0">
                    </div>
                    <div>
                        <label>Area</label>
                        <select name="area_id">
                            <option value="0">-- Select --</option>
                            <?php while($a=$areas->fetch_assoc()){ echo '<option value="'.$a['area_id'].'">'.htmlspecialchars($a['area_name']).'</option>'; } ?>
                        </select>
                    </div>
                    <div class="actions">
                        <button type="submit" class="button-primary">Create Bank</button>
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
