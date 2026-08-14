<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $area_id = $_POST['area_id'];

    $stmt = $conn->prepare("INSERT INTO Hospital (name, phone, area_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $name, $phone, $area_id);
    $stmt->execute();
    echo "<p style='color:green;'>Hospital registered!</p>";
}

$areas = $conn->query("SELECT area_id, area_name, district FROM Area ORDER BY area_name ASC");
$districts = $conn->query("SELECT DISTINCT district FROM Area ORDER BY district ASC");

$area_data = [];
while ($row = $areas->fetch_assoc()) {
    $area_data[] = [
        'id' => (int)$row['area_id'],
        'name' => $row['area_name'],
        'district' => $row['district']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head><title>Register Hospital</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
                <div>
                    <span class="eyebrow">Healthcare network</span>
                    <h1 class="page-title">Register a Hospital</h1>
                    <p>Add a hospital profile to the emergency matching network.</p>
                </div>
                <a class="button-outline" href="view_hospitals.php"><i class="bi bi-hospital"></i>View Hospitals</a>
            </div>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <h2 class="section-title">Registration notes</h2>
                <p>Keep the hospital name, contact number, district, and area aligned with the current records so request routing remains accurate.</p>
                <div class="grid" style="margin-top:14px; gap:12px;">
                    <div class="stat-card"><div class="stat-label">Network visibility</div><div class="stat-note">Hospitals appear in request forms and directory listings.</div></div>
                    <div class="stat-card"><div class="stat-label">Location mapping</div><div class="stat-note">District and area selection keeps records organized.</div></div>
                </div>
            </div>
            <div class="content-card">
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') { ?>
                    <div class="message-box message-success" style="margin-bottom:12px;">Hospital registered!</div>
                <?php } ?>
                <form method="POST">
                    <div>
                        <label>Hospital Name</label>
                        <input type="text" name="name" required placeholder="Enter hospital name">
                    </div>

                    <div>
                        <label>Phone</label>
                        <input type="text" name="phone" required placeholder="Enter contact number">
                    </div>

                    <div class="grid grid-2">
                        <div>
                            <label>District</label>
                            <select id="district" required>
                                <option value="">Select District</option>
                                <?php while ($row = $districts->fetch_assoc()) { ?>
                                    <option value="<?= htmlspecialchars($row['district']) ?>"><?= htmlspecialchars($row['district']) ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div>
                            <label>Area</label>
                            <select name="area_id" id="area_id" required disabled>
                                <option value="">Select Area</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="button-primary">Register Hospital</button>
                </form>
            </div>
        </section>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

    <script>
        const areaData = <?= json_encode($area_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const districtSelect = document.getElementById('district');
        const areaSelect = document.getElementById('area_id');

        districtSelect.addEventListener('change', function () {
            const district = this.value;
            areaSelect.innerHTML = '<option value="">Select Area</option>';

            if (!district) {
                areaSelect.disabled = true;
                return;
            }

            const filteredAreas = areaData
                .filter(area => area.district === district)
                .sort((a, b) => a.name.localeCompare(b.name));

            filteredAreas.forEach(area => {
                const option = document.createElement('option');
                option.value = area.id;
                option.textContent = area.name;
                areaSelect.appendChild(option);
            });

            areaSelect.disabled = filteredAreas.length === 0;
        });
    </script>
</body>
</html>