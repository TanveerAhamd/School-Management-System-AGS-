-- AGHS DATABASE BACKUP
-- Date: 31-Jan-2026 03:37 AM

DROP TABLE IF EXISTS academic_sessions;

CREATE TABLE `academic_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_name` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO academic_sessions VALUES("1","2025-26","1");
INSERT INTO academic_sessions VALUES("2","2024-25","0");
INSERT INTO academic_sessions VALUES("3","2026-27","0");
INSERT INTO academic_sessions VALUES("4","2027-28","0");



DROP TABLE IF EXISTS admins;

CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO admins VALUES("6","Tanni Ahmad","admin@admin.com","$2y$10$raukFPp0RwBSTUryLD3RLOAaXrsD0uHslrSq3ab4kt5QYc/esSVQ6","admin_1769773675_119504718.jpg","2026-01-30 16:44:21");



DROP TABLE IF EXISTS audit_logs;

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `page_url` varchar(255) DEFAULT NULL,
  `action_details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO audit_logs VALUES("74","5","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 16:40:07");
INSERT INTO audit_logs VALUES("75","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 16:44:31");
INSERT INTO audit_logs VALUES("76","6","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 16:45:01");
INSERT INTO audit_logs VALUES("77","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 16:45:08");
INSERT INTO audit_logs VALUES("78","6","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 16:46:19");
INSERT INTO audit_logs VALUES("79","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 16:46:26");
INSERT INTO audit_logs VALUES("80","6","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 16:47:58");
INSERT INTO audit_logs VALUES("81","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 16:48:28");
INSERT INTO audit_logs VALUES("82","6","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 16:48:56");
INSERT INTO audit_logs VALUES("83","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 16:49:02");
INSERT INTO audit_logs VALUES("84","6","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 17:04:50");
INSERT INTO audit_logs VALUES("85","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 17:26:26");
INSERT INTO audit_logs VALUES("86","6","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 17:27:03");
INSERT INTO audit_logs VALUES("87","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 17:27:21");
INSERT INTO audit_logs VALUES("88","6","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 17:27:58");
INSERT INTO audit_logs VALUES("89","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 17:28:06");
INSERT INTO audit_logs VALUES("90","6","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 18:44:52");
INSERT INTO audit_logs VALUES("91","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 18:45:08");
INSERT INTO audit_logs VALUES("92","6","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 18:49:17");
INSERT INTO audit_logs VALUES("93","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 18:49:25");
INSERT INTO audit_logs VALUES("94","6","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 18:50:20");
INSERT INTO audit_logs VALUES("95","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 18:51:42");
INSERT INTO audit_logs VALUES("96","6","LOGOUT","/School%20Management%20System%20(AGS)/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 19:04:49");
INSERT INTO audit_logs VALUES("97","6","LOGIN","/School%20Management%20System%20(AGS)/dashboard.php","Admin login successful.","::1","2026-01-30 19:04:55");
INSERT INTO audit_logs VALUES("98","6","LOGIN","/AGHS/dashboard.php","Admin login successful.","::1","2026-01-30 19:39:27");
INSERT INTO audit_logs VALUES("99","6","LOGOUT","/AGHS/logout.php","Admin ne successfully logout kiya.","::1","2026-01-30 20:39:23");
INSERT INTO audit_logs VALUES("100","6","LOGIN","/AGHS/dashboard.php","Admin login successful.","::1","2026-01-30 20:39:31");
INSERT INTO audit_logs VALUES("101","6","LOGIN","/AGHS/dashboard.php","Admin login successful.","::1","2026-01-31 03:29:18");
INSERT INTO audit_logs VALUES("102","6","LOGOUT","/AGHS/logout.php","Admin ne successfully logout kiya.","::1","2026-01-31 03:39:41");
INSERT INTO audit_logs VALUES("103","6","LOGIN","/AGHS/dashboard.php","Admin login successful.","::1","2026-01-31 03:40:26");
INSERT INTO audit_logs VALUES("104","6","LOGOUT","/AGHS/logout.php","Admin ne successfully logout kiya.","::1","2026-01-31 03:40:55");
INSERT INTO audit_logs VALUES("105","6","LOGIN","/AGHS/dashboard.php","Admin login successful.","::1","2026-01-31 03:41:04");



DROP TABLE IF EXISTS classes;

CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(100) NOT NULL,
  `numeric_name` varchar(50) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_class_teacher` (`teacher_id`),
  CONSTRAINT `fk_class_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO classes VALUES("17","KG -1","Kindergarten - 1","","2026-01-25 19:56:05");
INSERT INTO classes VALUES("18","KG-2","Kindergarten -2","","2026-01-25 19:56:59");
INSERT INTO classes VALUES("19","One","1","","2026-01-25 19:57:14");
INSERT INTO classes VALUES("20","Two","2","","2026-01-25 19:57:25");
INSERT INTO classes VALUES("21","Three","3","","2026-01-25 19:57:33");
INSERT INTO classes VALUES("22","4th","4","","2026-01-25 19:57:42");
INSERT INTO classes VALUES("23","5th","5","","2026-01-25 19:58:03");
INSERT INTO classes VALUES("24","6th","6","","2026-01-25 19:58:16");
INSERT INTO classes VALUES("25","7th","7","","2026-01-25 19:58:26");
INSERT INTO classes VALUES("26","8th","8","","2026-01-25 19:58:36");
INSERT INTO classes VALUES("27","9th","9","","2026-01-25 19:58:47");
INSERT INTO classes VALUES("28","10th","10","","2026-01-25 19:58:57");
INSERT INTO classes VALUES("29","11th","11th","","2026-01-27 11:03:07");



DROP TABLE IF EXISTS fee_payments;

CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(50) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fee_type_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `amount_payable` decimal(10,2) NOT NULL,
  `discount_id` int(11) DEFAULT NULL,
  `special_discount` decimal(10,2) DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `payment_status` enum('Paid','Partial') DEFAULT 'Paid',
  `payment_date` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO fee_payments VALUES("41","INV-2626000101","157","2","1","2700.00","","0.00","2700.00","","Paid","2026-01-31");
INSERT INTO fee_payments VALUES("42","INV-2626000102","157","1","1","3000.00","","0.00","3000.00","","Paid","2026-01-31");
INSERT INTO fee_payments VALUES("43","INV-2626000301","159","1","1","3000.00","","0.00","3000.00","","Paid","2026-01-31");
INSERT INTO fee_payments VALUES("44","INV-2626000501","161","0","1","500.00","","0.00","500.00","","Paid","2026-01-31");
INSERT INTO fee_payments VALUES("45","INV-2626000502","161","3","1","1500.00","","0.00","1500.00","","Paid","2026-01-31");
INSERT INTO fee_payments VALUES("46","INV-2626000103","157","3","1","1500.00","","0.00","1500.00","","Paid","2026-01-31");
INSERT INTO fee_payments VALUES("47","INV-2626000503","161","2","1","2700.00","","0.00","2700.00","","Paid","2026-01-31");
INSERT INTO fee_payments VALUES("48","INV-2626000504","161","1","1","3000.00","","399.00","2601.00","","Paid","2026-01-31");
INSERT INTO fee_payments VALUES("49","INV-2626000302","159","2","1","2700.00","","0.00","299.00","","Partial","2026-01-31");
INSERT INTO fee_payments VALUES("50","INV-2626000601","162","1","1","3000.00","","0.00","499.00","","Partial","2026-01-31");



DROP TABLE IF EXISTS fee_types;

CREATE TABLE `fee_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fee_title` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `class_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_fee_class_id` (`class_id`),
  CONSTRAINT `fk_fee_class_id` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO fee_types VALUES("1","Registration Fee (9th_25-27)","3000.00","27","2026-01-15","2026-01-25 22:35:40");
INSERT INTO fee_types VALUES("2","Examination Fee (9th-2025)","2700.00","27","2026-01-16","2026-01-25 22:36:48");
INSERT INTO fee_types VALUES("3","Paper Fund(6th-10th)","1500.00","27","2026-01-23","2026-01-25 22:37:31");
INSERT INTO fee_types VALUES("4","Paper Fund(KG-5th)","1000.00","17","2026-01-07","2026-01-25 22:38:48");
INSERT INTO fee_types VALUES("5","Paper Fund ( KG-5th )","1000.00","22","2026-01-20","2026-01-25 22:39:36");
INSERT INTO fee_types VALUES("6","Examination Fee (10th-2025)","3200.00","28","2026-01-22","2026-01-25 22:40:14");
INSERT INTO fee_types VALUES("7","Paper Fund(6th-10th)","1500.00","28","2026-01-21","2026-01-25 22:40:59");



DROP TABLE IF EXISTS school_profile;

CREATE TABLE `school_profile` (
  `id` int(11) NOT NULL DEFAULT 1,
  `school_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




