USE blood_donation_db;

DELIMITER $$

CREATE PROCEDURE sp_match_donor_to_request(
    IN p_request_id INT,
    IN p_donor_id INT
)
BEGIN
    DECLARE v_request_status VARCHAR(20);
    DECLARE v_donor_status VARCHAR(20);
    DECLARE v_donor_blood VARCHAR(5);
    DECLARE v_request_blood VARCHAR(5);

    SELECT status, blood_group
    INTO v_request_status, v_request_blood
    FROM Emergency_Request
    WHERE request_id = p_request_id;

    SELECT availability_status, blood_group
    INTO v_donor_status, v_donor_blood
    FROM Donor
    WHERE donor_id = p_donor_id;

    IF v_request_status IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Request not found';

    ELSEIF v_request_status <> 'Pending' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Request is no longer pending';

    ELSEIF v_donor_status IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Donor not found';

    ELSEIF v_donor_status <> 'Available' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Donor is unavailable';

    ELSEIF v_donor_blood <> v_request_blood THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Blood group does not match';

    ELSEIF EXISTS (
        SELECT 1
        FROM Donor_Match
        WHERE request_id = p_request_id
          AND donor_id = p_donor_id
          AND match_status = 'Confirmed'
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Donor is already confirmed for this request';

    ELSE
        UPDATE Donor_Match
        SET match_status = 'Confirmed',
            match_date = CURRENT_TIMESTAMP
        WHERE request_id = p_request_id
          AND donor_id = p_donor_id;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO Donor_Match (
                request_id,
                donor_id,
                match_status
            )
            VALUES (
                p_request_id,
                p_donor_id,
                'Confirmed'
            );
        END IF;
    END IF;
END$$


CREATE PROCEDURE sp_log_donation(
    IN p_donor_id INT,
    IN p_bank_id INT,
    IN p_donation_date DATE,
    IN p_units INT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM Donor
        WHERE donor_id = p_donor_id
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Donor not found';

    ELSEIF NOT EXISTS (
        SELECT 1 FROM Blood_Bank
        WHERE bank_id = p_bank_id
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Blood bank not found';

    ELSEIF p_units <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid donation amount';

    ELSE
        INSERT INTO Donation (
            donor_id,
            bank_id,
            donation_date,
            units_donated
        )
        VALUES (
            p_donor_id,
            p_bank_id,
            p_donation_date,
            p_units
        );
    END IF;
END$$


CREATE PROCEDURE sp_get_dashboard_stats()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM Donor) AS total_donors,
        (SELECT COUNT(*) FROM Donor
         WHERE availability_status = 'Available') AS available_donors,
        (SELECT COUNT(*) FROM Emergency_Request
         WHERE status = 'Pending') AS pending_requests,
        (SELECT COUNT(*) FROM Emergency_Request
         WHERE status = 'Matched') AS matched_requests,
        (SELECT COUNT(*) FROM Hospital) AS total_hospitals,
        (SELECT COUNT(*) FROM Blood_Bank) AS total_banks,
        (SELECT COUNT(*) FROM Donation) AS total_donations;
END$$


CREATE PROCEDURE sp_get_bank_inventory(
    IN p_bank_id INT
)
BEGIN
    SELECT
        bi.inventory_id,
        bi.bank_id,
        bb.name AS bank_name,
        bi.blood_group,
        bi.units_available,
        bi.last_updated
    FROM Blood_Inventory bi
    JOIN Blood_Bank bb
        ON bb.bank_id = bi.bank_id
    WHERE bi.bank_id = p_bank_id
    ORDER BY
        FIELD(
            bi.blood_group,
            'A+', 'A-',
            'B+', 'B-',
            'AB+', 'AB-',
            'O+', 'O-'
        );
END$$

DELIMITER ;