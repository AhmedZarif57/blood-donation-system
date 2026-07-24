<?php
include '../includes/db_connect.php';

// area টেবিল থেকে এলাকার তালিকা আনা
$area_query = "SELECT * FROM area";
$area_result = mysqli_query($conn, $area_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Donor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow p-4">
        <h2 class="mb-4 text-danger">Register New Donor</h2>
        <form action="process_donor.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Blood Group</label>
                <select name="blood_group" class="form-select" required>
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Select Area</label>
                <select name="area_id" class="form-select" required>
                    <option value="">Select Area</option>
                    <?php 
                    if($area_result && mysqli_num_rows($area_result) > 0) {
                        while($area = mysqli_fetch_assoc($area_result)) {
                            // আপনার area টেবিলের কলাম অনুযায়ী adjust করা হয়েছে
                            $a_id = isset($area['area_id']) ? $area['area_id'] : $area['id'];
                            $a_name = isset($area['area_name']) ? $area['area_name'] : (isset($area['name']) ? $area['name'] : 'Area '.$a_id);
                            echo "<option value='{$a_id}'>{$a_name}</option>";
                        }
                    } else {
                        echo "<option value='1'>Default Area</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <button type="submit" name="add_donor" class="btn btn-danger">Save Donor</button>
            <a href="index.php" class="btn btn-secondary">View Donors</a>
        </form>
    </div>
</div>
</body>
</html>