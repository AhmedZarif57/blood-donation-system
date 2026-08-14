<?php
include 'includes/db_connect.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page-1)*$perPage;

// Top donors by donation count using the database view
$sql = "SELECT * FROM vw_donor_leaderboard ORDER BY total_donations DESC, name ASC LIMIT 100";
$all = $conn->query($sql);
$rows = [];
while ($r = $all->fetch_assoc()) $rows[] = $r;
$total = count($rows);
$pageRows = array_slice($rows, $offset, $perPage);

function leaderboard_rank_class($rank)
{
    if ($rank === 1) return 'status-success';
    if ($rank === 2) return 'status-info';
    if ($rank === 3) return 'status-warning';
    return 'status-neutral';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Donors Leaderboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <div class="space-between" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
                <div>
                    <span class="eyebrow">Community impact</span>
                    <h1 class="page-title">Top Donors Leaderboard</h1>
                    <p>Recognizing the donors who have contributed the most to the network.</p>
                </div>
            </div>
        </section>

        <section class="section-block grid grid-3">
            <div class="stat-card card">
                <div class="stat-label">Ranked donors</div>
                <div class="stat-value"><?= $total ?></div>
                <div class="stat-note">Leaderboard results are based on recorded donation counts.</div>
            </div>
            <div class="stat-card card">
                <div class="stat-label">Current page</div>
                <div class="stat-value"><?= $page ?></div>
                <div class="stat-note">Showing <?= count($pageRows) ?> donors per page.</div>
            </div>
            <div class="stat-card card">
                <div class="stat-label">Donation records</div>
                <div class="stat-value"><?= min(100, $total) ?></div>
                <div class="stat-note">Capped at the top 100 donors from the current query.</div>
            </div>
        </section>

        <section class="section-block">
            <?php if ($total === 0) { ?>
                <div class="empty-state">
                    <h2 class="section-title">No donation records found</h2>
                    <p>Once donations are logged, the leaderboard will appear here automatically.</p>
                </div>
            <?php } else { ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Rank</th><th>Name</th><th>Blood Group</th><th>Area</th><th>Total Donations</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pageRows as $i => $row) { $rank = $offset + $i + 1; ?>
                        <tr>
                            <td><span class="badge <?= leaderboard_rank_class($rank) ?>">#<?= $rank ?></span></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><span class="badge status-neutral"><?= htmlspecialchars($row['blood_group']) ?></span></td>
                            <td><?= htmlspecialchars($row['area_name'] ?? '') ?></td>
                            <td><strong><?= (int)$row['total_donations'] ?></strong></td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="actions" style="margin-top:16px; justify-content:center;">
                    <?php $totalPages = ceil(min(100,$total)/$perPage); for ($p=1;$p<=$totalPages;$p++) { if ($p==$page) { echo '<span class="badge status-success">'.$p.'</span>'; } else { echo '<a class="button-outline" href="?page='.$p.'">'.$p.'</a>'; } } ?>
                </div>
            <?php } ?>
        </section>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>
