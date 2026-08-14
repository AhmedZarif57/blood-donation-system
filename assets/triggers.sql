USE blood_donation_db;

DELIMITER $$

CREATE TRIGGER trg_initialize_bank_inventory
AFTER INSERT ON Blood_Bank
FOR EACH ROW
BEGIN
    INSERT INTO Blood_Inventory (bank_id, blood_group)
    VALUES
        (NEW.bank_id, 'A+'),
        (NEW.bank_id, 'A-'),
        (NEW.bank_id, 'B+'),
        (NEW.bank_id, 'B-'),
        (NEW.bank_id, 'AB+'),
        (NEW.bank_id, 'AB-'),
        (NEW.bank_id, 'O+'),
        (NEW.bank_id, 'O-');
END$$


CREATE TRIGGER trg_auto_match_donor
AFTER INSERT ON Emergency_Request
FOR EACH ROW
BEGIN
    INSERT INTO Donor_Match (request_id, donor_id)
    SELECT NEW.request_id, d.donor_id
    FROM Donor d
    WHERE d.blood_group = NEW.blood_group
      AND d.availability_status = 'Available';
END$$


CREATE TRIGGER trg_update_inventory_after_donation
AFTER INSERT ON Donation
FOR EACH ROW
BEGIN
    UPDATE Blood_Inventory bi
    JOIN Donor d ON d.donor_id = NEW.donor_id
    SET bi.units_available = bi.units_available + NEW.units_donated
    WHERE bi.bank_id = NEW.bank_id
      AND bi.blood_group = d.blood_group;
END$$


CREATE TRIGGER trg_update_donor_after_donation
AFTER INSERT ON Donation
FOR EACH ROW
BEGIN
    UPDATE Donor
    SET last_donation_date = NEW.donation_date
    WHERE donor_id = NEW.donor_id;
END$$


CREATE TRIGGER trg_confirm_match_request
AFTER UPDATE ON Donor_Match
FOR EACH ROW
BEGIN
    IF NEW.match_status = 'Confirmed'
       AND OLD.match_status <> 'Confirmed' THEN

        UPDATE Emergency_Request
        SET status = 'Matched'
        WHERE request_id = NEW.request_id
          AND status = 'Pending';

    END IF;
END$$

DELIMITER ;