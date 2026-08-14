<?php
include 'auth.php';
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area_name = $_POST['area_name'] ?? '';
    $district = $_POST['district'] ?? '';
    if ($area_name && $district) {
        $stmt = $conn->prepare('INSERT INTO Area (area_name, district) VALUES (?, ?)');
        $stmt->bind_param('ss', $area_name, $district);
        $stmt->execute();
        header('Location: manage_areas.php'); exit;
    }
}

$districts = $conn->query("SELECT DISTINCT district FROM Area ORDER BY district ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Area</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Admin create</span>
            <h1 class="page-title">Add Area</h1>
            <p>Create a district-area record for donor, hospital, and blood bank workflows.</p>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <p class="helper-text">This directly inserts a new Area row and returns to the management screen after save.</p>
            </div>
            <div class="content-card">
                <form method="POST">
                    <div>
                        <label>Area Name</label>
                        <input name="area_name" required placeholder="Enter area name">
                    </div>
                    <div>
                        <label>District</label>
                        <input name="district" required list="districts" placeholder="Enter district">
                        <datalist id="districts"><?php while($d=$districts->fetch_assoc()){ echo '<option value="'.htmlspecialchars($d['district']).'">'; } ?></datalist>
                    </div>
                    <div class="actions">
                        <button type="submit" class="button-primary">Add Area</button>
                        <a class="button-outline" href="manage_areas.php">Back</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
