<?php 

/**
 * contacts module
 * common functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015, 2018, 2021-2024, 2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * read all contactdetails for a contact from database
 *
 * @param mixed $contact_ids (int or array)
 * @param array|string|false $settings (optional) settings array, or deprecated string
 *		restrict_to context (e.g. 'places'); array keys:
 *		- restrict_to: restrict to category parameter=1
 *		- hidden: false to omit categories with hidden=1 (opt-in; default shows all)
 * @return array
 */
function mf_contacts_contactdetails($contact_ids, $settings = false) {
	if (!$contact_ids) return [];
	$settings = mf_contacts_detail_settings_normalize($settings);
	$ids = !is_array($contact_ids) ? [$contact_ids] : $contact_ids;
	$sql = 'SELECT contact_id, contactdetail_id, identification, contact
			, categories.parameters, category, category_short, label, link
			, category_id
		FROM contactdetails
		LEFT JOIN contacts USING (contact_id)
		LEFT JOIN categories
			ON categories.category_id = contactdetails.channel_category_id
		WHERE contact_id IN (%s)
		ORDER BY categories.sequence, identification
	';
	$sql = sprintf($sql, implode(',', $ids));
	$details = wrap_db_fetch($sql, ['contact_id', 'contactdetail_id']);
	$data = [];
	$last_category = false;
	foreach ($details as $contact_id => $contactdetails) {
		foreach ($contactdetails as $id => $detail) {
			if ($detail['parameters'])
				parse_str($detail['parameters'], $detail['parameters']);
			else
				$detail['parameters'] = ['type' => ''];
			if (!mf_contacts_detail_settings_match($detail['parameters'], $settings)) continue;
			if (!empty($settings['restrict_to'])
				AND !empty($detail['parameters']['if'][$settings['restrict_to']]['title'])) {
				$detail['category'] = $detail['parameters']['if'][$settings['restrict_to']]['title'];
			}
			switch ($detail['parameters']['type']) {
			case 'mail':
				$detail['mailto'] = wrap_mailto($detail['contact'], $detail['identification']);
				break;
			case 'username':
				if (!empty($detail['link']))
					$detail['username_url'] = $detail['link'];
				elseif (!empty($detail['parameters']['url']))
					$detail['username_url'] = sprintf($detail['parameters']['url'], $detail['identification']);
				break;
			}
			if ($last_category === $detail['category'])
				$detail['same_category'] = true;
			$last_category = $detail['category'];
			$data[$contact_id][$detail['parameters']['type']][] = $detail;
			
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
 * @param mixed $contact_ids (int or array)
 * @param array|string|false $settings (optional) settings array, or deprecated string
 *		restrict_to context; see mf_contacts_contactdetails()
 * @return array
 */
function mf_contacts_addresses($contact_ids, $settings = false) {
	if (!$contact_ids) return [];
	$settings = mf_contacts_detail_settings_normalize($settings);
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
		WHERE contact_id IN (%s)
		ORDER BY contact_id, categories.sequence, postcode, address';
	$sql = sprintf($sql, implode(',', $ids));
	$addresses = wrap_db_fetch($sql, 'address_id');
	$addresses = wrap_translate($addresses, 'countries', 'country_id');
	$addresses = wrap_translate($addresses, 'categories', 'category_id');
	$data = [];
	foreach ($addresses as $address_id => $address) {
		if ($address['parameters'])
			parse_str($address['parameters'], $address['parameters']);
		else
			$address['parameters'] = [];
		if (!mf_contacts_detail_settings_match($address['parameters'], $settings)) continue;
		if (!empty($settings['restrict_to'])
			AND !empty($address['parameters']['if'][$settings['restrict_to']]['title'])) {
			$address['category'] = $address['parameters']['if'][$settings['restrict_to']]['title'];
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
 * normalize settings for mf_contacts_contactdetails() / mf_contacts_addresses()
 *
 * @param array|string|false $settings
 * @return array
 */
function mf_contacts_detail_settings_normalize($settings) {
	if (!$settings) return [];
	if (is_string($settings)) return ['restrict_to' => $settings];
	if (!is_array($settings)) return [];
	return $settings;
}

/**
 * whether a category parameters array passes detail/address settings filters
 *
 * @param array $parameters parsed category parameters
 * @param array $settings normalized settings
 * @return bool
 */
function mf_contacts_detail_settings_match($parameters, $settings) {
	if (!$settings) return true;
	if (!empty($settings['restrict_to'])) {
		if (empty($parameters[$settings['restrict_to']])) return false;
	}
	foreach ($settings as $key => $value) {
		if ($key === 'restrict_to') continue;
		if ($value === false) {
			if (!empty($parameters[$key])) return false;
			continue;
		}
		if ($value === true) {
			if (empty($parameters[$key])) return false;
			continue;
		}
		if (($parameters[$key] ?? null) != $value) return false;
	}
	return true;
}
