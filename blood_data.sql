-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 14, 2026 at 04:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blood_donation_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_bank_inventory` (IN `p_bank_id` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_dashboard_stats` ()   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_log_donation` (IN `p_donor_id` INT, IN `p_bank_id` INT, IN `p_donation_date` DATE, IN `p_units` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_match_donor_to_request` (IN `p_request_id` INT, IN `p_donor_id` INT)   BEGIN
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

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `full_name`, `username`, `phone`, `password_hash`) VALUES
(1, 'Admin', 'admin', '', '$2y$10$zU4Z84FLdIGQ3p6DVCDOt.sbZPLyCS5LDdU61x17sz0BGvGsV8o3e');

-- --------------------------------------------------------

--
-- Table structure for table `area`
--

CREATE TABLE `area` (
  `area_id` int(11) NOT NULL,
  `area_name` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `area`
--

INSERT INTO `area` (`area_id`, `area_name`, `district`) VALUES
(105, 'Abhaynagar', 'Jashore'),
(256, 'Adamdighi', 'Bogra'),
(195, 'Adarsha Sadar', 'Cumilla'),
(394, 'Aditmari', 'Lalmonirhat'),
(466, 'Agailjhara', 'Barishal'),
(324, 'Ajmiriganj', 'Habiganj'),
(158, 'Akhaura', 'Brahmanbaria'),
(269, 'Akkelpur', 'Joypurhat'),
(101, 'Alamdanga', 'Chuadanga'),
(8, 'Alfadanga', 'Faridpur'),
(151, 'Alikadam', 'Bandarban'),
(460, 'Amtali', 'Barguna'),
(175, 'Anwara', 'Chattogram'),
(58, 'Araihazar', 'Narayanganj'),
(162, 'Ashuganj', 'Brahmanbaria'),
(144, 'Assasuni', 'Satkhira'),
(297, 'Atgharia', 'Pabna'),
(445, 'Atpara', 'Netrokona'),
(274, 'Atrai', 'Naogaon'),
(405, 'Atwari', 'Panchagarh'),
(27, 'Austagram', 'Kishoreganj'),
(467, 'Babuganj', 'Barishal'),
(283, 'Badalgachhi', 'Naogaon'),
(410, 'Badarganj', 'Rangpur'),
(285, 'Bagatipara', 'Natore'),
(100, 'Bagerhat Sadar', 'Bagerhat'),
(306, 'Bagha', 'Rajshahi'),
(246, 'Baghaichhari', 'Rangamati'),
(106, 'Bagherpara', 'Jashore'),
(307, 'Bagmara', 'Rajshahi'),
(325, 'Bahubal', 'Habiganj'),
(28, 'Bajitpur', 'Kishoreganj'),
(468, 'Bakerganj', 'Barishal'),
(425, 'Bakshiganj', 'Jamalpur'),
(352, 'Balaganj', 'Sylhet'),
(419, 'Baliadangi', 'Thakurgaon'),
(69, 'Baliakandi', 'Rajbari'),
(461, 'Bamna', 'Barguna'),
(469, 'Banaripara', 'Barishal'),
(159, 'Bancharampur', 'Brahmanbaria'),
(62, 'Bandar', 'Narayanganj'),
(152, 'Bandarban Sadar', 'Bandarban'),
(326, 'Baniyachong', 'Habiganj'),
(176, 'Banshkhali', 'Chattogram'),
(286, 'Baraigram', 'Natore'),
(462, 'Barguna Sadar', 'Barguna'),
(446, 'Barhatta', 'Netrokona'),
(507, 'Barishal City Corporation', 'Barishal'),
(472, 'Barishal Sadar', 'Barishal'),
(247, 'Barkal', 'Rangamati'),
(333, 'Barlekha', 'Moulvibazar'),
(190, 'Barura', 'Cumilla'),
(80, 'Basail', 'Tangail'),
(119, 'Batiaghata', 'Khulna'),
(487, 'Bauphal', 'Patuakhali'),
(353, 'Beanibazar', 'Sylhet'),
(237, 'Begumganj', 'Noakhali'),
(63, 'Belabo', 'Narsingdi'),
(255, 'Belaichhari', 'Rangamati'),
(315, 'Belkuchi', 'Sirajganj'),
(298, 'Bera', 'Pabna'),
(463, 'Betagi', 'Barguna'),
(29, 'Bhairab', 'Kishoreganj'),
(432, 'Bhaluka', 'Mymensingh'),
(495, 'Bhandaria', 'Pirojpur'),
(9, 'Bhanga', 'Faridpur'),
(299, 'Bhangura', 'Pabna'),
(74, 'Bhedarganj', 'Shariatpur'),
(128, 'Bheramara', 'Kushtia'),
(476, 'Bhola Sadar', 'Bhola'),
(293, 'Bholahat', 'Chapainawabganj'),
(81, 'Bhuapur', 'Tangail'),
(423, 'Bhulli', 'Thakurgaon'),
(386, 'Bhurungamari', 'Kurigram'),
(160, 'Bijoynagar', 'Brahmanbaria'),
(365, 'Birampur', 'Dinajpur'),
(366, 'Birganj', 'Dinajpur'),
(367, 'Birol', 'Dinajpur'),
(340, 'Bishwambharpur', 'Sunamganj'),
(354, 'Bishwanath', 'Sylhet'),
(177, 'Boalkhali', 'Chattogram'),
(10, 'Boalmari', 'Faridpur'),
(368, 'Bochaganj', 'Dinajpur'),
(406, 'Boda', 'Panchagarh'),
(514, 'Bogra City Corporation', 'Bogra'),
(257, 'Bogra Sadar', 'Bogra'),
(477, 'Borhanuddin', 'Bhola'),
(161, 'Brahmanbaria Sadar', 'Brahmanbaria'),
(191, 'Brahmanpara', 'Cumilla'),
(192, 'Burichang', 'Cumilla'),
(207, 'Chakaria', 'Cox\'s Bazar'),
(178, 'Chandanaish', 'Chattogram'),
(193, 'Chandina', 'Cumilla'),
(167, 'Chandpur Sadar', 'Chandpur'),
(236, 'Chandraganj', 'Lakshmipur'),
(296, 'Chapainawabganj Sadar', 'Chapainawabganj'),
(482, 'Char Fasson', 'Bhola'),
(387, 'Char Rajibpur', 'Kurigram'),
(11, 'Charbhadrasan', 'Faridpur'),
(308, 'Charghat', 'Rajshahi'),
(238, 'Chatkhil', 'Noakhali'),
(300, 'Chatmohar', 'Pabna'),
(504, 'Chattogram City Corporation', 'Chattogram'),
(194, 'Chauddagram', 'Cumilla'),
(107, 'Chaugachha', 'Jashore'),
(316, 'Chauhali', 'Sirajganj'),
(216, 'Chhagalnaiya', 'Feni'),
(341, 'Chhatak', 'Sunamganj'),
(388, 'Chilmari', 'Kurigram'),
(369, 'Chirirbandar', 'Dinajpur'),
(92, 'Chitalmari', 'Bagerhat'),
(102, 'Chuadanga Sadar', 'Chuadanga'),
(327, 'Chunarughat', 'Habiganj'),
(239, 'Companiganj', 'Noakhali'),
(355, 'Companiganj', 'Sylhet'),
(208, 'Cox\'s Bazar Sadar', 'Cox\'s Bazar'),
(510, 'Cumilla City Corporation', 'Cumilla'),
(120, 'Dacope', 'Khulna'),
(217, 'Daganbhuiyan', 'Feni'),
(356, 'Dakshin Surma', 'Sylhet'),
(75, 'Damudya', 'Shariatpur'),
(103, 'Damurhuda', 'Chuadanga'),
(44, 'Dasar', 'Madaripur'),
(488, 'Dashmina', 'Patuakhali'),
(197, 'Daudkandi', 'Cumilla'),
(478, 'Daulatkhan', 'Bhola'),
(129, 'Daulatpur', 'Kushtia'),
(45, 'Daulatpur', 'Manikganj'),
(145, 'Debhata', 'Satkhira'),
(198, 'Debidwar', 'Cumilla'),
(407, 'Debiganj', 'Panchagarh'),
(82, 'Delduar', 'Tangail'),
(342, 'Derai', 'Sunamganj'),
(426, 'Dewanganj', 'Jamalpur'),
(502, 'Dhaka North City Corporation', 'Dhaka'),
(503, 'Dhaka South City Corporation', 'Dhaka'),
(275, 'Dhamoirhat', 'Naogaon'),
(3, 'Dhamrai', 'Dhaka'),
(83, 'Dhanbari', 'Tangail'),
(343, 'Dharmapasha', 'Sunamganj'),
(433, 'Dhobaura', 'Mymensingh'),
(258, 'Dhunat', 'Bogra'),
(127, 'Dighalia', 'Khulna'),
(222, 'Dighinala', 'Khagrachhari'),
(404, 'Dimla', 'Nilphamari'),
(377, 'Dinajpur Sadar', 'Dinajpur'),
(4, 'Dohar', 'Dhaka'),
(399, 'Domar', 'Nilphamari'),
(344, 'Dowarabazar', 'Sunamganj'),
(489, 'Dumki', 'Patuakhali'),
(121, 'Dumuria', 'Khulna'),
(259, 'Dupchanchia', 'Bogra'),
(447, 'Durgapur', 'Netrokona'),
(309, 'Durgapur', 'Rajshahi'),
(215, 'Eidgaon', 'Cox\'s Bazar'),
(93, 'Fakirhat', 'Bagerhat'),
(168, 'Faridganj', 'Chandpur'),
(301, 'Faridpur', 'Pabna'),
(12, 'Faridpur Sadar', 'Faridpur'),
(179, 'Fatikchhari', 'Chattogram'),
(357, 'Fenchuganj', 'Sylhet'),
(218, 'Feni Sadar', 'Feni'),
(434, 'Fulbaria', 'Mymensingh'),
(219, 'Fulgazi', 'Feni'),
(260, 'Gabtali', 'Bogra'),
(435, 'Gaffargaon', 'Mymensingh'),
(379, 'Gaibandha Sadar', 'Gaibandha'),
(494, 'Galachipa', 'Patuakhali'),
(417, 'Gangachara', 'Rangpur'),
(138, 'Gangni', 'Meherpur'),
(436, 'Gauripur', 'Mymensingh'),
(52, 'Gazaria', 'Munshiganj'),
(512, 'Gazipur City Corporation', 'Gazipur'),
(17, 'Gazipur Sadar', 'Gazipur'),
(84, 'Ghatail', 'Tangail'),
(46, 'Ghior', 'Manikganj'),
(371, 'Ghoraghat', 'Dinajpur'),
(70, 'Goalanda', 'Rajbari'),
(380, 'Gobindaganj', 'Gaibandha'),
(310, 'Godagari', 'Rajshahi'),
(358, 'Golapganj', 'Sylhet'),
(294, 'Gomastapur', 'Chapainawabganj'),
(22, 'Gopalganj Sadar', 'Gopalganj'),
(85, 'Gopalpur', 'Tangail'),
(76, 'Gosairhat', 'Shariatpur'),
(470, 'Gournadi', 'Barishal'),
(359, 'Gowainghat', 'Sylhet'),
(230, 'Guimara', 'Khagrachhari'),
(287, 'Gurudaspur', 'Natore'),
(328, 'Habiganj Sadar', 'Habiganj'),
(169, 'Haimchar', 'Chandpur'),
(170, 'Hajiganj', 'Chandpur'),
(372, 'Hakimpur', 'Dinajpur'),
(437, 'Haluaghat', 'Mymensingh'),
(113, 'Harinakunda', 'Jhenaidah'),
(420, 'Haripur', 'Thakurgaon'),
(47, 'Harirampur', 'Manikganj'),
(180, 'Hathazari', 'Chattogram'),
(395, 'Hatibandha', 'Lalmonirhat'),
(240, 'Hatiya', 'Noakhali'),
(471, 'Hizla', 'Barishal'),
(199, 'Homna', 'Cumilla'),
(30, 'Hossainpur', 'Kishoreganj'),
(302, 'Ishwardi', 'Pabna'),
(438, 'Ishwarganj', 'Mymensingh'),
(427, 'Islampur', 'Jamalpur'),
(31, 'Itna', 'Kishoreganj'),
(345, 'Jagannathpur', 'Sunamganj'),
(360, 'Jaintiapur', 'Sylhet'),
(400, 'Jaldhaka', 'Nilphamari'),
(346, 'Jamalganj', 'Sunamganj'),
(428, 'Jamalpur Sadar', 'Jamalpur'),
(110, 'Jashore Sadar', 'Jashore'),
(483, 'Jhalakathi Sadar', 'Jhalakathi'),
(114, 'Jhenaidah Sadar', 'Jhenaidah'),
(455, 'Jhenaigati', 'Sherpur'),
(108, 'Jhikargachha', 'Jashore'),
(104, 'Jibannagar', 'Chuadanga'),
(270, 'Joypurhat Sadar', 'Joypurhat'),
(250, 'Juraichhari', 'Rangamati'),
(334, 'Juri', 'Moulvibazar'),
(245, 'Kabirhat', 'Noakhali'),
(94, 'Kachua', 'Bagerhat'),
(171, 'Kachua', 'Chandpur'),
(261, 'Kahaloo', 'Bogra'),
(373, 'Kaharol', 'Dinajpur'),
(271, 'Kalai', 'Joypurhat'),
(490, 'Kalapara', 'Patuakhali'),
(146, 'Kalaroa', 'Satkhira'),
(141, 'Kalia', 'Narail'),
(18, 'Kaliakair', 'Gazipur'),
(19, 'Kaliganj', 'Gazipur'),
(115, 'Kaliganj', 'Jhenaidah'),
(396, 'Kaliganj', 'Lalmonirhat'),
(147, 'Kaliganj', 'Satkhira'),
(86, 'Kalihati', 'Tangail'),
(40, 'Kalkini', 'Madaripur'),
(449, 'Kalmakanda', 'Netrokona'),
(71, 'Kalukhali', 'Rajbari'),
(335, 'Kamalganj', 'Moulvibazar'),
(231, 'Kamalnagar', 'Lakshmipur'),
(317, 'Kamarkhand', 'Sirajganj'),
(361, 'Kanaighat', 'Sylhet'),
(20, 'Kapasia', 'Gazipur'),
(249, 'Kaptai', 'Rangamati'),
(32, 'Karimganj', 'Kishoreganj'),
(189, 'Karnafuli', 'Chattogram'),
(163, 'Kasba', 'Brahmanbaria'),
(23, 'Kashiani', 'Gopalganj'),
(485, 'Kathalia', 'Jhalakathi'),
(33, 'Katiadi', 'Kishoreganj'),
(496, 'Kaukhali', 'Pirojpur'),
(248, 'Kaukhali', 'Rangamati'),
(411, 'Kaunia', 'Rangpur'),
(318, 'Kazipur', 'Sirajganj'),
(450, 'Kendua', 'Netrokona'),
(5, 'Keraniganj', 'Dhaka'),
(109, 'Keshabpur', 'Jashore'),
(224, 'Khagrachhari Sadar', 'Khagrachhari'),
(448, 'Khaliajuri', 'Netrokona'),
(374, 'Khansama', 'Dinajpur'),
(273, 'Khetlal', 'Joypurhat'),
(130, 'Khoksa', 'Kushtia'),
(505, 'Khulna City Corporation', 'Khulna'),
(401, 'Kishoreganj', 'Nilphamari'),
(34, 'Kishoreganj Sadar', 'Kishoreganj'),
(24, 'Kotalipara', 'Gopalganj'),
(116, 'Kotchandpur', 'Jhenaidah'),
(122, 'Koyra', 'Khulna'),
(336, 'Kulaura', 'Moulvibazar'),
(35, 'Kuliarchar', 'Kishoreganj'),
(131, 'Kumarkhali', 'Kushtia'),
(389, 'Kurigram Sadar', 'Kurigram'),
(132, 'Kushtia Sadar', 'Kushtia'),
(209, 'Kutubdia', 'Cox\'s Bazar'),
(329, 'Lakhai', 'Habiganj'),
(200, 'Laksam', 'Cumilla'),
(225, 'Lakshmichhari', 'Khagrachhari'),
(232, 'Lakshmipur Sadar', 'Lakshmipur'),
(206, 'Lalmai', 'Cumilla'),
(479, 'Lalmohan', 'Bhola'),
(397, 'Lalmonirhat Sadar', 'Lalmonirhat'),
(288, 'Lalpur', 'Natore'),
(153, 'Lama', 'Bandarban'),
(251, 'Langadu', 'Rangamati'),
(181, 'Lohagara', 'Chattogram'),
(142, 'Lohagara', 'Narail'),
(53, 'Louhajang', 'Munshiganj'),
(451, 'Madan', 'Netrokona'),
(429, 'Madarganj', 'Jamalpur'),
(41, 'Madaripur Sadar', 'Madaripur'),
(330, 'Madhabpur', 'Habiganj'),
(13, 'Madhukhali', 'Faridpur'),
(87, 'Madhupur', 'Tangail'),
(351, 'Madhyanagar', 'Sunamganj'),
(134, 'Magura Sadar', 'Magura'),
(277, 'Mahadevpur', 'Naogaon'),
(226, 'Mahalchhari', 'Khagrachhari'),
(210, 'Maheshkhali', 'Cox\'s Bazar'),
(117, 'Maheshpur', 'Jhenaidah'),
(276, 'Manda', 'Naogaon'),
(223, 'Manikchhari', 'Khagrachhari'),
(48, 'Manikganj Sadar', 'Manikganj'),
(111, 'Manirampur', 'Jashore'),
(497, 'Mathbaria', 'Pirojpur'),
(227, 'Matiranga', 'Khagrachhari'),
(172, 'Matlab Dakshin', 'Chandpur'),
(173, 'Matlab Uttar', 'Chandpur'),
(202, 'Meghna', 'Cumilla'),
(473, 'Mehendiganj', 'Barishal'),
(140, 'Meherpur Sadar', 'Meherpur'),
(430, 'Melandaha', 'Jamalpur'),
(133, 'Mirpur', 'Kushtia'),
(182, 'Mirsharai', 'Chattogram'),
(491, 'Mirzaganj', 'Patuakhali'),
(88, 'Mirzapur', 'Tangail'),
(36, 'Mithamoin', 'Kishoreganj'),
(413, 'Mithapukur', 'Rangpur'),
(135, 'Mohammadpur', 'Magura'),
(452, 'Mohanganj', 'Netrokona'),
(311, 'Mohanpur', 'Rajshahi'),
(268, 'Mokamtala', 'Bogra'),
(95, 'Mollahat', 'Bagerhat'),
(96, 'Mongla', 'Bagerhat'),
(64, 'Monohardi', 'Narsingdi'),
(201, 'Monoharganj', 'Cumilla'),
(480, 'Monpura', 'Bhola'),
(97, 'Morrelganj', 'Bagerhat'),
(337, 'Moulvibazar Sadar', 'Moulvibazar'),
(139, 'Mujibnagar', 'Meherpur'),
(25, 'Muksudpur', 'Gopalganj'),
(440, 'Muktagachha', 'Mymensingh'),
(474, 'Muladi', 'Barishal'),
(54, 'Munshiganj Sadar', 'Munshiganj'),
(203, 'Muradnagar', 'Cumilla'),
(513, 'Mymensingh City Corporation', 'Mymensingh'),
(439, 'Mymensingh Sadar', 'Mymensingh'),
(331, 'Nabiganj', 'Habiganj'),
(164, 'Nabinagar', 'Brahmanbaria'),
(14, 'Nagarkanda', 'Faridpur'),
(89, 'Nagarpur', 'Tangail'),
(390, 'Nageshwari', 'Kurigram'),
(154, 'Naikhongchhari', 'Bandarban'),
(456, 'Nakla', 'Sherpur'),
(484, 'Nalchity', 'Jhalakathi'),
(291, 'Naldanga', 'Natore'),
(457, 'Nalitabari', 'Sherpur'),
(441, 'Nandail', 'Mymensingh'),
(262, 'Nandigram', 'Bogra'),
(204, 'Nangalkot', 'Cumilla'),
(252, 'Naniarchar', 'Rangamati'),
(278, 'Naogaon Sadar', 'Naogaon'),
(143, 'Narail Sadar', 'Narail'),
(509, 'Narayanganj City Corporation', 'Narayanganj'),
(60, 'Narayanganj Sadar', 'Narayanganj'),
(77, 'Naria', 'Shariatpur'),
(65, 'Narsingdi Sadar', 'Narsingdi'),
(165, 'Nasirnagar', 'Brahmanbaria'),
(289, 'Natore Sadar', 'Natore'),
(6, 'Nawabganj', 'Dhaka'),
(375, 'Nawabganj', 'Dinajpur'),
(498, 'Nazirpur', 'Pirojpur'),
(500, 'Nesarabad', 'Pirojpur'),
(453, 'Netrokona Sadar', 'Netrokona'),
(279, 'Niamatpur', 'Naogaon'),
(37, 'Nikli', 'Kishoreganj'),
(402, 'Nilphamari Sadar', 'Nilphamari'),
(244, 'Noakhali Sadar', 'Noakhali'),
(364, 'Osmani Nagar', 'Sylhet'),
(312, 'Paba', 'Rajshahi'),
(303, 'Pabna Sadar', 'Pabna'),
(123, 'Paikgachha', 'Khulna'),
(38, 'Pakundia', 'Kishoreganj'),
(66, 'Palash', 'Narsingdi'),
(381, 'Palashbari', 'Gaibandha'),
(408, 'Panchagarh Sadar', 'Panchagarh'),
(272, 'Panchbibi', 'Joypurhat'),
(228, 'Panchhari', 'Khagrachhari'),
(72, 'Pangsha', 'Rajbari'),
(376, 'Parbatipur', 'Dinajpur'),
(220, 'Parshuram', 'Feni'),
(398, 'Patgram', 'Lalmonirhat'),
(464, 'Patharghata', 'Barguna'),
(183, 'Patiya', 'Chattogram'),
(280, 'Patnitala', 'Naogaon'),
(492, 'Patuakhali Sadar', 'Patuakhali'),
(211, 'Pekua', 'Cox\'s Bazar'),
(370, 'Phulbari', 'Dinajpur'),
(385, 'Phulbari', 'Kurigram'),
(378, 'Phulchhari', 'Gaibandha'),
(442, 'Phulpur', 'Mymensingh'),
(124, 'Phultala', 'Khulna'),
(414, 'Pirgachha', 'Rangpur'),
(415, 'Pirganj', 'Rangpur'),
(418, 'Pirganj', 'Thakurgaon'),
(499, 'Pirojpur Sadar', 'Pirojpur'),
(284, 'Porsha', 'Naogaon'),
(454, 'Purbadhala', 'Netrokona'),
(313, 'Puthia', 'Rajshahi'),
(319, 'Raiganj', 'Sirajganj'),
(233, 'Raipur', 'Lakshmipur'),
(67, 'Raipura', 'Narsingdi'),
(486, 'Rajapur', 'Jhalakathi'),
(391, 'Rajarhat', 'Kurigram'),
(254, 'Rajasthali', 'Rangamati'),
(73, 'Rajbari Sadar', 'Rajbari'),
(338, 'Rajnagar', 'Moulvibazar'),
(42, 'Rajoir', 'Madaripur'),
(506, 'Rajshahi City Corporation', 'Rajshahi'),
(234, 'Ramganj', 'Lakshmipur'),
(229, 'Ramgarh', 'Khagrachhari'),
(235, 'Ramgati', 'Lakshmipur'),
(98, 'Rampal', 'Bagerhat'),
(212, 'Ramu', 'Cox\'s Bazar'),
(493, 'Rangabali', 'Patuakhali'),
(253, 'Rangamati Sadar', 'Rangamati'),
(511, 'Rangpur City Corporation', 'Rangpur'),
(412, 'Rangpur Sadar', 'Rangpur'),
(184, 'Rangunia', 'Chattogram'),
(281, 'Raninagar', 'Naogaon'),
(421, 'Ranisankail', 'Thakurgaon'),
(185, 'Raozan', 'Chattogram'),
(155, 'Rowangchhari', 'Bandarban'),
(392, 'Rowmari', 'Kurigram'),
(424, 'Ruhia', 'Thakurgaon'),
(156, 'Ruma', 'Bandarban'),
(61, 'Rupganj', 'Narayanganj'),
(125, 'Rupsa', 'Khulna'),
(196, 'Sadar Dakshin', 'Cumilla'),
(15, 'Sadarpur', 'Faridpur'),
(382, 'Sadullapur', 'Gaibandha'),
(383, 'Saghata', 'Gaibandha'),
(403, 'Saidpur', 'Nilphamari'),
(90, 'Sakhipur', 'Tangail'),
(16, 'Saltha', 'Faridpur'),
(186, 'Sandwip', 'Chattogram'),
(304, 'Santhia', 'Pabna'),
(282, 'Sapahar', 'Naogaon'),
(166, 'Sarail', 'Brahmanbaria'),
(99, 'Sarankhola', 'Bagerhat'),
(431, 'Sarishabari', 'Jamalpur'),
(187, 'Satkania', 'Chattogram'),
(148, 'Satkhira Sadar', 'Satkhira'),
(49, 'Saturia', 'Manikganj'),
(7, 'Savar', 'Dhaka'),
(241, 'Senbagh', 'Noakhali'),
(320, 'Shahjadpur', 'Sirajganj'),
(174, 'Shahrasti', 'Chandpur'),
(118, 'Shailkupa', 'Jhenaidah'),
(264, 'Shajahanpur', 'Bogra'),
(136, 'Shalikha', 'Magura'),
(347, 'Shalla', 'Sunamganj'),
(350, 'Shantiganj', 'Sunamganj'),
(263, 'Shariakandi', 'Bogra'),
(78, 'Shariatpur Sadar', 'Shariatpur'),
(112, 'Sharsha', 'Jashore'),
(332, 'Shayestaganj', 'Habiganj'),
(265, 'Sherpur', 'Bogra'),
(458, 'Sherpur Sadar', 'Sherpur'),
(50, 'Shibaloy', 'Manikganj'),
(43, 'Shibchar', 'Madaripur'),
(266, 'Shibganj', 'Bogra'),
(292, 'Shibganj', 'Chapainawabganj'),
(68, 'Shibpur', 'Narsingdi'),
(149, 'Shyamnagar', 'Satkhira'),
(51, 'Singair', 'Manikganj'),
(290, 'Singra', 'Natore'),
(55, 'Sirajdikhan', 'Munshiganj'),
(321, 'Sirajganj Sadar', 'Sirajganj'),
(188, 'Sitakunda', 'Chattogram'),
(221, 'Sonagazi', 'Feni'),
(242, 'Sonaimuri', 'Noakhali'),
(59, 'Sonargaon', 'Narayanganj'),
(267, 'Sonatala', 'Bogra'),
(459, 'Sreebardi', 'Sherpur'),
(339, 'Sreemangal', 'Moulvibazar'),
(56, 'Sreenagar', 'Munshiganj'),
(21, 'Sreepur', 'Gazipur'),
(137, 'Sreepur', 'Magura'),
(243, 'Subarnachar', 'Noakhali'),
(305, 'Sujanagar', 'Pabna'),
(348, 'Sunamganj Sadar', 'Sunamganj'),
(384, 'Sundarganj', 'Gaibandha'),
(508, 'Sylhet City Corporation', 'Sylhet'),
(362, 'Sylhet Sadar', 'Sylhet'),
(349, 'Tahirpur', 'Sunamganj'),
(150, 'Tala', 'Satkhira'),
(465, 'Taltali', 'Barguna'),
(91, 'Tangail Sadar', 'Tangail'),
(314, 'Tanore', 'Rajshahi'),
(416, 'Taraganj', 'Rangpur'),
(39, 'Tarail', 'Kishoreganj'),
(443, 'Tarakanda', 'Mymensingh'),
(322, 'Tarash', 'Sirajganj'),
(481, 'Tazumuddin', 'Bhola'),
(213, 'Teknaf', 'Cox\'s Bazar'),
(126, 'Terokhada', 'Khulna'),
(409, 'Tetulia', 'Panchagarh'),
(422, 'Thakurgaon Sadar', 'Thakurgaon'),
(157, 'Thanchi', 'Bandarban'),
(205, 'Titas', 'Cumilla'),
(57, 'Tongibari', 'Munshiganj'),
(444, 'Trishal', 'Mymensingh'),
(26, 'Tungipara', 'Gopalganj'),
(214, 'Ukhiya', 'Cox\'s Bazar'),
(393, 'Ulipur', 'Kurigram'),
(323, 'Ullapara', 'Sirajganj'),
(475, 'Wazirpur', 'Barishal'),
(79, 'Zajira', 'Shariatpur'),
(363, 'Zakiganj', 'Sylhet'),
(501, 'Zianagar', 'Pirojpur');

-- --------------------------------------------------------

--
-- Table structure for table `blood_bank`
--

CREATE TABLE `blood_bank` (
  `bank_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_bank`
--

INSERT INTO `blood_bank` (`bank_id`, `name`, `phone`, `contact_number`, `area_id`) VALUES
(2, 'Quantum Blood Bank', NULL, '029351969', 503),
(3, 'Bangladesh Red Crescent Blood Bank', NULL, '028121497', 503),
(4, 'Badhan Blood Bank', NULL, '0258612545', 502),
(5, 'Shandhani Dhaka Medical College Blood Bank', NULL, '029668690', 503),
(6, 'Sir Salimullah College Blood Bank', NULL, '027319123', 503),
(7, 'Islami Bank Hospital Blood Bank', NULL, '028317090', 503),
(8, 'Bangladesh Blood Bank and Transfusion Center', NULL, '01850077185', 502),
(9, 'Alif Blood Bank and Transfusion Center', NULL, '01712392923', 503),
(10, 'Maliha Blood Bank', NULL, '01736989326', 502),
(11, 'Oriental Blood Bank', NULL, '01812700053', 503),
(12, 'Police Hospital Blood Bank', NULL, '029362573', 503),
(13, 'Blood Link Faridpur', NULL, '01700000000', 12),
(14, 'Faridpur Blood Bank', NULL, '01700000001', 12);

--
-- Triggers `blood_bank`
--
DELIMITER $$
CREATE TRIGGER `trg_initialize_bank_inventory` AFTER INSERT ON `blood_bank` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `blood_inventory`
--

CREATE TABLE `blood_inventory` (
  `inventory_id` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `units_available` int(11) NOT NULL DEFAULT 0,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_inventory`
--

INSERT INTO `blood_inventory` (`inventory_id`, `bank_id`, `blood_group`, `units_available`, `last_updated`) VALUES
(9, 2, 'A+', 0, '2026-08-14 19:36:23'),
(10, 2, 'A-', 0, '2026-08-14 19:36:23'),
(11, 2, 'B+', 0, '2026-08-14 19:36:23'),
(12, 2, 'B-', 0, '2026-08-14 19:36:23'),
(13, 2, 'AB+', 0, '2026-08-14 19:36:23'),
(14, 2, 'AB-', 0, '2026-08-14 19:36:23'),
(15, 2, 'O+', 0, '2026-08-14 19:36:23'),
(16, 2, 'O-', 0, '2026-08-14 19:36:23'),
(17, 3, 'A+', 0, '2026-08-14 19:36:23'),
(18, 3, 'A-', 0, '2026-08-14 19:36:23'),
(19, 3, 'B+', 0, '2026-08-14 19:36:23'),
(20, 3, 'B-', 0, '2026-08-14 19:36:23'),
(21, 3, 'AB+', 0, '2026-08-14 19:36:23'),
(22, 3, 'AB-', 0, '2026-08-14 19:36:23'),
(23, 3, 'O+', 0, '2026-08-14 19:36:23'),
(24, 3, 'O-', 0, '2026-08-14 19:36:23'),
(25, 4, 'A+', 0, '2026-08-14 19:36:23'),
(26, 4, 'A-', 0, '2026-08-14 19:36:23'),
(27, 4, 'B+', 0, '2026-08-14 19:36:23'),
(28, 4, 'B-', 0, '2026-08-14 19:36:23'),
(29, 4, 'AB+', 0, '2026-08-14 19:36:23'),
(30, 4, 'AB-', 0, '2026-08-14 19:36:23'),
(31, 4, 'O+', 0, '2026-08-14 19:36:23'),
(32, 4, 'O-', 0, '2026-08-14 19:36:23'),
(33, 5, 'A+', 0, '2026-08-14 19:36:23'),
(34, 5, 'A-', 0, '2026-08-14 19:36:23'),
(35, 5, 'B+', 0, '2026-08-14 19:36:23'),
(36, 5, 'B-', 0, '2026-08-14 19:36:23'),
(37, 5, 'AB+', 0, '2026-08-14 19:36:23'),
(38, 5, 'AB-', 0, '2026-08-14 19:36:23'),
(39, 5, 'O+', 0, '2026-08-14 19:36:23'),
(40, 5, 'O-', 0, '2026-08-14 19:36:23'),
(41, 6, 'A+', 0, '2026-08-14 19:36:23'),
(42, 6, 'A-', 0, '2026-08-14 19:36:23'),
(43, 6, 'B+', 0, '2026-08-14 19:36:23'),
(44, 6, 'B-', 0, '2026-08-14 19:36:23'),
(45, 6, 'AB+', 0, '2026-08-14 19:36:23'),
(46, 6, 'AB-', 0, '2026-08-14 19:36:23'),
(47, 6, 'O+', 0, '2026-08-14 19:36:23'),
(48, 6, 'O-', 0, '2026-08-14 19:36:23'),
(49, 7, 'A+', 0, '2026-08-14 19:36:23'),
(50, 7, 'A-', 0, '2026-08-14 19:36:23'),
(51, 7, 'B+', 0, '2026-08-14 19:36:23'),
(52, 7, 'B-', 0, '2026-08-14 19:36:23'),
(53, 7, 'AB+', 0, '2026-08-14 19:36:23'),
(54, 7, 'AB-', 0, '2026-08-14 19:36:23'),
(55, 7, 'O+', 0, '2026-08-14 19:36:23'),
(56, 7, 'O-', 0, '2026-08-14 19:36:23'),
(57, 8, 'A+', 0, '2026-08-14 19:36:23'),
(58, 8, 'A-', 0, '2026-08-14 19:36:23'),
(59, 8, 'B+', 0, '2026-08-14 19:36:23'),
(60, 8, 'B-', 0, '2026-08-14 19:36:23'),
(61, 8, 'AB+', 0, '2026-08-14 19:36:23'),
(62, 8, 'AB-', 0, '2026-08-14 19:36:23'),
(63, 8, 'O+', 0, '2026-08-14 19:36:23'),
(64, 8, 'O-', 0, '2026-08-14 19:36:23'),
(65, 9, 'A+', 0, '2026-08-14 19:36:23'),
(66, 9, 'A-', 0, '2026-08-14 19:36:23'),
(67, 9, 'B+', 0, '2026-08-14 19:36:23'),
(68, 9, 'B-', 0, '2026-08-14 19:36:23'),
(69, 9, 'AB+', 0, '2026-08-14 19:36:23'),
(70, 9, 'AB-', 0, '2026-08-14 19:36:23'),
(71, 9, 'O+', 0, '2026-08-14 19:36:23'),
(72, 9, 'O-', 0, '2026-08-14 19:36:23'),
(73, 10, 'A+', 0, '2026-08-14 19:36:23'),
(74, 10, 'A-', 0, '2026-08-14 19:36:23'),
(75, 10, 'B+', 0, '2026-08-14 19:36:23'),
(76, 10, 'B-', 0, '2026-08-14 19:36:23'),
(77, 10, 'AB+', 0, '2026-08-14 19:36:23'),
(78, 10, 'AB-', 0, '2026-08-14 19:36:23'),
(79, 10, 'O+', 0, '2026-08-14 19:36:23'),
(80, 10, 'O-', 0, '2026-08-14 19:36:23'),
(81, 11, 'A+', 0, '2026-08-14 19:36:23'),
(82, 11, 'A-', 0, '2026-08-14 19:36:23'),
(83, 11, 'B+', 0, '2026-08-14 19:36:23'),
(84, 11, 'B-', 0, '2026-08-14 19:36:23'),
(85, 11, 'AB+', 0, '2026-08-14 19:36:23'),
(86, 11, 'AB-', 0, '2026-08-14 19:36:23'),
(87, 11, 'O+', 0, '2026-08-14 19:36:23'),
(88, 11, 'O-', 0, '2026-08-14 19:36:23'),
(89, 12, 'A+', 0, '2026-08-14 19:36:23'),
(90, 12, 'A-', 0, '2026-08-14 19:36:23'),
(91, 12, 'B+', 0, '2026-08-14 19:36:23'),
(92, 12, 'B-', 0, '2026-08-14 19:36:23'),
(93, 12, 'AB+', 0, '2026-08-14 19:36:23'),
(94, 12, 'AB-', 0, '2026-08-14 19:36:23'),
(95, 12, 'O+', 0, '2026-08-14 19:36:23'),
(96, 12, 'O-', 0, '2026-08-14 19:36:23'),
(97, 13, 'A+', 0, '2026-08-14 19:36:23'),
(98, 13, 'A-', 0, '2026-08-14 19:36:23'),
(99, 13, 'B+', 0, '2026-08-14 19:36:23'),
(100, 13, 'B-', 0, '2026-08-14 19:36:23'),
(101, 13, 'AB+', 0, '2026-08-14 19:36:23'),
(102, 13, 'AB-', 0, '2026-08-14 19:36:23'),
(103, 13, 'O+', 0, '2026-08-14 19:36:23'),
(104, 13, 'O-', 0, '2026-08-14 19:36:23'),
(105, 14, 'A+', 0, '2026-08-14 19:36:23'),
(106, 14, 'A-', 0, '2026-08-14 19:36:23'),
(107, 14, 'B+', 0, '2026-08-14 19:36:23'),
(108, 14, 'B-', 0, '2026-08-14 19:36:23'),
(109, 14, 'AB+', 0, '2026-08-14 19:36:23'),
(110, 14, 'AB-', 0, '2026-08-14 19:36:23'),
(111, 14, 'O+', 0, '2026-08-14 19:36:23'),
(112, 14, 'O-', 0, '2026-08-14 19:36:23');

-- --------------------------------------------------------

--
-- Table structure for table `donation`
--

CREATE TABLE `donation` (
  `donation_id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `donation_date` date NOT NULL,
  `units_donated` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `donation`
--
DELIMITER $$
CREATE TRIGGER `trg_update_donor_after_donation` AFTER INSERT ON `donation` FOR EACH ROW BEGIN
    UPDATE Donor
    SET last_donation_date = NEW.donation_date
    WHERE donor_id = NEW.donor_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_update_inventory_after_donation` AFTER INSERT ON `donation` FOR EACH ROW BEGIN
    UPDATE Blood_Inventory bi
    JOIN Donor d ON d.donor_id = NEW.donor_id
    SET bi.units_available = bi.units_available + NEW.units_donated
    WHERE bi.bank_id = NEW.bank_id
      AND bi.blood_group = d.blood_group;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `donor`
--

CREATE TABLE `donor` (
  `donor_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `phone` varchar(20) NOT NULL,
  `last_donation_date` date DEFAULT NULL,
  `availability_status` enum('Available','Unavailable') DEFAULT 'Available',
  `area_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donor_match`
--

CREATE TABLE `donor_match` (
  `match_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `match_date` datetime DEFAULT current_timestamp(),
  `match_status` enum('Suggested','Contacted','Confirmed','Declined') DEFAULT 'Suggested'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `donor_match`
--
DELIMITER $$
CREATE TRIGGER `trg_confirm_match_request` AFTER UPDATE ON `donor_match` FOR EACH ROW BEGIN
    IF NEW.match_status = 'Confirmed'
       AND OLD.match_status <> 'Confirmed' THEN

        UPDATE Emergency_Request
        SET status = 'Matched'
        WHERE request_id = NEW.request_id
          AND status = 'Pending';

    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `emergency_request`
--

CREATE TABLE `emergency_request` (
  `request_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `units_needed` int(11) NOT NULL,
  `urgency_level` enum('Low','Medium','Critical') DEFAULT 'Medium',
  `request_date` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Matched','Cancelled') DEFAULT 'Pending',
  `matched_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `emergency_request`
--
DELIMITER $$
CREATE TRIGGER `trg_auto_match_donor` AFTER INSERT ON `emergency_request` FOR EACH ROW BEGIN
    INSERT INTO Donor_Match (request_id, donor_id)
    SELECT NEW.request_id, d.donor_id
    FROM Donor d
    WHERE d.blood_group = NEW.blood_group
      AND d.availability_status = 'Available';
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `hospital`
--

CREATE TABLE `hospital` (
  `hospital_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hospital`
--

INSERT INTO `hospital` (`hospital_id`, `name`, `phone`, `area_id`) VALUES
(2, 'Dhamrai Upazila Health Complex', '01810158701', 3),
(3, 'Faridpur General Hospital', '63162580', 12),
(4, 'Gazipur District Hospital', '9261281', 17),
(5, 'Gopalganj 250 Bed General Hospital', '026685424', 22),
(6, 'Kishoreganj 250 Bed District Sadar Hospital', '94161776', 34),
(7, 'Madaripur District Hospital', '66161614', 41),
(8, 'Manikganj 250 Bed District Hospital', '027710778', 48),
(9, 'Munshiganj 250 Bed District Hospital', '027610477', 54),
(10, 'Narayanganj 300 Bed Hospital', '7643622', 60),
(11, 'Narsingdi District Hospital', '29462092', 65),
(12, 'Rajbari District Hospital', '64165673', 73),
(13, 'Shariatpur District Hospital', '60161652', 78),
(14, 'Tangail 250 Bed District Hospital', '092163027', 91),
(15, 'Bagerhat District Hospital', '046862214', 100),
(16, 'Chuadanga District Hospital', '76162445', 102),
(17, 'Jessore 250 Bed General Hospital', '042161090', 110),
(18, 'Jhenaidah District Hospital', '45162202', 114),
(19, 'Khulna 250 Bed General Hospital', '041721128', 505),
(20, 'Kushtia 250 Bed General Hospital', '07171152', 132),
(21, 'Magura District Hospital', '48862443', 134),
(22, 'Meherpur 250 Bed District Hospital', '079162215', 140),
(23, 'Narail District Hospital', '048163345', 143),
(24, 'Satkhira District Hospital', '047163502', 148),
(25, 'Bandarban District Hospital', '036162544', 152),
(26, 'Brahmanbaria 250 Bed District Sadar Hospital', '85159282', 161),
(27, 'Chandpur 250 Bed General Hospital', '84163293', 167),
(28, 'Chittagong 250 Bed General Hospital', '031616786', 504),
(29, 'Cox\'s Bazar 250 Bed District Sadar Hospital', '034163884', 208),
(30, 'Comilla General Hospital', '08176762', 195),
(31, 'Feni 250 Bed District Sadar Hospital', '33174045', 218),
(32, 'Khagrachari District Hospital', '037161523', 224),
(33, 'Lakshmipur District Hospital', '38155585', 232),
(34, 'Noakhali 250 Bed General Hospital', '032161333', 244),
(35, 'Rangamati General Hospital', '35162119', 253),
(36, 'Bogra 250 Bed Mohammad Ali District Hospital', '05163633', 257),
(37, 'Chapai Nababganj District Hospital', '78152489', 296),
(38, 'Joypurhat District Hospital', '057162220', 270),
(39, 'Naogaon District Hospital', '074162020', 278),
(40, 'Natore District Hospital', '077166912', 289),
(41, 'Pabna 250 Bed General Hospital', '73166110', 303),
(42, '250 Bed Bongamata Sheikh Fazilatunnesa Mujib General Hospital', '075162930', 321),
(43, 'Dinajpur 250 Bed General Hospital', '053164023', 377),
(44, 'Gaibandha District Hospital', '054161516', 379),
(45, 'Kurigram 250 Bed District Hospital', '058161466', 389),
(46, 'Lalmonirhat District Hospital', '059161429', 397),
(47, 'Nilphamari District Hospital', '55161222', 402),
(48, 'Saidpur 100 Bed Hospital', '0552672333', 403),
(49, 'Panchagarh District Hospital', '056861656', 408),
(50, 'Thakurgaon District Hospital', '56152021', 422),
(51, 'Jamalpur 250 Beded General Hospital', '98163560', 428),
(52, 'Netrokona District Hospital', '95161344', 453),
(53, 'Sherpur District Hospital', '093161296', 458),
(54, 'Habiganj District Hospital', '083162004', 328),
(55, 'Moulvibazar 250 Bed District Sadar Hospital', '086153038', 337),
(56, 'Sunamganj 250 Bed District Sadar Hospital', '087161704', 348),
(57, 'Sylhet Shahid Shamsuddin Ahmed District Hospital', '821713506', 362);

-- --------------------------------------------------------

--
-- Table structure for table `recipient`
--

CREATE TABLE `recipient` (
  `recipient_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `phone` varchar(20) NOT NULL,
  `hospital_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_active_requests`
-- (See below for the actual view)
--
CREATE TABLE `vw_active_requests` (
`request_id` int(11)
,`recipient_name` varchar(100)
,`recipient_phone` varchar(20)
,`blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-')
,`units_needed` int(11)
,`urgency_level` enum('Low','Medium','Critical')
,`request_date` datetime
,`status` enum('Pending','Matched','Cancelled')
,`hospital_name` varchar(150)
,`hospital_phone` varchar(20)
,`area_name` varchar(100)
,`district` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_available_donors`
-- (See below for the actual view)
--
CREATE TABLE `vw_available_donors` (
`donor_id` int(11)
,`name` varchar(100)
,`blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-')
,`phone` varchar(20)
,`availability_status` enum('Available','Unavailable')
,`area_name` varchar(100)
,`district` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_blood_bank_inventory`
-- (See below for the actual view)
--
CREATE TABLE `vw_blood_bank_inventory` (
`bank_id` int(11)
,`bank_name` varchar(150)
,`contact_number` varchar(20)
,`area_name` varchar(100)
,`district` varchar(100)
,`blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-')
,`units_available` int(11)
,`last_updated` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_donor_leaderboard`
-- (See below for the actual view)
--
CREATE TABLE `vw_donor_leaderboard` (
`donor_id` int(11)
,`name` varchar(100)
,`blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-')
,`phone` varchar(20)
,`area_name` varchar(100)
,`total_donations` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_recent_donations`
-- (See below for the actual view)
--
CREATE TABLE `vw_recent_donations` (
`donation_id` int(11)
,`donation_date` date
,`units_donated` int(11)
,`donor_id` int(11)
,`donor_name` varchar(100)
,`blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-')
,`bank_id` int(11)
,`bank_name` varchar(150)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_active_requests`
--
DROP TABLE IF EXISTS `vw_active_requests`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_active_requests`  AS SELECT `er`.`request_id` AS `request_id`, `r`.`name` AS `recipient_name`, `r`.`phone` AS `recipient_phone`, `er`.`blood_group` AS `blood_group`, `er`.`units_needed` AS `units_needed`, `er`.`urgency_level` AS `urgency_level`, `er`.`request_date` AS `request_date`, `er`.`status` AS `status`, `h`.`name` AS `hospital_name`, `h`.`phone` AS `hospital_phone`, `a`.`area_name` AS `area_name`, `a`.`district` AS `district` FROM (((`emergency_request` `er` join `recipient` `r` on(`r`.`recipient_id` = `er`.`recipient_id`)) join `hospital` `h` on(`h`.`hospital_id` = `er`.`hospital_id`)) left join `area` `a` on(`a`.`area_id` = `h`.`area_id`)) WHERE `er`.`status` in ('Pending','Matched') ;

-- --------------------------------------------------------

--
-- Structure for view `vw_available_donors`
--
DROP TABLE IF EXISTS `vw_available_donors`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_available_donors`  AS SELECT `d`.`donor_id` AS `donor_id`, `d`.`name` AS `name`, `d`.`blood_group` AS `blood_group`, `d`.`phone` AS `phone`, `d`.`availability_status` AS `availability_status`, `a`.`area_name` AS `area_name`, `a`.`district` AS `district` FROM (`donor` `d` left join `area` `a` on(`a`.`area_id` = `d`.`area_id`)) WHERE `d`.`availability_status` = 'Available' ;

-- --------------------------------------------------------

--
-- Structure for view `vw_blood_bank_inventory`
--
DROP TABLE IF EXISTS `vw_blood_bank_inventory`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_blood_bank_inventory`  AS SELECT `bb`.`bank_id` AS `bank_id`, `bb`.`name` AS `bank_name`, `bb`.`contact_number` AS `contact_number`, `a`.`area_name` AS `area_name`, `a`.`district` AS `district`, `bi`.`blood_group` AS `blood_group`, `bi`.`units_available` AS `units_available`, `bi`.`last_updated` AS `last_updated` FROM ((`blood_bank` `bb` left join `area` `a` on(`a`.`area_id` = `bb`.`area_id`)) join `blood_inventory` `bi` on(`bi`.`bank_id` = `bb`.`bank_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_donor_leaderboard`
--
DROP TABLE IF EXISTS `vw_donor_leaderboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_donor_leaderboard`  AS SELECT `d`.`donor_id` AS `donor_id`, `d`.`name` AS `name`, `d`.`blood_group` AS `blood_group`, `d`.`phone` AS `phone`, `a`.`area_name` AS `area_name`, count(`do`.`donation_id`) AS `total_donations` FROM ((`donor` `d` left join `donation` `do` on(`do`.`donor_id` = `d`.`donor_id`)) left join `area` `a` on(`a`.`area_id` = `d`.`area_id`)) GROUP BY `d`.`donor_id`, `d`.`name`, `d`.`blood_group`, `d`.`phone`, `a`.`area_name` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_recent_donations`
--
DROP TABLE IF EXISTS `vw_recent_donations`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_recent_donations`  AS SELECT `do`.`donation_id` AS `donation_id`, `do`.`donation_date` AS `donation_date`, `do`.`units_donated` AS `units_donated`, `d`.`donor_id` AS `donor_id`, `d`.`name` AS `donor_name`, `d`.`blood_group` AS `blood_group`, `bb`.`bank_id` AS `bank_id`, `bb`.`name` AS `bank_name` FROM ((`donation` `do` join `donor` `d` on(`d`.`donor_id` = `do`.`donor_id`)) join `blood_bank` `bb` on(`bb`.`bank_id` = `do`.`bank_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `area`
--
ALTER TABLE `area`
  ADD PRIMARY KEY (`area_id`),
  ADD UNIQUE KEY `area_name` (`area_name`,`district`);

--
-- Indexes for table `blood_bank`
--
ALTER TABLE `blood_bank`
  ADD PRIMARY KEY (`bank_id`),
  ADD KEY `area_id` (`area_id`);

--
-- Indexes for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD UNIQUE KEY `bank_id` (`bank_id`,`blood_group`);

--
-- Indexes for table `donation`
--
ALTER TABLE `donation`
  ADD PRIMARY KEY (`donation_id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `bank_id` (`bank_id`);

--
-- Indexes for table `donor`
--
ALTER TABLE `donor`
  ADD PRIMARY KEY (`donor_id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `area_id` (`area_id`);

--
-- Indexes for table `donor_match`
--
ALTER TABLE `donor_match`
  ADD PRIMARY KEY (`match_id`),
  ADD UNIQUE KEY `request_id` (`request_id`,`donor_id`),
  ADD UNIQUE KEY `uq_request_donor` (`request_id`,`donor_id`),
  ADD KEY `donor_id` (`donor_id`);

--
-- Indexes for table `emergency_request`
--
ALTER TABLE `emergency_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `hospital_id` (`hospital_id`);

--
-- Indexes for table `hospital`
--
ALTER TABLE `hospital`
  ADD PRIMARY KEY (`hospital_id`),
  ADD UNIQUE KEY `unique_hospital_area` (`name`,`area_id`),
  ADD KEY `area_id` (`area_id`);

--
-- Indexes for table `recipient`
--
ALTER TABLE `recipient`
  ADD PRIMARY KEY (`recipient_id`),
  ADD KEY `hospital_id` (`hospital_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `area`
--
ALTER TABLE `area`
  MODIFY `area_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=517;

--
-- AUTO_INCREMENT for table `blood_bank`
--
ALTER TABLE `blood_bank`
  MODIFY `bank_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `donation`
--
ALTER TABLE `donation`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `donor`
--
ALTER TABLE `donor`
  MODIFY `donor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1001;

--
-- AUTO_INCREMENT for table `donor_match`
--
ALTER TABLE `donor_match`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `emergency_request`
--
ALTER TABLE `emergency_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hospital`
--
ALTER TABLE `hospital`
  MODIFY `hospital_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `recipient`
--
ALTER TABLE `recipient`
  MODIFY `recipient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blood_bank`
--
ALTER TABLE `blood_bank`
  ADD CONSTRAINT `blood_bank_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `area` (`area_id`) ON UPDATE CASCADE;

--
-- Constraints for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  ADD CONSTRAINT `blood_inventory_ibfk_1` FOREIGN KEY (`bank_id`) REFERENCES `blood_bank` (`bank_id`) ON UPDATE CASCADE;

--
-- Constraints for table `donation`
--
ALTER TABLE `donation`
  ADD CONSTRAINT `donation_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `donor` (`donor_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `donation_ibfk_2` FOREIGN KEY (`bank_id`) REFERENCES `blood_bank` (`bank_id`) ON UPDATE CASCADE;

--
-- Constraints for table `donor`
--
ALTER TABLE `donor`
  ADD CONSTRAINT `donor_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `area` (`area_id`) ON UPDATE CASCADE;

--
-- Constraints for table `donor_match`
--
ALTER TABLE `donor_match`
  ADD CONSTRAINT `donor_match_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `emergency_request` (`request_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `donor_match_ibfk_2` FOREIGN KEY (`donor_id`) REFERENCES `donor` (`donor_id`) ON UPDATE CASCADE;

--
-- Constraints for table `emergency_request`
--
ALTER TABLE `emergency_request`
  ADD CONSTRAINT `emergency_request_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `recipient` (`recipient_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `emergency_request_ibfk_2` FOREIGN KEY (`hospital_id`) REFERENCES `hospital` (`hospital_id`) ON UPDATE CASCADE;

--
-- Constraints for table `hospital`
--
ALTER TABLE `hospital`
  ADD CONSTRAINT `hospital_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `area` (`area_id`) ON UPDATE CASCADE;

--
-- Constraints for table `recipient`
--
ALTER TABLE `recipient`
  ADD CONSTRAINT `recipient_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospital` (`hospital_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
