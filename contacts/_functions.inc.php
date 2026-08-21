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


wrap_include('context', 'default');


/**
 * read all contactdetails for a contact from database
 *
 * @param mixed $contact_ids (int or array)
 * @param array|string|false $settings (optional) settings array, or context string
 *		array keys:
 *		- context: form context
 *		- published: 1 to return only published channel rows (default: all rows)
 * @return array
 */
function mf_contacts_contactdetails($contact_ids, $settings = false) {
	if (!$contact_ids) return [];
	$settings = mf_contacts_detail_settings_normalize($settings);
	$ids = !is_array($contact_ids) ? [$contact_ids] : $contact_ids;
	$sql = 'SELECT contact_id, contactdetail_id, identification, contact
			, categories.parameters, category, category_short, label, link
			, category_id
			, IF(categories.published = "yes", 1, NULL) AS published
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
				$detail['parameters'] = ['zzform' => ['type' => '']];
			if (!empty($settings['context'])
				AND !mf_default_category_context($detail['parameters'], $settings['context'])) continue;
			if (!empty($settings['context'])) {
				$parameters = mf_default_apply_context_if($detail['parameters'], $settings['context']);
				if (!empty($parameters['title']))
					$detail['category'] = $parameters['title'];
			}
			switch ($detail['parameters']['zzform']['type']) {
			case 'mail':
				$detail['mailto'] = wrap_mailto($detail['contact'], $detail['identification']);
				break;
			case 'username':
				if (!empty($detail['link']))
					$detail['username_url'] = $detail['link'];
				elseif (!empty($detail['parameters']['zzform']['url']))
					$detail['username_url'] = sprintf($detail['parameters']['zzform']['url'], $detail['identification']);
				break;
			}
			if ($last_category === $detail['category'])
				$detail['same_category'] = true;
			$last_category = $detail['category'];
			$data[$contact_id][$detail['parameters']['zzform']['type']][] = $detail;
			
		}
	}
	if (!empty($settings['published']))
		$data = mf_contacts_filter_published($data);
	if (is_array($contact_ids)) return $data;
	$data = reset($data);
	if (!$data) return [];
	return $data;
}

/**
 * read all addresses for a contact from database
 *
 * @param mixed $contact_ids (int or array)
 * @param array|string|false $settings (optional) settings array, or context string;
 *		see mf_contacts_contactdetails()
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
			, IF(categories.published = "yes", 1, NULL) AS published
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
		if (!empty($settings['context'])
			AND !mf_default_category_context($address['parameters'], $settings['context'])) continue;
		if (!empty($settings['context'])) {
			$parameters = mf_default_apply_context_if($address['parameters'], $settings['context']);
			if (!empty($parameters['title']))
				$address['category'] = $parameters['title'];
		}
		$data[$address['contact_id']][$address['address_id']] = $address;
		if (count($addresses) === 1)
			$data[$address['contact_id']][$address['address_id']]['receive_mail'] = false;
	}
	if (!empty($settings['published']))
		$data = mf_contacts_filter_published($data);
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
	if (is_string($settings)) return ['context' => $settings];
	if (!is_array($settings)) return [];
	return $settings;
}

/**
 * drop rows without published = 1 from nested contact data
 *
 * @param array $data
 * @return array
 */
function mf_contacts_filter_published($data) {
	if (!$data) return $data;
	foreach ($data as $key => $value) {
		if (!is_array($value)) continue;
		if (array_key_exists('published', $value)) {
			if (empty($value['published'])) unset($data[$key]);
			continue;
		}
		$data[$key] = mf_contacts_filter_published($value);
		if (!$data[$key]) unset($data[$key]);
	}
	return $data;
}
