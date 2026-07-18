<?php
$conn = new mysqli("localhost", "root", "", "blood_donation_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>