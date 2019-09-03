SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `address_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) unsigned NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `postcode` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `place` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` int(10) unsigned NOT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  `address_category_id` int(10) unsigned DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`address_id`),
  KEY `contact_id` (`contact_id`),
  KEY `country_id` (`country_id`),
  KEY `address_category_id` (`address_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `contactdetails`;
CREATE TABLE `contactdetails` (
  `contactdetail_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) unsigned NOT NULL,
  `identification` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_category_id` int(10) unsigned NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`contactdetail_id`),
  KEY `provider_category_id` (`provider_category_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `contact_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` varchar(63) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `contact_category_id` int(10) unsigned NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`contact_id`),
  UNIQUE KEY `identifier` (`identifier`),
  KEY `contact_category_id` (`contact_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `contacts_contacts`;
CREATE TABLE `contacts_contacts` (
  `cc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) unsigned NOT NULL,
  `main_contact_id` int(10) unsigned NOT NULL,
  `relation_category_id` int(10) unsigned NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sequence` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `published` enum('yes','no') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'no',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cc_id`),
  UNIQUE KEY `contact_id` (`contact_id`,`main_contact_id`),
  KEY `main_contact_id` (`main_contact_id`),
  KEY `relation_category_id` (`relation_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `contacts_verifications`;
CREATE TABLE `contacts_verifications` (
  `cv_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) unsigned NOT NULL,
  `entry_date` datetime NOT NULL,
  `entry_ip` varbinary(16) NOT NULL,
  `verification_date` datetime DEFAULT NULL,
  `verification_ip` varbinary(16) DEFAULT NULL,
  `verification_hash` varchar(8) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL,
  `confirmed_mail` varchar(127) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mails_sent` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` enum('unverified','confirmed per link','confirmed manually','complete','unsubscribed','deleted') CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `language_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cv_id`),
  UNIQUE KEY `contact_id` (`contact_id`),
  UNIQUE KEY `verification_hash` (`verification_hash`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
