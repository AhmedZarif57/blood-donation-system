<?php
include '../includes/db_connect.php';

$filter = $_GET['urgency'] ?? 'All';
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$modalState = ['requestId' => 0, 'message' => '', 'type' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donate_request_id']) && isset($_POST['donor_unique_id'])) {
    $request_id = (int)$_POST['donate_request_id'];
    $donor_id = (int)$_POST['donor_unique_id'];

    if ($request_id > 0 && $donor_id > 0) {
        $stmt = $conn->prepare("CALL sp_match_donor_to_request(?, ?)");
        $stmt->bind_param('ii', $request_id, $donor_id);

        if ($stmt->execute()) {
            $modalState = [
                'requestId' => 0,
                'message' => 'Donation recorded successfully. The request is now matched.',
                'type' => 'success'
            ];
        } else {
            $modalState = [
                'requestId' => $request_id,
                'message' => $conn->error,
                'type' => 'error'
            ];
        }

        $stmt->close();

        while ($conn->more_results() && $conn->next_result()) {
        }
    } else {
        $modalState = [
            'requestId' => $request_id,
            'message' => 'Please enter a valid donor ID.',
            'type' => 'error'
        ];
    }
}

$sql = "SELECT *
        FROM vw_active_requests";

if ($filter !== 'All') {
    $sql .= " WHERE urgency_level = '" . $conn->real_escape_string($filter) . "'";
}