DROP TABLE IF EXISTS school_settings;

CREATE TABLE `school_settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `school_name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO school_settings VALUES("1","Amina Girls High School","Chak# 21/MPR Adda Sikandary Main Shujabad Road, Lodhran.","0300-695646","logo_1769812888_AGS logo.png","2026-01-31 03:41:28");



DROP TABLE IF EXISTS sections;

CREATE TABLE `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_name` varchar(100) NOT NULL,
  `nick_name` varchar(100) DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sections_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO sections VALUES("1","Pre-9th","Pre-Ninth","27","21");
INSERT INTO sections VALUES("2","9th-A","9th-A","27","20");
INSERT INTO sections VALUES("3","9th-B","9th-B","27","20");
INSERT INTO sections VALUES("5","10th-A","10th-A","28","16");
INSERT INTO sections VALUES("6","10th-B","10th-B","28","18");
INSERT INTO sections VALUES("7","11th-A","11th-A","29","19");



DROP TABLE IF EXISTS students;

CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(50) NOT NULL,
  `admission_date` date NOT NULL,
  `session` varchar(20) DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `medium` varchar(255) NOT NULL,
  `subject_group_id` int(11) DEFAULT NULL,
  `student_name` varchar(255) NOT NULL,
  `cnic_bform` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(255) NOT NULL,
  `mother_language` varchar(50) DEFAULT NULL,
  `caste` varchar(50) DEFAULT NULL,
  `tehsil` varchar(255) NOT NULL,
  `district` varchar(255) NOT NULL,
  `student_contact` varchar(20) DEFAULT NULL,
  `student_address` text DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `relation` varchar(50) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `guardian_address` text NOT NULL,
  `guardian_contact` varchar(20) DEFAULT NULL,
  `guardian_cnic` varchar(255) NOT NULL,
  `prev_school_name` varchar(255) DEFAULT NULL,
  `last_class` varchar(50) DEFAULT NULL,
  `passing_year` varchar(10) DEFAULT NULL,
  `board_name` varchar(100) DEFAULT NULL,
  `disability` enum('Yes','No') DEFAULT 'No',
  `hafiz_quran` enum('Yes','No') DEFAULT 'No',
  `transport` enum('Yes','No') DEFAULT 'No',
  `route_id` int(11) DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `student_photo` longtext DEFAULT NULL,
  `cnic_doc` longtext DEFAULT NULL,
  `guardian_cnic_front` longtext DEFAULT NULL,
  `guardian_cnic_back` longtext DEFAULT NULL,
  `result_card_doc` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `is_promoted` tinyint(1) DEFAULT 0,
  `is_detained` tinyint(1) DEFAULT 0,
  `is_dropout` tinyint(1) DEFAULT 0,
  `is_passout` tinyint(1) DEFAULT 0,
  `is_certified` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reg_no` (`reg_no`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO students VALUES("157","26-AGS-0001","2025-04-01","4","27","3","ENGLISH","16","MAHEEN SHAHID","36203-2356429-4","2010-06-06","FEMALE","SARAIKI ","WAHOCHA","LODHRAN","LODHRAN","0300-1115824","MOZA MERAN PUR LODHRAN","SHAHID HAMEED","Father","AGRICULTURE","MOZA MERAN PUR LODHRAN","0300-1115824","","","","","","No","No","No","","Cricket,Volleyball","Approved","","","","","","2026-01-31 05:00:29","0","1","0","0","0","0");
INSERT INTO students VALUES("158","26-AGS-0002","2025-04-01","4","27","3","ENGLISH","16","MUNAZAH ","36203-5671356-6","2010-06-04","FEMALE","SARAIKI ","PANWAR","LODHRAN","LODHRAN","0329-9448841","CHAH PATHAN WALA P/O MERAN PUR ","ABDUL MAJEED","Father","AGRICULTURE","CHAH PATHAN WALA P/O MERAN PUR ","0329-9448841","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","1","0","0","0","0");
INSERT INTO students VALUES("159","26-AGS-0003","2020-11-14","4","27","3","ENGLISH","16","SAWAIRA SALEEM ","36203-1650358-2","2012-06-10","FEMALE","SARAIKI ","SOMRA","LODHRAN","LODHRAN","0346-8342027","CHAK NO. 48-M P/O MUJAHID ABAD STATION ","MUHAMMAD SALEEM AKHTAR ","Father","AGRICULTURE","CHAK NO. 48-M P/O MUJAHID ABAD STATION ","0346-8342027","","","","","","No","No","No","","Cricket,Volleyball,Chess","Approved","","","","","","2026-01-31 05:00:29","0","1","0","0","0","0");
INSERT INTO students VALUES("160","26-AGS-0004","2022-10-20","4","27","3","ENGLISH","16","LAIBA SAJJAD ","36203-9251159-8","2010-02-25","FEMALE","SARAIKI ","AHEER","LODHRAN","LODHRAN","0344-6041331","CHAH KUMMO WALA KOTLI P/O GAILY WALA ","SAJJAD AHMED ","Father","AGRICULTURE","CHAH KUMMO WALA KOTLI P/O GAILY WALA ","0344-6041331","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","1","0","0","0","0");
INSERT INTO students VALUES("161","26-AGS-0005","2023-04-18","4","27","3","ENGLISH","16","KANWAL RIAZ ","36203-4872447-4","2010-08-12","FEMALE","SARAIKI ","SIYAL","LODHRAN","LODHRAN","0344-7414693","CHAK NO. 11-MPR P/O GAILY WALA","MUHAMMAD RIAZ","Father","AGRICULTURE","CHAK NO. 11-MPR P/O GAILY WALA","0344-7414693","","","","","","Yes","No","Yes","1","","Approved","","","","","","2026-01-31 05:00:29","0","1","0","0","0","0");
INSERT INTO students VALUES("162","26-AGS-0006","2024-04-01","4","27","3","ENGLISH","16","RABIA FALAK SHAIR ","36203-6458060-8","2011-01-01","FEMALE","SARAIKI ","MUGHAL PATHAN","LODHRAN","LODHRAN","0326-6995745","CHAK FAREED ABAD P/O MIRANPUR ","FALAK SHAIR ","Father","AGRICULTURE","CHAK FAREED ABAD P/O MIRANPUR ","0326-6995745","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","1","0","0","0","0");
INSERT INTO students VALUES("163","26-AGS-0007","2015-04-04","4","27","3","ENGLISH","16","MAHEEN FATIMA ","36203-5843741-4","2011-07-03","FEMALE","HINDKU","AWAN","LODHRAN","LODHRAN","0345-8797088","CHAK NO. 23-MPR P/O CHAK NO. 49-M","AMEER HAIDER ","Father","AGRICULTURE","CHAK NO. 23-MPR P/O CHAK NO. 49-M","0345-8797088","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","1","0","0","0","0");
INSERT INTO students VALUES("164","26-AGS-0008","2019-05-24","4","27","3","ENGLISH","16","MAHEEN KHAN","36203-7184549-4","2011-01-01","FEMALE","SARAIKI ","MAGSI","LODHRAN","LODHRAN","0301-7751455","CHAK NO. 48-M P/O MUJAHID ABAD STATION ","SABTAIN RAZA KHAN","Father","AGRICULTURE","CHAK NO. 48-M P/O MUJAHID ABAD STATION ","0301-7751455","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","1","0","0","0","0");
INSERT INTO students VALUES("165","26-AGS-0009","2018-04-04","4","27","3","ENGLISH","16","ABEERA ","36302-5422820-4","2012-02-29","FEMALE","SARAIKI ","JAAM","LODHRAN","LODHRAN","0344-8120127","CHAK NO. 15-MPR P/O GAILY WALA","TARIQ MEHMOOD ","Father","AGRICULTURE","CHAK NO. 15-MPR P/O GAILY WALA","0344-8120127","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","1","0","0","0","0");
INSERT INTO students VALUES("166","26-AGS-0010","2022-08-26","4","27","3","ENGLISH","16","NOSHABA AKMAL ","36203-3725129-0","2007-11-15","FEMALE","SARAIKI ","BAJWA ","LODHRAN","LODHRAN","0305-7548303","DUBBY WALA P/O RAWANI ","MUHAMMAD AKMAL ","Father","AGRICULTURE","DUBBY WALA P/O RAWANI ","0305-7548303","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","1","0","0","0","0");
INSERT INTO students VALUES("167","26-AGS-0011","2024-04-01","1","27","2","ENGLISH","16","MARIA SOBY KHAN","36203-5114348-8","2011-02-02","FEMALE","SARAIKI ","JAAM","LODHRAN","LODHRAN","0345-7633216","BOHAR BOGGY SHAH P/O JALAL ABAD","SOBY KHAN ","Father","AGRICULTURE","BOHAR BOGGY SHAH P/O JALAL ABAD","0345-7633216","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("168","26-AGS-0012","2022-08-01","1","27","2","ENGLISH","16","MEHNAZ BIBI","36203-9057157-0","2010-11-13","FEMALE","SARAIKI ","SIYAL","LODHRAN","LODHRAN","0341-2300429","MIANPUR MATAM P/O CHAK NO. 49-M","MUHAMMAD NAWAZ ","Father","AGRICULTURE","MIANPUR MATAM P/O CHAK NO. 49-M","0341-2300429","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("169","26-AGS-0013","2024-04-01","1","27","2","ENGLISH","16","SHABANA BIBI ","36203-7442009-0","2008-05-01","FEMALE","SARAIKI ","JAAM","LODHRAN","LODHRAN","0345-7633216","BOHAR BOGGY SHAH P/O JALAL ABAD","ABDUL MAJEED ","Father","AGRICULTURE","BOHAR BOGGY SHAH P/O JALAL ABAD","0345-7633216","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("170","26-AGS-0014","2022-08-01","1","27","2","ENGLISH","16","AYESHA KHALIL ","36203-1589279-6","2011-06-24","FEMALE","SARAIKI ","JATIAL","LODHRAN","LODHRAN","0344-9079609","CHANNU SHAHBAZ P/O GAILY WALA","HAFIZ KHALIL AHMED ","Father","AGRICULTURE","CHANNU SHAHBAZ P/O GAILY WALA","0344-9079609","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("171","26-AGS-0015","2018-05-19","1","27","2","ENGLISH","16","MARYAM BIBI ","36203-1811595-0","2013-05-05","FEMALE","SARAIKI ","KUMHAR","LODHRAN","LODHRAN","0345-8792969","MIANPUR BANGLA P/O MIRAN PUR ","MUHAMMAD ASHRAF ","Father","AGRICULTURE","MIANPUR BANGLA P/O MIRAN PUR ","0345-8792969","","","","","","Yes","No","Yes","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("172","26-AGS-0016","2022-08-01","1","27","2","ENGLISH","16","ZOHA JAMEEL ","36203-3133588-0","2012-02-17","FEMALE","SARAIKI ","ARAIN","LODHRAN","LODHRAN","0344-7139826","MIANPUR MATAM P/O CHAK NO. 49-M","MUHAMMAD JAMEEL SHAHEEN ","Father","AGRICULTURE","MIANPUR MATAM P/O CHAK NO. 49-M","0344-7139826","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("173","26-AGS-0017","2022-09-28","1","27","2","ENGLISH","16","AROOJ SALEEM ","36203-9060929-0","2011-04-05","FEMALE","SARAIKI ","JAAM","LODHRAN","LODHRAN","0344-7121969","MIANPUR BANGLA P/O MIRAN PUR ","JAM MUHAMMAD SALEEM ","Father","AGRICULTURE","MIANPUR BANGLA P/O MIRAN PUR ","0344-7121969","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("174","26-AGS-0018","2022-08-01","1","27","2","ENGLISH","16","HAFSA SALEEM ","36203-6855038-0","2013-02-01","FEMALE","SARAIKI ","JAAM","LODHRAN","LODHRAN","0300-6844310","BOHAR BOGGY SHAH P/O JALAL ABAD","MUHAMMAD SALEEM ","Father","AGRICULTURE","BOHAR BOGGY SHAH P/O JALAL ABAD","0300-6844310","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("175","26-AGS-0019","2022-08-01","1","27","2","ENGLISH","16","IQRA YASEEN ","36203-8559060-4","2012-06-01","FEMALE","SARAIKI ","JAAM","LODHRAN","LODHRAN","0300-7326001","BOHAR BOGGY SHAH P/O JALAL ABAD","GHULAM YASEEN ","Father","AGRICULTURE","BOHAR BOGGY SHAH P/O JALAL ABAD","0300-7326001","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("176","26-AGS-0020","2015-04-04","1","27","2","ENGLISH","16","TAHIRA BIBI","36203-2441958-2","2009-03-01","FEMALE","SARAIKI ","MATAM","LODHRAN","LODHRAN","0342-4397226","MIANPUR MATAM P/O CHAK NO. 49-M","MUKHTIAR AHMED ","Father","AGRICULTURE","MIANPUR MATAM P/O CHAK NO. 49-M","0342-4397226","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("177","26-AGS-0021","2015-12-02","1","27","2","ENGLISH","16","SIDRA ULFAT","36203-1015846-2","2010-03-03","FEMALE","SARAIKI ","SOMRA","LODHRAN","LODHRAN","0346-8108013","MIANPUR BANGLA P/O MIRAN PUR ","ULFAT HUSSAIN ","Father","AGRICULTURE","MIANPUR BANGLA P/O MIRAN PUR ","0346-8108013","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("178","26-AGS-0022","2015-04-06","1","27","2","ENGLISH","16","RIMSHA BIBI ","36203-5798062-0","2009-01-23","FEMALE","SARAIKI ","MATAM","LODHRAN","LODHRAN","0345-7245089","ARIF WALA P/O CHAK NO. 49-M","MUNIR HUSSAIN","Father","AGRICULTURE","ARIF WALA P/O CHAK NO. 49-M","0345-7245089","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("179","26-AGS-0023","2018-05-28","1","27","2","ENGLISH","16","TANZEELA SAJID ","36203-9122708-4","2013-01-02","FEMALE","SARAIKI ","MATAM","LODHRAN","LODHRAN","0346-7076358","MIANPUR MATAM P/O CHAK NO. 49-M","MUHAMMAD SAJID ","Father","AGRICULTURE","MIANPUR MATAM P/O CHAK NO. 49-M","0346-7076358","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("180","26-AGS-0024","2024-04-01","1","27","2","ENGLISH","16","HIFSA GUL ","36203-3258534-8","2010-12-15","FEMALE","HINDKU","AWAN","LODHRAN","LODHRAN","0342-2277206","CHAK NO. 17-MPR P/O CHAK NO. 19-MPR","NISAR AHMED","Father","AGRICULTURE","CHAK NO. 17-MPR P/O CHAK NO. 19-MPR","0342-2277206","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("181","26-AGS-0025","2024-04-01","1","27","2","ENGLISH","16","EMAN GUL ","36203-1489208-0","2010-12-15","FEMALE","HINDKU","AWAN","LODHRAN","LODHRAN","0342-2277206","CHAK NO. 17-MPR P/O CHAK NO. 19-MPR","NISAR AHMED","Father","AGRICULTURE","CHAK NO. 17-MPR P/O CHAK NO. 19-MPR","0342-2277206","","","","","","Yes","No","Yes","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("182","26-AGS-0026","2023-05-08","1","27","2","ENGLISH","16","SADAF IFTIKHAR ","36203-9402741-4","2011-10-05","FEMALE","SARAIKI ","WAHOCHA","LODHRAN","LODHRAN","0300-3745504","WAHOCHA NAGAR P/O MIRAN PUR "," IFTIKHAR AHMAD","Father","AGRICULTURE","WAHOCHA NAGAR P/O MIRAN PUR ","0300-3745504","","","","","","No","No","No","","","Approved","","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("183","26-AGS-0027","2024-04-01","1","27","2","ENGLISH","16","BAKHTAWAR","36203-3158225-2","2010-11-27","FEMALE","SARAIKI ","AHEER","LODHRAN","LODHRAN","0345-8049038","CHAH KARMU WALA P/O RAWANI ","GHULAM SHABIR ","Father","AGRICULTURE","CHAH KARMU WALA P/O RAWANI ","0345-8049038","00000-0000000-0","","","","","No","No","No","","","Approved","uploads/PHOTO_26_AGS_0027_BAKHTAWAR_20260131_024438.JPEG","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");
INSERT INTO students VALUES("184","26-AGS-0028","2021-06-15","1","27","2","ENGLISH","16","MEHREEN ZAHRA ","36203-8755366-6","2013-02-11","FEMALE","SARAIKI ","MAGSI","LODHRAN","LODHRAN","0344-0704409","CHAK NO. 48-M P/O MUJAHID ABAD STATION ","KHAWAR ABBAS","Father","AGRICULTURE","CHAK NO. 48-M P/O MUJAHID ABAD STATION ","0344-0704409","00000-0000000-0","","","","","No","No","No","","","Approved","uploads/PHOTO_26_AGS_0028_MEHREEN_ZAHRA__20260131_015307.png","","","","","2026-01-31 05:00:29","0","0","0","0","0","0");



DROP TABLE IF EXISTS subject_group_items;

CREATE TABLE `subject_group_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_link_group` (`group_id`),
  KEY `fk_link_subject` (`subject_id`),
  CONSTRAINT `fk_link_group` FOREIGN KEY (`group_id`) REFERENCES `subject_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_link_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO subject_group_items VALUES("1","16","33");
