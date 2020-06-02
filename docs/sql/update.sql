/**
 * Zugzwang Project
 * SQL updates for contacts module
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2018-2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */

/* 2018-05-17-1 */	ALTER TABLE `contacts_verifications` CHANGE `status` `status` enum('unverified','confirmed per link','confirmed manually','complete','unsubscribed','deleted') COLLATE 'latin1_general_cs' NOT NULL AFTER `verification_hash`;
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
