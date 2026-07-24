<?php
include '../includes/db_connect.php';

if (isset($_POST['add_donor'])) {
    $name = $_POST['name'];
    $blood_group = $_POST['blood_group'];
    $area_id = $_POST['area_id'];
    $phone = $_POST['phone'];

    // আসল ডাটাবেজ টেবিল 'donor'-এ সেভ করা
    $sql = "INSERT INTO donor (name, blood_group, phone, area_id, availability_status) 
            VALUES ('$name', '$blood_group', '$phone', '$area_id', 'Available')";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?status=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
