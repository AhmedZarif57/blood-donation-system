CREATE TABLE Area (
  area_id INT AUTO_INCREMENT PRIMARY KEY,
  area_name VARCHAR(100) NOT NULL,
  district VARCHAR(100) NOT NULL
);

CREATE TABLE Donor (
  donor_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  blood_group VARCHAR(5) NOT NULL,
  phone VARCHAR(20),
  last_donation_date DATE,
  availability_status ENUM('Available','Unavailable') DEFAULT 'Available',
  area_id INT,
  FOREIGN KEY (area_id) REFERENCES Area(area_id)
);

CREATE TABLE Hospital (
  hospital_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(20),
  area_id INT,
  FOREIGN KEY (area_id) REFERENCES Area(area_id)
);

CREATE TABLE Recipient (
  recipient_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  blood_group VARCHAR(5) NOT NULL,
  phone VARCHAR(20),
  hospital_id INT,
  FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id)
);

CREATE TABLE Blood_Bank (
  bank_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  capacity INT NOT NULL,
  area_id INT,
  FOREIGN KEY (area_id) REFERENCES Area(area_id)
);

CREATE TABLE Blood_Inventory (
  inventory_id INT AUTO_INCREMENT PRIMARY KEY,
  bank_id INT,
  blood_group VARCHAR(5) NOT NULL,
  units_available INT DEFAULT 0,
  last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (bank_id) REFERENCES Blood_Bank(bank_id)
);

CREATE TABLE Emergency_Request (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  recipient_id INT,
  hospital_id INT,
  blood_group VARCHAR(5) NOT NULL,
  units_needed INT NOT NULL,
  urgency_level ENUM('Low','Medium','Critical') DEFAULT 'Medium',
  request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  status ENUM('Pending','Matched','Fulfilled','Cancelled') DEFAULT 'Pending',
  FOREIGN KEY (recipient_id) REFERENCES Recipient(recipient_id),
  FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id)
);

CREATE TABLE Donation (
  donation_id INT AUTO_INCREMENT PRIMARY KEY,
  donor_id INT,
  bank_id INT,
  donation_date DATE NOT NULL,
  units_donated INT DEFAULT 1,
  FOREIGN KEY (donor_id) REFERENCES Donor(donor_id),
  FOREIGN KEY (bank_id) REFERENCES Blood_Bank(bank_id)
);

CREATE TABLE Donor_Match (
  match_id INT AUTO_INCREMENT PRIMARY KEY,
  request_id INT,
  donor_id INT,
  match_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  match_status ENUM('Suggested','Contacted','Confirmed','Declined') DEFAULT 'Suggested',
  FOREIGN KEY (request_id) REFERENCES Emergency_Request(request_id),
  FOREIGN KEY (donor_id) REFERENCES Donor(donor_id)
);