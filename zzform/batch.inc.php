<?php 

/**
 * contacts module
 * batch functions for zzform
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * add person to database
 *
 * @param array $data fields for database, as many as possible, e. g.
 *		'first_name', 'last_name', 'title_prefix', 'date_of_birth', 'sex'
 * @param int $error_code
 * @return int
 */
function mf_contacts_add_person($data, $error_code = E_USER_ERROR) {
	$line = [
		'contact_category_id' => wrap_category_id('contact/person')
	];
	// @todo add persons IDs
	foreach ($data as $field_name => $value) {
		if ($field_name === 'e_mail') continue;
		$line['persons'][0][$field_name] = $value;
	}
	$contact_id = zzform_insert('forms/persons', $line);
	if (!$contact_id) return 0;

	if (!empty($data['e_mail'])) {
		$line = [
			'contact_id' => $contact_id,
			'identification' => $data['e_mail'],
			'provider_category_id' => wrap_category_id('provider/e-mail')
		];
		zzform_insert('contactdetails', $line);
	}
	return $contact_id;
}
