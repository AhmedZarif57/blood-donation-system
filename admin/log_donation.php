<?php
include 'auth.php';
include '../includes/db_connect.php';

$donors = $conn->query("SELECT donor_id, name FROM Donor ORDER BY name");
$banks = $conn->query("SELECT bank_id, name FROM Blood_Bank ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor_id = (int)($_POST['donor_id'] ?? 0);
    $bank_id = (int)($_POST['bank_id'] ?? 0);
    $units = (int)($_POST['units'] ?? 0);
    $dateInput = $_POST['donation_date'] ?? date('Y-m-d\TH:i');
    $date = date('Y-m-d', strtotime($dateInput));

    if ($donor_id && $bank_id && $units > 0) {
        try {
            $stmt = $conn->prepare('CALL sp_log_donation(?, ?, ?, ?)');
            $stmt->bind_param('iisi', $donor_id, $bank_id, $date, $units);
            $stmt->execute();
            $stmt->close();

            while ($conn->more_results()) {
                $conn->next_result();
            }

            header('Location: manage_donations.php');
            exit;
        } catch (Throwable $e) {
            $message = 'Unable to log the donation: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
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
            <span class="eyebrow">Admin operations</span>
            <h1 class="page-title">Log Donation</h1>
            <p>Record a donation using the project database procedure.</p>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <p class="helper-text">This form stores the donation through the database procedure and keeps validation centralized in SQL.</p>
            </div>
            <div class="content-card">
                <?php if (isset($message) && $message !== '') { ?>
                    <div class="message-box message-error" style="margin-bottom:12px;">
                        <?= $message ?>
                    </div>
                <?php } ?>
                <form method="POST">
                    <div>
                        <label>Donor</label>
                        <select name="donor_id" required>
                            <option value="0">-- Select Donor --</option>
                            <?php while($d=$donors->fetch_assoc()){ echo '<option value="'.$d['donor_id'].'">'.htmlspecialchars($d['name']).'</option>'; } ?>
                        </select>
                    </div>
                    <div>
                        <label>Blood Bank</label>
                        <select name="bank_id" required>
                            <option value="0">-- Select Bank --</option>
                            <?php while($b=$banks->fetch_assoc()){ echo '<option value="'.$b['bank_id'].'">'.htmlspecialchars($b['name']).'</option>'; } ?>
                        </select>
                    </div>
                    <div class="grid grid-2">
                        <div>
                            <label>Units Donated</label>
                            <input type="number" name="units" min="1" value="1" required>
                        </div>
                        <div>
                            <label>Donation Date</label>
                            <input type="datetime-local" name="donation_date" value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                    </div>
                    <div class="actions">
                        <button type="submit" class="button-primary">Log Donation</button>
                        <a class="button-outline" href="manage_donations.php">Back</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