$sql .= " ORDER BY
            CASE urgency_level
                WHEN 'Critical' THEN 1
                WHEN 'Medium' THEN 2
                WHEN 'Low' THEN 3
                ELSE 4
            END,
            request_date DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Requests</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="page-shell">
    <div class="page-shell">

        <section class="page-hero">
            <div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
                <div>
                    <span class="eyebrow">Emergency monitoring</span>
                    <h1 class="page-title">Emergency Requests</h1>
                    <p>
                        View active blood requests and contact pending recipients
                        to help fulfill their requests.
                    </p>
                </div>

                <a class="button-primary" href="raise_request.php">
                    <i class="bi bi-plus-circle"></i>
                    Raise Emergency Request
                </a>
            </div>
        </section>

        <section class="section-block filters">
            <div class="actions">
                <a class="<?= $filter === 'All' ? 'button-primary' : 'button-outline' ?>" href="?urgency=All">
                    All
                </a>

                <a class="<?= $filter === 'Critical' ? 'button-primary' : 'button-outline' ?>" href="?urgency=Critical">
                    Critical
                </a>

                <a class="<?= $filter === 'Medium' ? 'button-primary' : 'button-outline' ?>" href="?urgency=Medium">
                    Medium
                </a>

                <a class="<?= $filter === 'Low' ? 'button-primary' : 'button-outline' ?>" href="?urgency=Low">
                    Low
                </a>
            </div>
        </section>

        <?php if ($modalState['type'] === 'success' && $modalState['message'] !== '') { ?>
            <section class="section-block">
                <div class="message-box message-success">
                    <?= htmlspecialchars($modalState['message']) ?>
                </div>
            </section>
        <?php } ?>

        <section class="section-block">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Hospital</th>
                            <th>Blood Group</th>
                            <th>Units</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <?php if ($isAdmin) { ?>
                                <th>Action</th>
                            <?php } ?>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result && $result->num_rows > 0) { ?>

                        <?php while ($row = $result->fetch_assoc()) { ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($row['recipient_name']) ?>

                                    <?php if (strtolower($row['status']) === 'pending') { ?>

                                        <div>
                                            <button
                                                type="button"
                                                class="button-primary button-small contact-trigger"
                                                data-request-id="<?= (int)$row['request_id'] ?>"
                                                data-recipient-name="<?= htmlspecialchars($row['recipient_name'], ENT_QUOTES) ?>"
                                                data-hospital="<?= htmlspecialchars($row['hospital_name'], ENT_QUOTES) ?>"
                                                data-blood-group="<?= htmlspecialchars($row['blood_group'], ENT_QUOTES) ?>"
                                                data-units="<?= (int)$row['units_needed'] ?>"
                                                data-phone="<?= htmlspecialchars($row['recipient_phone'] ?? '', ENT_QUOTES) ?>"
                                                data-urgency="<?= htmlspecialchars($row['urgency_level'], ENT_QUOTES) ?>"
                                            >
                                                Contact
                                            </button>
                                        </div>

                                    <?php } ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['hospital_name']) ?>
                                </td>

                                <td>
                                    <span class="badge status-neutral">
                                        <?= htmlspecialchars($row['blood_group']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= (int)$row['units_needed'] ?>
                                </td>

                                <td>
                                    <?php
                                    $urgency = strtolower($row['urgency_level']);

                                    if ($urgency === 'critical') {
                                        $urgencyClass = 'status-danger';
                                    } elseif ($urgency === 'medium') {
                                        $urgencyClass = 'status-warning';
                                    } else {
                                        $urgencyClass = 'status-neutral';
                                    }
                                    ?>

                                    <span class="badge <?= $urgencyClass ?>">
                                        <?= htmlspecialchars($row['urgency_level']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    $status = strtolower($row['status']);

                                    if ($status === 'pending') {
                                        $statusClass = 'status-warning';
                                        $displayStatus = 'Pending';
                                    } elseif ($status === 'matched') {
                                        $statusClass = 'status-success';
                                        $displayStatus = 'Fulfilled';
                                    } elseif ($status === 'cancelled') {
                                        $statusClass = 'status-danger';
                                        $displayStatus = 'Cancelled';
                                    } else {
                                        $statusClass = 'status-neutral';
                                        $displayStatus = $row['status'];
                                    }
                                    ?>

                                    <span class="badge <?= $statusClass ?>">
                                        <?= htmlspecialchars($displayStatus) ?>
                                    </span>
                                </td>

                                <?php if ($isAdmin) { ?>
                                    <td>
                                        <a
                                            class="button-outline button-small"
                                            href="update_request_status.php?id=<?= (int)$row['request_id'] ?>"
                                        >
                                            Update
                                        </a>
                                    </td>
                                <?php } ?>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>
                            <td colspan="<?= $isAdmin ? '7' : '6' ?>">
                                <div class="empty-state">
                                    <h3 class="section-title">No requests found</h3>
                                    <p>
                                        There are currently no requests matching this filter.
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


<div
    class="contact-modal-backdrop hidden"
    id="contact-modal-backdrop"
    aria-hidden="true"
>
    <div
        class="contact-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="contact-modal-title"
    >

        <button
            type="button"
            class="modal-close"
            data-close-modal
            aria-label="Close contact details"
        >
            ×
        </button>

        <div class="eyebrow">Pending recipient</div>

        <h3 id="contact-modal-title">
            Recipient details
        </h3>

        <div class="modal-details">

            <div class="detail-row">
                <strong>Recipient</strong>
                <span id="modal-recipient">-</span>
            </div>

            <div class="detail-row">
                <strong>Hospital</strong>
                <span id="modal-hospital">-</span>
            </div>

            <div class="detail-row">
                <strong>Blood Group</strong>
                <span id="modal-blood">-</span>
            </div>

            <div class="detail-row">
                <strong>Units Needed</strong>
                <span id="modal-units">-</span>
            </div>

            <div class="detail-row">
                <strong>Urgency</strong>
                <span id="modal-urgency">-</span>
            </div>

            <div class="detail-row">
                <strong>Contact</strong>
                <span id="modal-phone">-</span>
            </div>

        </div>

        <div id="contact-modal-message" class="message-box hidden"></div>

        <div id="contact-modal-actions" class="actions">
            <button
                type="button"
                class="button-primary"
                id="show-donate-form"
            >
                Donate
            </button>

            <button
                type="button"
                class="button-outline"
                data-close-modal
            >
                Close
            </button>
        </div>

        <form method="POST" id="donor-id-form" class="hidden">

            <input
                type="hidden"
                name="donate_request_id"
                id="donate_request_id"
                value="0"
            >

            <label for="donor_unique_id">
                Donor Unique ID
            </label>

            <input
                type="number"
                id="donor_unique_id"
                name="donor_unique_id"
                min="1"
                placeholder="Enter donor unique ID"
                required
            >

            <div class="actions" style="margin-top: 12px;">

                <button
                    type="submit"
                    class="button-primary"
                >
                    Confirm Donation
                </button>

                <button
                    type="button"
                    class="button-outline"
                    data-close-modal
                >
                    Cancel
                </button>

            </div>

        </form>

    </div>
</div>


<?php include '../includes/footer.php'; ?>


<script>
const modalState = <?= json_encode(
    $modalState,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

const modalBackdrop = document.getElementById('contact-modal-backdrop');
const modalMessage = document.getElementById('contact-modal-message');
const donorForm = document.getElementById('donor-id-form');
const showDonateFormBtn = document.getElementById('show-donate-form');

const donateRequestIdInput = document.getElementById('donate_request_id');

const modalRecipient = document.getElementById('modal-recipient');
const modalHospital = document.getElementById('modal-hospital');
const modalBlood = document.getElementById('modal-blood');
const modalUnits = document.getElementById('modal-units');
const modalUrgency = document.getElementById('modal-urgency');
const modalPhone = document.getElementById('modal-phone');


function setModalMessage(message, type) {

    if (!message) {
        modalMessage.classList.add('hidden');
        modalMessage.textContent = '';
        return;
    }

    modalMessage.classList.remove('hidden');
    modalMessage.classList.remove(
        'message-success',
        'message-error'
    );

    modalMessage.classList.add(
        type === 'success'
            ? 'message-success'
            : 'message-error'
    );

    modalMessage.textContent = message;
}


function openContactModal(button) {

    if (!button) return;

    const requestId = button.dataset.requestId || '0';
    const recipientName = button.dataset.recipientName || 'Unknown recipient';
    const hospital = button.dataset.hospital || 'Unknown hospital';
    const bloodGroup = button.dataset.bloodGroup || 'Unknown';
    const units = button.dataset.units || '0';
    const urgency = button.dataset.urgency || 'Not set';
    const phone = button.dataset.phone || 'Unavailable';

    modalRecipient.textContent = recipientName;
    modalHospital.textContent = hospital;
    modalBlood.textContent = bloodGroup;
    modalUnits.textContent = units;
    modalUrgency.textContent = urgency;
    modalPhone.textContent = phone;

    donateRequestIdInput.value = requestId;

    donorForm.classList.add('hidden');

    document.getElementById('donor_unique_id').value = '';

    modalBackdrop.classList.remove('hidden');
    modalBackdrop.setAttribute('aria-hidden', 'false');

    setModalMessage('', '');
}


function closeContactModal() {

    modalBackdrop.classList.add('hidden');
    modalBackdrop.setAttribute('aria-hidden', 'true');

    donorForm.classList.add('hidden');

    setModalMessage('', '');
}


document.querySelectorAll('.contact-trigger').forEach((button) => {

    button.addEventListener('click', () => {
        openContactModal(button);
    });

});


document.querySelectorAll('[data-close-modal]').forEach((button) => {

    button.addEventListener('click', closeContactModal);

});


showDonateFormBtn.addEventListener('click', () => {

    donorForm.classList.remove('hidden');

    document.getElementById('donor_unique_id').focus();

});


if (
    modalState &&
    modalState.requestId &&
    Number(modalState.requestId) > 0
) {

    const trigger = document.querySelector(
        '.contact-trigger[data-request-id="' +
        modalState.requestId +
        '"]'
    );

    if (trigger) {
        openContactModal(trigger);
    }

    setModalMessage(
        modalState.message || '',
        modalState.type || 'error'
    );
}
</script>

</body>
</html>