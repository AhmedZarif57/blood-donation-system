<?php
include '../includes/db_connect.php';

$district = $_GET['district'] ?? '';
$area_id = isset($_GET['area_id']) ? (int)$_GET['area_id'] : 0;
$blood_group = $_GET['blood_group'] ?? '';

$where = [];
$params = [];
$types = '';

if ($district !== '') {
    $where[] = 'district = ?';
    $params[] = $district;
    $types .= 's';
}

if ($area_id > 0) {
    $where[] = 'area_name = (
        SELECT area_name
        FROM Area
        WHERE area_id = ?
    )';
    $params[] = $area_id;
    $types .= 'i';
}

if ($blood_group !== '') {
    $where[] = 'blood_group = ?';
    $params[] = $blood_group;
    $types .= 's';
}

$sql = "SELECT
            bank_id,
            bank_name AS name,
            contact_number,
            area_name,
            district,
            blood_group,
            units_available
        FROM vw_blood_bank_inventory";

if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY bank_name ASC, blood_group ASC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = false;
}

$areas = $conn->query(
    "SELECT area_id, area_name, district
     FROM Area
     ORDER BY area_name ASC"
);

$districts = $conn->query(
    "SELECT DISTINCT district
     FROM Area
     ORDER BY district ASC"
);

$area_data = [];

if ($areas) {
    while ($r = $areas->fetch_assoc()) {
        $area_data[] = [
            'id' => (int)$r['area_id'],
            'name' => $r['area_name'],
            'district' => $r['district']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><title>Blood Banks</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
                <div>
                    <span class="eyebrow">Blood bank network</span>
                    <h1 class="page-title">Blood Banks</h1>
                    <p>Browse bank locations and capacity using the current district and area filters.</p>
                </div>
            </div>
        </section>

        <section class="section-block filters">
        <form method="GET" class="grid grid-4">

        <div class="filter-field">
            <label>District</label>
            <select id="district" name="district">
                <option value="">All</option>

                <?php while ($d = $districts->fetch_assoc()) {
                    $selected = ($district === $d['district']) ? 'selected' : '';
                ?>
                    <option value="<?= htmlspecialchars($d['district']) ?>" <?= $selected ?>>
                        <?= htmlspecialchars($d['district']) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="filter-field">
            <label>Area</label>
            <select id="area_id" name="area_id">
                <option value="0">All</option>
            </select>
        </div>

        <div class="filter-field">
            <label>Blood Group</label>
            <select name="blood_group">
                <option value="">All</option>

                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg) {
                    $selected = ($blood_group === $bg) ? 'selected' : '';
                ?>
                    <option value="<?= htmlspecialchars($bg) ?>" <?= $selected ?>>
                        <?= htmlspecialchars($bg) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="filter-field" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
            <button type="submit" class="button-primary">
                Filter
            </button>

            <a class="button-outline" href="blood_banks.php">
                Reset
            </a>
        </div>

        </form>
        </section>

        <section class="section-block">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Blood Group</th>
                    <th>Units Available</th>
                    <th>District</th>
                    <th>Area</th>
                    <th>Contact</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($res && $res->num_rows > 0) { ?>

                    <?php while ($row = $res->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($row['name']) ?>
                            </td>

                            <td>
                                <span class="badge status-neutral">
                                    <?= htmlspecialchars($row['blood_group']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="badge status-info">
                                    <?= (int)$row['units_available'] ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['district'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['area_name'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['contact_number'] ?? '') ?>
                            </td>
                        </tr>
                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <h3 class="section-title">No blood banks found</h3>
                                <p>
                                    No blood banks match the selected district or area.
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
    <script>
        const areaData = <?= json_encode($area_data, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
        const districtSelect = document.getElementById('district');
        const areaSelect = document.getElementById('area_id');
        function populate(){ const district=districtSelect.value; areaSelect.innerHTML='<option value="0">All</option>'; areaData.filter(a=>!district||a.district===district).forEach(a=>{const o=document.createElement('option'); o.value=a.id; o.textContent=a.name; areaSelect.appendChild(o);}); <?php if ($area_id>0) echo "areaSelect.value='".(int)$area_id."';"; ?> }
        populate(); districtSelect.addEventListener('change', populate);
    </script>
</body>
</html>
