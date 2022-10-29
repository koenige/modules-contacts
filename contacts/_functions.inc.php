<?php 

/**
 * contacts module
 * common functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015, 2018, 2021-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mf_contacts_random_hash($fields) {
	if (!empty($fields['verification_hash'])) return $fields['verification_hash'];
	$duplicate = true;
	while ($duplicate) {
		$hash = wrap_random_hash(8);
		$sql = 'SELECT contact_id FROM /*_PREFIX_*/contacts_verifications
			WHERE verification_hash = "%s"';
		$sql = sprintf($sql, $hash);
		$duplicate = wrap_db_fetch($sql, '', 'single value');
	}
	return $hash;
}

/**
 * read all contactdetails for a contact from database
 *
 * @param mixed $contactdetails (int or array)
 * @param string $restrict_to (optional, restrict to parameter=1)
 * @return array
 */
function mf_contacts_contactdetails($contact_ids, $restrict_to = false) {
	if (!$contact_ids) return [];
	$ids = !is_array($contact_ids) ? [$contact_ids] : $contact_ids;
	$sql = 'SELECT contact_id, contactdetail_id, identification, contact
			, categories.parameters, category, category_short, label
			, category_id
		FROM contactdetails
		LEFT JOIN contacts USING (contact_id)
		LEFT JOIN categories
			ON categories.category_id = contactdetails.provider_category_id
		WHERE contact_id IN (%s)
		ORDER BY categories.sequence, identification
	';
	$sql = sprintf($sql, implode(',', $ids));
	$details = wrap_db_fetch($sql, ['contact_id', 'contactdetail_id']);
	$data = [];
	foreach ($details as $contact_id => $contactdetails) {
		foreach ($contactdetails as $id => $detail) {
			if ($detail['parameters'])
				parse_str($detail['parameters'], $parameters);
			else {
				$parameters = [];
				$parameters['type'] = '';
			}
			if ($restrict_to AND empty($parameters[$restrict_to])) continue;
			if (!empty($parameters['if'][$restrict_to]['title'])) {
				$detail['category'] = $parameters['if'][$restrict_to]['title'];
			}
			switch ($parameters['type']) {
			case 'mail':
				$detail['mailto'] = wrap_mailto($detail['contact'], $detail['identification']);
				break;
			case 'username':
				if (!empty($parameters['url']))
					$detail['username_url'] = sprintf($parameters['url'], $detail['identification']);
				break;
			}
			$data[$contact_id][$parameters['type']][] = $detail;
			
		}
	}
	if (is_array($contact_ids)) return $data;
	$data = reset($data);
	if (!$data) return [];
	return $data;
}

/**
 * read all addresses for a contact from database
 *
 * @param mixed $contactdetails (int or array)
 * @param string $restrict_to (optional, restrict to parameter=1)
 * @return array
 */
function mf_contacts_addresses($contact_ids, $restrict_to = false) {
	if (!$contact_ids) return [];
	$ids = !is_array($contact_ids) ? [$contact_ids] : $contact_ids;
	$sql = 'SELECT address_id, address, postcode, place
			, country_id, country
			, latitude, longitude
			, category_id, category
			, contact_id
			, IF(receive_mail = "yes", 1, NULL) AS receive_mail
			, parameters
		FROM /*_PREFIX_*/addresses
		LEFT JOIN /*_PREFIX_*/countries USING (country_id)
		LEFT JOIN /*_PREFIX_*/categories
			ON /*_PREFIX_*/categories.category_id = /*_PREFIX_*/addresses.address_category_id
		WHERE contact_id IN (%d)
		ORDER BY contact_id, categories.sequence, postcode, address';
	$sql = sprintf($sql, implode(',', $ids));
	$addresses = wrap_db_fetch($sql, 'address_id');
	$addresses = wrap_translate($addresses, 'countries', 'country_id');
	$addresses = wrap_translate($addresses, 'categories', 'category_id');
	$data = [];
	foreach ($addresses as $address_id => $address) {
		if ($address['parameters'])
			parse_str($address['parameters'], $parameters);
		else
			$parameters = [];
		if ($restrict_to AND empty($parameters[$restrict_to])) continue;
		if (!empty($parameters['if'][$restrict_to]['title'])) {
			$detail['category'] = $parameters['if'][$restrict_to]['title'];
		}
		$data[$address['contact_id']][$address['address_id']] = $address;
		if (count($addresses) === 1)
			$data[$address['contact_id']][$address['address_id']]['receive_mail'] = false;
	}
	if (is_array($contact_ids)) return $data;
	$data = reset($data);
	if (!$data) return [];
	return $data;
}

/**
 * get path to profile for a person
 *
 * @param array $values
 *		string identifier (or whatever it is called, first parameter)
 *		string contact_parameters
 * @return string
 */
function mf_contacts_profile_path($values) {
	global $zz_setting;
	if (!wrap_access('contacts_profile')) return false;
	if (empty($values['contact_parameters'])) return false;
	parse_str($values['contact_parameters'], $params);
	if (empty($params['type'])) $params['type'] = '*';
	if (empty($zz_setting['contacts_profile_path'][$params['type']])) {
		$success = wrap_setting_path(
			'contacts_profile_path['.$params['type'].']'
			, 'request contact'
			, ['scope' => $params['type']]
		);
		if (!$success) return false;
	}
	return sprintf($zz_setting['base'].$zz_setting['contacts_profile_path'][$params['type']], reset($values));
}
