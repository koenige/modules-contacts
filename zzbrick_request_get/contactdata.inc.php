<?php 

/**
 * news module
 * get contact data per ID
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/news
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get contact data per ID, pre-sorted
 * existing data is appended to contact data
 *
 * @param array $data
 * @param array $settings (optional)
 * @param string $id_field_name (optional, if key does not equal event_id)
 * @param string $lang_field_name (optional, if not current language shall be used)
 * @return array
 */
function mod_contacts_get_contactdata($data, $settings = [], $id_field_name = '', $lang_field_name = '') {
	if (!$data) return $data;
	require_once wrap_setting('core').'/data.inc.php';

	$ids = wrap_data_ids($data, $id_field_name);
	$langs = wrap_data_langs($data, $lang_field_name);

	$sql = 'SELECT contact_id, contact, contact_short, contact_abbr,
			contacts.identifier, contacts.description, remarks
			, SUBSTRING_INDEX(path, "/", -1) AS scope
			, categories.parameters
			, IF (ISNULL(date_end), 1, NULL) AS alive
			, start_date, end_date
			, IFNULL(
				TIMESTAMPDIFF(YEAR, start_date, IFNULL(CAST(IF(
					SUBSTRING(end_date, -6) = "-00-00",
					CONCAT(YEAR(end_date), "-01-01"), end_date) AS DATE
				), CURDATE())),
				YEAR(IFNULL(end_date, CURDATE())) - YEAR(start_date)
			) AS age
			, category_id, category
			, country_id, country
			%s
	    FROM contacts
	    LEFT JOIN countries USING (country_id)
	    LEFT JOIN categories
	    	ON contacts.contact_category_id = categories.category_id
	    WHERE contacts.contact_id IN (%s)
	    ORDER BY FIELD(contacts.contact_id, %s)';
	$sql = sprintf($sql
		, !empty($settings['extra_fields']) ? ','.implode(',', $settings['extra_fields']) : ''
		, implode(',', $ids)
		, implode(',', $ids)
	);
	$contactdata = wrap_db_fetch($sql, 'contact_id');
	foreach ($contactdata as $contact_id => $contact) {
		if (!$contact['parameters']) continue;
		parse_str($contact['parameters'], $contactdata[$contact_id]['parameters']);
	}

	foreach ($langs as $lang) {
		$contacts[$lang] = wrap_translate($contactdata, 'contacts', '', true, $lang);
		$contacts[$lang] = wrap_translate($contacts[$lang], 'categories', 'category_id', true, $lang);
		$contacts[$lang] = wrap_translate($contacts[$lang], 'countries', 'country_id', true, $lang);
		foreach (array_keys($contacts[$lang]) as $contact_id) {
			$contacts[$lang][$contact_id][$lang] = true;
		}
	}

	// media
	$contacts = wrap_data_media($contacts, $ids, $langs, 'contacts', 'contact');

	// contact details
	// @todo translations (categories)
	$contactdetails[wrap_setting('lang')] = mf_contacts_contactdetails($ids);

	// addresses
	// @todo translations
	$addresses = mf_contacts_addresses($ids);
	foreach ($addresses as $contact_id => $contactaddresses)
		$data[$contact_id]['addresses'] = $contactaddresses;

	// contacts_identifiers
	$identifiers[wrap_setting('lang')] = mf_contacts_identifiers($ids);

	$data = wrap_data_merge($data, $contacts, $id_field_name, $lang_field_name);
	$data = wrap_data_merge($data, $contactdetails, $id_field_name, $lang_field_name);
	$data = wrap_data_merge($data, $identifiers, $id_field_name, $lang_field_name);
	
	foreach ($data as $contact_id => $line)
		$data[$contact_id]['profiles'] = wrap_profiles($line);
	
	return $data;
}	

/**
 * get identifiers per contact
 *
 * @param array $ids
 * @return array
 */
function mf_contacts_identifiers($ids) {
	$sql = 'SELECT contact_id, contact_identifier_id, identifier
			, IF(current = "yes", 1, NULL) AS current
			, category_id, category
		FROM contacts_identifiers
		LEFT JOIN categories
			ON contacts_identifiers.identifier_category_id = categories.category_id
		WHERE contact_id IN (%s)
		ORDER BY categories.sequence, categories.path, contacts_identifiers.identifier';
	$sql = sprintf($sql, implode(',', $ids));
	$identifiers = wrap_db_fetch($sql, 'contact_identifier_id');
	$identifiers = wrap_translate($identifiers, 'categories', 'category_id');
	
	$data = [];
	foreach ($identifiers as $contact_identifier_id => $identifier)
		$data[$identifier['contact_id']]['identifiers'][$contact_identifier_id] = $identifier;
	return $data;
}
