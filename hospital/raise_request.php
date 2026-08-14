<?php
include '../includes/db_connect.php';

$message = '';
$messageType = '';

$donors = $conn->query("SELECT donor_id, name, blood_group, phone FROM Donor ORDER BY name ASC");
$districts = $conn->query("SELECT DISTINCT district FROM Area WHERE district IS NOT NULL AND district <> '' ORDER BY district ASC");
$hospitals = $conn->query("SELECT h.hospital_id, h.name, a.district FROM Hospital h LEFT JOIN Area a ON a.area_id = h.area_id ORDER BY a.district ASC, h.name ASC");

$donor_data = [];
while ($row = $donors->fetch_assoc()) {
    $donor_data[] = [
        'id' => (int)$row['donor_id'],
        'name' => $row['name'],
        'blood_group' => $row['blood_group'],
        'phone' => $row['phone'] ?? ''
    ];
}

$hospital_data = [];
while ($row = $hospitals->fetch_assoc()) {
    $hospital_data[] = [
        'id' => (int)$row['hospital_id'],
        'name' => $row['name'],
        'district' => $row['district'] ?? ''
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor_unique_id = trim($_POST['donor_unique_id'] ?? '');
    $recipient_name = trim($_POST['recipient_name'] ?? '');
    $blood_group = trim($_POST['blood_group'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $hospital_id = (int)($_POST['hospital_id'] ?? 0);
    $units_needed = (int)($_POST['units_needed'] ?? 0);
    $urgency_level = $_POST['urgency_level'] ?? 'Medium';

    $resolvedDonor = null;
    if ($donor_unique_id !== '') {
        $donorId = (int)$donor_unique_id;
        $donorStmt = $conn->prepare("SELECT donor_id, name, blood_group, phone FROM Donor WHERE donor_id = ?");
        if ($donorStmt) {
            $donorStmt->bind_param("i", $donorId);
            if ($donorStmt->execute()) {
                $donorResult = $donorStmt->get_result();
                $resolvedDonor = $donorResult ? $donorResult->fetch_assoc() : null;
            }
        }

        if ($resolvedDonor) {
            $recipient_name = $resolvedDonor['name'];
            $blood_group = $resolvedDonor['blood_group'];
            $phone = $resolvedDonor['phone'] ?? '';
        }
    }

    if ($recipient_name === '' || $blood_group === '' || $phone === '' || $hospital_id <= 0 || $units_needed <= 0) {
        $message = 'Please complete the recipient and request details before submitting.';
        $messageType = 'error';
    } else {
        $conn->begin_transaction();

        $recipientStmt = $conn->prepare("INSERT INTO Recipient (name, blood_group, phone, hospital_id) VALUES (?, ?, ?, ?)");
        if ($recipientStmt) {
            $recipientStmt->bind_param("sssi", $recipient_name, $blood_group, $phone, $hospital_id);
        }

        if ($recipientStmt && $recipientStmt->execute()) {
            $recipient_id = $conn->insert_id;

            $requestStmt = $conn->prepare("INSERT INTO Emergency_Request (recipient_id, hospital_id, blood_group, units_needed, urgency_level) VALUES (?, ?, ?, ?, ?)");
            if ($requestStmt) {
                $requestStmt->bind_param("iisis", $recipient_id, $hospital_id, $blood_group, $units_needed, $urgency_level);
            }

            if ($requestStmt && $requestStmt->execute()) {
                $request_id = $conn->insert_id;

                // Commit so trigger can run and insert any Donor_Match rows.
                $conn->commit();

                // Redirect to matched donors page showing matches (phone numbers visible only here).
                header('Location: matched_donors.php?request_id=' . (int)$request_id);
                exit;
            } else {
                $conn->rollback();
                $message = 'Unable to submit the request right now. Please try again.';
                $messageType = 'error';
            }
        } else {
            $conn->rollback();
            $message = 'Unable to submit the request right now. Please try again.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><title>Raise Emergency Request</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="hero">
            <div class="grid grid-2" style="align-items:center;">
                <div>
                    <span class="eyebrow">Emergency response</span>
                    <h1 class="hero-title">Raise a Blood Request Fast</h1>
                    <p class="hero-copy">Capture recipient details, set urgency, and trigger matching against available donors without changing the existing workflow.</p>
                    <div class="hero-actions" style="margin-top:22px;">
                        <a class="button-secondary" href="view_requests.php"><i class="bi bi-list-ul"></i>View Requests</a>
                    </div>
                </div>
                <div class="content-card" style="background:rgba(255,255,255,0.08); border-color:rgba(255,255,255,0.12); color:#fff;">
                    <div class="grid grid-2">
                        <div class="stat-card" style="background:rgba(255,255,255,0.08); border-color:rgba(255,255,255,0.10); box-shadow:none; color:#fff;">
                            <div class="stat-label" style="color:rgba(255,255,255,0.72);">Urgency</div>
                            <div class="stat-value" style="color:#fff; font-size:1.8rem;">Critical</div>
                        </div>
                        <div class="stat-card" style="background:rgba(255,255,255,0.08); border-color:rgba(255,255,255,0.10); box-shadow:none; color:#fff;">
                            <div class="stat-label" style="color:rgba(255,255,255,0.72);">Workflow</div>
                            <div class="stat-value" style="color:#fff; font-size:1.8rem;">Match</div>
                        </div>
                    </div>
                    <p style="margin-top:14px; color:rgba(255,255,255,0.82);">Use the optional donor ID shortcut to prefill recipient and blood group information when available.</p>
                </div>
            </div>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <h2 class="section-title">Emergency request guidance</h2>
                <p>Use the existing recipient and hospital fields to submit the request. The matching trigger will continue to operate exactly as before.</p>
                <div class="grid" style="margin-top:14px; gap:12px;">
                    <div class="stat-card"><div class="stat-label">Critical</div><div class="stat-note">Use for the most urgent response cases.</div></div>
                    <div class="stat-card"><div class="stat-label">Medium</div><div class="stat-note">Default operational urgency for standard cases.</div></div>
                    <div class="stat-card"><div class="stat-label">Low</div><div class="stat-note">Routine requests with lower urgency.</div></div>
                </div>
            </div>

            <div class="content-card">
                <span class="eyebrow">Request form</span>
                <h2 class="section-title">Register Recipient and Raise Emergency Request</h2>
                <?php if ($message !== '') { ?>
                    <div class="message-box <?= $messageType === 'success' ? 'message-success' : 'message-error' ?>" style="margin-bottom:12px;">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php } ?>
                <form method="POST" id="requestForm">
                    <div>
                        <label>Unique Donor ID shortcut (optional)</label>
                        <div class="actions" style="align-items:stretch;">
                            <input type="number" id="donor_unique_id" name="donor_unique_id" min="1" placeholder="Enter donor ID">
                            <button type="button" id="lookupDonor">Load Donor Details</button>
                        </div>
                        <div id="donorLookupError" class="message-box message-error" style="display:none; margin-top:12px;"></div>
                    </div>

                    <div>
                        <label>Recipient Name</label>
                        <input type="text" name="recipient_name" id="recipient_name" required placeholder="Recipient name">
                    </div>

                    <div>
                        <label>Blood Group Needed</label>
                        <select name="blood_group" id="blood_group_select" required>
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
                        <input type="text" name="phone" id="phone" required placeholder="Recipient contact number">
                    </div>

                    <div class="grid grid-2">
                        <div>
                            <label>District</label>
                            <select id="hospital_district" name="hospital_district">
                                <option value="">All Districts</option>
                                <?php while ($district = $districts->fetch_assoc()) { ?>
                                    <option value="<?= htmlspecialchars($district['district']) ?>"><?= htmlspecialchars($district['district']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div>
                            <label>Admitted At</label>
                            <select id="hospital_id" name="hospital_id" required>
                                <option value="">Select Hospital</option>
                                <?php foreach ($hospital_data as $hospital) { ?>
                                    <option value="<?= (int)$hospital['id'] ?>" data-district="<?= htmlspecialchars($hospital['district']) ?>"><?= htmlspecialchars($hospital['name']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-2">
                        <div>
                            <label>Units Needed</label>
                            <input type="number" name="units_needed" min="1" required placeholder="Units required">
                        </div>
                        <div>
                            <label>Urgency</label>
                            <select name="urgency_level" required>
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="button-primary">Submit Request</button>
                </form>
            </div>
        </section>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

    <script>
        const donorData = <?= json_encode($donor_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const donorLookupError = document.getElementById('donorLookupError');
        const donorIdInput = document.getElementById('donor_unique_id');
        const lookupButton = document.getElementById('lookupDonor');
        const recipientNameInput = document.getElementById('recipient_name');
        const bloodGroupSelect = document.getElementById('blood_group_select');
        const phoneInput = document.getElementById('phone');
        const hospitalDistrictSelect = document.getElementById('hospital_district');
        const hospitalSelect = document.getElementById('hospital_id');

        function populateHospitalsByDistrict() {
            const selectedDistrict = hospitalDistrictSelect.value;
            const hospitalOptions = Array.from(hospitalSelect.options);
            let hasVisibleHospital = false;

            hospitalOptions.forEach((option) => {
                if (option.value === '') {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const matchesDistrict = !selectedDistrict || (option.dataset.district || '') === selectedDistrict;
                option.hidden = !matchesDistrict;
                option.disabled = !matchesDistrict;
                if (matchesDistrict) {
                    hasVisibleHospital = true;
                }
            });

            if (!hasVisibleHospital) {
                hospitalSelect.value = '';
            } else if (selectedDistrict && hospitalSelect.value && hospitalSelect.selectedOptions[0] && hospitalSelect.selectedOptions[0].hidden) {
                hospitalSelect.value = '';
            }

            if (!selectedDistrict) {
                hospitalSelect.value = hospitalSelect.value || '';
            }
        }

        hospitalDistrictSelect.addEventListener('change', populateHospitalsByDistrict);
        populateHospitalsByDistrict();

        const donorMap = {};
        donorData.forEach(donor => {
            donorMap[String(donor.id)] = donor;
        });

        function setBloodGroupLock(locked, value) {
            let hiddenInput = document.getElementById('blood_group_hidden');

            if (locked) {
                bloodGroupSelect.disabled = true;
                bloodGroupSelect.style.background = '#f0f0f0';
                bloodGroupSelect.style.color = '#555';

                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'blood_group';
                    hiddenInput.id = 'blood_group_hidden';
                    bloodGroupSelect.insertAdjacentElement('afterend', hiddenInput);
                }

                hiddenInput.value = value;
                bloodGroupSelect.value = value;
            } else {
                bloodGroupSelect.disabled = false;
                bloodGroupSelect.style.background = '';
                bloodGroupSelect.style.color = '';

                if (hiddenInput) {
                    hiddenInput.remove();
                }
            }
        }

        function unlockManualEntry() {
            recipientNameInput.readOnly = false;
            recipientNameInput.style.background = '';
            recipientNameInput.style.color = '';

            phoneInput.readOnly = false;
            phoneInput.style.background = '';
            phoneInput.style.color = '';

            setBloodGroupLock(false, '');
        }

        function applyDonorLookup() {
            const donor = donorMap[String(donorIdInput.value)];

            if (!donorIdInput.value) {
                donorLookupError.style.display = 'none';
                donorLookupError.textContent = '';
                unlockManualEntry();
                return;
            }

            if (!donor) {
                donorLookupError.textContent = 'No donor matches that Unique ID. Please fill the recipient details manually.';
                donorLookupError.style.display = 'block';
                unlockManualEntry();
                return;
            }

            donorLookupError.style.display = 'none';
            donorLookupError.textContent = '';

            recipientNameInput.value = donor.name || '';
            phoneInput.value = donor.phone || '';
            bloodGroupSelect.value = donor.blood_group || '';

            recipientNameInput.readOnly = true;
            recipientNameInput.style.background = '#f0f0f0';
            recipientNameInput.style.color = '#555';

            phoneInput.readOnly = true;
            phoneInput.style.background = '#f0f0f0';
            phoneInput.style.color = '#555';

            setBloodGroupLock(true, donor.blood_group || '');
        }

        lookupButton.addEventListener('click', applyDonorLookup);
        donorIdInput.addEventListener('blur', applyDonorLookup);
        donorIdInput.addEventListener('input', function () {
            if (!this.value) {
                donorLookupError.style.display = 'none';
                donorLookupError.textContent = '';
                unlockManualEntry();
            }
        });

        unlockManualEntry();
    </script>
</body>
</html>