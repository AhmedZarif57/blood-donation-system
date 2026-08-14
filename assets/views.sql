USE blood_donation_db;

CREATE VIEW vw_available_donors AS
SELECT
    d.donor_id,
    d.name,
    d.blood_group,
    d.phone,
    d.availability_status,
    a.area_name,
    a.district
FROM Donor d
LEFT JOIN Area a ON a.area_id = d.area_id
WHERE d.availability_status = 'Available';


CREATE VIEW vw_active_requests AS
SELECT
    er.request_id,
    r.name AS recipient_name,
    r.phone AS recipient_phone,
    er.blood_group,
    er.units_needed,
    er.urgency_level,
    er.request_date,
    er.status,
    h.name AS hospital_name,
    h.phone AS hospital_phone,
    a.area_name,
    a.district
FROM Emergency_Request er
JOIN Recipient r
    ON r.recipient_id = er.recipient_id
JOIN Hospital h
    ON h.hospital_id = er.hospital_id
LEFT JOIN Area a
    ON a.area_id = h.area_id
WHERE er.status IN ('Pending', 'Matched');


CREATE VIEW vw_blood_bank_inventory AS
SELECT
    bb.bank_id,
    bb.name AS bank_name,
    bb.contact_number,
    a.area_name,
    a.district,
    bi.blood_group,
    bi.units_available,
    bi.last_updated
FROM Blood_Bank bb
LEFT JOIN Area a
    ON a.area_id = bb.area_id
JOIN Blood_Inventory bi
    ON bi.bank_id = bb.bank_id;


CREATE VIEW vw_donor_leaderboard AS
SELECT
    d.donor_id,
    d.name,
    d.blood_group,
    d.phone,
    a.area_name,
    COUNT(do.donation_id) AS total_donations
FROM Donor d
LEFT JOIN Donation do
    ON do.donor_id = d.donor_id
LEFT JOIN Area a
    ON a.area_id = d.area_id
GROUP BY
    d.donor_id,
    d.name,
    d.blood_group,
    d.phone,
    a.area_name;


CREATE VIEW vw_recent_donations AS
SELECT
    do.donation_id,
    do.donation_date,
    do.units_donated,
    d.donor_id,
    d.name AS donor_name,
    d.blood_group,
    bb.bank_id,
    bb.name AS bank_name
FROM Donation do
JOIN Donor d
    ON d.donor_id = do.donor_id
JOIN Blood_Bank bb
    ON bb.bank_id = do.bank_id;