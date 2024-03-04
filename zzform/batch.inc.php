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
	$values = [];
	$values['action'] = 'insert';
	$values['POST']['contact_category_id'] = wrap_category_id('contact/person');
	$values['ids'] = ['contact_id', 'contact_category_id', 'country_id'];
	// @todo add persons IDs
	foreach ($data as $field_name => $value) {
		if ($field_name === 'e_mail') continue;
		$values['POST']['persons'][0][$field_name] = $value;
	}
	$ops = zzform_multi('persons', $values, 'forms');
	if (!$ops['id']) {
		wrap_error(wrap_text('Unable to add person (values %s), Reason: %s'
			, ['values' => [json_encode($data), json_encode($ops['error'])]]
		), $error_code);
		return 0;
	}
	if (!empty($data['e_mail'])) {
		$data['contact_id'] = $ops['id'];
		$data['provider_category'] = 'e-mail';
		$data['identification'] = $data['e_mail'];
		mf_contacts_add_details($data);
	}
	return $ops['id'];
}

/**
 * add contact details to a contact
 *
 * @param array $data
 * @return int
 */
function mf_contacts_add_details($data) {
	$values = [];
	$values['action'] = 'insert';
	$values['ids'] = ['contact_id', 'provider_category_id'];
	$values['POST']['contact_id'] = $data['contact_id'];
	$values['POST']['identification'] = $data['identification'];
	$values['POST']['provider_category_id'] = $data['provider_category_id'] ?? wrap_category_id('provider/'.$data['provider_category']);
	$ops = zzform_multi('contactdetails', $values);
	if (!$ops['id']) {
		wrap_error(wrap_text('Unable to add %s address %s to contact ID %d, Reason: %s'
			, ['values' => [$path, $data['e_mail'], $data['contact_id'], json_encode($ops['error'])]]
		));
		return 0;
	}
	return $ops['id'];
}
