<?php
if (session_status() == PHP_SESSION_NONE) session_start();
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$currentPath = str_replace('\\', '/', $currentPath);

function nav_is_active($currentPath, $target)
{
    return strpos($currentPath, $target) !== false;
}
?>
<header class="nav-shell">
    <div class="nav-shell-inner">
        <a class="nav-brand" href="/blood_donation/index.php" aria-label="Smart Blood Donation home">
            <span class="brand-mark"><i class="bi bi-droplet-half"></i></span>
            <span class="brand-copy">
                <span class="brand-title">Smart Blood Donation</span>
                <span class="brand-subtitle">Emergency matching network</span>
            </span>
        </a>

        <nav class="nav-links" aria-label="Primary navigation">
            <a href="/blood_donation/index.php" class="<?= nav_is_active($currentPath, '/blood_donation/index.php') ? 'is-active' : '' ?>"><i class="bi bi-house-door"></i>Home</a>
            <a href="/blood_donation/donor/view_donors.php" class="<?= nav_is_active($currentPath, '/blood_donation/donor/') ? 'is-active' : '' ?>"><i class="bi bi-people"></i>Donors</a>
            <a href="/blood_donation/hospital/view_requests.php" class="<?= nav_is_active($currentPath, '/blood_donation/hospital/view_requests.php') || nav_is_active($currentPath, '/blood_donation/hospital/matched_donors.php') || nav_is_active($currentPath, '/blood_donation/hospital/raise_request.php') ? 'is-active' : '' ?>"><i class="bi bi-lightning-charge"></i>Requests</a>
            <a href="/blood_donation/inventory/blood_banks.php" class="<?= nav_is_active($currentPath, '/blood_donation/inventory/') ? 'is-active' : '' ?>"><i class="bi bi-building"></i>Blood Banks</a>
            <a href="/blood_donation/donation/view_donation_history.php" class="<?= nav_is_active($currentPath, '/blood_donation/donation/') ? 'is-active' : '' ?>"><i class="bi bi-clock-history"></i>Donations</a>
            <a href="/blood_donation/leaderboard.php" class="<?= nav_is_active($currentPath, '/blood_donation/leaderboard.php') ? 'is-active' : '' ?>"><i class="bi bi-trophy"></i>Leaderboard</a>
            <?php if ($isAdmin) { ?>
                <a href="/blood_donation/admin/dashboard.php" class="<?= nav_is_active($currentPath, '/blood_donation/admin/') ? 'is-active' : '' ?>"><i class="bi bi-speedometer2"></i>Admin</a>
            <?php } ?>
        </nav>

        <div class="nav-actions">
            <a href="/blood_donation/hospital/raise_request.php" class="button-primary"><i class="bi bi-exclamation-triangle"></i>Raise Emergency Request</a>
            <a href="/blood_donation/donor/register_donor.php" class="button-outline"><i class="bi bi-person-heart"></i>Register Donor</a>
        </div>
    </div>
</header>