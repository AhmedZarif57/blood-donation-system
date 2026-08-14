<?php
include '../includes/db_connect.php';
include 'auth.php';

// Dashboard statistics
$totalDonors = $conn->query('SELECT COUNT(*) FROM Donor')->fetch_row()[0] ?? 0;
$availableDonors = $conn->query("SELECT COUNT(*) FROM Donor WHERE availability_status='Available'")->fetch_row()[0] ?? 0;
$unavailableDonors = $conn->query("SELECT COUNT(*) FROM Donor WHERE availability_status='Unavailable'")->fetch_row()[0] ?? 0;

$totalRequests = $conn->query('SELECT COUNT(*) FROM Emergency_Request')->fetch_row()[0] ?? 0;
$pendingRequests = $conn->query("SELECT COUNT(*) FROM Emergency_Request WHERE status='Pending'")->fetch_row()[0] ?? 0;
$fulfilledRequests = $conn->query("SELECT COUNT(DISTINCT request_id) FROM Donor_Match")->fetch_row()[0] ?? 0;
$totalHospitals = $conn->query('SELECT COUNT(*) FROM Hospital')->fetch_row()[0] ?? 0;
$totalBanks = $conn->query('SELECT COUNT(*) FROM Blood_Bank')->fetch_row()[0] ?? 0;
$totalDonations = $conn->query('SELECT COUNT(*) FROM Donation')->fetch_row()[0] ?? 0;
$totalAdmins = $conn->query('SELECT COUNT(*) FROM Admin')->fetch_row()[0] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>

<?php include '../includes/header.php'; ?>

<main class="page-shell">

    <div class="page-shell">

        <!-- Admin Header -->
        <section class="page-hero">
            <div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">

                <div>
                    <span class="eyebrow">Admin control center</span>

                    <h1 class="page-title">
                        Admin Dashboard
                    </h1>

                    <p>
                        Operational overview for donor records, requests, banks,
                        donations, and system administration.
                    </p>
                </div>

                <a class="button-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>

            </div>
        </section>


        <!-- Main Statistics -->
        <section class="section-block grid grid-4">

            <div class="stat-card card">
                <div class="stat-label">Total donors</div>
                <div class="stat-value">
                    <?= (int)$totalDonors ?>
                </div>
                <div class="stat-note">
                    Registered donor profiles
                </div>
            </div>

            <div class="stat-card card">
                <div class="stat-label">Available donors</div>
                <div class="stat-value">
                    <?= (int)$availableDonors ?>
                </div>
                <div class="stat-note">
                    Currently available for matching
                </div>
            </div>

            <div class="stat-card card">
                <div class="stat-label">Requests</div>
                <div class="stat-value">
                    <?= (int)$totalRequests ?>
                </div>
                <div class="stat-note">
                    Emergency requests on record
                </div>
            </div>

            <div class="stat-card card">
                <div class="stat-label">Fulfilled</div>
                <div class="stat-value">
                    <?= (int)$fulfilledRequests ?>
                </div>
                <div class="stat-note">
                    Completed request records
                </div>
            </div>

        </section>


        <!-- Admin Operations -->
        <section class="section-block grid grid-3">

            <!-- Operational Breakdown -->
            <div class="content-card">

                <h2 class="section-title">
                    Operational breakdown
                </h2>

                <div class="grid grid-2" style="margin-top:14px;">

                    <div class="stat-card">
                        <div class="stat-label">Unavailable donors</div>
                        <div class="stat-value">
                            <?= (int)$unavailableDonors ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-label">Pending requests</div>
                        <div class="stat-value">
                            <?= (int)$pendingRequests ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-label">Hospitals</div>
                        <div class="stat-value">
                            <?= (int)$totalHospitals ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-label">Blood banks</div>
                        <div class="stat-value">
                            <?= (int)$totalBanks ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-label">Donations</div>
                        <div class="stat-value">
                            <?= (int)$totalDonations ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-label">Admins</div>
                        <div class="stat-value">
                            <?= (int)$totalAdmins ?>
                        </div>
                    </div>

                </div>

            </div>


            <!-- Quick Actions -->
            <div class="content-card">

                <h2 class="section-title">
                    Quick actions
                </h2>

                <div class="actions" style="margin-top:14px; align-items:stretch;">

                    <!-- Only emphasized admin action -->
                    <a class="button-primary" href="log_donation.php">
                        Log Donation
                    </a>

                    <a class="button-outline" href="manage_requests.php">
                        Manage Requests
                    </a>

                    <a class="button-outline" href="manage_donors.php">
                        Manage Donors
                    </a>

                    <a class="button-outline" href="manage_banks.php">
                        Manage Banks
                    </a>

                    <a class="button-outline" href="manage_hospitals.php">
                        Manage Hospitals
                    </a>

                    <a class="button-outline" href="manage_admins.php">
                        Manage Admins
                    </a>

                    <a class="button-outline" href="manage_areas.php">
                        Manage Areas
                    </a>

                    <a class="button-outline" href="../inventory/view_inventory.php">
                        View Inventory
                    </a>

                </div>

            </div>


            <!-- Status Summary -->
            <div class="content-card">

                <h2 class="section-title">
                    Status summary
                </h2>

                <p>
                    Current status of the main system operations.
                </p>

                <div style="margin-top:14px;">

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid rgba(120,0,20,0.10);">
                        <span>Donor matching</span>

                        <span class="badge status-success">
                            Operational
                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid rgba(120,0,20,0.10);">
                        <span>Pending requests</span>

                        <span class="badge <?= $pendingRequests > 0 ? 'status-warning' : 'status-success' ?>">
                            <?= (int)$pendingRequests ?>
                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid rgba(120,0,20,0.10);">
                        <span>Blood banks</span>

                        <span class="badge status-success">
                            <?= (int)$totalBanks ?>
                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0;">
                        <span>Administrators</span>

                        <span class="badge status-success">
                            <?= (int)$totalAdmins ?>
                        </span>
                    </div>

                </div>

            </div>

        </section>

    </div>

</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>