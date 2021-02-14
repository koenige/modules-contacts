<?php 

/**
 * Zugzwang Project
 * Common functions for contacts module
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015, 2018, 2021 Gustaf Mossakowski
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
 * @return array
 */
function mf_contacts_contactdetails($contact_ids) {
	if (!$contact_ids) return [];
	$ids = !is_array($contact_ids) ? [$contact_ids] : $contact_ids;
	$sql = 'SELECT contact_id, contactdetail_id, identification, contact
			, categories.parameters, category, label
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
			parse_str($detail['parameters'], $type);
			if ($type['type'] === 'mail') {
				$detail['mailto'] = wrap_mailto($detail['contact'], $detail['identification']);
			}
			$data[$contact_id][$type['type']][] = $detail;
			
		}
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
 *		string identifier
 *		string contact_parameters
 * @return string
 */
function mf_contacts_person_path($values) {
	global $zz_setting;
	parse_str($values['contact_parameters'], $params);
	if (empty($params['type'])) return '';
	if (empty($zz_setting['contacts_profile_path'][$params['type']])) {
		switch ($params['type']) {
		case 'person':
			$sql = 'SELECT CONCAT(identifier, IF(ending = "none", "", ending)) AS path
				FROM webpages
				WHERE content LIKE "%%%% request contact * scope=person %%%%"';
			$path = wrap_db_fetch($sql, '', 'single value');
			if (!$path) {
				$sql = 'SELECT CONCAT(identifier, IF(ending = "none", "", ending)) AS path
					FROM webpages
					WHERE content LIKE "%%%% request contact * %%%%"';
				$path = wrap_db_fetch($sql, '', 'single value');
				if (!$path) return false;
			}
			$path = str_replace('*', '/%s', $path);
			break;
		default:
			return '';
		}
		wrap_setting_write('contacts_profile_path['.$params['type'].']', $path);
	}
	return sprintf($zz_setting['contacts_profile_path'][$params['type']], $values['identifier']);
}