INSERT INTO subject_group_items VALUES("2","16","34");
INSERT INTO subject_group_items VALUES("3","16","31");
INSERT INTO subject_group_items VALUES("4","16","32");
INSERT INTO subject_group_items VALUES("10","18","43");
INSERT INTO subject_group_items VALUES("11","18","45");
INSERT INTO subject_group_items VALUES("12","18","31");
INSERT INTO subject_group_items VALUES("13","18","44");
INSERT INTO subject_group_items VALUES("14","17","35");
INSERT INTO subject_group_items VALUES("15","17","33");
INSERT INTO subject_group_items VALUES("16","17","31");
INSERT INTO subject_group_items VALUES("17","17","32");



DROP TABLE IF EXISTS subject_groups;

CREATE TABLE `subject_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_group_class` (`class_id`),
  CONSTRAINT `fk_group_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO subject_groups VALUES("16","Computer Science","27","2026-01-25 21:59:47");
INSERT INTO subject_groups VALUES("17","Biology Science","27","2026-01-25 22:00:38");
INSERT INTO subject_groups VALUES("18","Arts Subject","27","2026-01-25 22:01:26");



DROP TABLE IF EXISTS subjects;

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_subject_class` (`class_id`),
  KEY `fk_subject_teacher` (`teacher_id`),
  CONSTRAINT `fk_subject_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_subject_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO subjects VALUES("28","English","17","17","2026-01-25 20:03:49");
INSERT INTO subjects VALUES("29","Urdu","17","16","2026-01-25 21:53:20");
INSERT INTO subjects VALUES("30","Math","17","16","2026-01-25 21:53:39");
INSERT INTO subjects VALUES("31","Math","27","16","2026-01-25 21:54:01");
INSERT INTO subjects VALUES("32","Physics","27","19","2026-01-25 21:54:18");
INSERT INTO subjects VALUES("33","Chemistry","27","19","2026-01-25 21:54:42");
INSERT INTO subjects VALUES("34","Computer","27","20","2026-01-25 21:54:55");
INSERT INTO subjects VALUES("35","Biology","27","15","2026-01-25 21:55:16");
INSERT INTO subjects VALUES("36","Islamiat","27","20","2026-01-25 21:55:34");
INSERT INTO subjects VALUES("37","ICT","28","17","2026-01-25 21:56:11");
INSERT INTO subjects VALUES("38","C++","27","18","2026-01-25 21:56:28");
INSERT INTO subjects VALUES("39","Web Dev","28","16","2026-01-25 21:56:44");
INSERT INTO subjects VALUES("40","Islamiat","28","17","2026-01-25 21:57:06");
INSERT INTO subjects VALUES("41","Virus","28","15","2026-01-25 21:57:45");
INSERT INTO subjects VALUES("42","Coding","28","21","2026-01-25 21:57:59");
INSERT INTO subjects VALUES("43","English","27","19","2026-01-25 22:01:51");
INSERT INTO subjects VALUES("44","Urdu","27","20","2026-01-25 22:02:03");
INSERT INTO subjects VALUES("45","Islamiat Ikhtyari","27","15","2026-01-25 22:02:35");



DROP TABLE IF EXISTS teachers;

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact` varchar(100) NOT NULL,
  `assigned_class` varchar(100) DEFAULT 'Not Assigned',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO teachers VALUES("15","Riaz Sb","riaz@gmail.com","03300330303","Not Assigned","2026-01-25 20:00:12","2026-01-25 20:00:12");
