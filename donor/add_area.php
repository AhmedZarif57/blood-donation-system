<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area_name = $_POST['area_name'];
    $district = $_POST['district'];

    $stmt = $conn->prepare("INSERT INTO Area (area_name, district) VALUES (?, ?)");
    $stmt->bind_param("ss", $area_name, $district);
    $stmt->execute();

    echo "<p style='color:green;'>Area added successfully!</p>";
}
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
            <span class="eyebrow">Location setup</span>
            <h1 class="page-title">Add a New Area</h1>
            <p>Create a location entry used by donors, hospitals, and blood banks.</p>
        </section>

        <section class="section-block content-card">
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') { ?>
                <div class="message-box message-success" style="margin-bottom:12px;">Area added successfully!</div>
            <?php } ?>
            <form method="POST">
                <div>
                    <label>Area Name</label>
                    <input type="text" name="area_name" required placeholder="Enter area name">
                </div>

                <div>
                    <label>District</label>
                    <input type="text" name="district" required placeholder="Enter district">
                </div>

                <div class="actions">
                    <button type="submit" class="button-primary">Add Area</button>
                    <a class="button-outline" href="../admin/manage_areas.php">Back</a>
                </div>
            </form>
        </section>
    </div>
</main>
</body>
</html>