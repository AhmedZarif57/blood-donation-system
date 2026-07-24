<?php
include '../includes/db_connect.php';

$blood_filter = isset($_GET['blood_group']) ? $_GET['blood_group'] : '';

$query = "SELECT donor.*, area.area_name FROM donor LEFT JOIN area ON donor.area_id = area.area_id WHERE 1=1";

if (!empty($blood_filter)) {
    $query .= " AND donor.blood_group = '$blood_filter'";
}

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donor List & Area Search</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Donor & Area Search</h2>
        <a href="add_donor.php" class="btn btn-danger">+ Add New Donor</a>
    </div>

    <!-- Search Form -->
    <form method="GET" class="row g-3 mb-4 bg-white p-3 rounded shadow-sm">
        <div class="col-md-8">
            <select name="blood_group" class="form-select">
                <option value="">All Blood Groups</option>
                <option value="A+" <?php if($blood_filter == 'A+') echo 'selected'; ?>>A+</option>
                <option value="A-" <?php if($blood_filter == 'A-') echo 'selected'; ?>>A-</option>
                <option value="B+" <?php if($blood_filter == 'B+') echo 'selected'; ?>>B+</option>
                <option value="B-" <?php if($blood_filter == 'B-') echo 'selected'; ?>>B-</option>
                <option value="O+" <?php if($blood_filter == 'O+') echo 'selected'; ?>>O+</option>
                <option value="O-" <?php if($blood_filter == 'O-') echo 'selected'; ?>>O-</option>
                <option value="AB+" <?php if($blood_filter == 'AB+') echo 'selected'; ?>>AB+</option>
                <option value="AB-" <?php if($blood_filter == 'AB-') echo 'selected'; ?>>AB-</option>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Filter by Blood Group</button>
        </div>
    </form>

    <!-- Donor List Table -->
    <div class="card shadow">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Name</th>
                        <th>Blood Group</th>
                        <th>Area</th>
                        <th>Phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $id = isset($row['donor_id']) ? $row['donor_id'] : $row['id'];
                            $area_name = !empty($row['area_name']) ? $row['area_name'] : 'N/A';
                            $status = isset($row['availability_status']) ? $row['availability_status'] : 'Available';
                            echo "<tr>
                                <td>{$id}</td>
                                <td>{$row['name']}</td>
                                <td><span class='badge bg-danger'>{$row['blood_group']}</span></td>
                                <td><strong>{$area_name}</strong></td>
                                <td>{$row['phone']}</td>
                                <td><span class='badge bg-success'>{$status}</span></td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-muted'>No donors found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>