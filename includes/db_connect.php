<?php
$conn = new mysqli("localhost", "root", "", "blood_donation_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SHOW COLUMNS FROM Blood_Bank LIKE 'phone'");
if ($result && $result->num_rows === 0) {
    $conn->query("ALTER TABLE Blood_Bank ADD COLUMN phone VARCHAR(20) NULL AFTER name");
}

$reqCols = $conn->query("SHOW COLUMNS FROM Emergency_Request LIKE 'matched_count'");
if ($reqCols && $reqCols->num_rows === 0) {
    $conn->query("ALTER TABLE Emergency_Request ADD COLUMN matched_count INT NOT NULL DEFAULT 0 AFTER status");
}

$matchIndex = $conn->query("SHOW INDEX FROM Donor_Match WHERE Key_name = 'uq_request_donor'");
if ($matchIndex && $matchIndex->num_rows === 0) {
    $conn->query("ALTER TABLE Donor_Match ADD UNIQUE KEY uq_request_donor (request_id, donor_id)");
}
?>