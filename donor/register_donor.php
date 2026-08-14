<?php

include '../includes/db_connect.php';

$conn->query("ALTER TABLE Donor AUTO_INCREMENT = 1001");

$message = '';
$error = '';

function valid_phone($p) {
    // allow +, digits, spaces, hyphens; require 7-20 digits
    $digits = preg_replace('/\D+/', '', $p);
    return strlen($digits) >= 7 && strlen($digits) <= 20;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $blood_group = $_POST['blood_group'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $last_donation_date = $_POST['last_donation_date'] ?: NULL;
    $area_id = (int)($_POST['area_id'] ?? 0);

    // Server-side validation
    if ($name === '' || $blood_group === '' || $phone === '' || $area_id <= 0) {
        $error = 'Please complete all required fields.';
    } elseif (!in_array($blood_group, ['A+','A-','B+','B-','AB+','AB-','O+','O-'])) {
        $error = 'Invalid blood group.';
    } elseif (!valid_phone($phone)) {
        $error = 'Invalid phone number format.';
    }

    // Prevent duplicate by phone
    if ($error === '') {
        $dup = $conn->prepare('SELECT donor_id FROM Donor WHERE phone = ? LIMIT 1');
        $dup->bind_param('s', $phone);
        $dup->execute();
        $dr = $dup->get_result();
        if ($dr && $dr->num_rows > 0) {
            $error = 'A donor with this phone number is already registered.';
        }
    }

    if ($error === '') {
        $stmt = $conn->prepare("INSERT INTO Donor (name, blood_group, phone, last_donation_date, area_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $blood_group, $phone, $last_donation_date, $area_id);
        if ($stmt->execute()) {
            $newDonorId = $conn->insert_id;
            $message = "<div style='margin:20px 0; padding:16px 20px; border:2px solid #990011; background:#eef9f1; color:#1f7a3f; font-size:1.1rem; font-weight:700;'>Registration successful. Your Unique Donor ID is: {$newDonorId}.</div>";
        } else {
            $error = 'Database error: ' . $conn->error;
        }
    }
}

// Fetch areas and districts for cascading dropdown
$areas = $conn->query("SELECT area_id, area_name, district FROM Area ORDER BY area_name ASC");
$districts = $conn->query("SELECT DISTINCT district FROM Area ORDER BY district ASC");

$area_data = [];
while ($row = $areas->fetch_assoc()) {
    $area_data[] = [ 'id' => (int)$row['area_id'], 'name' => $row['area_name'], 'district' => $row['district'] ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Registration</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
                <div>
                    <span class="eyebrow">Donor onboarding</span>
                    <h1 class="page-title">Register as a Donor</h1>
                    <p>Join the emergency response network and make your availability visible to hospitals and requesters.</p>
                </div>
                <a class="button-outline" href="view_donors.php"><i class="bi bi-people"></i>Browse Donors</a>
            </div>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <h2 class="section-title">Why register?</h2>
                <p>Your profile helps the matching engine surface compatible donors faster when an urgent request is created.</p>
                <div class="grid" style="margin-top:14px; gap:12px;">
                    <div class="stat-card"><div class="stat-label">Available visibility</div><div class="stat-note">Show your blood group and area to the network.</div></div>
                    <div class="stat-card"><div class="stat-label">Eligibility tracking</div><div class="stat-note">Keep donation timing visible to administrators.</div></div>
                    <div class="stat-card"><div class="stat-label">Emergency response</div><div class="stat-note">Help hospitals find donors in less time.</div></div>
                </div>
            </div>

            <div class="content-card">
                <?php if ($message !== '') { ?>
                    <div class="message-box message-success" style="margin-bottom:12px;"> <?= $message ?> </div>
                <?php } ?>
                <?php if ($error) { ?>
                    <div class="message-box message-error" style="margin-bottom:12px;"><?= htmlspecialchars($error) ?></div>
                <?php } ?>
                <form method="POST">
                    <div>
                        <label>Full Name</label>
                        <input type="text" name="name" required placeholder="Enter your full name">
                    </div>

                    <div>
                        <label>Blood Group</label>
                        <select name="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>

                    <div>
                        <label>Phone</label>
                        <input type="text" name="phone" required placeholder="Enter contact number">
                    </div>

                    <div>
                        <label>Last Donation Date</label>
                        <input type="date" name="last_donation_date">
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

                    <button type="submit" class="button-primary">Register Donor</button>
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