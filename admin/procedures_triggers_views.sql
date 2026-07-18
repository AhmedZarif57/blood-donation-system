DELIMITER //
   CREATE TRIGGER trg_auto_match_donor
   AFTER INSERT ON Emergency_Request
   FOR EACH ROW
   BEGIN
       IF NEW.urgency_level = 'Critical' THEN
           INSERT INTO Donor_Match (request_id, donor_id, match_status)
           SELECT NEW.request_id, d.donor_id, 'Suggested'
           FROM Donor d
           JOIN Hospital h ON h.hospital_id = NEW.hospital_id
           WHERE d.blood_group = NEW.blood_group
             AND d.availability_status = 'Available'
             AND d.area_id = h.area_id;
       END IF;
   END //
   DELIMITER ;

   INSERT INTO Area (area_name, district) VALUES ('Mirpur', 'Dhaka');
INSERT INTO Donor (name, blood_group, phone, availability_status, area_id) VALUES ('Test Donor', 'O+', '01700000000', 'Available', 1);
INSERT INTO Hospital (name, phone, area_id) VALUES ('Test Hospital', '01711111111', 1);
INSERT INTO Recipient (name, blood_group, phone, hospital_id) VALUES ('Test Recipient', 'O+', '01722222222', 1);

INSERT INTO Emergency_Request (recipient_id, hospital_id, blood_group, units_needed, urgency_level)
VALUES (1, 1, 'O+', 2, 'Critical');

SELECT * FROM Donor_Match;

DELETE FROM Donor_Match;
DELETE FROM Emergency_Request;
DELETE FROM Recipient;
DELETE FROM Hospital;
DELETE FROM Donor;
DELETE FROM Area;