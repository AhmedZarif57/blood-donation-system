<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'] ?? '';
    $capacity = $_POST['capacity'];
    $area_id = $_POST['area_id'];

    $stmt = $conn->prepare("INSERT INTO Blood_Bank (name, phone, capacity, area_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $name, $phone, $capacity, $area_id);
    $stmt->execute();
    $new_bank_id = $stmt->insert_id;

    // Automatically create empty inventory rows for all 8 blood groups
    $groups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
    foreach ($groups as $g) {
        $stmt2 = $conn->prepare("INSERT INTO Blood_Inventory (bank_id, blood_group, units_available) VALUES (?, ?, 0)");
        $stmt2->bind_param("is", $new_bank_id, $g);
        $stmt2->execute();
    }

    echo "<p style='color:green;'>Blood bank registered with empty inventory for all 8 groups!</p>";
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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Blood Bank</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Inventory setup</span>
            <h1 class="page-title">Register a Blood Bank</h1>
            <p>Create a bank record and initialize the inventory structure for all eight blood groups.</p>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <h2 class="section-title">What happens next</h2>
                <p>The existing workflow automatically creates zero-stock inventory rows for each blood group after the bank record is saved.</p>
                <div class="grid" style="margin-top:14px; gap:12px;">
                    <div class="stat-card"><div class="stat-label">Stock structure</div><div class="stat-note">Eight blood group rows are created automatically.</div></div>
                    <div class="stat-card"><div class="stat-label">Location mapping</div><div class="stat-note">District and area selection keeps records organized.</div></div>
                </div>
            </div>
            <div class="content-card">
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') { ?>
                    <div class="message-box message-success" style="margin-bottom:12px;">Blood bank registered with empty inventory for all 8 groups!</div>
                <?php } ?>
                <form method="POST">
                    <div>
                        <label>Bank Name</label>
                        <input type="text" name="name" required placeholder="Enter blood bank name">
                    </div>

                    <div>
                        <label>Primary Contact Number</label>
                        <input type="text" name="phone" placeholder="Enter contact number">
                    </div>

                    <div>
                        <label>Capacity (total units storable)</label>
                        <input type="number" name="capacity" required placeholder="Enter capacity">
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

                    <button type="submit" class="button-primary">Register Bank</button>
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