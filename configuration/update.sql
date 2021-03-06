/**
 * contacts module
 * SQL updates
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2018-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */

/* 2018-03-20-1 */	ALTER TABLE `contacts_verifications` ADD `status` enum('unverified','confirmed per link','confirmed manually','unsubscribed','deleted') COLLATE 'latin1_general_cs' NOT NULL, ADD `language_id` int unsigned NOT NULL AFTER `status`;
/* 2018-03-20-2 */	ALTER TABLE `contacts_verifications` ADD INDEX `language_id` (`language_id`);
/* 2018-05-17-1 */	ALTER TABLE `contacts_verifications` CHANGE `status` `status` enum('unverified','confirmed per link','confirmed manually','complete','unsubscribed','deleted') COLLATE 'latin1_general_cs' NOT NULL AFTER `verification_hash`;
/* 2018-06-29-1 */	ALTER TABLE `contacts_verifications` ADD `completion_date` datetime NULL;
/* 2018-10-16-1 */	ALTER TABLE `contacts_verifications` ADD `mails_sent` tinyint unsigned NOT NULL DEFAULT '0' AFTER `verification_hash`;
/* 2019-04-05-1 */	ALTER TABLE `contacts_verifications` ADD `confirmed_mail` varchar(127) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `verification_hash`;
/* 2019-10-20-1 */	ALTER TABLE `contacts` ADD `contact_short` varchar(32) NULL AFTER `contact`;
/* 2019-10-20-2 */	ALTER TABLE `contacts` ADD `contact_abbr` varchar(8) NULL AFTER `contact_short`;
/* 2019-10-20-3 */	ALTER TABLE `contacts` ADD `description` text NULL AFTER `identifier`;
/* 2019-10-20-4 */	ALTER TABLE `contacts` ADD `remarks` text NULL AFTER `description`;
/* 2019-10-20-5 */	ALTER TABLE `contacts` ADD `parameters` varchar(255) NULL AFTER `contact_category_id`;
/* 2019-11-30-1 */	ALTER TABLE `contacts` CHANGE `identifier` `identifier` varchar(80) COLLATE 'latin1_general_ci' NULL;
/* 2019-11-30-2 */	ALTER TABLE `contacts` ADD `published` enum('yes','no') COLLATE 'latin1_general_ci' NOT NULL DEFAULT 'no' AFTER `contact_category_id`;
/* 2020-04-27-1 */	CREATE TABLE `persons` (`person_id` int unsigned NOT NULL AUTO_INCREMENT, `contact_id` int unsigned NOT NULL, `first_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, `name_particle` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL, `last_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, `sex` enum('female','male','diverse') CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL, `title_prefix` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL, `title_suffix` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL, `birthday` date DEFAULT NULL, `nationality_country_id` int unsigned DEFAULT NULL, `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`person_id`), UNIQUE KEY `contact_id` (`contact_id`), KEY `nationality_country_id` (`nationality_country_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/* 2020-06-02-1 */	ALTER TABLE `contacts` CHANGE `identifier` `identifier` varchar(80) COLLATE 'latin1_general_ci' NOT NULL;
/* 2020-08-17-1 */	ALTER TABLE `persons` CHANGE `birthday` `date_of_birth` date NULL;
/* 2020-08-17-2 */	ALTER TABLE `persons` ADD `date_of_death` date NULL AFTER `date_of_birth`;
/* 2020-08-17-3 */	ALTER TABLE `persons` ADD `birth_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `last_name`;
/* 2020-09-11-1 */	ALTER TABLE `contacts` ADD `created` datetime NULL AFTER `parameters`;
/* 2020-09-11-2 */	ALTER TABLE `contactdetails` ADD `label` varchar(127) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `identification`;
/* 2020-09-11-3 */	UPDATE contacts LEFT JOIN _logging ON _logging.query LIKE "INSERT INTO contacts %" AND _logging.record_id = contacts.contact_id SET contacts.created = _logging.last_update WHERE !ISNULL(_logging.last_update);
/* 2020-12-20-1 */	CREATE TABLE `contacts_media` (`contact_medium_id` int unsigned NOT NULL AUTO_INCREMENT, `contact_id` int unsigned NOT NULL, `medium_id` int unsigned NOT NULL, `sequence` tinyint NOT NULL, `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`contact_medium_id`), UNIQUE KEY `medium_contact` (`medium_id`,`contact_id`), KEY `contact_id` (`contact_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/* 2020-12-20-2 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'contacts_media', 'contact_medium_id', 'contact_id', 'delete');
/* 2020-12-20-3 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'media', 'medium_id', (SELECT DATABASE()), 'contacts_media', 'contact_medium_id', 'medium_id', 'no-delete');
/* 2021-02-25-1 */	ALTER TABLE `contacts_contacts` ADD `role` varchar(255) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `relation_category_id`;
/* 2021-06-09-1 */	ALTER TABLE `contacts` CHANGE `parameters` `parameters` varchar(750) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `published`;
/* 2021-07-05-1 */	CREATE TABLE `contacts_identifiers` (`contact_identifier_id` int unsigned NOT NULL AUTO_INCREMENT, `contact_id` int unsigned NOT NULL, `identifier` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, `identifier_category_id` int unsigned NOT NULL, `current` enum('yes') CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL, `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`contact_identifier_id`), UNIQUE KEY `identifier_category_id` (`identifier_category_id`,`identifier`), UNIQUE KEY `contact_id` (`contact_id`,`identifier_category_id`,`current`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/* 2021-07-05-2 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'contacts_identifiers', 'contact_identifier_id', 'contact_id', 'delete');
/* 2021-07-05-3 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'contacts_identifiers', 'contact_identifier_id', 'identifier_category_id', 'no-delete');
/* 2021-07-05-4 */	INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Identifiers', NULL, NULL, 'identifiers', NULL, NULL, NOW());
