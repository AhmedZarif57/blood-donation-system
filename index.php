<?php
include 'includes/db_connect.php';

$totalDonors = 0;
$totalMatches = 0;
$totalRequests = 0;
$totalHospitals = 0;
$totalBanks = 0;
$totalDonations = 0;

$stats = $conn->query("CALL sp_get_dashboard_stats()");

if ($stats && $row = $stats->fetch_assoc()) {
    $totalDonors = (int)$row['total_donors'];
    $totalMatches = (int)$row['matched_requests'];
    $totalRequests = (int)$row['pending_requests'] + (int)$row['matched_requests'];
    $totalHospitals = (int)$row['total_hospitals'];
    $totalBanks = (int)$row['total_banks'];
    $totalDonations = (int)$row['total_donations'];
}

$conn->next_result();

$recentRequests = $conn->query("
    SELECT *
    FROM vw_active_requests
    ORDER BY request_date DESC
    LIMIT 5
");

$requestCards = [];

if ($recentRequests) {
    while ($row = $recentRequests->fetch_assoc()) {
        $requestCards[] = $row;
    }
}

function home_status_class($value)
{
    $value = strtolower((string)$value);

    if ($value === 'pending') return 'status-warning';
    if ($value === 'fulfilled' || $value === 'matched' || $value === 'completed') return 'status-success';
    if ($value === 'cancelled') return 'status-danger';

    return 'status-neutral';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Smart Blood Donation &amp; Emergency Matching System</title>
	<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<main class="page-shell">
	<div>
		<section class="hero">
			<div class="grid grid-2" style="align-items:center;">
				<div>
					<span class="eyebrow">Emergency response platform</span>
					<h1 class="hero-title">Find Blood. Save Lives.</h1>
					<p class="hero-copy">Connect donors, hospitals, and blood banks through a fast, reliable emergency matching workflow built for urgent medical response.</p>
					<div class="hero-actions" style="margin-top:22px;">
						<a class="button-primary" href="hospital/raise_request.php"><i class="bi bi-lightning-charge"></i>Raise Emergency Request</a>
						<a class="button-secondary" href="donor/register_donor.php"><i class="bi bi-person-heart"></i>Register as Donor</a>
					</div>
				</div>
				<div class="page-intro" style="background:rgba(255,255,255,0.08); border-color:rgba(255,255,255,0.12); color:#fff;">
					<span class="eyebrow" style="color:rgba(255,255,255,0.7);">Live network overview</span>
					<div class="grid grid-2">
						<div class="stat-card" style="background:rgba(255,255,255,0.08); border-color:rgba(255,255,255,0.10); box-shadow:none; color:#fff;">
							<div class="stat-label" style="color:rgba(255,255,255,0.72);">Registered donors</div>
							<div class="stat-value" style="color:#fff;"><?= $totalDonors ?></div>
						</div>
						<div class="stat-card" style="background:rgba(255,255,255,0.08); border-color:rgba(255,255,255,0.10); box-shadow:none; color:#fff;">
							<div class="stat-label" style="color:rgba(255,255,255,0.72);">Active matches</div>
							<div class="stat-value" style="color:#fff;"><?= $totalMatches ?></div>
						</div>
					</div>
					<div class="grid grid-3" style="margin-top:16px;">
						<div><div class="stat-label" style="color:rgba(255,255,255,0.72);">Requests</div><div class="stat-value" style="color:#fff; font-size:1.8rem;"><?= $totalRequests ?></div></div>
						<div><div class="stat-label" style="color:rgba(255,255,255,0.72);">Hospitals</div><div class="stat-value" style="color:#fff; font-size:1.8rem;"><?= $totalHospitals ?></div></div>
						<div><div class="stat-label" style="color:rgba(255,255,255,0.72);">Banks</div><div class="stat-value" style="color:#fff; font-size:1.8rem;"><?= $totalBanks ?></div></div>
					</div>
				</div>
			</div>
		</section>

		<section class="section-block grid grid-4">
			<a class="stat-card card" href="hospital/raise_request.php">
				<span class="stat-label">Requests</span>
				<div class="stat-value">Raise Emergency Request</div>
				<div class="stat-note">Create an urgent blood request and trigger donor matching.</div>
			</a>
			<a class="stat-card card" href="donor/register_donor.php">
				<span class="stat-label">Donors</span>
				<div class="stat-value">Register as Donor</div>
				<div class="stat-note">Register yourself and make your blood availability visible.</div>
			</a>
			<a class="stat-card card" href="donor/view_donors.php">
				<span class="stat-label">Find</span>
				<div class="stat-value">Find Donor</div>
				<div class="stat-note">Search active donors by area, blood group, and availability.</div>
			</a>
			<a class="stat-card card" href="inventory/blood_banks.php">
				<span class="stat-label">Banks</span>
				<div class="stat-value">Blood Banks</div>
				<div class="stat-note">Browse blood banks and relevant locations.</div>
			</a>
		</section>

		<section class="section-block grid grid-3">
			<div class="content-card">
				<span class="eyebrow">How it works</span>
				<h2 class="section-title">A clear emergency workflow</h2>
				<p>Register donors, file urgent requests, and surface compatible matches using the existing matching engine.</p>
			</div>
			<div class="content-card">
				<span class="eyebrow">Speed</span>
				<h2 class="section-title">Designed for urgent response</h2>
				<p>Critical requests stay visually prominent so hospitals can move quickly and users can identify what needs attention first.</p>
			</div>
			<div class="content-card">
				<span class="eyebrow">Trust</span>
				<h2 class="section-title">Healthcare-grade presentation</h2>
				<p>Professional color, spacing, and table treatment make the system feel like a real medical operations platform.</p>
			</div>
		</section>

		<section class="section-block">
			<div class="page-hero">
				<div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
					<div>
						<span class="eyebrow">Recent activity</span>
						<h2 class="section-title">Latest emergency requests</h2>
					</div>
					<a class="button-outline" href="hospital/view_requests.php">View all requests</a>
				</div>
				<?php if (!empty($requestCards)) { ?>
					<div class="table-wrap">
						<table>
							<thead>
								<tr>
									<th>Request</th>
									<th>Recipient</th>
									<th>Hospital</th>
									<th>Blood Group</th>
									<th>Units</th>
									<th>Urgency</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($requestCards as $request) { ?>
								<tr>
									<td>#<?= (int)$request['request_id'] ?></td>
									<td><?= htmlspecialchars($request['recipient_name'] ?? '') ?></td>
									<td><?= htmlspecialchars($request['hospital_name'] ?? '') ?></td>
									<td><span class="badge status-neutral"><?= htmlspecialchars($request['blood_group']) ?></span></td>
									<td><?= (int)$request['units_needed'] ?></td>
									<td><span class="badge <?= home_status_class($request['urgency_level']) ?>"><?= htmlspecialchars($request['urgency_level']) ?></span></td>
									<td><span class="badge <?= home_status_class($request['status']) ?>"><?= htmlspecialchars($request['status']) ?></span></td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } else { ?>
					<div class="empty-state">
						<h3 class="section-title">No requests yet</h3>
						<p>When emergency requests are created, they will appear here with live status updates.</p>
					</div>
				<?php } ?>
			</div>
		</section>

	</div>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>