<?php
include 'auth.php';
include '../includes/db_connect.php';

$areas = $conn->query("SELECT area_id, area_name FROM Area ORDER BY area_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $area_id = (int)($_POST['area_id'] ?? 0);
    if ($name) {
        $stmt = $conn->prepare('INSERT INTO Hospital (name, phone, area_id) VALUES (?, ?, ?)');
        $stmt->bind_param('ssi', $name, $phone, $area_id);
        $stmt->execute();
        header('Location: manage_hospitals.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Hospital</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Admin create</span>
            <h1 class="page-title">Add Hospital</h1>
            <p>Create a hospital entry for the request and matching workflows.</p>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <p class="helper-text">Hospitals added here become available in emergency request forms immediately after save.</p>
            </div>
            <div class="content-card">
                <form method="POST">
                    <div>
                        <label>Name</label>
                        <input name="name" required placeholder="Enter hospital name">
                    </div>
                    <div>
                        <label>Phone</label>
                        <input name="phone" placeholder="Enter phone number">
                    </div>
                    <div>
                        <label>Area</label>
                        <select name="area_id">
                            <option value="0">-- Select --</option>
                            <?php while($a=$areas->fetch_assoc()){ echo '<option value="'.$a['area_id'].'">'.htmlspecialchars($a['area_name']).'</option>'; } ?>
                        </select>
                    </div>
                    <div class="actions">
                        <button type="submit" class="button-primary">Create Hospital</button>
                        <a class="button-outline" href="manage_hospitals.php">Back</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
