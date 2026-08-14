DROP DATABASE IF EXISTS blood_donation_db;
CREATE DATABASE blood_donation_db;
USE blood_donation_db;

CREATE TABLE Area (
    area_id INT AUTO_INCREMENT PRIMARY KEY,
    area_name VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    UNIQUE (area_name, district)
);

CREATE TABLE Donor (
    donor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    blood_group ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    last_donation_date DATE,
    availability_status ENUM('Available','Unavailable') DEFAULT 'Available',
    area_id INT,

    FOREIGN KEY (area_id) REFERENCES Area(area_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE Hospital (
    hospital_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    area_id INT,

    FOREIGN KEY (area_id) REFERENCES Area(area_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE Recipient (
    recipient_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    blood_group ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
    phone VARCHAR(20) NOT NULL,
    hospital_id INT,

    FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE Blood_Bank (
    bank_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    area_id INT,

    FOREIGN KEY (area_id) REFERENCES Area(area_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE Blood_Inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    bank_id INT NOT NULL,
    blood_group ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
    units_available INT NOT NULL DEFAULT 0,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE (bank_id, blood_group),

    FOREIGN KEY (bank_id) REFERENCES Blood_Bank(bank_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE Emergency_Request (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT NOT NULL,
    hospital_id INT NOT NULL,
    blood_group ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
    units_needed INT NOT NULL,
    urgency_level ENUM('Low','Medium','Critical') DEFAULT 'Medium',
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending','Matched','Cancelled') DEFAULT 'Pending',

    FOREIGN KEY (recipient_id) REFERENCES Recipient(recipient_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE Donor_Match (
    match_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    donor_id INT NOT NULL,
    match_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    match_status ENUM('Suggested','Contacted','Confirmed','Declined')
        DEFAULT 'Suggested',

    UNIQUE (request_id, donor_id),

    FOREIGN KEY (request_id) REFERENCES Emergency_Request(request_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    FOREIGN KEY (donor_id) REFERENCES Donor(donor_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE Donation (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    bank_id INT NOT NULL,
    donation_date DATE NOT NULL,
    units_donated INT NOT NULL DEFAULT 1,

    FOREIGN KEY (donor_id) REFERENCES Donor(donor_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    FOREIGN KEY (bank_id) REFERENCES Blood_Bank(bank_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE Admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL
);