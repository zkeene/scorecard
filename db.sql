-- --------------------------------------------------------
-- Host:                         kpnquality.local
-- Server version:               10.3.11-MariaDB-1:10.3.11+maria~bionic - mariadb.org binary distribution
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             10.1.0.5464
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for kpnquality
CREATE DATABASE IF NOT EXISTS `kpnquality` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `kpnquality`;

-- Dumping structure for table kpnquality.contracts
CREATE TABLE IF NOT EXISTS `contracts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) unsigned NOT NULL,
  `total_incentive_amount` decimal(10,2) unsigned DEFAULT NULL,
  `pay_cycle_id` int(11) unsigned NOT NULL,
  `fte` decimal(10,2) unsigned DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `effective_quality_date` date DEFAULT NULL,
  `amendment_date` date DEFAULT NULL,
  `default_expire_date` date DEFAULT NULL,
  `inactive_date` date DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `datetime_stamp` datetime NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `provider_id` (`provider_id`),
  KEY `pay_cycle_id` (`pay_cycle_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`pay_cycle_id`) REFERENCES `pay_cycles` (`id`),
  CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.locations
CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `location_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.messages
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `message` mediumtext NOT NULL,
  `message_title` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.metrics
CREATE TABLE IF NOT EXISTS `metrics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `metric` varchar(50) NOT NULL,
  `metric_def` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.pay_cycles
CREATE TABLE IF NOT EXISTS `pay_cycles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pay_cycle` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.performances
CREATE TABLE IF NOT EXISTS `performances` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) unsigned NOT NULL,
  `location_id` int(11) unsigned NOT NULL,
  `metric_id` int(11) unsigned NOT NULL,
  `numerator` int(11) unsigned NOT NULL,
  `denominator` int(11) unsigned NOT NULL,
  `quarter` int(1) unsigned NOT NULL,
  `year` year(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `provider_id` (`provider_id`),
  KEY `location_id` (`location_id`),
  KEY `metric_id` (`metric_id`),
  CONSTRAINT `performances_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`),
  CONSTRAINT `performances_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `performances_ibfk_3` FOREIGN KEY (`metric_id`) REFERENCES `metrics` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.providers
CREATE TABLE IF NOT EXISTS `providers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `SER` int(11) unsigned NOT NULL,
  `NPI` bigint(10) unsigned NOT NULL,
  `badge_num` int(11) unsigned NOT NULL,
  `service_line_id` int(11) unsigned NOT NULL,
  `provider_name` varchar(50) NOT NULL,
  `provider_status` int(11) unsigned NOT NULL,
  `provider_type_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service_line_id` (`service_line_id`),
  KEY `provider_type_id` (`provider_type_id`),
  CONSTRAINT `providers_ibfk_1` FOREIGN KEY (`service_line_id`) REFERENCES `service_lines` (`id`),
  CONSTRAINT `providers_ibfk_2` FOREIGN KEY (`provider_type_id`) REFERENCES `provider_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.provider_types
CREATE TABLE IF NOT EXISTS `provider_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `provider_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.service_lines
CREATE TABLE IF NOT EXISTS `service_lines` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `service_line` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.specific_metrics
CREATE TABLE IF NOT EXISTS `specific_metrics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `service_line_id` int(11) unsigned NOT NULL,
  `metric_id` int(11) unsigned NOT NULL,
  `threshold_direction` tinyint(1) NOT NULL,
  `is_gateway_metric` tinyint(1) NOT NULL,
  `year` year(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service_line_id` (`service_line_id`),
  KEY `metric_id` (`metric_id`),
  CONSTRAINT `specific_metrics_ibfk_1` FOREIGN KEY (`service_line_id`) REFERENCES `service_lines` (`id`),
  CONSTRAINT `specific_metrics_ibfk_2` FOREIGN KEY (`metric_id`) REFERENCES `metrics` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.specific_metric_thresholds
CREATE TABLE IF NOT EXISTS `specific_metric_thresholds` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `specific_metric_id` int(11) unsigned NOT NULL,
  `threshold` int(11) unsigned NOT NULL,
  `threshold_incentive_percent` decimal(10,2) unsigned NOT NULL,
  `message_id` int(11) unsigned NOT NULL,
  `threshold_color_id` int(11) unsigned NOT NULL,
  `is_gateway_threshold` tinyint(1) NOT NULL,
  `beta_metric` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `metrics_cross_id` (`specific_metric_id`),
  KEY `threshold_message_id` (`message_id`),
  KEY `threshold_color_id` (`threshold_color_id`),
  CONSTRAINT `specific_metric_thresholds_ibfk_1` FOREIGN KEY (`specific_metric_id`) REFERENCES `specific_metrics` (`id`),
  CONSTRAINT `specific_metric_thresholds_ibfk_2` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`),
  CONSTRAINT `specific_metric_thresholds_ibfk_3` FOREIGN KEY (`threshold_color_id`) REFERENCES `threshold_colors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COMMENT='Holds the thresholds specific to each service lines version ';

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.threshold_colors
CREATE TABLE IF NOT EXISTS `threshold_colors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `color` varchar(50) NOT NULL,
  `color_hex` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
-- Dumping structure for table kpnquality.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
