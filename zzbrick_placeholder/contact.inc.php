<?php 

/**
 * contacts module
 * placeholder function for contact
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_contacts_placeholder_contact($brick) {
	global $zz_page;
	
	$sql = 'SELECT contact_id, contact, identifier
			, SUBSTRING_INDEX(path, "/", -1) AS scope
			, CONCAT("contact_id:", contacts.contact_id) AS contact_rights
		FROM contacts
		LEFT JOIN categories
			ON contacts.contact_category_id = categories.category_id
		WHERE identifier = "%s"';
	$sql = sprintf($sql, wrap_db_escape($brick['parameter']));
	$brick['data'] = wrap_db_fetch($sql);
	if (!$brick['data']) wrap_quit(404);

	$zz_page['access'][] = $brick['data']['contact_rights'];
	wrap_access_page($zz_page['db']['parameters'] ?? '', $zz_page['access']);

	$zz_page['breadcrumb_placeholder'][] = [
		'title' => $brick['data']['contact'],
		'url_path' => $brick['data']['identifier']
	];
	return $brick;
}
