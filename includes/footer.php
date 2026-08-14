<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<footer class="footer-shell">
    <div class="footer-inner">
        <div class="footer-grid">
            <div>
                <h3>Smart Blood Donation</h3>
                <p>Modern emergency blood donation and matching platform built to connect donors, hospitals, and blood banks quickly and clearly.</p>
            </div>
            <div>
                <h4>Quick Links</h4>
                <p><a href="/blood_donation/index.php">Home</a><br><a href="/blood_donation/donor/view_donors.php">Donors</a><br><a href="/blood_donation/hospital/view_requests.php">Requests</a></p>
            </div>
            <div>
                <h4>Donation</h4>
                <p><a href="/blood_donation/donor/register_donor.php">Register as Donor</a><br><a href="/blood_donation/donation/view_donation_history.php">Donation History</a><br><a href="/blood_donation/leaderboard.php">Leaderboard</a></p>
            </div>
            <div>
                <h4>Operations</h4>
                <p><a href="/blood_donation/inventory/blood_banks.php">Blood Banks</a><br><a href="/blood_donation/inventory/view_inventory.php">Inventory</a><br><a href="/blood_donation/admin/dashboard.php">Admin</a></p>
            </div>
        </div>
        <div class="footer-note">
            Built for the Smart Blood Donation &amp; Emergency Matching System. Designed to preserve the existing backend workflows, database structure, and request routing.
        </div>
    </div>
</footer>
