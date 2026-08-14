<?php
include '../includes/db_connect.php';

$district = $_GET['district'] ?? '';
$area_id = isset($_GET['area_id']) ? (int)$_GET['area_id'] : 0;
$blood_group = $_GET['blood_group'] ?? '';
$availability = $_GET['availability'] ?? 'All';

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

if ($availability === 'Available') {
    $sql = "SELECT donor_id, name, blood_group, availability_status, area_name, district
            FROM vw_available_donors";
} else {
    $sql = "SELECT
                d.donor_id,
                d.name,
                d.blood_group,
                d.availability_status,
                a.area_name,
                a.district
            FROM Donor d
            LEFT JOIN Area a ON a.area_id = d.area_id";
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY name ASC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
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
    while ($row = $areas->fetch_assoc()) {
        $area_data[] = [
            'id' => (int)$row['area_id'],
            'name' => $row['area_name'],
            'district' => $row['district']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donors Directory</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
                <div>
                    <span class="eyebrow">Public directory</span>
                    <h1 class="page-title">Donors Directory</h1>
                    <p>Search donors by district, area, blood group, and availability.</p>
                </div>
                <a class="button-primary" href="register_donor.php"><i class="bi bi-person-plus"></i>Register Donor</a>
            </div>
        </section>

        <section class="section-block filters">
            <form method="GET" class="grid grid-4">
                <div class="filter-field">
                    <label>District</label>
                    <select id="district" name="district">
                        <option value="">All</option>
                        <?php while ($d = $districts->fetch_assoc()) { $sel = ($district === $d['district']) ? 'selected' : ''; ?>
                            <option value="<?= htmlspecialchars($d['district']) ?>" <?= $sel ?>><?= htmlspecialchars($d['district']) ?></option>
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
                        <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg) {
                            $s = ($blood_group === $bg) ? 'selected' : ''; echo "<option value=\"$bg\" $s>$bg</option>";
                        } ?>
                    </select>
                </div>

                <div class="filter-field">
                    <label>Availability</label>
                    <select name="availability">
                        <option value="All">All</option>
                        <option value="Available" <?= $availability==='Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Unavailable" <?= $availability==='Unavailable' ? 'selected' : '' ?>>Unavailable</option>
                    </select>
                </div>

                <div class="filter-field" style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="submit" class="button-primary">Filter</button>
                    <a class="button-outline" href="view_donors.php">Reset</a>
                </div>
            </form>
        </section>

        <section class="section-block">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th><th>Blood Group</th><th>District</th><th>Area</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><span class="badge status-neutral"><?= htmlspecialchars($row['blood_group']) ?></span></td>
                            <td><?= htmlspecialchars($row['district'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['area_name'] ?? '') ?></td>
                            <td><span class="badge <?= strtolower($row['availability_status']) === 'available' ? 'status-success' : 'status-warning' ?>"><?= htmlspecialchars($row['availability_status']) ?></span></td>
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

        function populateAreas() {
            const district = districtSelect.value;
            areaSelect.innerHTML = '<option value="0">All</option>';
            const filtered = areaData.filter(a=>!district||a.district===district).sort((a,b)=>a.name.localeCompare(b.name));
            filtered.forEach(a=>{ const o=document.createElement('option'); o.value=a.id; o.textContent=a.name; areaSelect.appendChild(o); });
            // preserve selected area from server
            <?php if ($area_id>0) { echo "areaSelect.value='" . (int)$area_id . "';"; } ?>
        }
        populateAreas();
        districtSelect.addEventListener('change', populateAreas);
    </script>
</body>
</html>