# Smart Blood Donation & Emergency Matching System

## Setup Instructions
1. Install XAMPP, start Apache + MySQL.
2. Clone this repo into `C:\xampp\htdocs\`.
3. Open phpMyAdmin (`http://localhost/phpmyadmin`), create a database named
   `blood_donation_db`, then import `blood_data.sql` via the SQL tab.
4. Visit `http://localhost/blood_donation/index.php` to confirm it connects.
5. Add Data on the team's Behalf

## Folder Rules
- Only edit files inside YOUR assigned folder (donor/, hospital/, inventory/, donation/, admin/).
- Never edit `includes/db_connect.php` — it's shared by everyone.
- If you need a shared file changed, message Zarif instead of editing it directly.

## Module Owners
- Donor & Area — Ridoy
- Hospital & Emergency Request — Hemonto
- Blood Bank & Inventory — Samiul
- Donation & Eligibility — Naymur
- Database Core, Matching Engine, Integration — Zarif
