-- Dumping structure for table ksrp.arrests
CREATE TABLE IF NOT EXISTS `arrests` (
  `arrest_id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `officer_name` varchar(100) DEFAULT NULL,
  `arrest_date` datetime DEFAULT NULL,
  `charges` text DEFAULT NULL,
  `bail_amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`arrest_id`),
  KEY `char_id` (`char_id`),
  CONSTRAINT `arrests_ibfk_1` FOREIGN KEY (`char_id`) REFERENCES `nd_characters` (`charid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping structure for table ksrp.civillians
CREATE TABLE IF NOT EXISTS `civillians` (
  `civillian_id` int(10) NOT NULL AUTO_INCREMENT,
  `discord_id` varchar(255) DEFAULT NULL,
  `char_id` int(10) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `cash` int(10) DEFAULT NULL,
  `bank` int(10) DEFAULT NULL,
  `phonenumber` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`civillian_id`),
  KEY `char_id` (`char_id`),
  KEY `discord_id` (`discord_id`),
  CONSTRAINT `civillians_ibfk_1` FOREIGN KEY (`char_id`) REFERENCES `nd_characters` (`charid`),
  CONSTRAINT `civillians_ibfk_2` FOREIGN KEY (`discord_id`) REFERENCES `users` (`discord_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping structure for table ksrp.incidents
CREATE TABLE IF NOT EXISTS `incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(255) DEFAULT 'Open',
  PRIMARY KEY (`id`),
  KEY `reported_by` (`reported_by`),
  CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Dumping structure for table ksrp.reports
CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `perpetrator` varchar(100) DEFAULT NULL,
  `report_date` datetime DEFAULT NULL,
  `report_content` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Open',
  PRIMARY KEY (`report_id`),
  KEY `char_id` (`char_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`char_id`) REFERENCES `nd_characters` (`charid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping structure for table ksrp.tickets
CREATE TABLE IF NOT EXISTS `tickets` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `issued_by` varchar(100) DEFAULT NULL,
  `issue_date` datetime DEFAULT NULL,
  `violation` text DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`ticket_id`),
  KEY `char_id` (`char_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`char_id`) REFERENCES `nd_characters` (`charid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Dumping structure for table ksrp.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discord_id` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dept` varchar(100) DEFAULT 'CIV',
  `rank` varchar(100) DEFAULT NULL,
  `badge_number` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discord_id` (`discord_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Adding columns to `nd_characters`
ALTER TABLE `nd_characters`
ADD COLUMN `mugshot` VARCHAR(255) DEFAULT NULL,
ADD COLUMN `driverslicense` VARCHAR(255) DEFAULT NULL;

