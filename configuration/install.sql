/**
 * contacts module
 * SQL for installation
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2018-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */

-- addresses --
CREATE TABLE `addresses` (
  `address_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `postcode` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `place` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` int unsigned NOT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  `address_category_id` int unsigned NOT NULL,
  `receive_mail` enum('yes','no') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`address_id`),
  KEY `contact_id` (`contact_id`),
  KEY `receive_mail` (`receive_mail`),
  KEY `country_id` (`country_id`),
  KEY `address_category_id` (`address_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'addresses', 'address_id', 'contact_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'countries', 'country_id', (SELECT DATABASE()), 'addresses', 'address_id', 'country_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'addresses', 'address_id', 'address_category_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Addresses', NULL, NULL, 'addresses', 'alias=address', NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Home', NULL, (SELECT category_id FROM categories c WHERE path = 'addresses'), 'addresses/home', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Work', NULL, (SELECT category_id FROM categories c WHERE path = 'addresses'), 'addresses/work', NULL, NULL, NOW());


-- awards --
CREATE TABLE `awards` (
  `award_id` int unsigned NOT NULL AUTO_INCREMENT,
  `award_category_id` int unsigned NOT NULL,
  `contact_id` int unsigned NOT NULL,
  `contact_display_name` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `award_date` date DEFAULT NULL,
  `award_year` year NOT NULL,
  `award_year_to` year DEFAULT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `laudation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `published` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`award_id`),
  KEY `contact_id` (`contact_id`),
  KEY `award_category_id` (`award_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'awards', 'award_id', 'contact_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'awards', 'award_id', 'award_category_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Awards', NULL, NULL, 'awards', 'alias=awards', NULL, NOW());


-- contactdetails --
CREATE TABLE `contactdetails` (
  `contactdetail_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `identification` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_category_id` int unsigned NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`contactdetail_id`),
  KEY `contact_id` (`contact_id`),
  KEY `provider_category_id` (`provider_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'contactdetails', 'contactdetail_id', 'contact_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'contactdetails', 'contactdetail_id', 'provider_category_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Provider', NULL, NULL, 'provider', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('E-Mail', NULL, (SELECT category_id FROM categories c WHERE path = 'provider'), 'provider/e-mail', 'type=mail', NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Website', NULL, (SELECT category_id FROM categories c WHERE path = 'provider'), 'provider/website', 'type=url', NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Phone', NULL, (SELECT category_id FROM categories c WHERE path = 'provider'), 'provider/phone', 'type=phone', NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Fax', NULL, (SELECT category_id FROM categories c WHERE path = 'provider'), 'provider/fax', 'type=phone', NULL, NOW());


-- contacts --
CREATE TABLE `contacts` (
  `contact_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_short` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_abbr` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_sort` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identifier` varchar(80) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contact_category_id` int unsigned NOT NULL,
  `country_id` int unsigned DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `published` enum('yes','no') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'no',
  `parameters` varchar(750) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`contact_id`),
  UNIQUE KEY `identifier` (`identifier`),
  KEY `contact_category_id` (`contact_category_id`),
  KEY `country_id` (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'contacts', 'contact_id', 'contact_category_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'countries', 'country_id', (SELECT DATABASE()), 'contacts', 'contact_id', 'country_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Contact', NULL, NULL, 'contact', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Organisation', NULL, (SELECT category_id FROM categories c WHERE path = 'contact'), 'contact/organisation', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Person', NULL, (SELECT category_id FROM categories c WHERE path = 'contact'), 'contact/person', NULL, NULL, NOW());


-- contacts_contacts --
CREATE TABLE `contacts_contacts` (
  `cc_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `main_contact_id` int unsigned NOT NULL,
  `relation_category_id` int unsigned NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sequence` tinyint unsigned NOT NULL DEFAULT '1',
  `published` enum('yes','no') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'no',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cc_id`),
  UNIQUE KEY `contact_id` (`contact_id`,`main_contact_id`),
  KEY `main_contact_id` (`main_contact_id`),
  KEY `relation_category_id` (`relation_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'contacts_contacts', 'cc_id', 'contact_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'contacts_contacts', 'cc_id', 'main_contact_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'contacts_contacts', 'cc_id', 'relation_category_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Relation', NULL, NULL, 'relation', NULL, NULL, NOW());


-- contacts_identifiers --
CREATE TABLE `contacts_identifiers` (
  `contact_identifier_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `identifier` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier_category_id` int unsigned NOT NULL,
  `current` enum('yes') CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`contact_identifier_id`),
  UNIQUE KEY `identifier_category_id` (`identifier_category_id`,`identifier`),
  UNIQUE KEY `contact_id` (`contact_id`,`identifier_category_id`,`current`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'contacts_identifiers', 'contact_identifier_id', 'contact_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'contacts_identifiers', 'contact_identifier_id', 'identifier_category_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Identifiers', NULL, NULL, 'identifiers', NULL, NULL, NOW());


-- contacts_media --
CREATE TABLE `contacts_media` (
  `contact_medium_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `medium_id` int unsigned NOT NULL,
  `sequence` tinyint NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`contact_medium_id`),
  UNIQUE KEY `medium_contact` (`medium_id`,`contact_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'contacts_media', 'contact_medium_id', 'contact_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'media', 'medium_id', (SELECT DATABASE()), 'contacts_media', 'contact_medium_id', 'medium_id', 'no-delete');


-- contacts_verifications --
CREATE TABLE `contacts_verifications` (
  `cv_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `entry_date` datetime NOT NULL,
  `entry_ip` varbinary(16) NOT NULL,
  `verification_date` datetime DEFAULT NULL,
  `verification_ip` varbinary(16) DEFAULT NULL,
  `verification_hash` varchar(8) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL,
  `confirmed_mail` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mails_sent` tinyint unsigned NOT NULL DEFAULT '0',
  `status` enum('unverified','confirmed per link','confirmed manually','complete','unsubscribed','deleted') CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `language_id` int unsigned NOT NULL,
  `completion_date` datetime DEFAULT NULL,
  PRIMARY KEY (`cv_id`),
  UNIQUE KEY `contact_id` (`contact_id`),
  UNIQUE KEY `verification_hash` (`verification_hash`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- persons --
CREATE TABLE `persons` (
  `person_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `first_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_particle` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex` enum('female','male','diverse') CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `title_prefix` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_suffix` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `nationality_country_id` int unsigned DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`person_id`),
  UNIQUE KEY `contact_id` (`contact_id`),
  KEY `nationality_country_id` (`nationality_country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'persons', 'person_id', 'contact_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'countries', 'country_id', (SELECT DATABASE()), 'persons', 'person_id', 'nationality_country_id', 'no-delete');