INSERT INTO teachers VALUES("16","Jamal Sb","jamal@gmail.com","03300330303","Not Assigned","2026-01-25 20:00:32","2026-01-25 20:00:32");
INSERT INTO teachers VALUES("17","Arje Saba","arjesaba@gmail.com","03300330303","Not Assigned","2026-01-25 20:00:50","2026-01-25 20:00:50");
INSERT INTO teachers VALUES("18","Fiaz Sb","fiazahmad@gmail.com","03300330303","Not Assigned","2026-01-25 20:01:22","2026-01-25 20:01:22");
INSERT INTO teachers VALUES("19","Mis Memona","memona@gmail.com","03300330303","Not Assigned","2026-01-25 20:02:08","2026-01-25 20:02:08");
INSERT INTO teachers VALUES("20","Maham Mis","maham@gmail.com","03300330303","Not Assigned","2026-01-25 20:02:54","2026-01-25 20:02:54");
INSERT INTO teachers VALUES("21","Mis Amina","amina@gmail.com","03300330303","Not Assigned","2026-01-25 20:03:13","2026-01-25 20:03:13");



DROP TABLE IF EXISTS transport_allocations;

CREATE TABLE `transport_allocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transport_name` varchar(255) NOT NULL,
  `route_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO transport_allocations VALUES("1","Noori Lal Darbar","1","1","500.00","","2026-01-25 23:59:07");
INSERT INTO transport_allocations VALUES("2","Jinah Colony","2","1","430.00","","2026-01-25 23:59:44");
INSERT INTO transport_allocations VALUES("3","Phatak","3","2","540.00","","2026-01-26 00:00:07");
INSERT INTO transport_allocations VALUES("4","Fatima School","4","1","700.00","","2026-01-26 00:00:40");
INSERT INTO transport_allocations VALUES("5","Makhdoomi bazar","5","1","800.00","","2026-01-26 00:01:07");
INSERT INTO transport_allocations VALUES("6","Rana ji","6","1","1000.00","","2026-01-26 00:01:33");
INSERT INTO transport_allocations VALUES("7","Ali Tareen Form","7","2","1100.00","","2026-01-26 00:01:59");
INSERT INTO transport_allocations VALUES("8","Jug Jug Jugo wla","8","2","1500.00","","2026-01-26 00:03:09");
INSERT INTO transport_allocations VALUES("9","Jholy Lal","9","1","1287.00","","2026-01-26 00:03:51");
INSERT INTO transport_allocations VALUES("10","Mangty","10","2","100.00","","2026-01-26 00:04:29");
INSERT INTO transport_allocations VALUES("11","Garma Garm Andy","11","2","200.00","","2026-01-26 00:04:55");
INSERT INTO transport_allocations VALUES("12","Ghar hy Ghar","12","1","2000.00","","2026-01-26 00:05:32");



DROP TABLE IF EXISTS transport_routes;

CREATE TABLE `transport_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route_name` varchar(255) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO transport_routes VALUES("1","Norilal_Point","1","Pikup point is near at Milk shop","1","2026-01-25 22:25:14");
INSERT INTO transport_routes VALUES("2","Jinah Colony","1","Main chowk","1","2026-01-25 22:25:52");
INSERT INTO transport_routes VALUES("3","Railway Phatak","1","Railway Cross","1","2026-01-25 22:26:42");
INSERT INTO transport_routes VALUES("4","FAF School","1","FAF School pikup point","1","2026-01-25 22:27:26");
INSERT INTO transport_routes VALUES("5","Makhdoom Wala Station","2","Makhdoom pikup point","1","2026-01-25 22:28:25");
INSERT INTO transport_routes VALUES("6","Rani Mukhar G","2","Fateh moor","1","2026-01-25 22:28:56");
INSERT INTO transport_routes VALUES("7","ATF School","2","Ali Tareen Form","1","2026-01-25 22:29:26");
INSERT INTO transport_routes VALUES("8","Jugowala","2","Jugowala main chowk","1","2026-01-25 22:30:39");
INSERT INTO transport_routes VALUES("9","Norilal Darbar","2","Darbari Qalandar","1","2026-01-25 22:31:11");
INSERT INTO transport_routes VALUES("10","Allah Wasty","2","Mola Khus Rkhy","1","2026-01-25 22:31:44");
INSERT INTO transport_routes VALUES("11","Garam Andy","2","Nai hy garam","1","2026-01-25 22:32:12");
INSERT INTO transport_routes VALUES("12","Amina School","2","Phnch gy","1","2026-01-25 22:32:37");



DROP TABLE IF EXISTS vehicles;

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_name` varchar(255) NOT NULL,
  `vehicle_no` varchar(100) NOT NULL,
  `vehicle_model` varchar(100) DEFAULT NULL,
  `driver_name` varchar(255) DEFAULT NULL,
  `driver_contact` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO vehicles VALUES("1","Coaster-1","BRE-2412","2025","Abdul Khaliq","0300-1234567","Good in Driving","1","2026-01-25 22:22:19");
INSERT INTO vehicles VALUES("2","Coaster-2","MN-1226","2026","Fatih Muhammad","0300-1234567","Good in Driving","1","2026-01-25 22:23:54");



