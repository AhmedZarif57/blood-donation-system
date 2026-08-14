<?php
include '../includes/db_connect.php';
include '../admin/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor_id = $_POST['donor_id'];
    $bank_id = $_POST['bank_id'];
    $donation_date = $_POST['donation_date'];
    $units_donated = $_POST['units_donated'];

    // 1. Insert the donation record
    $stmt = $conn->prepare("INSERT INTO Donation (donor_id, bank_id, donation_date, units_donated)
                            VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $donor_id, $bank_id, $donation_date, $units_donated);
    $stmt->execute();

    // 2. Update the donor's last_donation_date so eligibility stays accurate
    $stmt2 = $conn->prepare("UPDATE Donor SET last_donation_date = ? WHERE donor_id = ?");
    $stmt2->bind_param("si", $donation_date, $donor_id);
    $stmt2->execute();

    // 3. Also add these units into that bank's inventory for this donor's blood group
    $donor_info = $conn->query("SELECT blood_group FROM Donor WHERE donor_id = $donor_id")->fetch_assoc();
    $bg = $donor_info['blood_group'];
    $stmt3 = $conn->prepare("UPDATE Blood_Inventory SET units_available = units_available + ?
                             WHERE bank_id = ? AND blood_group = ?");
    $stmt3->bind_param("iis", $units_donated, $bank_id, $bg);
    $stmt3->execute();

    echo "<p style='color:green;'>Donation logged, donor record updated, and inventory increased!</p>";
}

$donors = $conn->query("SELECT donor_id, name, blood_group FROM Donor ORDER BY name ASC, donor_id ASC");
$banks = $conn->query("SELECT b.bank_id, b.name, a.district
                       FROM Blood_Bank b
                       JOIN Area a ON b.area_id = a.area_id
                       ORDER BY b.name ASC");
$districts = $conn->query("SELECT DISTINCT district FROM Area ORDER BY district ASC");

$bank_data = [];
while ($row = $banks->fetch_assoc()) {
    $bank_data[] = [
        'id' => (int)$row['bank_id'],
        'name' => $row['name'],
        'district' => $row['district']
    ];
}

$donor_data = [];
while ($row = $donors->fetch_assoc()) {
    $donor_data[] = [
        'id' => (int)$row['donor_id'],
        'name' => $row['name'],
        'blood_group' => $row['blood_group'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Donation</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Donation operations</span>
            <h1 class="page-title">Log a Donation</h1>
            <p>Record a donation, update donor eligibility, and increase inventory using the existing backend path.</p>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <h2 class="section-title">Search and filter donors</h2>
                <p>Use donor name or ID search and district filtering to narrow the donor and bank selection lists.</p>
                <div class="grid" style="margin-top:14px; gap:12px;">
                    <div class="stat-card"><div class="stat-label">Eligibility</div><div class="stat-note">The donor record is updated with the latest donation date.</div></div>
                    <div class="stat-card"><div class="stat-label">Inventory</div><div class="stat-note">The selected blood group stock is increased for the bank.</div></div>
                </div>
            </div>
            <div class="content-card">
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') { ?>
                    <div class="message-box message-success" style="margin-bottom:12px;">Donation logged, donor record updated, and inventory increased!</div>
                <?php } ?>
                <form method="POST">
                    <div>
                        <label>Donor</label>
                        <input type="text" id="donor_search" placeholder="Type donor name or ID to filter" autocomplete="off">
                        <select name="donor_id" id="donor_id" required>
                            <option value="">Select Donor</option>
                            <?php foreach ($donor_data as $donor) { ?>
                                <option value="<?= $donor['id'] ?>" data-name="<?= htmlspecialchars(strtolower($donor['name'])) ?>" data-id="<?= $donor['id'] ?>">
                                    <?= htmlspecialchars($donor['name']) ?> (<?= $donor['id'] ?>)
                                </option>
                            <?php } ?>
                        </select>
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
                            <label>Blood Bank</label>
                            <select name="bank_id" id="bank_id" required disabled>
                                <option value="">Select Blood Bank</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-2">
                        <div>
                            <label>Donation Date</label>
                            <input type="date" name="donation_date" required>
                        </div>

                        <div>
                            <label>Units Donated</label>
                            <input type="number" name="units_donated" value="1" min="1" required>
                        </div>
                    </div>

                    <button type="submit" class="button-primary">Log Donation</button>
                </form>
            </div>
        </section>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

    <script>
        const bankData = <?= json_encode($bank_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const donorSearch = document.getElementById('donor_search');
        const donorSelect = document.getElementById('donor_id');
        const districtSelect = document.getElementById('district');
        const bankSelect = document.getElementById('bank_id');

        function filterDonors() {
            const term = donorSearch.value.trim().toLowerCase();
            const options = Array.from(donorSelect.options);
            let firstVisible = null;

            options.forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const name = option.dataset.name || '';
                const id = option.dataset.id || '';
                const matches = term === '' || name.startsWith(term) || id.startsWith(term);
                option.hidden = !matches;

                if (matches && !firstVisible) {
                    firstVisible = option;
                }
            });

            const selectedOption = donorSelect.selectedOptions[0];
            if (selectedOption && !selectedOption.hidden) {
                return;
            }

            if (firstVisible) {
                donorSelect.value = firstVisible.value;
            } else {
                donorSelect.value = '';
            }
        }

        donorSearch.addEventListener('input', filterDonors);

        districtSelect.addEventListener('change', function () {
            const district = this.value;
            bankSelect.innerHTML = '<option value="">Select Blood Bank</option>';

            if (!district) {
                bankSelect.disabled = true;
                return;
            }

            const filteredBanks = bankData
                .filter(bank => bank.district === district)
                .sort((a, b) => a.name.localeCompare(b.name));

            filteredBanks.forEach(bank => {
                const option = document.createElement('option');
                option.value = bank.id;
                option.textContent = bank.name;
                bankSelect.appendChild(option);
            });

            bankSelect.disabled = filteredBanks.length === 0;
        });

        filterDonors();
    </script>
</body>
</html>
