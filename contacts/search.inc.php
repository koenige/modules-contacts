<?php

/**
 * contacts module
 * search functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020, 2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mf_contacts_search($q) {
	$where_sql = '(contact LIKE "%%%s%%")';
	$where = [];
	foreach ($q as $string) {
		$where[] = sprintf($where_sql, $string, $string, $string);
	}
	$sql = 'SELECT contact_id, contact, description
			, (SELECT identification FROM contactdetails
				WHERE contactdetails.contact_id = contacts.contact_id
				AND provider_category_id = %d
			) AS website
		FROM contacts
		WHERE %s
		AND published = "yes"
		AND !ISNULL(description)';
	$sql = sprintf($sql
		, wrap_category_id('provider/website')
		, implode(' AND ', $where)
	);
	$data['contacts'] = wrap_db_fetch($sql, 'contact_id');
	return $data;
}
