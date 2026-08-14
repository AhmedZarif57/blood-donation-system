<?php
include '../includes/db_connect.php';

$district = $_GET['district'] ?? '';
$area_id = isset($_GET['area_id']) ? (int)$_GET['area_id'] : 0;

$where = [];
$params = [];
if ($district !== '') { $where[] = 'a.district = ?'; $params[] = $district; }
if ($area_id > 0) { $where[] = 'h.area_id = ?'; $params[] = $area_id; }
$where_sql = count($where)>0 ? 'WHERE '.implode(' AND ', $where) : '';

$sql = "SELECT h.hospital_id, h.name, h.phone, a.area_name, a.district FROM Hospital h LEFT JOIN Area a ON a.area_id = h.area_id $where_sql ORDER BY h.name ASC";
$stmt = $conn->prepare($sql);
if ($stmt) {
    if (count($params)>0) { $types = str_repeat('s', count($params)); $stmt->bind_param($types, ...array_map('strval',$params)); }
    $stmt->execute();
    $res = $stmt->get_result();
} else { $res = $conn->query($sql); }

$areas = $conn->query("SELECT area_id, area_name, district FROM Area ORDER BY area_name ASC");
$districts = $conn->query("SELECT DISTINCT district FROM Area ORDER BY district ASC");
$area_data = [];
while ($r = $areas->fetch_assoc()) { $area_data[] = ['id'=>(int)$r['area_id'],'name'=>$r['area_name'],'district'=>$r['district']]; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospitals</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
                <div>
                    <span class="eyebrow">Directory</span>
                    <h1 class="page-title">Hospitals</h1>
                    <p>Browse hospital records by district and area.</p>
                </div>
                <a class="button-primary" href="register_hospital.php"><i class="bi bi-hospital"></i>Register Hospital</a>
            </div>
        </section>

        <section class="section-block filters">
            <form method="GET" class="grid grid-3">
                <div class="filter-field">
                    <label>District</label>
                    <select id="district" name="district"><option value="">All</option><?php while ($d=$districts->fetch_assoc()){ $s = ($district===$d['district'])?'selected':''; ?><option value="<?=htmlspecialchars($d['district'])?>" <?=$s?>><?=htmlspecialchars($d['district'])?></option><?php } ?></select>
                </div>
                <div class="filter-field">
                    <label>Area</label>
                    <select id="area_id" name="area_id"><option value="0">All</option></select>
                </div>
                <div class="filter-field" style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="submit" class="button-primary">Filter</button>
                    <a class="button-outline" href="view_hospitals.php">Reset</a>
                </div>
            </form>
        </section>

        <section class="section-block">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Name</th><th>Phone</th><th>District</th><th>Area</th></tr></thead>
                    <tbody>
                        <?php while ($row = $res->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['district'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['area_name'] ?? '') ?></td>
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
